<?php

declare(strict_types=1);
use Heart\Character\Domain\Actions\FindCharacter;
use Heart\Character\Domain\Entities\CharacterEntity;
use Heart\Character\Domain\Repositories\CharacterRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Character\CharacterProviderTrait::class);

beforeEach(function () {
    $this->characterRepositoryStub = m::mock(CharacterRepository::class);
    $this->characterEntity = $this->validCharacterEntity();
});
afterEach(function () {
    m::close();
});
test('find character success', function () {
    $this->characterRepositoryStub
        ->shouldReceive('findById')
        ->with($this->characterEntity->id)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new FindCharacter($this->characterRepositoryStub);

    $test->handle($this->characterEntity->id);
});
