<?php

declare(strict_types=1);

namespace He4rt\Activity\Database\Factories;

use He4rt\Activity\Tracking\Enums\ActivityType;
use He4rt\Activity\Tracking\Enums\AttributionMethod;
use He4rt\Activity\Tracking\Models\Interaction;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use He4rt\Identity\ExternalIdentity\Models\ExternalIdentity;
use He4rt\Identity\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interaction>
 */
final class InteractionFactory extends Factory
{
    protected $model = Interaction::class;

    public function definition(): array
    {
        $identity = ExternalIdentity::factory()
            ->for(User::factory(), 'model')
            ->state(['provider' => IdentityProvider::DevTo]);

        return [
            'external_identity_id' => $identity,
            'user_id' => fn (array $attributes): string => ExternalIdentity::query()
                ->findOrFail($attributes['external_identity_id'])
                ->model_id,
            'type' => ActivityType::Article,
            'attributed_by' => AttributionMethod::Owned,
            'external_ref' => fn (): string => 'devto:article:'.fake()->unique()->randomNumber(7),
            'occurred_at' => now(),
        ];
    }

    public function forIdentity(ExternalIdentity $identity): self
    {
        return $this->state([
            'external_identity_id' => $identity->id,
            'user_id' => $identity->model_id,
        ]);
    }

    public function ofType(ActivityType $type): self
    {
        return $this->state([
            'type' => $type,
            'external_ref' => 'github:'.$type->value.':he4rt/heartdevs.com:'.fake()->unique()->randomNumber(6),
        ]);
    }

    public function hidden(): self
    {
        return $this->state([
            'hidden_at' => now(),
        ]);
    }
}
