<?php

declare(strict_types=1);

namespace He4rt\Meeting\Actions;

use Heart\Provider\Domain\Enums\ProviderEnum;
use Heart\Shared\Domain\Paginator;

final readonly class PaginateMeetings
{
    public function __construct(private PaginateMeetingsAction $paginateMeetingsAction) {}

    public function handle(string $provider): Paginator
    {
        $provider = ProviderEnum::from($provider);

        return $this->paginateMeetingsAction->handle($provider);
    }
}
