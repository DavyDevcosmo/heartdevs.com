<?php

declare(strict_types=1);
use Heart\Meeting\Domain\Actions\PaginateMeetings;
use Heart\Meeting\Domain\Entities\MeetingEntity;
use Heart\Meeting\Domain\Repositories\MeetingRepository;
use Heart\Shared\Domain\Paginator;
use Mockery\MockInterface;
uses(\Tests\Unit\Meeting\MeetingProviderTrait::class);

beforeEach(function () {
    $this->meetingRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
    $this->paginatorStub = m::mock(Paginator::class);
});
afterEach(function () {
    m::close();
});
test('paginate meetings', function () {
    $this->meetingRepositoryStub
        ->shouldReceive('paginate')
        ->with(['meetingType'])
        ->once()
        ->andReturn($this->paginatorStub);

    $test = new PaginateMeetings($this->meetingRepositoryStub);

    $test->handle();
});
