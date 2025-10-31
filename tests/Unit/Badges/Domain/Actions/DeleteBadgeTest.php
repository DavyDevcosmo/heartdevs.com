<?php

declare(strict_types=1);
use Heart\Badges\Domain\Actions\DeleteBadge;
use Heart\Badges\Domain\Entities\BadgeEntity;
use Heart\Badges\Domain\Repositories\BadgeRepository;
use Mockery\MockInterface;
uses(\He4rt\Badge\Tests\Unit\BadgeProviderTrait::class);

beforeEach(function () {
    $this->badgeRepositoryStub = m::mock(BadgeRepository::class);
    $this->badgeEntity = $this->validBadgeEntity();
});
afterEach(function () {
    m::close();
});
test('delete badge success', function () {
    $this->badgeRepositoryStub
        ->shouldReceive('delete')
        ->with($this->badgeEntity->id)
        ->once();

    $test = new DeleteBadge($this->badgeRepositoryStub);

    $test->handle($this->badgeEntity->id);
});
