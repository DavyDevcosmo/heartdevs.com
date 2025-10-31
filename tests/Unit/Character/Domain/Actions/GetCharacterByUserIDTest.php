<?php

declare(strict_types=1);
use Heart\Character\Domain\Actions\GetCharacterByUserId;
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
test('get character by user id', function () {
    $this->characterRepositoryStub
        ->shouldReceive('findByUserId')
        ->with(12)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new GetCharacterByUserId($this->characterRepositoryStub);

    $test->handle(12);
});
