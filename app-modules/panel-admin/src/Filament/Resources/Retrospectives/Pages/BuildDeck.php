<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Retrospectives\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Community\Retrospective\Contracts\CuratableSource;
use He4rt\Community\Retrospective\Enums\ExclusionKind;
use He4rt\Community\Retrospective\Models\Retrospective;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Actions\PublishRetrospectiveAction;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\RetrospectiveResource;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\AvailableSources;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\DeckStructure;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\ExclusionPicker;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorSelection;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\SlideEntry;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\SourceBlock;

/**
 * Deck Builder: monta o deck vendo o deck (ADR-0002 do panel-admin). Três colunas
 * — estrutura seleciona, preview só lê, inspector edita — e o inspector escreve
 * exatamente onde o CRUD da Fase 2 escrevia, sem coluna nova.
 *
 * Registrada na chave `edit` do resource com rota `/{record}/deck`: a chave
 * preserva o clique na tabela e o getUrl('edit'), a rota deixa a URL honesta.
 * Não existe uma segunda tela editando `deck_config` — seriam duas fontes de
 * verdade de curadoria.
 *
 * @property-read Schema $form
 */
class BuildDeck extends Page
{
    use InteractsWithRecord;

    /**
     * Token da seleção, tal como vem da wire. `selection()` o reparsa a cada
     * leitura, então um valor inválido degrada para a capa.
     */
    public string $selection = 'cover';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Contador de recargas do iframe. O `updated_at` sozinho não basta: dois
     * salvamentos no mesmo segundo dariam o mesmo token e o preview não recarregaria.
     */
    public int $previewVersion = 0;

    protected static string $resource = RetrospectiveResource::class;

