<?php

declare(strict_types=1);
use Heart\Shared\Domain\Paginator;
use Heart\User\Application\GetUsersPaginated;
use Heart\User\Domain\Repositories\UserRepository;
use Mockery\MockInterface;
beforeEach(function () {
    $this->repositoryStub = m::mock(UserRepository::class);
    $this->paginatorStub = m::mock(Paginator::class);
});
afterEach(function () {
    m::close();
});
test('get users paginated', function () {
    $this->repositoryStub
        ->shouldReceive('paginated')
        ->once()
        ->andReturn($this->paginatorStub);

    $test = new GetUsersPaginated($this->repositoryStub);

    $test->handle();
});
