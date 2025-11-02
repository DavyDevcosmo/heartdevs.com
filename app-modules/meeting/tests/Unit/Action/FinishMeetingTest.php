<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\FinishMeetingAction;
use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\Tests\Unit\MeetingProviderTrait;

uses(MeetingProviderTrait::class);

beforeEach(function (): void {
    $this->meetingRepositoryStub = Mockery::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
});

afterEach(function (): void {
    Mockery::close();
});

test('finish meeting', function (): void {
    $this->meetingRepositoryStub
        ->shouldReceive('endMeeting')
        ->with($this->meetingEntity->id)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new FinishMeetingAction($this->meetingRepositoryStub);

    $test->handle($this->meetingEntity->id);
});
