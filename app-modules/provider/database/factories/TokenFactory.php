<?php

declare(strict_types=1);

namespace He4rt\Provider\Database\Factories;

use He4rt\Provider\Models\Provider;
use He4rt\Provider\Models\Token;
use Illuminate\Database\Eloquent\Factories\Factory;

final class TokenFactory extends Factory
{
    protected $model = Token::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'provider_id' => Provider::factory(),
            'access_token' => $this->faker->uuid(),
            'refresh_token' => $this->faker->uuid(),
            'expires_in' => $this->faker->randomNumber(4),
        ];
    }
}
