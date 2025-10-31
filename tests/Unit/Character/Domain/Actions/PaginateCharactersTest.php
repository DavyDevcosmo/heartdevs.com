<?php

declare(strict_types=1);
use Heart\Character\Domain\Actions\PaginateCharacters;
use Heart\Character\Domain\Repositories\CharacterRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
beforeEach(function () {
    $this->characterRepository = m::mock(CharacterRepository::class);
    $this->paginateCharactersAction = new PaginateCharacters($this->characterRepository);
});
afterEach(function () {
    m::close();
});
test('can paginate', function () {
    $this->characterRepository
        ->shouldReceive('paginate')
        ->once()
        ->andReturn(m::mock(LengthAwarePaginator::class));

    $result = $this->paginateCharactersAction->handle();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
});
