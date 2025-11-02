<?php

declare(strict_types=1);

namespace He4rt\User\Database\Factories;

use He4rt\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'username' => fake()->userName(),
            'is_donator' => false,
        ];
    }
}
