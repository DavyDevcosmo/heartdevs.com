<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\PersistAttendMeetingAction;
use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\Tests\Unit\MeetingProviderTrait;

uses(MeetingProviderTrait::class);

beforeEach(function (): void {
    $this->meetingTypeRepositoryStub = Mockery::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
});

afterEach(function (): void {
    Mockery::close();
});

test('persist attend meeting', function (): void {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('attendMeeting')
        ->with($this->meetingEntity->id, '12')
        ->once();

    $test = new PersistAttendMeetingAction($this->meetingTypeRepositoryStub);

    $test->handle($this->meetingEntity->id, '12');
});
