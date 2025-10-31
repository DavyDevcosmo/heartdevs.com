<?php

declare(strict_types=1);
use Heart\Meeting\Domain\Actions\FinishMeeting;
use Heart\Meeting\Domain\Entities\MeetingEntity;
use Heart\Meeting\Domain\Repositories\MeetingRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Meeting\MeetingProviderTrait::class);

beforeEach(function () {
    $this->meetingRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
});
afterEach(function () {
    m::close();
});
test('finish meeting', function () {
    $this->meetingRepositoryStub
        ->shouldReceive('endMeeting')
        ->with($this->meetingEntity->id)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new FinishMeeting($this->meetingRepositoryStub);

    $test->handle($this->meetingEntity->id);
});
