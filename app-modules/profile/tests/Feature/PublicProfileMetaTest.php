<?php

declare(strict_types=1);

use He4rt\Identity\User\Models\User;
use He4rt\Profile\Actions\BuildPublicProfile;
use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\WorkExperience;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->withoutVite();
});

it('publishes the open graph card of a profile page', function (): void {
    $user = User::factory()->create(['name' => 'Daniel', 'username' => 'danielhe4rt']);

    Profile::factory()->for($user)->create(['headline' => 'Developer Relations na He4rt']);

    $this->get('/@danielhe4rt')
        ->assertOk()
        ->assertSee('<meta property="og:type" content="profile" />', escape: false)
        ->assertSee('<meta property="og:url" content="'.url('/@danielhe4rt').'" />', escape: false)
        ->assertSee('<link rel="canonical" href="'.url('/@danielhe4rt').'" />', escape: false)
        ->assertSee('<meta property="og:site_name" content="'.e(config('app.name')).'" />', escape: false)
        ->assertSee('<meta property="og:title" content="Daniel - '.e(config('app.name')).'" />', escape: false)
        ->assertSee('<meta property="og:description" content="Developer Relations na He4rt" />', escape: false)
        ->assertSee('<meta name="description" content="Developer Relations na He4rt" />', escape: false);
});

it('falls back to the current role when there is no headline', function (): void {
    $user = User::factory()->create(['username' => 'sem-headline']);
    $profile = Profile::factory()->for($user)->create([
        'headline' => null,
        // Filled on purpose: the role has to win over the bio, not just over nothing.
        'about' => 'Bio que não deve ganhar do cargo atual.',
    ]);

    WorkExperience::factory()->for($profile)->create([
        'position' => 'Backend Engineer',
        'company_name' => 'He4rt',
        'is_currently_working_here' => true,
    ]);

    $this->get('/@sem-headline')
        ->assertOk()
        ->assertSee('<meta property="og:description" content="Backend Engineer · He4rt" />', escape: false);
});

it('falls back to the bio when there is no headline nor current role', function (): void {
    $user = User::factory()->create(['username' => 'so-bio']);

    Profile::factory()->for($user)->create([
        'headline' => null,
        'about' => 'Escrevo Go e cuido de comunidade.',
    ]);

    $this->get('/@so-bio')
        ->assertOk()
        ->assertSee('<meta property="og:description" content="Escrevo Go e cuido de comunidade." />', escape: false);
});

it('falls back to a plain sentence on an empty profile', function (): void {
    User::factory()->create(['name' => 'Fulano', 'username' => 'vazio']);

    $this->get('/@vazio')
        ->assertOk()
        ->assertSee(
            '<meta property="og:description" content="Fulano na comunidade He4rt Developers." />',
            escape: false,
        );
});

it('trims a long bio down to a card-sized description', function (): void {
    $user = User::factory()->create(['username' => 'prolixo']);

    Profile::factory()->for($user)->create([
        'headline' => null,
        'about' => str_repeat('a', 300),
    ]);

    $this->get('/@prolixo')
        ->assertOk()
        ->assertSee('<meta property="og:description" content="'.str_repeat('a', 157).'..." />', escape: false);
});

it('shares the cover image when there is one', function (): void {
    Storage::fake('public');

    $user = User::factory()->create(['username' => 'com-capa']);
    $user->addMediaFromString('fake-png')
        ->usingFileName('capa.png')
        ->toMediaCollection('cover');

    // Asserting the tag itself, not just the file name: the cover also shows up
    // in the header, so a plain assertSee would pass on the avatar too.
    $this->get('/@com-capa')
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.e($user->getFirstMediaUrl('cover')).'" />', escape: false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image" />', escape: false);
});

it('shares the avatar when there is no cover', function (): void {
    $user = User::factory()->create(['username' => 'sem-capa']);

    $avatarUrl = resolve(BuildPublicProfile::class)->handle($user)->avatarUrl;

    $this->get('/@sem-capa')
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.e($avatarUrl).'" />', escape: false);
});
