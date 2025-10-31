<?php

declare(strict_types=1);
use Heart\Badges\Domain\Actions\PersistBadge;
use Heart\Badges\Domain\DTOs\NewBadgeDTO;
use Heart\Badges\Domain\Entities\BadgeEntity;
use Heart\Badges\Domain\Repositories\BadgeRepository;
use Mockery\MockInterface;
uses(\He4rt\Badge\Tests\Unit\BadgeProviderTrait::class);

beforeEach(function () {
    $this->badgeRepositoryStub = m::mock(BadgeRepository::class);
    $this->badgeEntity = $this->validBadgeEntity();
    $this->badgeDTO = new NewBadgeDTO(
        'canhassi', // provider
        $this->badgeEntity->name,
        $this->badgeEntity->description,
        'https://canhassi.tech', // image URL
        $this->badgeEntity->redeemCode,
        $this->badgeEntity->active
    );
});
afterEach(function () {
    m::close();
});
test('persist badge success', function () {
    $this->badgeRepositoryStub
        ->shouldReceive('create')
        ->with($this->badgeDTO)
        ->once()
        ->andReturn($this->badgeEntity);

    $test = new PersistBadge($this->badgeRepositoryStub);

    $test->handle($this->badgeDTO);
});
