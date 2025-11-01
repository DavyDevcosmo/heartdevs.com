<?php

declare(strict_types=1);

namespace Heart\Core\Contracts;

abstract class DomainInterface
{
    public function __construct(private readonly bool $disabled = false) {}

    abstract public function registerProvider(): array;

    final public function isDisabled(): bool
    {
        return $this->disabled;
    }
}
