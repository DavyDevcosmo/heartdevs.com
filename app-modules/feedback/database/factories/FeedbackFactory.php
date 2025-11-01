<?php

declare(strict_types=1);

namespace He4rt\Feedback\Database\Factories;

use He4rt\Feedback\Models\Feedback;
use Heart\User\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

final class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'sender_id' => User::factory(),
            'target_id' => User::factory(),
            'type' => $this->faker->randomElement(['compliment', 'improvement']),
            'message' => $this->faker->sentence(),
        ];
    }
}
