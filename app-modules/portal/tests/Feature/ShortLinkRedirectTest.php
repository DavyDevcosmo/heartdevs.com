<?php

declare(strict_types=1);

use He4rt\Marketing\ShortLink\Jobs\RecordShortLinkClick;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->withoutVite();

    /*
     * O cache do encurtador grava o positivo com `forever` e o negativo com uma
     * sentinela de 60s. Com CACHE_STORE=array o store vive no processo, então
     * sem limpar aqui um slug resolvido num teste vazaria para o próximo.
     */
    Cache::flush();

    Queue::fake();
});

/**
 * Os quatro desfechos que não redirecionam. Estão juntos de propósito: a
 * garantia que interessa é que eles sejam indistinguíveis, e isso só dá para
 * afirmar comparando os quatro entre si.
 */
function portalDeadShortLinkSlug(string $case): string
{
    return match ($case) {
        'inexistente' => 'nunca-existiu-z9x8w',
        'desativado' => ShortLink::factory()->disabled()->create()->slug,
        'vencido' => ShortLink::factory()->expired()->create()->slug,
        'soft-deletado' => tap(
            ShortLink::factory()->create(),
            fn (ShortLink $link) => $link->delete(),
        )->slug,
    };
}

it('redireciona com 302 e anexa o UTM configurado no link', function (): void {
    $link = ShortLink::factory()
        ->withUtm(['utm_source' => 'discord', 'utm_medium' => 'post'])
        ->create(['destination_url' => 'https://discord.gg/he4rt']);

    get('/l/'.$link->slug)
        // 302 e não 301: um permanente ficaria no cache do browser e o clique
        // pararia de chegar ao servidor.
        ->assertStatus(302)
        ->assertHeader('Location', 'https://discord.gg/he4rt?utm_source=discord&utm_medium=post');
});

it('deixa o UTM que veio no clique ganhar do configurado no link', function (): void {
    $link = ShortLink::factory()
        ->withUtm(['utm_source' => 'discord'])
        ->create(['destination_url' => 'https://he4rt.dev/evento']);

    // Quem clicou vindo do Twitter trouxe intenção mais específica que a
    // configuração de origem do link.
    get('/l/'.$link->slug.'?utm_source=twitter')
        ->assertStatus(302)
        ->assertHeader('Location', 'https://he4rt.dev/evento?utm_source=twitter');
});

it('despacha o registro do clique no caminho feliz', function (): void {
    $link = ShortLink::factory()->create(['destination_url' => 'https://he4rt.dev']);

    get('/l/'.$link->slug)->assertStatus(302);

    Queue::assertPushed(RecordShortLinkClick::class, 1);
});

it('devolve 404 e não registra clique nenhum em desfecho morto', function (string $case): void {
    get('/l/'.portalDeadShortLinkSlug($case))->assertNotFound();

    // Slug morto não é tráfego de campanha: não pode inflar métrica de ninguém.
    Queue::assertNothingPushed();
})->with(['inexistente', 'desativado', 'vencido', 'soft-deletado']);

it('responde exatamente a mesma página nos quatro desfechos mortos', function (): void {
    $bodies = collect(['inexistente', 'desativado', 'vencido', 'soft-deletado'])
        ->map(fn (string $case): string => get('/l/'.portalDeadShortLinkSlug($case))
            ->assertNotFound()
            ->getContent())
        ->unique();

    // Uma resposta diferente por motivo transformaria /l/{slug} num oráculo de
    // enumeração: bastaria varrer slugs e ler o corpo para saber quais existem.
    expect($bodies)->toHaveCount(1);
});

it('mostra a página de marca com os dois CTAs quando o link não resolve', function (): void {
    get('/l/nunca-existiu-z9x8w')
        ->assertNotFound()
        ->assertSee('Esse link não está mais disponível')
        ->assertSee(route('home'), escape: false)
        ->assertSee(config()->string('he4rt.social_media.discord.url'), escape: false);
});

it('mantém a página de link morto fora do índice de busca', function (): void {
    get('/l/nunca-existiu-z9x8w')
        ->assertNotFound()
        ->assertSee('<meta name="robots" content="noindex, follow">', escape: false)
        ->assertSee('<title>Link indisponível - '.config()->string('app.name').'</title>', escape: false);
});

it('não casa slug com maiúscula, porque o canônico é minúsculo', function (): void {
    ShortLink::factory()->create(['slug' => 'discord-a3f9k', 'base_slug' => 'discord']);

    get('/l/Discord-A3F9K')->assertNotFound();

    Queue::assertNothingPushed();
});

it('passa a valer o destino novo já no clique seguinte à edição', function (): void {
    $link = ShortLink::factory()->create(['destination_url' => 'https://discord.gg/convite-antigo']);

    get('/l/'.$link->slug)->assertHeader('Location', 'https://discord.gg/convite-antigo');

    $link->update(['destination_url' => 'https://discord.gg/convite-novo']);

    // O observer esquece a chave no save; o cache positivo é eterno, então sem
    // ele este segundo clique ainda iria para o convite antigo.
    get('/l/'.$link->slug)->assertHeader('Location', 'https://discord.gg/convite-novo');
});
