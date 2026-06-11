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
                    ->label('Usuário')
                    ->description(fn (User $record): string => implode(' · ', array_filter([
                        '@'.$record->username,
                        $record->address?->state,
                        'membro há '.now()->diffInMonths($record->created_at).'m',
                    ])))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('providers_list')
                    ->label('Providers')
                    ->getStateUsing(fn (User $record): string => $record->providers
                        ->pluck('provider')
                        ->map(fn ($p) => mb_strtoupper(mb_substr((string) $p, 0, 1)))
                        ->join(' · ')
                    )
                    ->default('—'),

                TextColumn::make('profile.seniority_level')
                    ->label('Senioridade')
                    ->description(fn (User $record): string => $record->profile?->years_experience
                        ? $record->profile->years_experience.'a'
                        : '—'
                    )
                    ->badge()
                    ->default('—'),

                TextColumn::make('character_level')
                    ->label('Nível / XP')
                    ->getStateUsing(fn (User $record): string => $record->character
                        ? $record->character->level.' · '.$record->character->percentage_experience.'%'
                        : '—'
                    ),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['providers', 'character', 'address']))
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
