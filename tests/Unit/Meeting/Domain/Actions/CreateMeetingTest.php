<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\CreateMeetingAction;
use src\Contracts\MeetingRepository;
use src\DTO\NewMeetingDTO;
use Tests\Unit\Meeting\MeetingProviderTrait;

uses(MeetingProviderTrait::class);

beforeEach(function (): void {
    $this->meetingTypeRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
    $this->payloadDTO = NewMeetingDTO::make(
        'discord',
        'canhassi',
        $this->meetingEntity->meetingTypeId
    );
});
afterEach(function (): void {
    m::close();
});
test('create meeting', function (): void {
    $this->meetingTypeRepositoryStub
        ->shouldReceive('create')
        ->with($this->payloadDTO, $this->meetingEntity->adminId)
        ->once()
        ->andReturn($this->meetingEntity);

    $test = new CreateMeetingAction($this->meetingTypeRepositoryStub);

    $test->handle($this->payloadDTO, $this->meetingEntity->adminId);
});
