<?php

declare(strict_types=1);

use He4rt\Character\Actions\ManageReputation;
use He4rt\Character\Contracts\CharacterRepository;
use He4rt\Character\Tests\Unit\CharacterProviderTrait;
use Mockery as m;

uses(CharacterProviderTrait::class);

beforeEach(function (): void {
    $this->characterRepository = m::mock(CharacterRepository::class);
    $this->manageReputation = new ManageReputation($this->characterRepository);
});
afterEach(function (): void {
    m::close();
});
test('add reputation', function (): void {
    $character = $this->validCharacterEntity();
    $characterId = 'porra-careca';

    $this->characterRepository
        ->shouldReceive('findById')
        ->once()
        ->with($characterId)
        ->andReturn($character);

    $this->characterRepository
        ->shouldReceive('updateReputation')
        ->once()
        ->with($character);

    $this->manageReputation->handle($characterId, 'increment');
});
