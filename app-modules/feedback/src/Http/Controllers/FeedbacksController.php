<?php

declare(strict_types=1);

namespace He4rt\Feedback\Http\Controllers;

use He4rt\Feedback\Actions\CreateFeedback;
use He4rt\Feedback\Actions\GetFeedbackById;
use He4rt\Feedback\Actions\ReviewFeedback;
use He4rt\Feedback\Http\Requests\CreateFeedbackRequest;
use He4rt\Feedback\Http\Requests\FeedbackReviewRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class FeedbacksController
{
    public function getFeedback(
        string $feedbackId,
        GetFeedbackById $getFeedbackById,
    ): JsonResponse {
        return response()->json(
            $getFeedbackById->handle($feedbackId)
        );
    }

    public function postFeedback(CreateFeedbackRequest $request, CreateFeedback $create): JsonResponse
    {
        return response()->json($create->handle($request->validated()), Response::HTTP_CREATED);
    }

    public function postReview(
        FeedbackReviewRequest $request,
        string $feedbackId,
        string $reviewType,
        ReviewFeedback $review,
    ): JsonResponse {
        $review->handle($feedbackId, $reviewType, $request->input('staff_id'), $request->input('reason'));

        return response()->json(
            ['message' => 'Feedback recebido com sucesso!'],
            Response::HTTP_CREATED
        );
    }
}
