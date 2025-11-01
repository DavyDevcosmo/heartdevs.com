<?php

declare(strict_types=1);

namespace He4rt\Meeting\Http\Controllers;

use App\Http\Controllers\Controller;
use He4rt\Meeting\Actions\EndMeeting;
use He4rt\Meeting\Actions\PaginateMeetings;
use He4rt\Meeting\Actions\StartMeeting;
use He4rt\Meeting\Exceptions\MeetingException;
use He4rt\Meeting\Http\Requests\MeetingRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class MeetingController extends Controller
{
    public function getMeetings(string $provider, PaginateMeetings $paginateMeetings): JsonResponse
    {
        return response()->json($paginateMeetings->handle($provider));
    }

    public function postMeeting(
        string $provider,
        MeetingRequest $request,
        StartMeeting $startMeeting
    ): JsonResponse {
        try {
            return response()->json(
                $startMeeting->handle($provider, $request->input('provider_id'), $request->input('meeting_type_id')),
                Response::HTTP_CREATED
            );
        } catch (MeetingException $meetingException) {
            return response()->json([
                'error' => $meetingException->getMessage(),
            ], $meetingException->getCode());
        }
    }

    public function postEndMeeting(
        string $provider,
        EndMeeting $endMeeting,
    ): Response {
        $endMeeting->handle();

        return response()->noContent();
    }
}
