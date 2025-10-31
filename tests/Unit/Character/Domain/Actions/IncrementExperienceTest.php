<?php

declare(strict_types=1);
use Heart\Character\Domain\Actions\FindCharacter;
use Heart\Character\Domain\Actions\IncrementExperience;
use Heart\Character\Domain\Entities\CharacterEntity;
use Heart\Character\Domain\Repositories\CharacterRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Character\CharacterProviderTrait::class);

beforeEach(function () {
    $this->characterRepositoryStub = m::mock(CharacterRepository::class);
    $this->findCharacterStub = m::mock(FindCharacter::class);
    $this->characterEntity = $this->validCharacterEntity();
});
afterEach(function () {
    m::close();
});
test('increment experience success', function () {
    $this->characterRepositoryStub
        ->shouldReceive('updateExperience')
        ->with($this->characterEntity)
        ->once()
        ->andReturn($this->characterEntity);

    $this->findCharacterStub
        ->shouldReceive('handle')
        ->with($this->characterEntity->id)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new IncrementExperience($this->characterRepositoryStub, $this->findCharacterStub);

    $test->incrementByTextMessage($this->characterEntity->id, 'CONGRATS!!');
});
