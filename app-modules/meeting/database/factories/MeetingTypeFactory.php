<?php

declare(strict_types=1);

namespace He4rt\Meeting\Database\Factories;

use He4rt\Meeting\Models\MeetingType;
use Illuminate\Database\Eloquent\Factories\Factory;

final class MeetingTypeFactory extends Factory
{
    protected $model = MeetingType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'week_day' => $this->faker->numberBetween(0, 6),
            'start_at' => $this->faker->numberBetween(0, 1439),
        ];
    }
}
