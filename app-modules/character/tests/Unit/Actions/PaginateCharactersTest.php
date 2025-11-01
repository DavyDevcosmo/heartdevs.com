<?php

declare(strict_types=1);

use He4rt\Character\Actions\PaginateCharacters;
use He4rt\Character\Contracts\CharacterRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery as m;

beforeEach(function (): void {
    $this->characterRepository = m::mock(CharacterRepository::class);
    $this->paginateCharactersAction = new PaginateCharacters($this->characterRepository);
});
afterEach(function (): void {
    m::close();
});
test('can paginate', function (): void {
    $this->characterRepository
        ->shouldReceive('paginate')
        ->once()
        ->andReturn(m::mock(LengthAwarePaginator::class));

    $result = $this->paginateCharactersAction->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});
