<?php

declare(strict_types=1);

use He4rt\Character\Actions\FindCharacter;
use He4rt\Character\Actions\IncrementExperience;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;
use Mockery as m;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepositoryStub = m::mock(CharacterRepository::class);
    $this->findCharacterStub = m::mock(FindCharacter::class);
    $this->characterEntity = $this->validCharacterEntity();
});
afterEach(function (): void {
    m::close();
});
test('increment experience success', function (): void {
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
