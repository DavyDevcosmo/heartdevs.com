<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use Illuminate\Database\Eloquent\Builder;

/**
 * O log cru por trás dos agregados: cada linha é um clique que virou 302.
 *
 * `ip_address` e `user_agent` existem na tabela mas não têm coluna aqui — são
 * dado pessoal (LGPD) e o projeto ainda não tem política de privacidade que
 * justifique jogá-los na tela. Continuam no banco; não voltam para o painel.
 */
class RecentClicksTable extends TableWidget
{
    /** Injetado por `Filament\Schemas\Components\Livewire::getComponentProperties()`. */
    public ?ShortLink $record = null;

    /**
     * Renderizado como ilha Livewire, que só entrega dado serializável: o filtro
     * da página chega por parâmetro de mount, não por `pageFilters`.
     */
    public bool $includeBots = false;

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('panel-admin::marketing.short_links.widgets.recent_clicks.heading'))
            ->query($this->clicksQuery(...))
            ->defaultSort('clicked_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50])
            ->columns([
                TextColumn::make('clicked_at')
                    ->label(__('panel-admin::marketing.short_links.widgets.recent_clicks.columns.clicked_at'))
                    ->dateTime('d/m/Y H:i:s')
                    ->timezone(config('app.display_timezone'))
                    ->sortable(),

                TextColumn::make('referer')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.dimensions.referer'))
                    ->placeholder(__('panel-admin::marketing.short_links.placeholders.no_referer'))
                    ->limit(32)
                    ->tooltip(fn (ShortLinkClick $record): ?string => $record->referer),

                TextColumn::make('device')
                    ->label(__('panel-admin::marketing.short_links.widgets.recent_clicks.columns.device'))
                    ->state(fn (ShortLinkClick $record): string => collect([
                        $record->device_type,
                        $record->browser,
                        $record->os,
                    ])->filter()->implode(' / '))
                    ->placeholder(__('panel-admin::marketing.short_links.placeholders.unknown')),

                TextColumn::make('country_code')
                    ->label(__('panel-admin::marketing.short_links.widgets.top_referers.dimensions.country_code'))
                    ->badge()
                    ->color('gray')
                    ->placeholder(__('panel-admin::marketing.short_links.placeholders.unknown')),

                // Sem `->toggleable()`: uma única coluna alternável faz o Filament
                // reservar uma faixa inteira de toolbar só para o botão de colunas.
                TextColumn::make('utm_source')
                    ->label(__('panel-admin::marketing.short_links.fields.utm_source'))
                    ->placeholder(__('panel-admin::marketing.short_links.placeholders.none')),

                TextColumn::make('is_bot')
                    ->label(__('panel-admin::marketing.short_links.widgets.recent_clicks.columns.origin'))
                    ->badge()
                    ->state(fn (ShortLinkClick $record): string => $record->is_bot
                        ? ($record->bot_name ?? __('panel-admin::marketing.short_links.widgets.recent_clicks.bot'))
                        : __('panel-admin::marketing.short_links.widgets.recent_clicks.human'))
                    ->color(fn (ShortLinkClick $record): string => $record->is_bot ? 'warning' : 'gray'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCursorArrowRays)
            ->emptyStateHeading(__('panel-admin::marketing.short_links.widgets.recent_clicks.empty_heading'))
            ->emptyStateDescription(__('panel-admin::marketing.short_links.widgets.recent_clicks.empty_description'));
    }

    /**
     * Query Eloquent de verdade (e não `records()`): é o que dá paginação e
     * ordenação de graça. A PK `bigint` já serve de chave de linha.
     *
     * @return Builder<ShortLinkClick>
     */
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
