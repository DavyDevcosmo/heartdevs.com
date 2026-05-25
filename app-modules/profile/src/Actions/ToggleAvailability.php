<?php

declare(strict_types=1);

namespace He4rt\Profile\Actions;

use He4rt\Profile\Enums\StartAvailability;
use He4rt\Profile\Models\Profile;
use InvalidArgumentException;

final class ToggleAvailability
{
    public function handle(Profile $profile, bool $available, ?StartAvailability $startAvailability = null): Profile
    {
        throw_if($available && !$startAvailability instanceof StartAvailability, InvalidArgumentException::class, 'O prazo de disponibilidade é obrigatório ao ativar a disponibilidade.');

        $profile->update([
            'available_for_proposals' => $available,
            ...($available ? ['start_availability' => $startAvailability] : []),
        ]);

        return $profile->fresh();
    }
}
