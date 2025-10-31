<?php

declare(strict_types=1);
use Heart\Meeting\Domain\Actions\CreateMeeting;
use Heart\Meeting\Domain\DTO\NewMeetingDTO;
use Heart\Meeting\Domain\Entities\MeetingEntity;
use Heart\Meeting\Domain\Repositories\MeetingRepository;
use Mockery\MockInterface;
uses(\Tests\Unit\Meeting\MeetingProviderTrait::class);

beforeEach(function () {
    $this->meetingTypeRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
    $this->payloadDTO = NewMeetingDTO::make(
        'discord',
        'canhassi',
        $this->meetingEntity->meetingTypeId
    );
});
afterEach(function () {
    m::close();
});
test('create meeting', function () {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('create')
        ->with($this->payloadDTO, $this->meetingEntity->adminId)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new CreateMeeting($this->meetingTypeRepositoryStub);

    $test->handle($this->payloadDTO, $this->meetingEntity->adminId);
});
