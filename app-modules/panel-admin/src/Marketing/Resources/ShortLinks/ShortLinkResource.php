<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Marketing\Resources\ShortLinks;

use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\PanelAdmin\Marketing\MarketingCluster;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages\CreateShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages\EditShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages\ListShortLinks;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Pages\ViewShortLink;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Schemas\ShortLinkForm;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Schemas\ShortLinkInfolist;
use He4rt\PanelAdmin\Marketing\Resources\ShortLinks\Tables\ShortLinksTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShortLinkResource extends Resource
{
    protected static ?string $model = ShortLink::class;

    protected static ?string $cluster = MarketingCluster::class;

    protected static ?string $slug = 'short-links';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'slug';

    /**
     * A URL pública de um link curto.
     *
     * A rota nomeada mora no `portal` (`/l/{slug}`); usá-la aqui faz o painel
     * herdar qualquer mudança de prefixo ou domínio sem edição, e quebra em
     * `RouteNotFoundException` se a borda pública sumir — melhor do que copiar
     * silenciosamente uma URL que ninguém mais serve.
     */
    public static function shortUrl(ShortLink|string $link): string
    {
        return route('short-link.redirect', [
            'slug' => $link instanceof ShortLink ? $link->slug : $link,
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('panel-admin::marketing.navigation.short_links');
    }

    public static function getModelLabel(): string
    {
        return __('panel-admin::marketing.short_links.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::marketing.short_links.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return ShortLinkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShortLinkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShortLinksTable::table($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListShortLinks::route('/'),
            'create' => CreateShortLink::route('/create'),
            'view' => ViewShortLink::route('/{record}'),
            'edit' => EditShortLink::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['slug', 'base_slug', 'destination_url'];
    }

    /**
     * O slug segue reservado depois do soft delete, então a rota precisa
     * enxergar o registro removido — senão editar/restaurar dá 404.
     *
     * @return Builder<ShortLink>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        /** @var Builder<ShortLink> $query */
        $query = parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return $query;
    }
}
