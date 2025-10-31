<?php

declare(strict_types=1);
use Heart\Meeting\Domain\Actions\PersistAttendMeeting;
use Heart\Meeting\Domain\DTO\NewMeetingDTO;
use Heart\Meeting\Domain\Entities\MeetingEntity;
use Heart\Meeting\Domain\Repositories\MeetingRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Meeting\MeetingProviderTrait::class);

beforeEach(function () {
    $this->meetingTypeRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
});
afterEach(function () {
    m::close();
});
test('persist attend meeting', function () {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('attendMeeting')
        ->with($this->meetingEntity->id, 12)
        ->once();

    $test = new PersistAttendMeeting($this->meetingTypeRepositoryStub);

    $test->handle($this->meetingEntity->id, 12);
});
