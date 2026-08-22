<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

/**
 * Ranking de origem do clique. A dimensão é escolhida no filtro — referer,
 * `utm_source` ou país — porque as três respondem à mesma pergunta ("de onde
 * veio isso?") e mereciam uma tabela só em vez de três meio vazias.
 *
 * Usa dados customizados (`records()`) em vez de `query()`: são agregados com
 * `GROUP BY`, que não têm chave primária para o Filament diferenciar linhas.
 */
class TopReferersTable extends TableWidget
{
    /** Injetado por `Filament\Schemas\Components\Livewire::getComponentProperties()`. */
    public ?ShortLink $record = null;

    /**
     * Renderizado como ilha Livewire (`Filament\Schemas\Components\Livewire`), que
     * só entrega dado serializável: o filtro da página chega por parâmetro de mount,
     * não por `pageFilters`. Quem troca o valor é a `key()` dinâmica da ilha, que
     * remonta o componente com o novo `includeBots`.
     */
    public bool $includeBots = false;

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            // `TableWidget::makeTable()` deriva o heading do nome da classe quando
            // ninguém o define — sem isto o card diz "Top Referers Table".
            ->heading(__('panel-admin::marketing.short_links.widgets.top_referers.heading'))
            ->records(fn (array $filters): Collection => $this->rows($filters))
            ->columns([
                TextColumn::make('label')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.origin'))
                    ->wrap(),

                TextColumn::make('clicks')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.clicks'))
                    ->numeric()
                    ->alignEnd(),

                TextColumn::make('share')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.share'))
                    ->alignEnd(),
            ])
            ->filters([
                SelectFilter::make('dimension')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.dimension'))
                    ->options([
                        'referer' => __('panel-admin::marketing.short_links.widgets.top_referers.dimensions.referer'),
                        'utm_source' => __('panel-admin::marketing.short_links.widgets.top_referers.dimensions.utm_source'),
                        'country_code' => __('panel-admin::marketing.short_links.widgets.top_referers.dimensions.country_code'),
                    ])
                    ->default('referer')
                    ->selectablePlaceholder(condition: false),
            ])
            /*
             * Inline em vez de Dropdown: com layout Dropdown o Filament reserva
             * uma faixa de toolbar só para o funil e ainda desenha a barra de
             * "filtros ativos" — quatro faixas de moldura para uma linha de dado.
             */
            ->filtersLayout(FiltersLayout::AboveContent)
            ->hiddenFilterIndicators()
            ->paginated(condition: false)
            ->emptyStateIcon(Heroicon::OutlinedCursorArrowRays)
            ->emptyStateHeading(__('panel-admin::marketing.short_links.widgets.top_referers.empty_heading'))
            ->emptyStateDescription(__('panel-admin::marketing.short_links.widgets.top_referers.empty_description'));
    }

    /**
     * @param  array<array-key, mixed>  $filters
     * @return Collection<int, array{label: string, clicks: int, share: string}>
     */
    private function rows(array $filters): Collection
    {
        if (!$this->record instanceof ShortLink) {
            return new Collection;
        }

        $dimension = $this->dimension($filters);

        $query = DB::table((new ShortLinkClick)->getTable())
            ->where('short_link_id', $this->record->getKey())
            ->selectRaw($this->bucketExpression($dimension), [$this->emptyLabel($dimension)])
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('bucket')
            ->orderByDesc('total')
            ->limit(10);

        // Bots de preview (Discord, WhatsApp, Slack) ficam de fora por padrão.
        if (!$this->includeBots) {
            $query->where('is_bot', operator: false);
        }

        /** @var Collection<int, object{bucket: string, total: int}> $buckets */
        $buckets = $query->get();

        $grandTotal = (int) $buckets->sum('total');

        return $buckets
            ->map(fn (object $bucket): array => [
                'label' => (string) $bucket->bucket,
                'clicks' => (int) $bucket->total,
                'share' => $this->share((int) $bucket->total, $grandTotal),
            ])
            ->values();
    }

    private function share(int $clicks, int $grandTotal): string
    {
        if ($grandTotal <= 0) {
            return __('panel-admin::marketing.short_links.placeholders.none');
        }

        $formatted = Number::percentage($clicks / $grandTotal * 100, maxPrecision: 1);

        return is_string($formatted)
            ? $formatted
            : __('panel-admin::marketing.short_links.placeholders.none');
    }

    /**
     * O valor vem do filtro, que é estado do cliente. O `match` devolve um nome
     * de coluna literal, então nada recebido do browser entra no SQL.
     *
     * @param  array<array-key, mixed>  $filters
     * @return 'referer'|'utm_source'|'country_code'
     */
    private function dimension(array $filters): string
    {
        $dimension = $filters['dimension'] ?? null;
        $value = is_array($dimension) ? ($dimension['value'] ?? null) : null;

        return match ($value) {
            'utm_source' => 'utm_source',
            'country_code' => 'country_code',
            default => 'referer',
        };
    }

    /**
     * A expressão inteira é literal, nunca interpolada — é o que `selectRaw()`
     * exige e o que garante que a dimensão vinda do browser não vire SQL.
     *
     * @param  'referer'|'utm_source'|'country_code'  $dimension
     * @return literal-string
     */
    private function bucketExpression(string $dimension): string
    {
        return match ($dimension) {
            'utm_source' => "COALESCE(NULLIF(utm_source, ''), ?) AS bucket",
            'country_code' => "COALESCE(NULLIF(country_code, ''), ?) AS bucket",
            'referer' => "COALESCE(NULLIF(referer, ''), ?) AS bucket",
        };
    }

    /**
     * @param  'referer'|'utm_source'|'country_code'  $dimension
     */
    private function emptyLabel(string $dimension): string
    {
        return $dimension === 'referer'
            ? __('panel-admin::marketing.short_links.placeholders.no_referer')
            : __('panel-admin::marketing.short_links.placeholders.unknown');
    }
}
