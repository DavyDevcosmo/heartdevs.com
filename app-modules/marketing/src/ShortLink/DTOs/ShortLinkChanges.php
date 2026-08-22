<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\DTOs;

use Carbon\CarbonInterface;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Support\FormPayloadNormalizer;
use He4rt\Marketing\ShortLink\ValueObjects\TagList;
use He4rt\Marketing\ShortLink\ValueObjects\UtmParameters;

/**
 * A partial edit of a short link.
 *
 * Only the keys actually present are applied — that is what lets the caller say
 * "clear the expiry" (`expires_at => null` present) without it being confused
 * with "leave the expiry alone" (key absent). The distinction matters because
 * `UpdateShortLink` decides whether to close a destination interval by looking
 * at what this object carries.
 *
 * The slug is deliberately not editable: the whole point of the link is that
 * the printed/pasted URL never moves.
 */
final readonly class ShortLinkChanges
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    private function __construct(
        private array $attributes,
        public ?string $changedBy = null,
    ) {}

    /**
     * Explicit builder. `null` means "not changing"; use the `clear*` flags to
     * blank a nullable column on purpose.
     */
    public static function make(
        ?string $destinationUrl = null,
        ?UtmParameters $utm = null,
        ?TagList $tags = null,
        ?bool $active = null,
        ?CarbonInterface $expiresAt = null,
        bool $clearExpiresAt = false,
        ?string $changedBy = null,
    ): self {
        $attributes = [];

        if ($destinationUrl !== null) {
            $attributes['destination_url'] = $destinationUrl;
        }

        if ($utm instanceof UtmParameters) {
            $attributes['utm'] = $utm;
        }

        if ($tags instanceof TagList) {
            $attributes['tags'] = $tags;
        }

        if ($active !== null) {
            $attributes['active'] = $active;
        }

        if ($clearExpiresAt) {
            $attributes['expires_at'] = null;
        } elseif ($expiresAt instanceof CarbonInterface) {
            $attributes['expires_at'] = $expiresAt;
        }

        return new self($attributes, $changedBy);
    }

    /**
     * Built from a Filament form payload — every key the form submitted counts
     * as an intent, including the ones submitted as `null`.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromForm(array $data, ?string $changedBy = null): self
    {
        $attributes = [];

        if (array_key_exists('destination_url', $data) && is_string($data['destination_url'])) {
            $attributes['destination_url'] = $data['destination_url'];
        }

        $utm = FormPayloadNormalizer::utm($data);

        if ($utm instanceof UtmParameters) {
            $attributes['utm'] = $utm;
        }

        $tags = FormPayloadNormalizer::tags($data);

        if ($tags instanceof TagList) {
            $attributes['tags'] = $tags;
        }

        if (array_key_exists('active', $data)) {
            $attributes['active'] = (bool) $data['active'];
        }

        if (array_key_exists('expires_at', $data)) {
            $attributes['expires_at'] = FormPayloadNormalizer::date($data['expires_at']);
        }

        return new self($attributes, $changedBy);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return $this->attributes;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function isEmpty(): bool
    {
        return $this->attributes === [];
    }

    public function destinationUrl(): ?string
    {
        $value = $this->attributes['destination_url'] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Does this edit move where the link points?
     *
     * Only the destination URL and the appended UTM count — retagging a link is
     * bookkeeping, not a redirect change, and must not forge a history row.
     * Call this **before** filling the model, while it still holds the old values.
     */
    public function hasDestinationChange(ShortLink $link): bool
    {
        if ($this->has('destination_url') && $this->destinationUrl() !== $link->destination_url) {
            return true;
        }

        return $this->has('utm') && $this->utmToArray($this->attributes['utm']) !== $this->utmToArray($link->utm);
    }

    /**
     * @return array<string, string|null>
     */
    private function utmToArray(mixed $utm): array
    {
        if ($utm instanceof UtmParameters) {
            return $utm->toArray();
        }

        return UtmParameters::fromArray(is_array($utm) ? $utm : [])->toArray();
    }
}
