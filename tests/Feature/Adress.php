<?php

declare(strict_types=1);

use App\Models\Address;
use He4rt\Events\Models\EventModel;
use He4rt\Identity\User\Models\User;

it('user tem um endereço polimórfico', function (): void {
    $user = User::factory()->create();

    $address = Address::factory()->forUser($user)->create([
        'country' => 'BR',
        'state' => 'SP',
        'city' => 'São Paulo',
    ]);

    expect($user->fresh()->address)->not->toBeNull();
    expect($user->fresh()->address->addressable_type)->toBe('user');
    expect($user->fresh()->address->addressable_id)->toBe($user->id);
    expect($user->fresh()->address->country)->toBe('BR');
    expect($user->fresh()->address->state)->toBe('SP');
});

it('múltiplas entidades podem ter endereços distintos', function (): void {
    $user = User::factory()->create();
    $event = EventModel::factory()->create();

    Address::factory()->forUser($user)->create();
    Address::factory()->forModel($event)->create();

    $userAddress = Address::query()->where('addressable_id', $user->id)->first();
    $eventAddress = Address::query()->where('addressable_id', $event->id)->first();

    expect($userAddress->addressable_type)->not->toBe($eventAddress->addressable_type);
    expect($userAddress->addressable_id)->not->toBe($eventAddress->addressable_id);
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

    expect($address->addressable_type)->toBe('user');
    expect($address->addressable_id)->toBe($user->id);
    expect($address->country)->toBe('BR');
    expect($address->state)->toBe('SP');
});
