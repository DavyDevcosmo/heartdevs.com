<?php

declare(strict_types=1);

use He4rt\Character\Actions\GetCharacterByUserId;
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
test('get character by user id', function (): void {
    $this->characterRepositoryStub
        ->shouldReceive('findByUserId')
        ->with(12)
        ->once()
        ->andReturn($this->characterEntity);

    $test = new GetCharacterByUserId($this->characterRepositoryStub);

    $test->handle(12);
});
