<?php

declare(strict_types=1);

use He4rt\Meeting\Actions\PaginateMeetingsAction;
use He4rt\Meeting\Contracts\MeetingRepository;
use He4rt\Meeting\Tests\Unit\MeetingTypeProviderTrait;
use He4rt\Shared\Contract\Paginator;
use Mockery as m;

uses(MeetingTypeProviderTrait::class);

beforeEach(function (): void {
    $this->meetingRepositoryStub = m::mock(MeetingRepository::class);
    $this->meetingEntity = $this->validMeetingEntity();
    $this->paginatorStub = m::mock(Paginator::class);
});
afterEach(function (): void {
    m::close();
});
test('paginate meetings', function (): void {
    $this->meetingRepositoryStub
        ->shouldReceive('paginate')
        ->with(['meetingType'])
        ->once()
        ->andReturn($this->paginatorStub);

    $test = new PaginateMeetingsAction($this->meetingRepositoryStub);

    $test->handle();
});
