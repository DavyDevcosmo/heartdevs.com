<?php

declare(strict_types=1);

use He4rt\Character\Actions\FindCharacter;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;
use Mockery as m;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepositoryStub = m::mock(CharacterRepository::class);
    $this->characterEntity = $this->validCharacterEntity();
});
afterEach(function (): void {
    m::close();
});
test('find character success', function (): void {
    $this->characterRepositoryStub
        ->shouldReceive('findById')
        ->with($this->characterEntity->id)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new FindCharacter($this->characterRepositoryStub);

    $test->handle($this->characterEntity->id);
});
