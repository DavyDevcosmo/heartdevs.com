<?php

declare(strict_types=1);

use He4rt\Identity\Tenant\Models\Tenant;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Actions\ToggleAvailability;
use He4rt\Profile\Actions\UpsertProfile;
use He4rt\Profile\DTOs\UpsertProfileDTO;
use He4rt\Profile\Enums\StartAvailability;
use He4rt\Profile\Models\Profile;

test('atualiza todos os campos do perfil', function (): void {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
    ]);

    $dto = UpsertProfileDTO::fromArray([
        'headline' => 'Backend Developer',
        'seniority_level' => 'mid',
        'years_experience' => 5,
        'about' => 'Dev PHP',
        'nickname' => 'Dan',
    ]);

    $updated = resolve(UpsertProfile::class)->handle($profile, $dto);

    expect($updated->headline)->toBe('Backend Developer')
        ->and($updated->nickname)->toBe('Dan')
        ->and($updated->about)->toBe('Dev PHP')
        ->and($updated->years_experience)->toBe(5);
});

test('atualiza parcialmente apenas o headline', function (): void {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'tenant_id' => $tenant->id,
        'nickname' => 'Dan',
        'about' => 'Dev PHP',
        'headline' => 'Old Headline',
    ]);

    $dto = UpsertProfileDTO::fromArray(['headline' => 'Senior Dev']);

    $updated = resolve(UpsertProfile::class)->handle($profile, $dto);

    expect($updated->headline)->toBe('Senior Dev')
        ->and($updated->nickname)->toBe('Dan')
        ->and($updated->about)->toBe('Dev PHP');
});

test('rejeita bio acima de 500 caracteres', function (): void {
    $profile = Profile::factory()->create();
    $dto = UpsertProfileDTO::fromArray(['about' => str_repeat('a', 501)]);

    expect(fn () => resolve(UpsertProfile::class)->handle($profile, $dto))
        ->toThrow(InvalidArgumentException::class);
});

test('rejeita headline acima de 100 caracteres', function (): void {
    $profile = Profile::factory()->create();
    $dto = UpsertProfileDTO::fromArray(['headline' => str_repeat('a', 101)]);

    expect(fn () => resolve(UpsertProfile::class)->handle($profile, $dto))
        ->toThrow(InvalidArgumentException::class);
});

test('salva social_links com plataformas validas', function (): void {
    $profile = Profile::factory()->create();
    $dto = UpsertProfileDTO::fromArray([
        'social_links' => ['instagram' => '@dan', 'website' => 'https://dan.dev'],
    ]);

    $updated = resolve(UpsertProfile::class)->handle($profile, $dto);

    expect($updated->social_links)->toMatchArray(['instagram' => '@dan', 'website' => 'https://dan.dev']);
});

test('rejeita social_links com plataforma invalida', function (): void {
    $profile = Profile::factory()->create();
    $dto = UpsertProfileDTO::fromArray([
        'social_links' => ['tiktok' => '@dan'],
    ]);

    expect(fn () => resolve(UpsertProfile::class)->handle($profile, $dto))
        ->toThrow(InvalidArgumentException::class);
});

test('rejeita years_experience fora do range', function (): void {
    $profile = Profile::factory()->create();
    $dto = UpsertProfileDTO::fromArray(['years_experience' => 51]);

    expect(fn () => resolve(UpsertProfile::class)->handle($profile, $dto))
        ->toThrow(InvalidArgumentException::class);
});

test('ativa disponibilidade com prazo', function (): void {
    $profile = Profile::factory()->create(['available_for_proposals' => false]);

    $updated = resolve(ToggleAvailability::class)->handle($profile, true, StartAvailability::Immediate);

    expect($updated->available_for_proposals)->toBeTrue()
        ->and($updated->start_availability)->toBe(StartAvailability::Immediate);
});

test('rejeita ativar disponibilidade sem prazo', function (): void {
    $profile = Profile::factory()->create(['available_for_proposals' => false]);

    expect(fn () => resolve(ToggleAvailability::class)->handle($profile, true))
        ->toThrow(InvalidArgumentException::class);
});

test('desativar disponibilidade mantem prazo anterior', function (): void {
    $profile = Profile::factory()->create([
        'available_for_proposals' => true,
        'start_availability' => StartAvailability::OneWeek,
    ]);

    $updated = resolve(ToggleAvailability::class)->handle($profile, false);

    expect($updated->available_for_proposals)->toBeFalse()
        ->and($updated->start_availability)->toBe(StartAvailability::OneWeek);
});

test('altera prazo sem mudar disponibilidade', function (): void {
    $profile = Profile::factory()->create([
        'available_for_proposals' => true,
        'start_availability' => StartAvailability::Immediate,
    ]);

    $updated = resolve(ToggleAvailability::class)->handle($profile, true, StartAvailability::TwoWeeks);

    expect($updated->start_availability)->toBe(StartAvailability::TwoWeeks)
        ->and($updated->available_for_proposals)->toBeTrue();
});
