<?php

declare(strict_types=1);

namespace He4rt\Squads\Enums;

enum SquadStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';
}
