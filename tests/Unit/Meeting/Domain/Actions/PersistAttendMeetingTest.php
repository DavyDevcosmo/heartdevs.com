<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\PersistAttendMeetingAction;
use src\Contracts\MeetingRepository;
use Tests\Unit\Meeting\MeetingProviderTrait;

uses(MeetingProviderTrait::class);

beforeEach(function (): void {
    $this->meetingTypeRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
});
afterEach(function (): void {
    m::close();
});
test('persist attend meeting', function (): void {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('attendMeeting')
        ->with($this->meetingEntity->id, 12)
        ->once();

    $test = new PersistAttendMeetingAction($this->meetingTypeRepositoryStub);

    $test->handle($this->meetingEntity->id, 12);
});
