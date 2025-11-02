<?php

declare(strict_types=1);

use He4rt\Character\Actions\FindCharacterIdByUserId;
use He4rt\Character\Actions\GetCharacterByUserId;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepositoryStub = Mockery::mock(CharacterRepository::class);
    $this->getCharacterIdByUserId = new GetCharacterByUserId($this->characterRepositoryStub);
    $this->characterEntity = $this->validCharacterEntity();
});

afterEach(function (): void {
    Mockery::close();
});

test('find character by user id', function (): void {
    $this->characterRepositoryStub
        ->shouldReceive('findByUserId')
        ->with('canhassi-id')
        ->once()
        ->andReturn($this->characterEntity);

    $test = new FindCharacterIdByUserId($this->getCharacterIdByUserId);

    $test->handle('canhassi-id');
});
