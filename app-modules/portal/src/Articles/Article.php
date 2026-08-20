<?php

declare(strict_types=1);

namespace He4rt\Portal\Articles;

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
     * O corpo devolvido pela API é `array<array-key, mixed>` — nunca a forma que
     * esperamos —, então cada campo é lido com guarda e default próprios.
     *
     * @param  array<array-key, mixed>  $payload
     */
    public static function fromApi(array $payload): self
    {
        /** @var array<string, mixed> $user */
        $user = is_array($payload['user'] ?? null) ? $payload['user'] : [];

        /** @var list<string> $tags */
        $tags = array_values(array_filter(
            is_array($payload['tag_list'] ?? null) ? $payload['tag_list'] : [],
            is_string(...),
        ));

        return new self(
            id: (int) ($payload['id'] ?? 0),
            title: (string) ($payload['title'] ?? ''),
            description: (string) ($payload['description'] ?? ''),
            url: self::safeUrl($payload['url'] ?? null),
            publishedAt: CarbonImmutable::parse((string) ($payload['published_at'] ?? 'now')),
            reactions: (int) ($payload['positive_reactions_count'] ?? 0),
            comments: (int) ($payload['comments_count'] ?? 0),
            readingMinutes: (int) ($payload['reading_time_minutes'] ?? 0),
            // A API devolve null em artigos sem capa — a view cai no fallback `</>`.
            coverImage: self::safeUrl($payload['cover_image'] ?? null) ?: null,
            tags: $tags,
            authorName: (string) ($user['name'] ?? ''),
            authorUsername: (string) ($user['username'] ?? ''),
            authorAvatar: self::safeUrl($user['profile_image_90'] ?? null),
        );
    }

    public function publishedLabel(): string
    {
        return $this->publishedAt
            ->timezone(config()->string('app.display_timezone'))
            ->translatedFormat('M \d\e Y');
    }

    /**
     * O acervo é payload de terceiro e vai direto para `href`/`src`. Um `javascript:`
     * vindo de uma resposta adulterada viraria XSS que o escape do Blade não pega,
     * porque o problema é o esquema, não os caracteres.
     */
    private static function safeUrl(mixed $value): string
    {
        if (!is_string($value) || $value === '') {
            return '';
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], strict: true) ? $value : '';
    }
}
