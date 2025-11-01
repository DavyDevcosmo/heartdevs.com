<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\PaginateMeetingsAction;
use Heart\Shared\Domain\Paginator;
use src\Contracts\MeetingRepository;
use Tests\Unit\Meeting\MeetingProviderTrait;

uses(MeetingProviderTrait::class);

beforeEach(function (): void {
    $this->meetingRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
    $this->paginatorStub = m::mock(Paginator::class);
});
afterEach(function (): void {
    m::close();
});
test('paginate meetings', function (): void {
    $this->meetingRepositoryStub
        ->shouldReceive('paginate')
        ->with(['meetingType'])
        ->once()
        ->andReturn($this->paginatorStub);

    $test = new PaginateMeetingsAction($this->meetingRepositoryStub);

    $test->handle();
});
