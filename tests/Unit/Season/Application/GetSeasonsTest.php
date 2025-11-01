<?php

declare(strict_types=1);

use Tests\Unit\Season\SeasonProviderTrait;
use Heart\Season\Application\GetSeasons;
use Heart\Season\Domain\Collections\SeasonCollection;
use Heart\Season\Domain\Repositories\SeasonRepository;

uses(SeasonProviderTrait::class);

beforeEach(function (): void {
    $this->seasonRepositoryStub = m::mock(SeasonRepository::class);
    $this->seasonEntity = $this->validSeasonEntity();
});
afterEach(function (): void {
    m::close();
});
test('get season success', function (): void {
    $this->seasonRepositoryStub
        ->shouldReceive('getAll')
        ->once()
        ->andReturn(new SeasonCollection());

    $test = new GetSeasons($this->seasonRepositoryStub);

    $test->handle();
});
