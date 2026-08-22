<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use He4rt\Contents\Models\ContentEntry;
use He4rt\Identity\User\Models\User;
use He4rt\PanelAdmin\Filament\Resources\ContentEntries\ContentEntryResource;
use He4rt\PanelAdmin\Filament\Resources\ContentEntries\Pages\EditContentEntry;
use He4rt\PanelAdmin\Filament\Resources\ContentEntries\Pages\ListContentEntries;
use Illuminate\Foundation\Console\QueuedCommand;
use Illuminate\Support\Facades\Queue;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    config([
        'he4rt.admins' => 'danielhe4rt',
        'app.display_timezone' => 'America/Sao_Paulo',
    ]);

    $this->admin = User::factory()->create(['username' => 'danielhe4rt']);

    $this->actingAs($this->admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('a listagem carrega para admin', function (): void {
    $entries = ContentEntry::factory()->count(3)->create();

    livewire(ListContentEntries::class)
        ->loadTable()
        ->assertOk()
        ->assertCanSeeTableRecords($entries);
});

test('a capa abre a listagem, antes do título', function (): void {
    ContentEntry::factory()->create(['thumbnail_url' => 'https://example.test/capa.png']);

    $columns = array_keys(
        livewire(ListContentEntries::class)->loadTable()->instance()->getTable()->getColumns(),
    );

    expect($columns[0])->toBe('thumbnail_url')
        ->and($columns[1])->toBe('title');
});

test('artigo não pode ser criado nem apagado pelo painel', function (): void {
    $entry = ContentEntry::factory()->create();

    expect(ContentEntryResource::canCreate())->toBeFalse()
        ->and(ContentEntryResource::canDelete($entry))->toBeFalse()
        ->and(ContentEntryResource::canDeleteAny())->toBeFalse()
        ->and(ContentEntryResource::getPages())->not->toHaveKey('create');
});

test('o form só permite editar o vínculo de autor', function (): void {
    $entry = ContentEntry::factory()->create();

    livewire(EditContentEntry::class, ['record' => $entry->getKey()])
        ->assertSchemaComponentExists('author_id')
        ->assertSchemaComponentDoesNotExist('title')
        ->assertSchemaComponentDoesNotExist('url')
        ->assertSchemaComponentDoesNotExist('tags')
        ->assertSchemaComponentDoesNotExist('reactions_count');
});

test('vincular um autor persiste na entrada', function (): void {
    $entry = ContentEntry::factory()->create(['author_id' => null]);
    $author = User::factory()->create();

    livewire(EditContentEntry::class, ['record' => $entry->getKey()])
        ->fillForm(['author_id' => $author->getKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($entry->refresh()->author_id)->toBe($author->getKey());
});

test('o filtro de artigos sem autor mostra só os não vinculados', function (): void {
    $unlinked = ContentEntry::factory()->create(['author_id' => null]);
    $linked = ContentEntry::factory()->authoredBy($this->admin)->create();

    livewire(ListContentEntries::class)
        ->loadTable()
        ->filterTable('unlinked_author')
        ->assertCanSeeTableRecords([$unlinked])
        ->assertCanNotSeeTableRecords([$linked]);
});

test('o filtro de métricas desatualizadas ignora as sincronizadas na semana', function (): void {
    $fresh = ContentEntry::factory()->create(['metrics_synced_at' => now()->subDay()]);
    $stale = ContentEntry::factory()->create(['metrics_synced_at' => now()->subMonth()]);
    $never = ContentEntry::factory()->create(['metrics_synced_at' => null]);

    livewire(ListContentEntries::class)
        ->loadTable()
        ->filterTable('stale_metrics')
        ->assertCanSeeTableRecords([$stale, $never])
        ->assertCanNotSeeTableRecords([$fresh]);
});

test('a ação de sincronizar enfileira o comando em vez de rodar na requisição', function (): void {
    Queue::fake();

    livewire(ListContentEntries::class)
        ->loadTable()
        ->callAction('sync')
        ->assertNotified();

    Queue::assertPushed(QueuedCommand::class);
});
