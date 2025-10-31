<?php

declare(strict_types=1);
use Heart\Character\Application\FindCharacterIdByUserId;
use Heart\Character\Domain\Actions\GetCharacterByUserId;
use Heart\Character\Domain\Entities\CharacterEntity;
use Mockery\MockInterface;
uses(\Tests\Unit\Character\CharacterProviderTrait::class);

beforeEach(function () {
    $this->getCharacterIdByUserId = m::mock(GetCharacterByUserId::class);
    $this->characterEntity = $this->validCharacterEntity();
});
afterEach(function () {
    m::close();
});
test('find character by user id', function () {
    $this->getCharacterIdByUserId
        ->shouldReceive('handle')
        ->with('canhassi-id')
        ->once()
        ->andReturn($this->characterEntity);

    $test = new FindCharacterIdByUserId($this->getCharacterIdByUserId);

    $test->handle('canhassi-id');
});
