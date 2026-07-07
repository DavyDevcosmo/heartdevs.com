<?php

declare(strict_types=1);

namespace He4rt\Squads\Enums;

enum SquadStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    /**
     * The statuses this status may transition into.
     *
     * Lifecycle: draft → active → inactive → archived, with reactivation
     * (inactive → active). `archived` is terminal.
     *
     * @return array<int, SquadStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [self::Inactive],
            self::Inactive => [self::Active, self::Archived],
            self::Archived => [],
        };
    }

    public function canTransitionTo(SquadStatus $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }
}
