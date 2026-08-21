<?php

declare(strict_types=1);

use He4rt\Gamification\Badge\Models\Badge;
use He4rt\Gamification\Character\Models\Character;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Actions\BuildPublicProfile;
use He4rt\Profile\DTOs\ProfileBadgeData;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->withoutVite();
});

function characterFor(User $user, int $experience = 1_500): Character
{
    return Character::factory()->for($user)->create(['experience' => $experience]);
}

it('renders the level derived from the experience', function (): void {
    $user = User::factory()->create(['username' => 'danielhe4rt']);

    // 1500 XP is exactly the threshold for level 6.
    characterFor($user);

    $this->get('/@danielhe4rt')
        ->assertOk()
        ->assertSee('Comunidade')
        ->assertSee('Nível 6');
});

it('renders each earned badge with its name and description', function (): void {
    $user = User::factory()->create(['username' => 'colecionador']);
    $character = characterFor($user);

    $badge = Badge::factory()->create([
        'name' => 'Fundador',
        'description' => 'Esteve na He4rt desde o primeiro dia.',
    ]);

    $character->badges()->attach($badge, ['claimed_at' => now()]);

    $this->get('/@colecionador')
        ->assertOk()
        ->assertSee('Fundador')
        ->assertSee('Esteve na He4rt desde o primeiro dia.');
});

it('never publishes the badge redeem code', function (): void {
    $user = User::factory()->create(['username' => 'colecionador']);
    $character = characterFor($user);

    $badge = Badge::factory()->create([
        'name' => 'Fundador',
        'redeem_code' => 'HE4RT-SECRET-2026',
    ]);

    $character->badges()->attach($badge, ['claimed_at' => now()]);

    // The code is what anyone types to claim the badge for themselves: on a page
    // with no login in front of it, publishing it gives the badge away.
    $this->get('/@colecionador')
        ->assertOk()
        ->assertSee('Fundador')
        ->assertDontSee('HE4RT-SECRET-2026');
});

it('keeps the redeem code out of the DTO entirely', function (): void {
    $user = User::factory()->create();
    $character = characterFor($user);

    $character->badges()->attach(Badge::factory()->create(), ['claimed_at' => now()]);

    $badge = resolve(BuildPublicProfile::class)->handle($user)->badges[0];

    expect($badge)->toBeInstanceOf(ProfileBadgeData::class)
        ->and($badge)->not->toHaveProperty('redeem_code')
        ->and($badge)->not->toHaveProperty('redeemCode');
});

it('renders the badge image when the badge has one', function (): void {
    Storage::fake('public');

    $user = User::factory()->create(['username' => 'ilustrado']);
    $character = characterFor($user);

    $badge = Badge::factory()->create(['name' => 'Speaker']);
    $badge->addMediaFromString('fake-png')
        ->usingFileName('speaker.png')
        ->toMediaCollection('badge');

    $character->badges()->attach($badge, ['claimed_at' => now()]);

    $this->get('/@ilustrado')
        ->assertOk()
        ->assertSee('speaker.png');
});

it('renders a badge without an image', function (): void {
    $user = User::factory()->create(['username' => 'sem-imagem']);
    $character = characterFor($user);

    $character->badges()->attach(
        Badge::factory()->create(['name' => 'Beta Tester']),
        ['claimed_at' => now()],
    );

    expect(resolve(BuildPublicProfile::class)->handle($user)->badges[0]->imageUrl)->toBeNull();

    $this->get('/@sem-imagem')
        ->assertOk()
        ->assertSee('Beta Tester');
});

it('hides the whole community section when the member never played', function (): void {
    User::factory()->create(['username' => 'vazio']);

    $this->get('/@vazio')
        ->assertOk()
        ->assertDontSee('Comunidade')
        ->assertDontSee('Nível');
});
