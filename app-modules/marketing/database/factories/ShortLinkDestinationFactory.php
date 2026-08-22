<?php

declare(strict_types=1);

namespace He4rt\Marketing\Database\Factories;

use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkDestination;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ShortLinkDestination> */
final class ShortLinkDestinationFactory extends Factory
{
    protected $model = ShortLinkDestination::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'short_link_id' => ShortLink::factory(),
            'destination_url' => fake()->url(),
            'utm' => null,
            'changed_by' => null,
            'valid_from' => now(),
            'valid_until' => null,
        ];
    }

    /** Intervalo já fechado — um destino que o link teve no passado. */
    public function superseded(): static
    {
        return $this->state([
            'valid_from' => now()->subMonth(),
            'valid_until' => now()->subDay(),
        ]);
    }
}
