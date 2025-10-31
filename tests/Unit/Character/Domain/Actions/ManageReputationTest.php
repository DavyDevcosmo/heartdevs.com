<?php

declare(strict_types=1);
use Heart\Character\Domain\Actions\ManageReputation;
use Heart\Character\Domain\Actions\PersistDailyBonus;
use Heart\Character\Domain\Repositories\CharacterRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Character\CharacterProviderTrait::class);

beforeEach(function () {
    $this->characterRepository = m::mock(CharacterRepository::class);
    $this->manageReputation = new ManageReputation($this->characterRepository);
});
afterEach(function () {
    m::close();
});
test('add reputation', function () {
    $character = $this->validCharacterEntity();
    $characterId = 'porra-careca';

    $this->characterRepository
        ->shouldReceive('findById')
        ->once()
        ->with($characterId)
        ->andReturn($character);

    $this->characterRepository
        ->shouldReceive('updateReputation')
        ->once()
        ->with($character);

    $this->manageReputation->handle($characterId, 'increment');
});
