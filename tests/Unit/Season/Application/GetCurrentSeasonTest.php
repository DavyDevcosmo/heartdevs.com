<?php

declare(strict_types=1);

use Tests\Unit\Season\SeasonProviderTrait;
use Heart\Season\Application\GetCurrentSeason;
use Heart\Season\Domain\Repositories\SeasonRepository;

uses(SeasonProviderTrait::class);

beforeEach(function (): void {
    $this->seasonRepositoryStub = m::mock(SeasonRepository::class);
    $this->seasonEntity = $this->validSeasonEntity();
});
afterEach(function (): void {
    m::close();
});
test('get current season', function (): void {
    $this->seasonRepositoryStub
        ->shouldReceive('getCurrent')
        ->once()
        ->andReturn($this->seasonEntity);

    $test = new GetCurrentSeason($this->seasonRepositoryStub);

    $test->handle();
});
