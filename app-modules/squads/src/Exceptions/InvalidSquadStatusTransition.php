<?php

declare(strict_types=1);

namespace He4rt\Squads\Exceptions;

use Exception;
use He4rt\Squads\Enums\SquadStatus;

final class InvalidSquadStatusTransition extends Exception
{
    public static function between(SquadStatus $from, SquadStatus $to): self
    {
        return new self(
            sprintf('Cannot transition squad from "%s" to "%s".', $from->value, $to->value)
        );
    }
}
