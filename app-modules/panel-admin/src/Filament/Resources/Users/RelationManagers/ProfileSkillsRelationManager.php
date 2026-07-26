<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Users\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Enums\SkillProficiency;
use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\ProfileSkill;

class ProfileSkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'profileSkills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('skill_id')
                    ->label('Skill')
                    ->relationship('skill', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('proficiency')
                    ->label('Proficiency')
                    ->options(SkillProficiency::class)
                    ->required(),

                TextInput::make('years_experience')
                    ->label('Years of Experience')
                    ->integer(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('skill.name')
            ->columns([
                TextColumn::make('skill.name')
                    ->label('Skill')
                    ->searchable(),

                TextColumn::make('proficiency')
                    ->label('Proficiency')
                    ->badge(),

                TextColumn::make('years_experience')
                    ->label('Years of Experience')
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using($this->createProfileSkill(...))
                    ->visible($this->isEditableByCurrentUser(...)),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible($this->isEditableByCurrentUser(...)),
                DeleteAction::make()
                    ->visible($this->isEditableByCurrentUser(...)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible($this->isEditableByCurrentUser(...)),
            ]);
    }

    private function isEditableByCurrentUser(): bool
    {
        return auth()->user()?->role->isStaff() ?? false;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createProfileSkill(array $data): ProfileSkill
    {
        /** @var User $owner */
        $owner = $this->getOwnerRecord();

        $profile = Profile::ensureExists($owner->id);

        $data['profile_id'] = $profile->id;

        return ProfileSkill::query()->create($data);
    }
}
