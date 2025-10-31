<?php

declare(strict_types=1);
use Heart\Season\Application\GetCurrentSeason;
use Heart\Season\Domain\Entities\SeasonEntity;
use Heart\Season\Domain\Repositories\SeasonRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Season\SeasonProviderTrait::class);

beforeEach(function () {
    $this->seasonRepositoryStub = m::mock(SeasonRepository::class);
    $this->seasonEntity = $this->validSeasonEntity();
});
afterEach(function () {
    m::close();
});
test('get current season', function () {
    $this->seasonRepositoryStub
        ->shouldReceive('getCurrent')
        ->once()
        ->andReturn($this->seasonEntity);

    $test = new GetCurrentSeason($this->seasonRepositoryStub);

    $test->handle();
});
