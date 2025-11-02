<?php

declare(strict_types=1);

namespace He4rt\Character\Actions;

use He4rt\Character\Contracts\CharacterRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class PaginateCharacters
{
    public function __construct(private CharacterRepository $characterRepository) {}

    public function handle(): LengthAwarePaginator
    {
        return $this->characterRepository->paginate();
    }
}
