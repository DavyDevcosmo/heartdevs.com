<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets;

use Filament\Widgets\ChartWidget;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use Illuminate\Database\Eloquent\Builder;

class DeviceBreakdownChart extends ChartWidget
{
    /** @var array<int, string> */
    private const array PALETTE = [
        '#782bf1',
        '#a78bfa',
        '#c084fc',
        '#d8b4fe',
        '#7c3aed',
        '#6d28d9',
        '#5b21b6',
        '#9333ea',
    ];

    /** Injetado por `Filament\Schemas\Components\Livewire::getComponentProperties()`. */
    public ?ShortLink $record = null;

    /**
     * Renderizado como ilha Livewire (`Filament\Schemas\Components\Livewire`), que
     * só entrega dado serializável: o filtro da página chega por parâmetro de mount,
     * não por `pageFilters`. Quem troca o valor é a `key()` dinâmica da ilha, que
     * remonta o componente com o novo `includeBots`.
     */
    public bool $includeBots = false;

    public ?string $filter = 'device_type';

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('panel-admin::marketing.short_links.widgets.device_breakdown.heading');
    }

    public function getDescription(): ?string
    {
        if ($this->record instanceof ShortLink && $this->clicksQuery()->exists()) {
            return null;
        }

        return trans('panel-admin::marketing.short_links.widgets.device_breakdown.empty');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            'device_type' => trans('panel-admin::marketing.short_links.widgets.device_breakdown.dimensions.device_type'),
            'browser' => trans('panel-admin::marketing.short_links.widgets.device_breakdown.dimensions.browser'),
            'os' => trans('panel-admin::marketing.short_links.widgets.device_breakdown.dimensions.os'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $totals = $this->clicksQuery()
            ->selectRaw($this->bucketExpression(), [
                trans('panel-admin::marketing.short_links.placeholders.unknown'),
            ])
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('bucket')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'bucket');

        return [
            'datasets' => [
                [
                    'label' => $this->getFilters()[$this->dimension()] ?? '',
                    'data' => $totals->values()->map(static fn (mixed $total): int => (int) $total)->all(),
                    'backgroundColor' => array_slice(self::PALETTE, 0, max($totals->count(), 1)),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $totals->keys()->map(static fn (mixed $key): string => (string) $key)->all(),
        ];
    }

    /**
     * `$this->filter` é controlável pelo cliente. O `match` devolve um nome de
     * coluna literal — nunca o valor recebido — então nada forjado chega ao SQL.
     */
    private function dimension(): string
    {
        return match ($this->filter) {
            'browser' => 'browser',
            'os' => 'os',
            default => 'device_type',
        };
    }

    /**
     * A expressão inteira é literal (nunca interpolada), que é o que `selectRaw()`
     * exige e o que garante que a dimensão escolhida no browser não vire SQL.
     *
     * @return literal-string
     */
    private function bucketExpression(): string
    {
        return match ($this->filter) {
            'browser' => 'COALESCE(browser, ?) AS bucket',
            'os' => 'COALESCE(os, ?) AS bucket',
            default => 'COALESCE(device_type, ?) AS bucket',
        };
    }

    /** @return Builder<ShortLinkClick> */
    private function clicksQuery(): Builder
    {
        $query = ShortLinkClick::query()
            ->where('short_link_id', $this->record?->getKey());

        if (!$this->includeBots) {
            $query->where('is_bot', operator: false);
        }

        return $query;
    }
}
