<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\FindMeetingTypeAction;
use src\Contracts\MeetingTypeRepository;
use src\Exceptions\MeetingException;
use Tests\Unit\Meeting\MeetingTypeProviderTrait;

uses(MeetingTypeProviderTrait::class);

beforeEach(function (): void {
    $this->meetingTypeRepositoryStub = m::mock(MeetingTypeRepository::class);
    $this->meetingEntity = $this->validMeetingTypeEntity();
});
afterEach(function (): void {
    m::close();
});
test('meeting type is not found', function (): void {
    $this->expectException(MeetingException::class);

    $this->meetingTypeRepositoryStub
        ->shouldReceive('findById')
        ->with(12)
        ->once()
        ->andReturn(null);

    $test = new FindMeetingTypeAction($this->meetingTypeRepositoryStub);

    $test->handle(12);
});
test('find meeting type success', function (): void {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('findById')
        ->with(2)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new FindMeetingTypeAction($this->meetingTypeRepositoryStub);

    $test->handle(2);
});
