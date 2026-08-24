<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Interactions\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use He4rt\Activity\Tracking\Enums\ActivityType;
use He4rt\Activity\Tracking\Models\Interaction;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use He4rt\IntegrationGithub\Contributions\ResolveContributorIdentity;
use He4rt\PanelAdmin\Contributions\Actions\HideInteractionAction;
use He4rt\PanelAdmin\Contributions\Actions\UnhideInteractionAction;
use Illuminate\Database\Eloquent\Builder;

final class InteractionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pessoa')
                    ->description(static fn (Interaction $record): string => $record->user->username)
                    ->searchable(['users.name', 'users.username']),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('metadata.repo')
                    ->label('Repositório')
                    ->placeholder('—')
                    ->limit(32),

                TextColumn::make('externalIdentity.provider')
                    ->label('Origem')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('metadata.matched_by')
                    ->label('Casou por')
                    ->badge()
                    ->placeholder('—')
                    ->color(static fn (?string $state): string => $state === ResolveContributorIdentity::MATCHED_BY_LOGIN
                        ? 'warning'
                        : 'gray')
                    ->tooltip(static fn (?string $state): ?string => $state === ResolveContributorIdentity::MATCHED_BY_LOGIN
                        ? 'Login é mutável no GitHub — atribuição menos firme que actor_id.'
                        : null),

                TextColumn::make('occurred_at')
                    ->label('Ocorrido em')
                    ->dateTime('d/m/Y H:i')
                    ->timezone(config('app.display_timezone'))
                    ->sortable(),

                IconColumn::make('hidden_at')
                    ->label('Visível')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('gray')
                    ->falseColor('success')
                    ->tooltip(static fn (Interaction $record): ?string => $record->isVisible()
                        ? null
                        : 'Oculta por '.($record->hiddenByUser->name ?? 'alguém')),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(ActivityType::class)
                    ->multiple(),

                SelectFilter::make('provider')
                    ->label('Origem')
                    ->options(IdentityProvider::class)
                    ->query(static fn (Builder $query, array $data): Builder => filled($data['values'] ?? [])
                        ? $query->whereHas(
                            'externalIdentity',
                            static fn (Builder $identity): Builder => $identity->whereIn('provider', $data['values']),
                        )
                        : $query)
                    ->multiple(),

                SelectFilter::make('matched_by')
                    ->label('Casou por')
                    ->options([
                        ResolveContributorIdentity::MATCHED_BY_ACTOR_ID => 'actor_id (exato)',
                        ResolveContributorIdentity::MATCHED_BY_LOGIN => 'login (mutável)',
                    ])
                    ->query(static fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->where('metadata->matched_by', $data['value'])
                        : $query),

                Filter::make('hidden')
                    ->label('Somente ocultas')
                    ->query(static fn (Builder $query): Builder => $query->whereNotNull('hidden_at')),
            ])
            ->recordActions([
                HideInteractionAction::make(),
                UnhideInteractionAction::make(),
            ]);
    }
}
