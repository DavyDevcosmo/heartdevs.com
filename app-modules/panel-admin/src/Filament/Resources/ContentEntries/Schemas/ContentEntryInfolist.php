<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\ContentEntries\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use He4rt\Contents\Articles\Models\Article;
use He4rt\Contents\Models\ContentEntry;

class ContentEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Publicação')
                    ->icon(Heroicon::OutlinedNewspaper)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label('Título')
                            ->columnSpanFull(),

                        TextEntry::make('provider')
                            ->label('Provider')
                            ->badge(),

                        TextEntry::make('published_at')
                            ->label('Publicado em')
                            ->dateTime('d/m/Y H:i')
                            ->timezone(config('app.display_timezone')),

                        TextEntry::make('url')
                            ->label('Endereço')
                            ->url(static fn (ContentEntry $record): string => $record->url)
                            ->openUrlInNewTab()
                            ->columnSpanFull(),

                        TextEntry::make('tags')
                            ->label('Tags')
                            ->badge()
                            ->state(static fn (ContentEntry $record): array => $record->tags->toArray())
                            ->placeholder('Sem tags')
                            ->columnSpanFull(),

                        ImageEntry::make('thumbnail_url')
                            ->label('Capa')
                            ->placeholder('Sem capa')
                            ->columnSpanFull(),
                    ]),

                Section::make('Autoria')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('author.username')
                            ->label('Autor vinculado')
                            ->placeholder('Não vinculado'),

                        TextEntry::make('author_handle')
                            ->label('Handle no provider'),
                    ]),

                Section::make('Engajamento')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->columns(4)
                    ->schema([
                        TextEntry::make('reactions_count')
                            ->label('Reações')
                            ->numeric(0)
                            ->placeholder('—'),

                        TextEntry::make('comments_count')
                            ->label('Comentários')
                            ->numeric(0)
                            ->placeholder('—'),

                        TextEntry::make('saves_count')
                            ->label('Salvos')
                            ->numeric(0)
                            ->placeholder('—'),

                        TextEntry::make('metrics_synced_at')
                            ->label('Sincronizado')
                            ->since()
                            ->placeholder('Nunca'),
                    ]),

                Section::make('Artigo')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('contentable.description')
                            ->label('Descrição')
                            ->placeholder('—')
                            ->columnSpanFull(),

                        TextEntry::make('contentable.reading_time_minutes')
                            ->label('Tempo de leitura')
                            ->numeric(0)
                            ->suffix(' min')
                            ->placeholder('—'),

                        TextEntry::make('contentable.canonical_url')
                            ->label('URL canônica')
                            ->placeholder('—'),

                        TextEntry::make('contentable.source_edited_at')
                            ->label('Editado na origem')
                            ->dateTime('d/m/Y H:i')
                            ->timezone(config('app.display_timezone'))
                            ->placeholder('—'),

                        TextEntry::make('body')
                            ->label('Corpo')
                            ->markdown()
                            ->state(static fn (ContentEntry $record): string => $record->contentable instanceof Article
                                ? (string) $record->contentable->body_markdown
                                : '')
                            ->placeholder('Corpo não hidratado — o sync só busca o detalhe sob demanda.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
