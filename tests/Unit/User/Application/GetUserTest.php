<?php

declare(strict_types=1);
use Heart\User\Application\GetUser;
use Heart\User\Domain\Entities\UserEntity;
use Heart\User\Domain\Repositories\UserRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\User\UserProviderTrait::class);

beforeEach(function () {
    $this->repositoryStub = m::mock(UserRepository::class);
    $this->userEntity = $this->validUserEntity();
});
afterEach(function () {
    m::close();
});
test('get user', function () {
    $this->repositoryStub
        ->shouldReceive('find')
        ->with('12')
        ->once()
        ->andReturn($this->userEntity);

    $test = new GetUser($this->repositoryStub);

    $test->handle('12');
});
