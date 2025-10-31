<?php

declare(strict_types=1);
use Heart\Season\Application\GetSeasons;
use Heart\Season\Domain\Collections\SeasonCollection;
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
test('get season success', function () {
    $this->seasonRepositoryStub
        ->shouldReceive('getAll')
        ->once()
        ->andReturn(new SeasonCollection());

    $test = new GetSeasons($this->seasonRepositoryStub);

    $test->handle();
});
