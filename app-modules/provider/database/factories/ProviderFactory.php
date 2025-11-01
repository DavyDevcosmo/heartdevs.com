<?php

declare(strict_types=1);

namespace Heart\Provider\Database\Factories;

use Heart\Provider\Models\Provider;
use Heart\User\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(['twitch', 'discord']),
            'provider_id' => $this->faker->randomNumber(6),
            'email' => $this->faker->unique()->email,
        ];
    }
}
