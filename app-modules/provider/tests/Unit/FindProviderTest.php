<?php

declare(strict_types=1);

use He4rt\Provider\Actions\FindProvider;
use He4rt\Provider\Actions\GetProviderById;
use He4rt\Provider\Entities\ProviderEntity;
use Heart\Shared\Application\TTL;
use Illuminate\Support\Facades\Cache;
use Mockery as m;

test('cached provider', function (): void {
    $cacheKey = 'provider-twitch-123';
    $getProviderStub = m::mock(GetProviderById::class);

    Cache::shouldReceive('remember')
        ->once()
        ->with($cacheKey, TTL::fromDays(2), m::type('closure'))
        ->andReturn(new ProviderEntity('1', '1', '1', '1', '1'));

    $action = new FindProvider($getProviderStub);

    $result = $action->handle('twitch', '123');

    expect($result)->toBeInstanceOf(ProviderEntity::class);
});
