<?php

declare(strict_types=1);
use Heart\Meeting\Domain\Actions\FindMeetingType;
use Heart\Meeting\Domain\Entities\MeetingTypeEntity;
use Heart\Meeting\Domain\Exceptions\MeetingException;
use Heart\Meeting\Domain\Repositories\MeetingTypeRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Meeting\MeetingTypeProviderTrait::class);

beforeEach(function () {
    $this->meetingTypeRepositoryStub = m::mock(MeetingTypeRepository::class);
    $this->meetingEntity = $this->validMeetingTypeEntity();
});
afterEach(function () {
    m::close();
});
test('meeting type is not found', function () {
    $this->expectException(MeetingException::class);

    $this->meetingTypeRepositoryStub
        ->shouldReceive('findById')
        ->with(12)
        ->once()
        ->andReturn(null);

    $test = new FindMeetingType($this->meetingTypeRepositoryStub);

    $test->handle(12);
});
test('find meeting type success', function () {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('findById')
        ->with(2)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new FindMeetingType($this->meetingTypeRepositoryStub);

    $test->handle(2);
});
