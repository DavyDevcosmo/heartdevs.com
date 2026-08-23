<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Support\Enums\Width;
use He4rt\Community\Retrospective\DTOs\DeckConfig;
use He4rt\Community\Retrospective\DTOs\RetrospectiveSnapshot;
use He4rt\Community\Retrospective\DTOs\SourceFilters;
use He4rt\Community\Retrospective\Enums\RetrospectiveStatus;
use He4rt\Community\Retrospective\Jobs\CompileRetrospectiveSnapshot;
use He4rt\Community\Retrospective\Models\Retrospective;
use He4rt\Identity\User\Models\User;
use He4rt\IntegrationGithub\Models\GithubContribution;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Pages\BuildDeck;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\RetrospectiveResource;
use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode;
use Illuminate\Support\Facades\Bus;
use Tests\Support\Retrospective\PlainRetrospectiveSource;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $user = User::factory()->create(['username' => 'danielhe4rt']);

    config(['he4rt.admins' => 'danielhe4rt']);

    $this->actingAs($user);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

/**
 * Retrospectiva com ordem editorial explícita: a ordem de descoberta das fontes
 * depende do boot dos providers, então os testes de reordenação fixam o ponto
 * de partida em vez de assumi-lo.
 */
function retrospectiveWithOrder(array $order = ['github', 'discord'], array $exclusions = []): Retrospective
{
    return Retrospective::factory()->create([
        'deck_config' => new DeckConfig(order: $order, exclusions: $exclusions),
    ]);
}

/**
 * Um PR dentro do recorte da edição, para a varredura de exclusionCandidates()
 * do GithubSource ter o que oferecer no picker.
 */
function contributionWithin(Retrospective $retrospective, string $ref): GithubContribution
{
    return GithubContribution::factory()->create([
        'external_ref' => $ref,
        'occurred_at' => $retrospective->since->copy()->addDay(),
        'metadata' => ['title' => 'Um PR grande', 'additions' => 500, 'deletions' => 10],
    ]);
}

test('o builder atende na chave edit com a rota /deck', function (): void {
    $retrospective = Retrospective::factory()->create();

    $url = RetrospectiveResource::getUrl('edit', ['record' => $retrospective]);

    expect($url)->toEndWith('/deck');

    test()->get($url)->assertOk();
});

test('abre com a timeline das fontes e o iframe do preview', function (): void {
    $retrospective = retrospectiveWithOrder();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertSee('GitHub')
        ->assertSee('Discord')
        ->assertSee('Repositórios')
        ->assertSee(route('community.retrospective.preview', $retrospective), escape: false);
});

