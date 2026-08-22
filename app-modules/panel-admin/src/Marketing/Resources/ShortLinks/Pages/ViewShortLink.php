<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Marketing\ShortLink\Actions\UpdateShortLink;
use He4rt\Marketing\ShortLink\DTOs\ShortLinkChanges;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\ShortLinkResource;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

/**
 * Onde o dado cru vira resposta: a URL curta como título, os números logo abaixo
 * e, na faixa da direita, o filtro que manda em todos eles mais o histórico de
 * para onde o link já apontou.
 *
 * O `HasFiltersForm` mora em `Pages\Dashboard\Concerns` mas não tem nenhum
 * acoplamento com a Dashboard — ele só publica um schema `filtersForm` com
 * `statePath('filters')` e `->live()`.
 *
 * O toggle chega em cada peça por dois caminhos diferentes, e isso é proposital:
 *
 * - Os `TextEntry` de números vivem no infolist, que é schema DESTA página, então
 *   leem `includeBots()` direto e re-renderizam de graça.
 * - Gráficos e tabelas são ilhas `Filament\Schemas\Components\Livewire`, que só
 *   recebem dado serializável no mount. Para elas o filtro entra por parâmetro e
 *   a mudança se propaga trocando a `key()` da ilha (`islandKey()`), o que faz o
 *   Livewire tratar como componente novo e remontar com o valor novo.
 *
 * Custo assumido: as ilhas remontam (flicker curto) em vez de atualizar em
 * diferencial, e o filtro interno de cada widget volta ao padrão. É o preço do
 * layout 80/20 — widgets de rodapé não conseguem uma faixa lateral de altura
 * inteira, porque fluem linha a linha dentro do grid.
 *
 * @property ShortLink $record
 */
class ViewShortLink extends ViewRecord
{
    use HasFiltersForm;

    public const string INCLUDE_BOTS = 'include_bots';

    protected static string $resource = ShortLinkResource::class;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            /*
             * TODOS os breakpoints, não `->columns(1)`.
             *
             * `HasFiltersForm::getFiltersForm()` monta o schema com
             * `->columns(['md' => 2, 'xl' => 3, '2xl' => 4])` ANTES de chamar este
             * método, e `HasColumns::columns()` MESCLA em vez de substituir —
             * `columns(1)` vira `['lg' => 1]` e os breakpoints maiores sobrevivem.
             * O resultado era o filtro dividido em 4 colunas dentro da faixa
             * lateral, com o rótulo quebrando a cada palavra.
             */
            ->columns(['default' => 1, 'sm' => 1, 'md' => 1, 'lg' => 1, 'xl' => 1, '2xl' => 1])
            ->components([
                Toggle::make(self::INCLUDE_BOTS)
                    ->label(__('panel-admin::marketing.short_links.widgets.include_bots.label'))
                    ->helperText(__('panel-admin::marketing.short_links.widgets.include_bots.helper'))
                    ->default(state: false)
                    ->inline(condition: false),
            ]);
    }

    /** O toggle de bots, lido em um lugar só. */
    public function includeBots(): bool
    {
        return (bool) ($this->filters[self::INCLUDE_BOTS] ?? false);
    }

    /**
     * Sufixo de key que força as ilhas Livewire a remontarem quando o filtro muda.
     *
     * É o mecanismo inteiro: uma key estática aqui e os gráficos param de
     * responder ao toggle sem nada acusar.
     */
    public function islandKey(string $name): string
    {
        return $name.'-'.($this->includeBots() ? 'with-bots' : 'humans');
    }

    /** O link em cima dos números: a URL curta, sem o esquema. */
    public function getTitle(): string
    {
        return Str::after(ShortLinkResource::shortUrl($this->record), '://');
    }

    public function getSubheading(): string
    {
        return '↳ '.$this->record->destination_url;
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('panel-admin::marketing.short_links.actions.edit_destination')),

            // Mesmo handler Alpine que o `TextColumn::copyable()` gera internamente:
            // copiar é client-side puro, um round-trip ao servidor só para isso seria pior.
            Action::make('copy')
                ->label(__('panel-admin::marketing.short_links.actions.copy_url.label'))
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('gray')
                ->actionJs(fn (): string => sprintf(
                    'window.navigator.clipboard.writeText(%s); $tooltip(%s, { theme: $store.theme, timeout: 1500 })',
                    Js::from(ShortLinkResource::shortUrl($this->record)),
                    Js::from(__('panel-admin::marketing.short_links.actions.copy_url.copied')),
                )),

            Action::make('toggleActive')
                ->label(fn (): string => $this->record->active
                    ? __('panel-admin::marketing.short_links.actions.disable.label')
                    : __('panel-admin::marketing.short_links.actions.enable.label'))
                ->icon(fn (): Heroicon => $this->record->active ? Heroicon::OutlinedPause : Heroicon::OutlinedPlay)
                ->color(fn (): string => $this->record->active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (): string => $this->record->active
                    ? __('panel-admin::marketing.short_links.actions.disable.heading')
                    : __('panel-admin::marketing.short_links.actions.enable.heading'))
                ->modalDescription(fn (): string => $this->record->active
                    ? __('panel-admin::marketing.short_links.actions.disable.body')
                    : __('panel-admin::marketing.short_links.actions.enable.body'))
                ->action($this->toggleActive(...)),
        ];
    }

    /**
     * Quem grava é a Action de domínio: ela passa pelo observer que invalida o
     * cache do redirect. Um `$record->update()` daqui deixaria `/l/{slug}`
     * servindo o estado antigo até o TTL vencer.
     */
    private function toggleActive(): void
    {
        $wasActive = $this->record->active;

        resolve(UpdateShortLink::class)->execute(
            $this->record,
            ShortLinkChanges::make(
                active: !$wasActive,
                changedBy: $this->currentUserId(),
            ),
        );

        Notification::make()
            ->success()
            ->title($wasActive
                ? __('panel-admin::marketing.short_links.notifications.disabled.title')
                : __('panel-admin::marketing.short_links.notifications.enabled.title'))
            ->send();
    }

    /**
     * O `id` do User é um UUID (string); `auth()->id()` continua declarado como
     * `int|string|null` por causa das PKs auto-incremento que o contrato ainda
     * admite. Estreitar aqui é o que mantém o DTO honesto.
     */
    private function currentUserId(): ?string
    {
        $id = auth()->id();

        return is_string($id) ? $id : null;
    }
}
