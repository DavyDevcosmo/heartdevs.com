<?php

declare(strict_types=1);

namespace Heart\Core\Exceptions;

use Exception;

final class DomainNotExistsException extends Exception
{
    public static function domainNotInstantiable(string $domain): self
    {
        return new self('Domain '.$domain.' could not be instantiated.');
    }

    public static function pathNotFound(string $path): self
    {
        return new self('Path '.$path.' not exists.');
    }
}
