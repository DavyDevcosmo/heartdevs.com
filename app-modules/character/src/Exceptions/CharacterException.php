<?php

declare(strict_types=1);

namespace He4rt\Character\Exceptions;

use Exception;
use He4rt\Character\Entities\DailyRewardEntity;
use Symfony\Component\HttpFoundation\Response;

final class CharacterException extends Exception
{
    public static function alreadyClaimed(DailyRewardEntity $dailyReward): self
    {
        return new self(
            sprintf('Você já resgatou hoje! Faltam %s minutos para o próximo resgate.', $dailyReward->minutesUntilClaim()),
            Response::HTTP_FORBIDDEN
        );
    }
}
