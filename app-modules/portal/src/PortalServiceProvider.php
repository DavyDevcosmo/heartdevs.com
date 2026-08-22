<?php

declare(strict_types=1);

namespace He4rt\Portal;

use He4rt\Portal\Livewire\ArticlesPage;
use He4rt\Portal\Livewire\CommunityRetrospectivePage;
use He4rt\Portal\Livewire\HeroSection;
use He4rt\Portal\Livewire\Homepage;
use He4rt\Portal\Livewire\SocialLinksPage;
use He4rt\Portal\ShortLink\ShortLinkRedirectController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Head\HeadServiceProvider;
use Livewire\Livewire;

class PortalServiceProvider extends ServiceProvider
{
    /**
     * O laravel/head registra a macro Route::withHead() no boot dele, e o
     * autodiscovery ordena `he4rt/portal` antes de `laravel/head`. Registrar o
     * provider aqui o coloca na fila de boot à frente deste módulo, garantindo
     * que a macro exista quando as rotas abaixo forem declaradas.
     */
    public function register(): void
    {
        $this->app->register(HeadServiceProvider::class);
    }

    public function boot(): void
    {
        /*
         * O metadata de <head> mora na rota, não no componente: são páginas
         * semi-estáticas cujo título/description são conhecidos de antemão.
         * Os defaults (App\Support\Seo\SiteHead) preenchem o resto.
         */
        Route::get('/', Homepage::class)
            ->name('home')
            ->withHead(
                // `exact` evita o sufixo " - He4rt Developers" duplicar a marca na home.
                title: ['value' => 'He4rt Developers — Comunidade de desenvolvedores', 'exact' => true],
            );

        Route::get('/redes', SocialLinksPage::class)
            ->name('social-links')
            ->withHead(
                title: 'Nossas redes',
                description: 'Todos os canais oficiais da He4rt Developers: Discord, GitHub, LinkedIn, Instagram, X e WhatsApp.',
            );

        Route::get('/artigos', ArticlesPage::class)
            ->name('articles')
            ->withHead(
                title: 'Artigos da comunidade',
                description: 'Os artigos publicados pela organização He4rt Developers no dev.to, por tema e por quem escreveu.',
            );

        Route::get('/comunidade/retrospectiva', CommunityRetrospectivePage::class)
            ->name('community.retrospective')
            ->withHead(
                title: 'Quem fez a He4rt bater',
                description: 'Retrospectiva das contribuições open source da comunidade He4rt Developers: pull requests, issues e reviews por pessoa e por repositório.',
                /*
                 * A página guarda os filtros na query string (#[Url]), o que
                 * geraria uma URL canônica diferente por combinação de filtro.
                 * Fixar o canonical consolida tudo na versão sem parâmetros.
                 */
                canonical: '/comunidade/retrospectiva',
            );

        /*
         * A borda pública do encurtador (app-modules/marketing). O constraint em
         * minúsculo é intencional: slugs são canônicos em lowercase, então
         * `/l/Discord-A3F9K` simplesmente não casa a rota e cai no 404 do
         * framework — mesma parede que um slug morto, sem custo de resolução.
         *
         * O withHead aqui não é sobre indexar: o caminho feliz é um 302 sem
         * HTML, onde o metadata é inerte. Ele existe pelo caminho triste, o
         * único que renderiza página — sem isso, os defaults do portal marcariam
         * cada slug morto como `index, follow`, e o título seria o genérico do
         * site. `noindex, follow` espelha o default de erro do SiteHead.
         */
        Route::get('/l/{slug}', ShortLinkRedirectController::class)
            ->where('slug', '[a-z0-9-]+')
            ->name('short-link.redirect')
            ->withHead(
                title: 'Link indisponível',
                description: 'O link curto que você abriu não está mais disponível.',
                robots: ['noindex', 'follow'],
                /*
                 * O canonical default é a URL corrente, o que escreveria o slug
                 * dentro do <head> — e faria a resposta de um slug morto diferir
                 * da de outro. Fixá-lo mantém as quatro páginas mortas byte a
                 * byte idênticas, que é a propriedade que impede a enumeração.
                 */
                canonical: '/',
            );

        Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

        Livewire::component('hero-section', HeroSection::class);
    }
}
