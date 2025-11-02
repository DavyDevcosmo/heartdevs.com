<?php

declare(strict_types=1);

use He4rt\Character\Actions\FindCharacter;
use He4rt\Character\Actions\IncrementExperience;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepositoryStub = Mockery::mock(CharacterRepository::class);
    $this->findCharacter = new FindCharacter($this->characterRepositoryStub);
    $this->characterEntity = $this->validCharacterEntity();
});

afterEach(function (): void {
    Mockery::close();
});

test('increment experience success', function (): void {
    $this->characterRepositoryStub
        ->shouldReceive('findById')
        ->with($this->characterEntity->id)
        ->once()
        ->andReturn($this->characterEntity);

    $this->characterRepositoryStub
        ->shouldReceive('updateExperience')
        ->with($this->characterEntity)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new IncrementExperience($this->characterRepositoryStub, $this->findCharacter);

    $test->incrementByTextMessage($this->characterEntity->id, 'CONGRATS!!');
});
