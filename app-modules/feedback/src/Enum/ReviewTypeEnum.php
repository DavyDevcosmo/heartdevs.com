<?php

declare(strict_types=1);

namespace He4rt\Feedback\Enum;

enum ReviewTypeEnum: string
{
    case APPROVED = 'approved';
    case DECLINED = 'declined';

    public static function getTypes(): array
    {
        return [
            self::APPROVED->value,
            self::DECLINED->value,
        ];
    }
}
