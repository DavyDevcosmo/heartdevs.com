<?php

declare(strict_types=1);

namespace He4rt\Squads\Actions;

use He4rt\Identity\Tenant\Models\Tenant;
use He4rt\Identity\User\Models\User;
use He4rt\Squads\Enums\SquadStatus;
use He4rt\Squads\Models\Squad;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

final class CreateSquad
{
    public function handle(User $actor, Tenant $tenant, string $name, ?string $objective = null): Squad
    {
        throw_unless($actor->isAdmin(), AuthorizationException::class);

        /** @var Squad $squad */
        $squad = Squad::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $name,
            'slug' => $this->uniqueSlug($tenant, $name),
            'objective' => $objective,
            'status' => SquadStatus::Draft,
        ]);

        return $squad;
    }

    private function uniqueSlug(Tenant $tenant, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (Squad::query()
            ->whereBelongsTo($tenant)
            ->where('slug', $slug)
            ->exists()) {
            $slug = sprintf('%s-%d', $baseSlug, $suffix);
            $suffix++;
        }

        return $slug;
    }
}
