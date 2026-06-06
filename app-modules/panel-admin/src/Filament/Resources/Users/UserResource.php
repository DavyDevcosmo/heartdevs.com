<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Users;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use He4rt\Identity\User\Models\User;
use He4rt\PanelAdmin\Filament\Resources\Users\Pages\CreateUser;
use He4rt\PanelAdmin\Filament\Resources\Users\Pages\EditUser;
use He4rt\PanelAdmin\Filament\Resources\Users\Pages\ListUsers;
use He4rt\PanelAdmin\Filament\Resources\Users\Schemas\UserForm;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static bool $isScopedToTenant = false;

    protected static ?string $slug = 'users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Membro')
                    ->description(fn (User $record): string => $record->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('profile.headline')
                    ->label('Headline')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('profile.seniority_level')
                    ->label('Seniority')
                    ->badge()
                    ->default('—'),

                TextColumn::make('profile.years_experience')
                    ->label('Exp.')
                    ->formatStateUsing(fn ($state) => $state ? $state.' anos' : '—'),

                TextColumn::make('profile.available_for_proposals')
                    ->label('Disponível')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Sim' : 'Não')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Criado este mês')
                    ->query(fn (Builder $query) => $query->whereMonth('created_at', now()->month)),

                SelectFilter::make('tenant')
                    ->label('Tenant')
                    ->relationship('tenants', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
