<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\ValueObjects;

use Illuminate\Support\Str;

final readonly class UtmParameters
{
    public function __construct(
        public ?string $source = null,
        public ?string $medium = null,
        public ?string $campaign = null,
        public ?string $term = null,
        public ?string $content = null,
    ) {}

    /**
     * Aceita tanto as chaves canônicas (`utm_source`) quanto o nome curto (`source`),
     * porque o formulário do painel e o jsonb persistido não falam a mesma língua.
     *
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            source: self::read($data, 'source'),
            medium: self::read($data, 'medium'),
            campaign: self::read($data, 'campaign'),
            term: self::read($data, 'term'),
            content: self::read($data, 'content'),
        );
    }

    /**
     * Monta a URL final. Precedência, do mais forte pro mais fraco:
     * 1. o que já está na URL de destino cadastrada  (staff escreveu de propósito)
     * 2. o que veio na query da URL curta            (quem clicou trouxe)
     * 3. o UTM configurado no link                   (preenche só o que faltou)
     *
     * @param  array<string, mixed>  $incoming  query params que chegaram na URL curta
     */
    public function appendTo(string $destination, array $incoming = []): string
    {
        $configured = $this->filled();
        $carried = $this->filled($incoming);

        $hasNothingToAppend = $configured === [] && $carried === [];

        if ($hasNothingToAppend) {
            return $destination;
        }

        $components = parse_url($destination);

        if (!is_array($components)) {
            return $destination;
        }

        $existing = [];
        parse_str(is_string($components['query'] ?? null) ? $components['query'] : '', $existing);

        $query = $existing;

        foreach ([$carried, $configured] as $weaker) {
            foreach ($weaker as $key => $value) {
                if (!array_key_exists($key, $query)) {
                    $query[$key] = $value;
                }
            }
        }

        if ($query === []) {
            return $destination;
        }

        $base = Str::of($destination)->before('#')->before('?')->toString();

        $fragment = is_string($components['fragment'] ?? null)
            ? '#'.$components['fragment']
            : '';

        return $base.'?'.http_build_query($query, encoding_type: PHP_QUERY_RFC3986).$fragment;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'utm_source' => $this->source,
            'utm_medium' => $this->medium,
            'utm_campaign' => $this->campaign,
            'utm_term' => $this->term,
            'utm_content' => $this->content,
        ];
    }

    public function isEmpty(): bool
    {
        return $this->filled() === [];
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private static function read(array $data, string $name): ?string
    {
        $canonical = $data['utm_'.$name] ?? null;

        return self::normalize($canonical ?? $data[$name] ?? null);
    }

    private static function normalize(mixed $value): ?string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return null;
        }

        $value = mb_trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Só os pares com valor de verdade, prontos pra virar query string.
     *
     * @param  array<array-key, mixed>|null  $source
     * @return array<string, string>
     */
    private function filled(?array $source = null): array
    {
        $filled = [];

        foreach ($source ?? $this->toArray() as $key => $value) {
            $key = (string) $key;

            if (is_array($value)) {
                continue;
            }

            $value = self::normalize($value);

            if ($value !== null) {
                $filled[$key] = $value;
            }
        }

        return $filled;
    }
}
