<?php

declare(strict_types=1);

namespace He4rt\User\Exceptions;

use Exception;

final class UserEntityException extends Exception
{
    public static function failedToCreateEntity(): self
    {
        return new self('Failed to create entity', 422);
    }
}
