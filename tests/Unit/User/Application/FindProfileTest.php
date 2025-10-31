<?php

declare(strict_types=1);
use Heart\Provider\Domain\Entities\ProviderEntity;
use Heart\Provider\Domain\Repositories\ProviderRepository;
use Heart\User\Application\Exceptions\ProfileException;
use Heart\User\Application\FindProfile;
use Heart\User\Domain\Actions\GetProfile;
use Heart\User\Domain\Entities\ProfileEntity;
use Heart\User\Domain\Entities\UserEntity;
use Heart\User\Domain\Repositories\UserRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\User\ProfileProviderTrait::class);

uses(\Tests\Unit\Character\ProviderProviderTrait::class);

uses(\Tests\Unit\User\UserProviderTrait::class);

beforeEach(function () {
    $this->userRepositoryStub = m::mock(UserRepository::class);
    $this->getProfileStub = m::mock(GetProfile::class);
    $this->providerRepositoryStub = m::mock(ProviderRepository::class);
    $this->providerEntity = $this->validProviderEntity();
    $this->userEntity = $this->validUserEntity();
    $this->profileEntity = $this->validProfileEntity();
});
afterEach(function () {
    m::close();
});
test('find profile with username success', function () {
    $this->userRepositoryStub
        ->shouldReceive('findByUsername')
        ->with('canhassi')
        ->once()
        ->andReturn($this->userEntity);

    $this->getProfileStub
        ->shouldReceive('handle')
        ->with($this->userEntity->id)
        ->once()
        ->andReturn($this->profileEntity);

    $test = new FindProfile($this->getProfileStub, $this->userRepositoryStub, $this->providerRepositoryStub);

    $test->handle('canhassi');
});
test('find profile with provider id success', function () {
    $this->userRepositoryStub
        ->shouldReceive('findByUsername')
        ->with('canhassi-id')
        ->once();

    $this->providerRepositoryStub
        ->shouldReceive('findByProviderId')
        ->with('canhassi-id')
        ->once()
        ->andReturn($this->providerEntity);

    $this->getProfileStub
        ->shouldReceive('handle')
        ->with($this->providerEntity->userId)
        ->once()
        ->andReturn($this->profileEntity);

    $test = new FindProfile($this->getProfileStub, $this->userRepositoryStub, $this->providerRepositoryStub);

    $test->handle('canhassi-id');
});
test('profile not found', function () {
    $this->expectException(ProfileException::class);

    $this->userRepositoryStub
        ->shouldReceive('findByUsername')
        ->with('canhassi-id')
        ->once();

    $this->providerRepositoryStub
        ->shouldReceive('findByProviderId')
        ->with('canhassi-id')
        ->once();

    $test = new FindProfile($this->getProfileStub, $this->userRepositoryStub, $this->providerRepositoryStub);

    $test->handle('canhassi-id');
});
