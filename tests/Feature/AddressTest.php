<?php

declare(strict_types=1);

use App\Models\Address;
use He4rt\Identity\User\Models\User;

it('user tem um endereço polimórfico', function (): void {
    $user = User::factory()->create();

    Address::factory()->forUser($user)->create([
        'country' => 'BR',
        'state' => 'SP',
        'city' => 'São Paulo',
    ]);

    $address = $user->fresh()->address;

    expect($address)
        ->not->toBeNull()
        ->addressable_type->toBe('user')
        ->addressable_id->toBe($user->id)
        ->country->toBe('BR')
        ->state->toBe('SP');
});

it('user sem endereço retorna null', function (): void {
    $user = User::factory()->create();

    expect($user->address)->toBeNull();
});

it('deletar user deleta address via cascade', function (): void {
    $user = User::factory()->create();

    Address::factory()->forUser($user)->create();

    expect(Address::query()->where('addressable_id', $user->id)->exists())->toBeTrue();

    $user->delete();

    expect(Address::query()->where('addressable_id', $user->id)->exists())->toBeFalse();
});

it('factory cria address válido para user', function (): void {
    $user = User::factory()->create();

    $address = Address::factory()->forUser($user)->create();

    expect($address)
        ->addressable_type->toBe('user')
        ->addressable_id->toBe($user->id)
        ->country->toBe('BR')
        ->state->toBe('SP');
});
