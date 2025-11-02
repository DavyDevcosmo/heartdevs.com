<?php

declare(strict_types=1);

use He4rt\Character\Actions\GetCharacterByUserId;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepositoryStub = Mockery::mock(CharacterRepository::class);
    $this->characterEntity = $this->validCharacterEntity();
});

afterEach(function (): void {
    Mockery::close();
});

test('get character by user id', function (): void {
    $this->characterRepositoryStub
        ->shouldReceive('findByUserId')
        ->with('12')
        ->once()
        ->andReturn($this->characterEntity);

    $test = new GetCharacterByUserId($this->characterRepositoryStub);

    $test->handle('12');
});
