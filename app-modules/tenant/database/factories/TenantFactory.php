<?php

declare(strict_types=1);

namespace He4rt\Tenant\Database\Factories;

use Illuminate\Support\Facades\Date;
use He4rt\Tenant\Models\Tenant;
use He4rt\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tenant> */
final class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->slug(),
            'active' => fake()->boolean(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'owner_id' => User::factory(),
        ];
    }
}
