<?php

declare(strict_types=1);

namespace He4rt\Portal\Articles;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Página institucional do acervo de artigos.
 *
 * O componente só monta o read model. Recorte por tema e por pessoa, troca de
 * visualização e a lente de relação vivem no Alpine: a lente reage a hover e
 * não sobreviveria a um round-trip por evento.
 */
#[Layout(name: 'portal::components.layouts.app')]
final class ArticlesPage extends Component
{
    public function render(ArticleFeed $feed): View
    {
        return view('portal::articles', [
            'articles' => $feed->articles(),
            'authors' => $feed->authors(),
            'topics' => $feed->topics(),
            'stats' => $feed->stats(),
            'highlight' => $feed->highlight(),
        ]);
    }
}
