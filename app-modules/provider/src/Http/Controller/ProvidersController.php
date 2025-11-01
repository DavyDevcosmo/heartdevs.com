<?php

declare(strict_types=1);

namespace Heart\Provider\Http\Controller;

use App\Http\Controllers\Controller;
use Heart\Provider\Actions\NewAccountByProvider;
use Heart\Provider\Enums\ProviderEnum;
use Heart\Provider\Http\Requests\CreateProviderRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProvidersController extends Controller
{
    public function postProvider(
        CreateProviderRequest $request,
        string $provider,
        NewAccountByProvider $action,
    ): JsonResponse {
        $response = $action->handle(
            ProviderEnum::from($provider),
            $request->input('provider_id'),
            $request->input('username')
        );

        return response()->json($response, Response::HTTP_CREATED);
    }
}
