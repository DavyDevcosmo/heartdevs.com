<?php

declare(strict_types=1);

use He4rt\User\Actions\GetUser;
use He4rt\User\Contracts\UserRepository;
use Unit\UserProviderTrait;

uses(UserProviderTrait::class);

beforeEach(function (): void {
    $this->repositoryStub = m::mock(UserRepository::class);
    $this->userEntity = $this->validUserEntity();
});
afterEach(function (): void {
    m::close();
});
test('get user', function (): void {
    $this->repositoryStub
        ->shouldReceive('find')
        ->with('12')
        ->once()
        ->andReturn($this->userEntity);

    $test = new GetUser($this->repositoryStub);

    $test->handle('12');
});
