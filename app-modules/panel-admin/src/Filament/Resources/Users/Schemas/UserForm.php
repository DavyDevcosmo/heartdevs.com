<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use He4rt\Profile\Enums\SeniorityLevel;
use He4rt\Profile\Enums\SocialPlatform;
use He4rt\Profile\Enums\StartAvailability;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Conta')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required(),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                            ]),

                        Tab::make('Profile')
                            ->schema([
                                Section::make('Dados Pessoais')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextInput::make('profile.nickname')
                                                ->label('Apelido')
                                                ->maxLength(100)
                                                ->columnSpan(1),

                                            DatePicker::make('profile.birthdate')
                                                ->label('Data de nascimento')
                                                ->columnSpan(1),
                                        ]),
                                    ]),

                                Section::make('Dados Profissionais')
                                    ->schema([
                                        Grid::make(3)->schema([
                                            TextInput::make('profile.headline')
                                                ->label('Título')
                                                ->maxLength(100)
                                                ->columnSpan(1),

                                            Select::make('profile.seniority_level')
                                                ->label('Senioridade')
                                                ->options(SeniorityLevel::class)
                                                ->columnSpan(1),

                                            TextInput::make('profile.years_experience')
                                                ->label('Anos de experiência')
                                                ->numeric()
                                                ->minValue(0)
                                                ->maxValue(50)
                                                ->columnSpan(1),

                                            Textarea::make('profile.about')
                                                ->label('Bio')
                                                ->maxLength(500)
                                                ->rows(4)
                                                ->columnSpanFull(),
                                        ]),
                                    ]),

                                Section::make('Links Sociais')
                                    ->schema([
                                        Repeater::make('profile.social_links')
                                            ->label('')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    Select::make('platform')
                                                        ->label('Plataforma')
                                                        ->options(SocialPlatform::class)
                                                        ->required()
                                                        ->columnSpan(1),

                                                    TextInput::make('handle')
                                                        ->label('Handle')
                                                        ->required()
                                                        ->columnSpan(1),
                                                ]),
                                            ])
                                            ->defaultItems(0)
                                            ->reorderable(false)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Disponibilidade')
                                    ->schema([
                                        Toggle::make('profile.available_for_proposals')
                                            ->label('Disponível para propostas')
                                            ->live(),

                                        Select::make('profile.start_availability')
                                            ->label('Disponibilidade de início')
                                            ->options(StartAvailability::class)
                                            ->live()
                                            ->visible(fn (Get $get): bool => (bool) $get('profile.available_for_proposals')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
