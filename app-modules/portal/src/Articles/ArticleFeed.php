<?php

declare(strict_types=1);

namespace He4rt\Portal\Articles;

use Carbon\CarbonImmutable;
use He4rt\IntegrationDevTo\Polling\DevToApiClient;
use He4rt\Portal\Articles\DTOs\Article;
use He4rt\Portal\Articles\DTOs\ArticleAuthor;
use He4rt\Portal\Articles\DTOs\ArticleTopic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Read model do acervo de artigos publicados na organização do dev.to.
 *
 * A fonte é a API do dev.to. O módulo `contents` já espelha esse acervo em
 * banco, incluindo autores sem identidade vinculada; migrar esta leitura para
 * lá é trabalho pendente.
 */
final class ArticleFeed
{
    private const string CACHE_KEY = 'portal.articles.devto-org';

    /** @var list<Article>|null */
    private ?array $articles = null;

    public function __construct(
        private readonly DevToApiClient $client,
    ) {}

    /** @return list<Article> */
    public function articles(): array
    {
        if ($this->articles !== null) {
            return $this->articles;
        }

        $payload = $this->fetch();

        $articles = [];
        $rejected = 0;

        foreach ($payload as $item) {
            $article = is_array($item) ? Article::fromApi($item) : null;

            if (!$article instanceof Article) {
                $rejected++;

                continue;
            }

            $articles[] = $article;
        }

        if ($rejected > 0) {
            Log::warning('Portal: itens do acervo do dev.to descartados por falta de título ou data', [
                'descartados' => $rejected,
                'aceitos' => count($articles),
            ]);
        }

        // Payload com itens, nenhum aproveitável: contrato quebrado, não acervo
        // vazio. Sem descartar o cache, a janela obsoleta serviria o mesmo lixo
        // por um dia inteiro.
        if ($articles === [] && $payload !== []) {
            Cache::forget(self::CACHE_KEY);
        }

        usort($articles, fn (Article $a, Article $b): int => $b->publishedAt <=> $a->publishedAt);

        return $this->articles = $articles;
    }

    /**
     * Ordenados por volume e, no empate, por alcance. As duas métricas divergem
     * de propósito — quem escreveu uma vez pode ter mais reações que quem
     * escreveu quatro —, e a coluna de pessoas mostra as duas.
     *
     * @return list<ArticleAuthor>
     */
    public function authors(): array
    {
        return Collection::make($this->articles())
            ->groupBy(fn (Article $article): string => $article->authorUsername)
            ->map(function (Collection $items, string $username): ArticleAuthor {
                /** @var Article $first */
                $first = $items->first();

                return new ArticleAuthor(
                    username: $username,
                    name: $first->authorName !== '' ? $first->authorName : $username,
                    avatar: $first->authorAvatar,
                    articleCount: $items->count(),
                    reactions: (int) $items->sum(fn (Article $article): int => $article->reactions),
                );
            })
            ->sortByDesc(fn (ArticleAuthor $author): int => $author->articleCount * 100_000 + $author->reactions)
            ->pipe(fn (Collection $authors): array => array_values($authors->all()));
    }

    /** @return list<ArticleTopic> */
    public function topics(): array
    {
        return Collection::make($this->articles())
            ->flatMap(fn (Article $article): array => $article->tags)
            ->countBy()
            ->map(fn (int $count, string $tag): ArticleTopic => new ArticleTopic($tag, $count))
            ->sortByDesc(fn (ArticleTopic $topic): int => $topic->count)
            ->pipe(fn (Collection $topics): array => array_values($topics->all()));
    }

    /**
     * @return array{articles: int, authors: int, topics: int, reactions: int}
     */
    public function stats(): array
    {
        return [
            'articles' => count($this->articles()),
            'authors' => count($this->authors()),
            'topics' => count($this->topics()),
            'reactions' => (int) Collection::make($this->articles())->sum(fn (Article $article): int => $article->reactions),
        ];
    }

    /**
     * O mais reagido dos últimos 12 meses, não o campeão histórico: o campeão é
     * um guia de 2023 já usado como CTA em outro lugar do site, e repeti-lo aqui
     * gastaria a primeira dobra com algo conhecido.
     */
    public function highlight(): ?Article
    {
        $cutoff = CarbonImmutable::now()->subYear();

        $recent = Collection::make($this->articles())
            ->filter(fn (Article $article): bool => $article->publishedAt->greaterThanOrEqualTo($cutoff));

        $pool = $recent->isNotEmpty() ? $recent : Collection::make($this->articles());

        return $pool->sortByDesc(fn (Article $article): int => $article->reactions)->first();
    }

    /**
     * O acervo é de terceiro: uma queda do dev.to não pode derrubar uma página
     * institucional.
     *
     * `flexible` serve o valor fresco até o TTL e continua servindo o obsoleto
     * por até um dia enquanto revalida em segundo plano. Ninguém paga a latência
     * da revalidação, e uma queda depois do primeiro sucesso mantém a página com
     * conteúdo.
     *
     * @return array<array-key, mixed>
     */
    private function fetch(): array
    {
        $ttl = config()->integer('integration-devto.polling_interval_minutes');

        try {
            /** @var array<array-key, mixed> $payload */
            $payload = Cache::flexible(
                self::CACHE_KEY,
                [now()->addMinutes($ttl), now()->addDay()],
                function (): array {
                    $articles = $this->client->getArticlesByOrg(config()->string('integration-devto.org_slug'));

                    // Estourar aqui impede que uma resposta vazia entre em cache
                    // e preserva o valor anterior para a janela obsoleta servir.
                    throw_if($articles === [], RuntimeException::class, 'dev.to devolveu um acervo vazio');

                    return $articles;
                },
            );

            return $payload;
        } catch (Throwable $throwable) {
            Log::warning('Portal: acervo do dev.to indisponível', ['exception' => $throwable->getMessage()]);

            /** @var array<array-key, mixed> $stale */
            $stale = Cache::get(self::CACHE_KEY) ?? [];

            return $stale;
        }
    }
}
