<?php

declare(strict_types=1);

namespace App\Enums;

enum FilamentPanel
{
    case Admin;
    case Partner;
    case User;
    case Guest;
}
