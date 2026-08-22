<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Schemas;

use Carbon\CarbonImmutable;
use Closure;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use He4rt\Marketing\ShortLink\Enums\ShortLinkStatus;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use He4rt\Marketing\ShortLink\Models\ShortLinkDestination;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages\ViewShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets\ClicksOverTimeChart;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets\DeviceBreakdownChart;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets\RecentClicksTable;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets\TopReferersTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Number;

/**
 * O layout 80/20 da página de um link curto.
 *
 * `Grid::make(4)` com filhos de span 3 e 1 dá 75% / 25% e empilha
 * sozinho em tela estreita — nada de breakpoint na mão.
 *
 * Os números são `TextEntry` e não um `StatsOverviewWidget` de propósito: o
 * infolist é schema da própria página, então uma closure em `state()` que lê
 * `ViewShortLink::includeBots()` reage ao toggle sem remontar nada. Gráficos e
 * tabelas, que são ilhas Livewire isoladas, dependem da `key()` dinâmica.
 */
class ShortLinkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        Group::make(self::analytics())->columnSpan(3),
                        Group::make(self::sidebar())->columnSpan(1),
                    ]),
            ]);
    }

    /**
     * A coluna de 80%: o que o link é, seguido do que ele produziu.
     *
     * @return array<int, mixed>
     */
    private static function analytics(): array
    {
        return [
            Section::make(__('panel-admin::marketing.short_links.sections.about'))
                ->columnSpanFull()
                ->columns(4)
                ->schema([
                    TextEntry::make('status')
                        ->label(__('panel-admin::marketing.short_links.fields.status'))
                        // `status` é accessor derivado, nunca coluna: nada de
                        // sortable/searchable. O enum implementa HasLabel/HasColor/
                        // HasIcon, então `badge()` sozinho já pinta certo.
                        ->badge()
                        ->state(fn (ShortLink $record): ShortLinkStatus => $record->status),

                    TextEntry::make('creator.username')
                        ->label(__('panel-admin::marketing.short_links.fields.created_by'))
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                    TextEntry::make('created_at')
                        ->label(__('panel-admin::marketing.short_links.fields.created_at'))
                        ->dateTime('d/m/Y H:i')
                        ->timezone(config('app.display_timezone')),

                    TextEntry::make('expires_at')
                        ->label(__('panel-admin::marketing.short_links.fields.expires_at'))
                        ->dateTime('d/m/Y H:i')
                        ->timezone(config('app.display_timezone'))
                        ->placeholder(__('panel-admin::marketing.short_links.stats.never_expires')),

                    TextEntry::make('tags')
                        ->label(__('panel-admin::marketing.short_links.fields.tags'))
                        ->badge()
                        // `tags` é cast para TagList (VO), não array: sem o
                        // `toArray()` o badge recebe o objeto e quebra.
                        ->state(fn (ShortLink $record): array => $record->tags->toArray())
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none'))
                        ->columnSpanFull(),
                ]),

            Section::make(__('panel-admin::marketing.short_links.sections.utm'))
                ->description(__('panel-admin::marketing.short_links.helpers.utm'))
                ->columnSpanFull()
                ->columns(1)
                ->compact()
                ->collapsed()
                ->schema([
                    TextEntry::make('utm.source')
                        ->label(__('panel-admin::marketing.short_links.fields.utm_source'))
                        ->state(fn (ShortLink $record): ?string => $record->utm->source)
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                    TextEntry::make('utm.medium')
                        ->label(__('panel-admin::marketing.short_links.fields.utm_medium'))
                        ->state(fn (ShortLink $record): ?string => $record->utm->medium)
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                    TextEntry::make('utm.campaign')
                        ->label(__('panel-admin::marketing.short_links.fields.utm_campaign'))
                        ->state(fn (ShortLink $record): ?string => $record->utm->campaign)
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                    TextEntry::make('utm.term')
                        ->label(__('panel-admin::marketing.short_links.fields.utm_term'))
                        ->state(fn (ShortLink $record): ?string => $record->utm->term)
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                    TextEntry::make('utm.content')
                        ->label(__('panel-admin::marketing.short_links.fields.utm_content'))
                        ->state(fn (ShortLink $record): ?string => $record->utm->content)
                        ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),
                ]),

            Section::make(__('panel-admin::marketing.short_links.sections.numbers'))
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextEntry::make('clicks_total')
                        ->label(__('panel-admin::marketing.short_links.stats.clicks'))
                        ->state(fn (ShortLink $record, ViewShortLink $livewire): string => self::number(
                            $livewire->includeBots() ? $record->clicks_count : $record->human_clicks_count,
                        ))
                        ->size(TextSize::Large)
                        ->weight(FontWeight::Bold)
                        ->helperText(fn (ViewShortLink $livewire): string => $livewire->includeBots()
                            ? __('panel-admin::marketing.short_links.stats.including_bots')
                            : __('panel-admin::marketing.short_links.stats.humans_only')),

                    TextEntry::make('peak')
                        ->label(__('panel-admin::marketing.short_links.stats.peak'))
                        ->state(fn (ShortLink $record, ViewShortLink $livewire): string => self::peak($record, $livewire->includeBots())['value'])
                        ->size(TextSize::Large)
                        ->weight(FontWeight::Bold)
                        ->helperText(fn (ShortLink $record, ViewShortLink $livewire): string => self::peak($record, $livewire->includeBots())['helper']),

                    TextEntry::make('top_source')
                        ->label(__('panel-admin::marketing.short_links.stats.top_source'))
                        ->state(fn (ShortLink $record, ViewShortLink $livewire): string => self::topSource($record, $livewire->includeBots())['value'])
                        ->size(TextSize::Large)
                        ->weight(FontWeight::Bold)
                        ->helperText(fn (ShortLink $record, ViewShortLink $livewire): string => self::topSource($record, $livewire->includeBots())['helper']),
                ]),

            // Sem `Section` em volta: cada widget já renderiza o próprio card com
            // heading (`ChartWidget` monta um `<x-filament::section>`), e embrulhar
            // de novo daria moldura dentro de moldura.
            Livewire::make(ClicksOverTimeChart::class, self::islandData())
                ->key(fn (ViewShortLink $livewire): string => $livewire->islandKey('clicks-over-time'))
                ->columnSpanFull(),

            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    // Este widget já tem filtro de dimensão próprio (referer /
                    // utm_source / país) — por isso são duas ilhas aqui, não três.
                    Livewire::make(TopReferersTable::class, self::islandData())
                        ->key(fn (ViewShortLink $livewire): string => $livewire->islandKey('top-referers'))
                        ->columnSpan(1),

                    Livewire::make(DeviceBreakdownChart::class, self::islandData())
                        ->key(fn (ViewShortLink $livewire): string => $livewire->islandKey('device-breakdown'))
                        ->columnSpan(1),
                ]),

            Livewire::make(RecentClicksTable::class, self::islandData())
                ->key(fn (ViewShortLink $livewire): string => $livewire->islandKey('recent-clicks'))
                ->columnSpanFull(),
        ];
    }

    /**
     * A faixa de 20%: quem manda no filtro e para onde o link já apontou.
     *
     * @return array<int, mixed>
     */
    private static function sidebar(): array
    {
        return [
            Section::make(__('panel-admin::marketing.short_links.sections.filter'))
                ->columnSpanFull()
                ->compact()
                ->schema([
                    // O MESMO schema `filtersForm` da página, só que aqui. Duplicar
                    // o Toggle criaria um segundo state path e dois filtros rivais.
                    EmbeddedSchema::make('filtersForm'),
                ]),

            // O histórico é uma das três razões do projeto existir: sem ele um
            // gráfico de cliques mente, porque metade pode ter ido pro destino antigo.
            Section::make(__('panel-admin::marketing.short_links.sections.destination_history'))
                ->description(__('panel-admin::marketing.short_links.sections.destination_history_hint'))
                ->columnSpanFull()
                ->compact()
                ->schema([
                    RepeatableEntry::make('destinations')
                        ->hiddenLabel()
                        // Sem moldura por item: em 20% de largura ela come quase
                        // toda a largura útil.
                        ->contained(condition: false)
                        ->state(fn (ShortLink $record) => $record->destinations()
                            ->orderByDesc('valid_from')
                            ->get())
                        ->schema([
                            TextEntry::make('valid_from')
                                ->hiddenLabel()
                                ->size(TextSize::Small)
                                ->color('gray')
                                ->formatStateUsing(self::validity(...)),

                            TextEntry::make('destination_url')
                                ->hiddenLabel()
                                ->fontFamily(FontFamily::Mono)
                                ->size(TextSize::Small)
                                // Sem o limit a URL parte no meio da palavra
                                // ("he4 / rt"); o tooltip devolve o valor inteiro.
                                ->limit(30)
                                ->tooltip(fn (?string $state): ?string => $state),
                        ]),
                ]),
        ];
    }

    /**
     * Os parâmetros de mount de uma ilha. `record` o próprio componente já passa;
     * `includeBots` é o filtro da página congelado no instante do mount — quem o
     * atualiza é a troca de `key()`, não reatividade.
     *
     * @return Closure(ViewShortLink): array<string, mixed>
     */
    private static function islandData(): Closure
    {
        return static fn (ViewShortLink $livewire): array => [
            'includeBots' => $livewire->includeBots(),
        ];
    }

    /**
     * "Vigente desde 12/03/2026" para o intervalo aberto, "12/03 → 04/07" para
     * os fechados.
     */
    private static function validity(ShortLinkDestination $record): string
    {
        $timezone = config('app.display_timezone');
        $from = $record->valid_from->timezone($timezone)->format('d/m/Y');

        if ($record->isCurrent()) {
            return __('panel-admin::marketing.short_links.fields.valid_since', ['date' => $from]);
        }

        return $from.' → '.$record->valid_until?->timezone($timezone)->format('d/m/Y');
    }

    /**
     * O dia de maior volume, numa consulta só.
     *
     * `AT TIME ZONE` UMA vez: a conversão dupla desloca o dia em +3h.
     *
     * @return array{value: string, helper: string}
     */
    private static function peak(ShortLink $record, bool $includeBots): array
    {
        /** @var object{day: string, total: int}|null $busiest */
        $busiest = self::clicksQuery($record, $includeBots)
            ->selectRaw('(clicked_at AT TIME ZONE ?)::date AS day', [config('app.display_timezone')])
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('day')
            ->orderByDesc('total')
            ->first();

        if ($busiest === null) {
            return [
                'value' => self::number(0),
                'helper' => __('panel-admin::marketing.short_links.stats.no_clicks_yet'),
            ];
        }

        return [
            'value' => self::number((int) $busiest->total),
            'helper' => CarbonImmutable::parse($busiest->day)->format('d/m/Y'),
        ];
    }

    /**
     * A origem que mais trouxe clique, com a fatia que ela representa.
     *
     * @return array{value: string, helper: string}
     */
    private static function topSource(ShortLink $record, bool $includeBots): array
    {
        $total = self::clicksQuery($record, $includeBots)->count();

        if ($total === 0) {
            return [
                'value' => __('panel-admin::marketing.short_links.placeholders.none'),
                'helper' => __('panel-admin::marketing.short_links.stats.no_clicks_yet'),
            ];
        }

        /** @var object{bucket: string, total: int}|null $top */
        $top = self::clicksQuery($record, $includeBots)
            ->selectRaw("COALESCE(NULLIF(referer, ''), ?) AS bucket", [
                __('panel-admin::marketing.short_links.placeholders.no_referer'),
            ])
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('bucket')
            ->orderByDesc('total')
            ->first();

        if ($top === null) {
            return [
                'value' => __('panel-admin::marketing.short_links.placeholders.none'),
                'helper' => __('panel-admin::marketing.short_links.stats.no_clicks_yet'),
            ];
        }

        $share = Number::percentage((int) $top->total / $total * 100, maxPrecision: 1);

        return [
            'value' => (string) $top->bucket,
            'helper' => __('panel-admin::marketing.short_links.stats.share', [
                'clicks' => self::number((int) $top->total),
                'share' => is_string($share) ? $share : '',
            ]),
        ];
    }

    /**
     * Agregados vão por `DB::table()` (via query builder do model) porque não têm
     * chave primária — hidratar model para um `GROUP BY` seria mentira.
     */
    private static function clicksQuery(ShortLink $record, bool $includeBots): QueryBuilder
    {
        return ShortLinkClick::query()
            ->where('short_link_id', $record->getKey())
            ->unless($includeBots, fn (Builder $query): Builder => $query->where('is_bot', operator: false))
            ->toBase();
    }

    private static function number(int $value): string
    {
        $formatted = Number::format($value);

        return is_string($formatted) ? $formatted : (string) $value;
    }
}
