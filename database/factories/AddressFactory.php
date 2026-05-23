<?php

declare(strict_types=1);

namespace Database\Factories;

final class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'country' => 'BR',
            'state' => 'SP',
            'city' => fake()->city(),
            'zip_code' => fake()->postcode(),
        ];
    }

    public function forModel(Model $model): self
    {
        return $this->state([
            'addressable_type' => $model->getMorphClass(),
            'addressable_id' => $model->getKey(),
        ]);
    }

    public function forUser(User $user): self
    {
        return $this->state([
            'addressable_type' => 'user',
            'addressable_id' => $user->id,
        ]);
    }
}