    protected string $view = 'panel-admin::retrospective.build-deck';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->fillInspector();
    }

    public function getTitle(): string
    {
        return $this->getRetrospective()->title;
    }

    public function getSubheading(): string
    {
        return 'Monte o deck vendo o deck. Ordem e on/off re-derivam do snapshot; exclusion exige republicar.';
    }

    /**
     * Seleciona um alvo na coluna de estrutura e recarrega o inspector no modo
     * correspondente.
     */
    public function select(string $selection): void
    {
        $this->selection = InspectorSelection::parse($selection)->token();

        $this->fillInspector();
    }

    /**
     * Reordena por botão (subir/descer). Persiste a ordem INTEIRA da timeline, não
     * só o par trocado — é o que ancora as fontes que ainda não estavam em `order`.
     */
    public function moveSource(string $key, int $offset): void
    {
        $record = $this->getRetrospective();

        $record->update([
            'deck_config' => $record->deck_config->withOrder(
                DeckStructure::moved($this->blocks(), $key, $offset),
            ),
        ]);

        $this->refreshPreview();
    }

    public function save(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        $record = $this->getRetrospective();
        $selection = $this->selection();

        match ($selection->mode) {
            InspectorMode::Cover => $this->saveCover($record, $data),
            InspectorMode::Closing => $this->saveClosing($record, $data),
            InspectorMode::Source => $this->saveSource($record, $selection->requireTarget(), $data),
            InspectorMode::Slide => $this->saveSlide($record, $selection->requireTarget(), $data),
        };

        $this->refreshPreview();

        Notification::make()
            ->success()
            ->title('Deck salvo')
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make($this->inspectorComponents())
                    ->livewireSubmitHandler('save')
                    ->footer([
                        SchemaActions::make([
                            Action::make('save')
                                ->label('Salvar')
                                ->icon(Heroicon::OutlinedCheck)
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Timeline da coluna de estrutura, recomputada a cada render (só label() e
     * slideCatalog(), ambos sem tocar o banco).
     *
     * @return list<SourceBlock>
     */
    public function blocks(): array
    {
        return DeckStructure::blocks($this->getRetrospective()->deck_config);
    }

    public function selection(): InspectorSelection
    {
        return InspectorSelection::parse($this->selection);
    }

    /**
     * Aponta para a MESMA rota de preview que o operador abria em outra aba, que
     * passa pelo mesmo ComposeDeck da página pública. Preview que mente é pior que
     * preview nenhum, e a garantia de que não mente é ser literalmente a mesma coisa.
     */
    public function previewUrl(): string
    {
        $record = $this->getRetrospective();

        return route('community.retrospective.preview', $record)
            .'?v='.($record->updated_at?->getTimestamp() ?? 0).'-'.$this->previewVersion;
    }

    public function getRetrospective(): Retrospective
    {
        /** @var Retrospective $record */
        $record = $this->getRecord();

        return $record;
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            PublishRetrospectiveAction::make()
                ->record(fn (): Retrospective => $this->getRetrospective()),

            Action::make('preview')
                ->label('Abrir preview')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('community.retrospective.preview', $this->getRetrospective()))
                ->openUrlInNewTab(),

            DeleteAction::make()
                ->record(fn (): Retrospective => $this->getRetrospective())
                ->successRedirectUrl(RetrospectiveResource::getUrl('index')),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private function inspectorComponents(): array
    {
        $selection = $this->selection();

        return match ($selection->mode) {
            InspectorMode::Cover => $this->coverComponents(),
            InspectorMode::Closing => $this->closingComponents(),
            InspectorMode::Source => $this->sourceComponents($selection->requireTarget()),
            InspectorMode::Slide => $this->slideComponents($selection->requireTarget()),
        };
    }

    /**
     * @return array<int, Section>
     */
    private function coverComponents(): array
    {
        return [
            Section::make('Capa')
                ->description('Só texto editorial e recorte; números, avatares e período exibido são computados à parte.')
                ->schema([
                    TextInput::make('title')
                        ->label('Título da edição')
                        ->required()
                        ->maxLength(255),

                    DateTimePicker::make('since')
                        ->label('Início do período')
                        ->seconds(condition: false)
                        ->timezone(config('app.display_timezone'))
                        ->required(),

                    DateTimePicker::make('until')
                        ->label('Fim do período')
                        ->seconds(condition: false)
                        ->timezone(config('app.display_timezone'))
                        ->required(),

                    Toggle::make('hide_bots')
                        ->label('Ocultar bots')
                        ->helperText('Mexe nos números: republique para valer.'),

                    TextInput::make('cover_title')
                        ->label('Título da capa')
                        ->maxLength(255),

                    Textarea::make('cover_intro')
                        ->label('Introdução da capa')
                        ->rows(3),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private function closingComponents(): array
    {
        return [
            Section::make('Fecho')
                ->description('A última palavra do deck.')
                ->schema([
                    Textarea::make('closing_text')
                        ->label('Mensagem de fecho')
                        ->rows(4),
                ]),
        ];
    }

    /**
     * Inspector de um bloco de fonte. O picker só aparece para quem implementa
     * CuratableSource — a fonte crua fica com ordem e on/off, e o deck segue
     * montando (ISP, ADR-0002).
     *
     * @return array<int, Section>
     */
    private function sourceComponents(string $key): array
    {
        $sections = [
            Section::make('Bloco: '.$this->sourceLabel($key))
                ->description('Desligar re-deriva do snapshot na composição, sem republicar.')
                ->schema([
                    Toggle::make('visible')
                        ->label('Exibir no deck'),
                ]),
        ];

        $source = $this->curatableSource($key);

        if (!$source instanceof CuratableSource) {
            return $sections;
        }

        $picker = $this->picker($source);

        $sections[] = Section::make('Exclusions')
            ->description('Esconde um item ou pessoa desta fonte. Mexe no DADO: sai dos slides e também dos números, então exige republicar para valer.')
            ->schema([
                CheckboxList::make('exclusion_items')
                    ->label(ExclusionKind::Item->getLabel().'s escondidos')
                    ->helperText(ExclusionKind::Item->getDescription())
                    ->options($picker->options(ExclusionKind::Item))
                    ->descriptions($picker->descriptions(ExclusionKind::Item))
                    ->searchable()
                    ->bulkToggleable()
                    ->visible($picker->hasOptions(ExclusionKind::Item)),

                CheckboxList::make('exclusion_people')
                    ->label(ExclusionKind::Person->getLabel().'s escondidas')
                    ->helperText(ExclusionKind::Person->getDescription())
                    ->options($picker->options(ExclusionKind::Person))
                    ->descriptions($picker->descriptions(ExclusionKind::Person))
                    ->searchable()
                    ->bulkToggleable()
                    ->visible($picker->hasOptions(ExclusionKind::Person)),
            ]);

        return $sections;
    }

    /**
     * @return array<int, Section>
     */
    private function slideComponents(string $kind): array
    {
        $entry = $this->slideEntry($kind);

        return [
            // O kind pode não estar em catálogo nenhum (token velho vindo da wire);
            // nesse caso o próprio kind serve de rótulo.
            Section::make('Slide: '.($entry->label ?? $kind))
                ->description($entry->hint ?? InspectorMode::Slide->getDescription())
                ->schema([
                    Toggle::make('visible')
                        ->label('Exibir no deck')
                        ->helperText('O toggle vale para o kind inteiro — "'.$kind.'" pode render mais de um slide.'),
                ]),
        ];
    }

    private function fillInspector(): void
    {
        $this->form->fill($this->inspectorState());
    }

    /**
     * @return array<string, mixed>
     */
    private function inspectorState(): array
    {
        $record = $this->getRetrospective();
        $config = $record->deck_config;
        $selection = $this->selection();

        return match ($selection->mode) {
            InspectorMode::Cover => [
                'title' => $record->title,
                'since' => $record->since,
                'until' => $record->until,
                'hide_bots' => $record->hide_bots,
                'cover_title' => $record->cover_title,
                'cover_intro' => $record->cover_intro,
            ],
            InspectorMode::Closing => [
                'closing_text' => $record->closing_text,
            ],
            InspectorMode::Source => [
                'visible' => $config->showsSource($selection->requireTarget()),
                ...$this->exclusionState($selection->requireTarget()),
            ],
            InspectorMode::Slide => [
                'visible' => $config->showsSlide($selection->requireTarget()),
            ],
        };
    }

    /**
     * @return array<string, list<string>>
     */
    private function exclusionState(string $key): array
    {
        $source = $this->curatableSource($key);

        if (!$source instanceof CuratableSource) {
            return [];
        }

        $picker = $this->picker($source);
        $excluded = $this->getRetrospective()->deck_config->exclusionsFor($key);

        return [
            'exclusion_items' => $picker->selected(ExclusionKind::Item, $excluded),
            'exclusion_people' => $picker->selected(ExclusionKind::Person, $excluded),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveCover(Retrospective $record, array $data): void
    {
        $record->update([
            'title' => $data['title'],
            'since' => $data['since'],
            'until' => $data['until'],
            'hide_bots' => (bool) ($data['hide_bots'] ?? false),
            'cover_title' => $data['cover_title'] ?? null,
            'cover_intro' => $data['cover_intro'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveClosing(Retrospective $record, array $data): void
    {
        $record->update(['closing_text' => $data['closing_text'] ?? null]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSource(Retrospective $record, string $key, array $data): void
    {
        $config = $record->deck_config->withSourceVisible($key, (bool) ($data['visible'] ?? true));

        $source = $this->curatableSource($key);

        if ($source instanceof CuratableSource) {
            $before = $config->exclusionsFor($key);
            $config = $config->withExclusionsFor($key, $this->submittedRefs($source, $key, $data));

            if ($config->exclusionsFor($key) !== $before) {
                $this->warnAboutRepublishing();
            }
        }

        $record->update(['deck_config' => $config]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveSlide(Retrospective $record, string $kind, array $data): void
    {
        $record->update([
            'deck_config' => $record->deck_config->withSlideVisible($kind, (bool) ($data['visible'] ?? true)),
        ]);
    }

    /**
     * Os refs marcados nos dois checkbox lists MAIS os que o picker não conseguiu
     * exibir. Sem os órfãos, desmarcar qualquer coisa apagaria por omissão o que
     * ficou fora do teto da varredura.
     *
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    private function submittedRefs(CuratableSource $source, string $key, array $data): array
    {
        $picker = $this->picker($source);

        return [
            ...$this->refList($data['exclusion_items'] ?? []),
            ...$this->refList($data['exclusion_people'] ?? []),
            ...$picker->orphans($this->getRetrospective()->deck_config->exclusionsFor($key)),
        ];
    }

    /**
     * @return list<string>
     */
    private function refList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }

    private function warnAboutRepublishing(): void
    {
        Notification::make()
            ->warning()
            ->title('Exclusion alterada')
            ->body('Exclusion mexe nos números. Republique a edição para recompilar o snapshot.')
            ->persistent()
            ->send();
    }

    /**
     * A fonte, se ela souber se curar. Devolver null é o caminho legítimo da fonte
     * crua, não um erro.
     */
    private function curatableSource(string $key): ?CuratableSource
    {
        $source = AvailableSources::all()[$key] ?? null;

        return $source instanceof CuratableSource ? $source : null;
    }

    private function picker(CuratableSource $source): ExclusionPicker
    {
        return ExclusionPicker::for($source, $this->getRetrospective()->period());
    }

    private function sourceLabel(string $key): string
    {
        return AvailableSources::map()[$key] ?? $key;
    }

    private function slideEntry(string $kind): ?SlideEntry
    {
        foreach ($this->blocks() as $block) {
            foreach ($block->slides as $slide) {
                if ($slide->kind === $kind) {
                    return $slide;
                }
            }
        }

        return null;
    }

    /**
     * Custo aceito do ADR: o iframe recarrega inteiro em vez de atualizar o slide
     * no lugar. Uma coleta ao vivo por salvamento em rascunho, para um operador.
     */
    private function refreshPreview(): void
    {
        $this->getRetrospective()->refresh();

        $this->previewVersion++;
    }
}
