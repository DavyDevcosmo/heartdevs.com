<?php

declare(strict_types=1);

namespace Heart\Core\DTO;

final readonly class DomainDTO
{
    public function __construct(
        public string $namespace,
        public string $filePath,
        public string $fileName
    ) {}

    public static function make(string $namespace, array $domainPayload): self
    {
        return new self($namespace, $domainPayload['filePath'], $domainPayload['fileName']);
    }
}
