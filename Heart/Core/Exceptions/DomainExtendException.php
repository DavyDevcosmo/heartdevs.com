<?php

declare(strict_types=1);

namespace Heart\Core\Exceptions;

use Exception;

final class DomainExtendException extends Exception
{
    public static function abstractClassNotExtended(): self
    {
        return new self('The domain must extend the abstract class \Heart\Core\Contracts\DomainInterface');
    }
}
