<?php

declare(strict_types=1);

namespace He4rt\Provider\Database\Factories;

use He4rt\Provider\Models\Provider;
use Heart\User\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'user_id' => User::factory(),
            'provider' => fake()->randomElement(['twitch', 'discord']),
            'provider_id' => fake()->randomNumber(6),
            'email' => fake()->unique()->email(),
        ];
    }
}