test('desliga e religa uma fonte pelo inspector', function (): void {
    $retrospective = retrospectiveWithOrder();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:discord')
        ->assertFormSet(['visible' => true])
        ->fillForm(['visible' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($retrospective->fresh()->deck_config->showsSource('discord'))->toBeFalse();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:discord')
        ->assertFormSet(['visible' => false])
        ->fillForm(['visible' => true])
        ->call('save');

    expect($retrospective->fresh()->deck_config->showsSource('discord'))->toBeTrue();
});

test('desliga um kind de slide sem tocar os outros kinds da fonte', function (): void {
    $retrospective = retrospectiveWithOrder();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'slide:github.repos')
        ->fillForm(['visible' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $config = $retrospective->fresh()->deck_config;

    expect($config->hiddenSlides)->toBe(['github.repos'])
        ->and($config->showsSlide('github.panorama'))->toBeTrue();
});

test('sobe e desce um bloco na ordem editorial', function (): void {
    $retrospective = retrospectiveWithOrder(['github', 'discord']);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('moveSource', 'discord', -1);

    expect($retrospective->fresh()->deck_config->order)->toBe(['discord', 'github']);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('moveSource', 'discord', 1);

    expect($retrospective->fresh()->deck_config->order)->toBe(['github', 'discord']);
});

test('reordenar não mexe em on/off nem em exclusions', function (): void {
    $retrospective = Retrospective::factory()->create([
        'deck_config' => new DeckConfig(
            order: ['github', 'discord'],
            hiddenSources: ['discord'],
            hiddenSlides: ['github.repos'],
            exclusions: ['github' => ['pr:1']],
        ),
    ]);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('moveSource', 'discord', -1);

    $config = $retrospective->fresh()->deck_config;

    expect($config->order)->toBe(['discord', 'github'])
        ->and($config->hiddenSources)->toBe(['discord'])
        ->and($config->hiddenSlides)->toBe(['github.repos'])
        ->and($config->exclusionsFor('github'))->toBe(['pr:1']);
});

test('o picker oferece os candidatos que a fonte varreu no recorte', function (): void {
    $retrospective = retrospectiveWithOrder();
    $contribution = contributionWithin($retrospective, 'pr:4242');

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:github')
        ->assertSee('Um PR grande')
        ->assertSee($contribution->actor_login);
});

test('salva no deck_config as exclusions escolhidas no picker', function (): void {
    $retrospective = retrospectiveWithOrder();
    contributionWithin($retrospective, 'pr:4242');

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:github')
        ->fillForm(['exclusion_items' => ['pr:4242']])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($retrospective->fresh()->deck_config->exclusionsFor('github'))->toBe(['pr:4242']);
});

test('preserva refs já excluídos que ficaram fora do teto do picker', function (): void {
    $retrospective = retrospectiveWithOrder(exclusions: ['github' => ['pr:999999', 'pr:4242']]);
    contributionWithin($retrospective, 'pr:4242');

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:github')
        ->assertFormSet(['exclusion_items' => ['pr:4242']])
        ->fillForm(['exclusion_items' => []])
        ->call('save');

    expect($retrospective->fresh()->deck_config->exclusionsFor('github'))->toBe(['pr:999999']);
});

test('avisa que exclusion exige republicar, e só quando ela muda', function (): void {
    $retrospective = retrospectiveWithOrder();
    contributionWithin($retrospective, 'pr:4242');

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:github')
        ->fillForm(['exclusion_items' => ['pr:4242']])
        ->call('save')
        ->assertNotified('Exclusion alterada');

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:github')
        ->fillForm(['visible' => false])
        ->call('save')
        ->assertNotNotified('Exclusion alterada');
});

test('a fonte que não cura entra na timeline com on/off, mas sem picker', function (): void {
    PlainRetrospectiveSource::register();

    $retrospective = retrospectiveWithOrder();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertSee(PlainRetrospectiveSource::LABEL)
        ->call('select', 'source:'.PlainRetrospectiveSource::KEY)
        ->assertFormFieldExists('visible')
        ->assertFormFieldDoesNotExist('exclusion_items')
        ->fillForm(['visible' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($retrospective->fresh()->deck_config->showsSource(PlainRetrospectiveSource::KEY))->toBeFalse();
});

test('salva capa e período nas colunas da edição', function (): void {
    $retrospective = Retrospective::factory()->create(['hide_bots' => true]);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'cover')
        ->fillForm([
            'title' => 'Retro de Julho',
            'cover_title' => 'Julho foi grande',
            'cover_intro' => 'Uma introdução.',
            'hide_bots' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = $retrospective->fresh();

    expect($fresh->title)->toBe('Retro de Julho')
        ->and($fresh->cover_title)->toBe('Julho foi grande')
        ->and($fresh->cover_intro)->toBe('Uma introdução.')
        ->and($fresh->hide_bots)->toBeFalse();
});

test('a capa exige título', function (): void {
    $retrospective = Retrospective::factory()->create();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'cover')
        ->fillForm(['title' => ''])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);
});

test('salva a mensagem de fecho', function (): void {
    $retrospective = Retrospective::factory()->create();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'closing')
        ->fillForm(['closing_text' => 'Até a próxima.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($retrospective->fresh()->closing_text)->toBe('Até a próxima.');
});

test('publicar pelo builder marca publicando e enfileira o job', function (): void {
    Bus::fake();

    $retrospective = Retrospective::factory()->create();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->callAction('publish')
        ->assertNotified();

    expect($retrospective->fresh()->status)->toBe(RetrospectiveStatus::Publishing);

    Bus::assertDispatched(fn (CompileRetrospectiveSnapshot $job): bool => $job->retrospective->is($retrospective));
});

test('apagar pelo builder volta para a lista', function (): void {
    $retrospective = Retrospective::factory()->create();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->callAction('delete')
        ->assertRedirect(RetrospectiveResource::getUrl('index'));

    expect(Retrospective::query()->whereKey($retrospective->id)->exists())->toBeFalse();
});

test('o preview fura cache com a versão do registro', function (): void {
    $retrospective = Retrospective::factory()->create();

    $component = livewire(BuildDeck::class, ['record' => $retrospective->id]);

    $before = $component->instance()->previewUrl();

    $component
        ->call('select', 'closing')
        ->fillForm(['closing_text' => 'Novo fecho.'])
        ->call('save');

    expect($component->instance()->previewUrl())
        ->toContain('v=')
        ->not->toBe($before);
});

test('o builder mostra o status da edição', function (): void {
    $retrospective = Retrospective::factory()->create();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertSee(RetrospectiveStatus::Draft->getLabel());
});

test('o builder avisa quando a edição publicada está com exclusion alterada depois', function (): void {
    $retrospective = Retrospective::factory()
        ->published(new RetrospectiveSnapshot(
            sources: [],
            filters: new SourceFilters(hideBots: true, exclusions: ['pr:1']),
        ))
        ->create([
            'hide_bots' => true,
            'deck_config' => new DeckConfig(exclusions: ['github' => ['pr:1', 'pr:2']]),
        ]);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertSee('Republique');
});

test('o builder não avisa drift quando só ordem e on/off mudaram', function (): void {
    $retrospective = Retrospective::factory()
        ->published(new RetrospectiveSnapshot(sources: [], filters: new SourceFilters(hideBots: true)))
        ->create([
            'hide_bots' => true,
            'deck_config' => new DeckConfig(order: ['discord', 'github'], hiddenSources: ['discord']),
        ]);

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertDontSee('Republique');
});

test('o builder acompanha a transição de publicando para publicada', function (): void {
    $retrospective = Retrospective::factory()->create(['status' => RetrospectiveStatus::Publishing]);

    $component = livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->assertSee(RetrospectiveStatus::Publishing->getLabel());

    // O job termina em segundo plano; sem poll o operador ficaria olhando
    // "Publicando" até recarregar na mão.
    $retrospective->update([
        'status' => RetrospectiveStatus::Published,
        'published_at' => now(),
        'snapshot' => new RetrospectiveSnapshot(),
    ]);

    $component
        ->call('refreshStatus')
        ->assertSee(RetrospectiveStatus::Published->getLabel())
        ->assertDontSee(RetrospectiveStatus::Publishing->getLabel());
});

test('o builder ocupa a largura inteira da viewport', function (): void {
    // Três colunas com iframe de deck no meio não cabem no 7xl padrão do painel.
    $retrospective = Retrospective::factory()->create();

    $page = livewire(BuildDeck::class, ['record' => $retrospective->id])->instance();

    expect($page->getMaxContentWidth())->toBe(Width::Full);
});

test('o inspector não repete um cabeçalho genérico acima da seção do formulário', function (): void {
    // A Section do formulário já nomeia o alvo ("Bloco: Discord") e explica o efeito;
    // um cabeçalho com o label do modo em cima dizia a mesma coisa, mais vago.
    $retrospective = retrospectiveWithOrder();

    livewire(BuildDeck::class, ['record' => $retrospective->id])
        ->call('select', 'source:discord')
        ->assertSee('Bloco: Discord')
        ->assertDontSee(InspectorMode::Source->getLabel())
        ->assertDontSee(InspectorMode::Source->getDescription());
});
