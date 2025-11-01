<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use Heart\Shared\Application\TTL;
use Illuminate\Support\Facades\Cache;

class FindCharacterIdByUserId
{
    public function __construct(
        private readonly GetCharacterByUserId $finder
    ) {}

    public function handle(string $userId): string
    {
        $cacheCharacterKey = sprintf('user-%s-character-id', $userId);

        return Cache::remember(
            $cacheCharacterKey,
            TTL::fromDays(2),
            fn () => $this->findCharacterByUserId($userId)
        );
    }

    private function findCharacterByUserId(string $userId): string
    {
        return $this->finder->handle($userId)->id;
    }
}
