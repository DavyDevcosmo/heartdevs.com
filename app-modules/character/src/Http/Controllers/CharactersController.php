<?php

declare(strict_types=1);

namespace He4rt\Character\Http\Controllers;

use App\Http\Controllers\Controller;
use He4rt\Character\Actions\ClaimCharacterBadge;
use He4rt\Character\Actions\ClaimDailyBonus;
use He4rt\Character\Actions\FindCharacter;
use He4rt\Character\Actions\PaginateCharacters;
use He4rt\Character\Exceptions\CharacterException;
use He4rt\Character\Http\Requests\ClaimBadgeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class CharactersController extends Controller
{
    public function getCharacters(PaginateCharacters $action): JsonResponse
    {
        return response()->json($action->handle());
    }

    public function getCharacter(string $providerId, FindCharacter $action): JsonResponse
    {
        return response()->json($action->handle($providerId));
    }

    public function postDailyBonus(
        string $provider,
        string $providerId,
        ClaimDailyBonus $action
    ): Response|JsonResponse {
        try {
            $action->handle($provider, $providerId);

            return response()->noContent();
        } catch (CharacterException $characterException) {
            return response()->json($characterException->getMessage(), $characterException->getCode());
        }
    }

    public function postClaimBadge(
        ClaimBadgeRequest $request,
        string $provider,
        string $providerId,
        ClaimCharacterBadge $claimBadge
    ): Response {
        $claimBadge->handle($provider, $providerId, $request->input('redeem_code'));

        return response()->noContent();
    }
}
