<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\ProfileProject;
use He4rt\Profile\Models\WorkExperience;
use He4rt\Profile\Support\PublicProfileCache;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    $this->withoutVite();
});

function visitProfile(string $username): TestResponse
{
    app()->forgetScopedInstances();

    return test()->get('/@'.$username);
}

function queriesWhileVisiting(string $username): int
{
    $count = 0;

    DB::listen(function (QueryExecuted $query) use (&$count): void {
        $count++;
    });

    visitProfile($username)->assertOk();

    return $count;
}

it('serves a second visit without touching the database', function (): void {
    $user = User::factory()->create(['username' => 'cacheado']);
    Profile::factory()->for($user)->create(['headline' => 'Backend Engineer']);

    expect(queriesWhileVisiting('cacheado'))->toBeGreaterThan(0);
    expect(queriesWhileVisiting('cacheado'))->toBe(1);
});

it('keys the cache by user id, so renaming leaves no orphan entry', function (): void {
    $user = User::factory()->create(['username' => 'antigo']);

    visitProfile('antigo')->assertOk();

    expect(Cache::has(PublicProfileCache::key((string) $user->getKey())))->toBeTrue();

    $user->update(['username' => 'novo']);

    visitProfile('novo')->assertOk();

    expect(Cache::has(PublicProfileCache::key((string) $user->getKey())))->toBeTrue();
});

it('drops the cache when the profile itself changes', function (): void {
    $user = User::factory()->create(['username' => 'editor']);
    $profile = Profile::factory()->for($user)->create(['headline' => 'Antes']);

    visitProfile('editor')->assertOk()->assertSee('Antes');

    $profile->update(['headline' => 'Depois']);

    visitProfile('editor')
        ->assertOk()
        ->assertSee('Depois')
        ->assertDontSee('Antes');
});

it('drops the cache when a profile-owned row changes', function (string $model, array $attributes, string $before, string $after): void {
    $user = User::factory()->create(['username' => 'dono']);
    $profile = Profile::factory()->for($user)->create();

    $row = $model::factory()->for($profile)->create($attributes);

    visitProfile('dono')->assertOk()->assertSee($before);

    $row->update([array_key_first($attributes) => $after]);

    visitProfile('dono')->assertOk()->assertSee($after)->assertDontSee($before);
})->with([
    'work experience' => [
        WorkExperience::class,
        ['company_name' => 'Empresa Antiga'],
        'Empresa Antiga',
        'Empresa Nova',
    ],
    'project' => [
        ProfileProject::class,
        ['name' => 'Projeto Antigo'],
        'Projeto Antigo',
        'Projeto Novo',
    ],
]);

it('drops the cache when a profile-owned row is deleted', function (): void {
    $user = User::factory()->create(['username' => 'apagador']);
    $profile = Profile::factory()->for($user)->create();

    $project = ProfileProject::factory()->for($profile)->create(['name' => 'Some Sumido']);

    visitProfile('apagador')->assertOk()->assertSee('Some Sumido');

    $project->delete();

    visitProfile('apagador')->assertOk()->assertDontSee('Some Sumido');
});
