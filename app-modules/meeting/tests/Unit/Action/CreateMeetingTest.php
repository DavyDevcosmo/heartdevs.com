<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\CreateMeetingAction;
use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\DTO\NewMeetingDTO;
use He4rt\Meeting\Tests\Unit\MeetingProviderTrait;
use Mockery as m;

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
