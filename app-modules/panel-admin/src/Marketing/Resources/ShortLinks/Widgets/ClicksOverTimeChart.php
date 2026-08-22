<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets;

use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use Illuminate\Database\Eloquent\Builder;

class ClicksOverTimeChart extends ChartWidget
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

    public ?string $filter = '30d';

    /*
     * A série é uma linha só, quase sempre rasa. Sem teto, o Chart.js estica
     * até a largura do card e a altura vira 400px+ de espaço morto.
     */
    protected ?string $maxHeight = '240px';

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('panel-admin::marketing.short_links.widgets.clicks_over_time.heading');
    }

    public function getDescription(): ?string
    {
        if ($this->hasClicks()) {
            return null;
        }

        return trans('panel-admin::marketing.short_links.widgets.clicks_over_time.empty');
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        // Chaves com sufixo `d` de propósito: '7'/'30'/'90' virariam chaves int
        // por coerção do PHP e quebrariam o contrato `array<string, string>`.
        return [
            '7d' => trans('panel-admin::marketing.short_links.widgets.clicks_over_time.ranges.7'),
            '30d' => trans('panel-admin::marketing.short_links.widgets.clicks_over_time.ranges.30'),
            '90d' => trans('panel-admin::marketing.short_links.widgets.clicks_over_time.ranges.90'),
        ];
    }

    /**
     * A série é sempre preenchida com zeros no intervalo inteiro: um link sem
     * clique nenhum rende uma linha rasa, nunca um gráfico quebrado.
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $days = $this->rangeInDays();
        $timezone = config('app.display_timezone');
        $start = CarbonImmutable::now($timezone)->subDays($days - 1)->startOfDay();

        $totals = $this->clicksQuery()
            ->where('clicked_at', '>=', $start->utc())
            // Uma conversão só: `AT TIME ZONE` duplicado desloca o dia em +3h.
            ->selectRaw('(clicked_at AT TIME ZONE ?)::date AS day', [$timezone])
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $day = $start->addDays($offset);

            $labels[] = $day->format('d/m');
            $data[] = (int) ($totals->get($day->format('Y-m-d')) ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('panel-admin::marketing.short_links.widgets.clicks_over_time.dataset'),
                    'data' => $data,
                    'borderColor' => '#782bf1',
                    'backgroundColor' => 'rgba(120, 43, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * `$this->filter` chega do browser e não é validado pelo Livewire — resolver
     * por `match` com default é o que impede um valor forjado de virar consulta.
     */
    private function rangeInDays(): int
    {
        return match ($this->filter) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };
    }

    private function hasClicks(): bool
    {
        return $this->record instanceof ShortLink && $this->clicksQuery()->exists();
    }

    /** @return Builder<ShortLinkClick> */
    private function clicksQuery(): Builder
    {
        $query = ShortLinkClick::query()
            ->where('short_link_id', $this->record?->getKey());

        // Bots de preview (Discord, WhatsApp, Slack) ficam de fora por padrão.
        if (!$this->includeBots) {
            $query->where('is_bot', operator: false);
        }

        return $query;
    }
}
