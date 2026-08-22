<?php

declare(strict_types=1);

namespace He4rt\Portal\Articles\DTOs;

use Carbon\CarbonImmutable;

final readonly class Article
{
    /**
     * @param  list<string>  $tags
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $url,
        public CarbonImmutable $publishedAt,
        public int $reactions,
        public int $comments,
        public int $readingMinutes,
        public ?string $coverImage,
        public array $tags,
        public string $authorName,
        public string $authorUsername,
        public string $authorAvatar,
    ) {}

    /**
     * Devolve null quando o item não sustenta as duas invariantes de um artigo
     * exibível: título e data. Sem data não há lugar na ordenação nem na janela
     * de 12 meses do destaque.
     *
     * @param  array<array-key, mixed>  $payload
     */
    public static function fromApi(array $payload): ?self
    {
        $data = fluent($payload);

        $title = $data->string('title')->squish()->toString();
        $publishedAt = $data->date('published_at')?->toImmutable();

        if ($title === '' || !$publishedAt instanceof CarbonImmutable) {
            return null;
        }

        return new self(
            id: $data->integer('id'),
            title: $title,
            description: $data->string('description')->squish()->toString(),
            url: $data->string('url')->toString(),
            publishedAt: $publishedAt,
            reactions: $data->integer('positive_reactions_count'),
            comments: $data->integer('comments_count'),
            readingMinutes: $data->integer('reading_time_minutes'),
            // A API devolve null em artigos sem capa — a view cai no fallback `</>`.
            coverImage: $data->string('cover_image')->toString() ?: null,
            tags: array_values(
                $data->collect('tag_list')
                    ->filter(fn (mixed $tag): bool => is_string($tag))
                    ->all(),
            ),
            authorName: $data->string('user.name')->toString(),
            authorUsername: $data->string('user.username')->toString(),
            authorAvatar: $data->string('user.profile_image_90')->toString(),
        );
    }

    public function publishedLabel(): string
    {
        return $this->publishedAt
            ->timezone(config()->string('app.display_timezone'))
            ->translatedFormat('M \d\e Y');
    }
}
