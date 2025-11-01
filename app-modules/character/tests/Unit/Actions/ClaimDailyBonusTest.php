<?php

declare(strict_types=1);

use He4rt\Character\Actions\ClaimDailyBonus;
use He4rt\Character\Actions\FindCharacterIdByUserId;
use He4rt\Character\Actions\PersistDailyBonus;
use He4rt\Character\Tests\Unit\ProviderProviderTrait;
use He4rt\Provider\Actions\FindProvider;
use Mockery as m;

uses(ProviderProviderTrait::class);

beforeEach(function (): void {
    $this->persistDailyStub = m::mock(PersistDailyBonus::class);
    $this->findProviderStub = m::mock(FindProvider::class);
    $this->findCharacterIdByUserId = m::mock(FindCharacterIdByUserId::class);
    $this->providerEntity = $this->validProviderEntity();
});
afterEach(function (): void {
    m::close();
});
test('claim daily bonus success', function (): void {
    $this->findProviderStub
        ->shouldReceive('handle')
        ->with('canhassi-provider', 'canhassi-id')
        ->once()
        ->andReturn($this->providerEntity);

    $this->findCharacterIdByUserId
        ->shouldReceive('handle')
        ->with($this->providerEntity->userId)
        ->once()
        ->andReturn('character-id');

    $this->persistDailyStub
        ->shouldReceive('handle')
        ->with('character-id')
        ->once();

    $test = new ClaimDailyBonus(
        $this->persistDailyStub,
        $this->findProviderStub,
        $this->findCharacterIdByUserId
    );

    $test->handle('canhassi-provider', 'canhassi-id');
});
