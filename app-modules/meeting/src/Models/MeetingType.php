<?php

declare(strict_types=1);

namespace He4rt\Meeting\Models;

use Carbon\Carbon;
use He4rt\Factories\MeetingTypeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property int $week_day
 * @property Carbon $start_at
 */
final class MeetingType extends Model
{
    use HasFactory;

    protected $table = 'meeting_types';

    protected $fillable = [
        'name',
        'week_day',
        'start_at',
    ];

    protected static function newFactory(): MeetingTypeFactory
    {
        return MeetingTypeFactory::new();
    }

    protected function startAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->generateStartAt($value),
        );
    }

    private function generateStartAt(string $value): string
    {
        $hours = (string) intdiv($value, 60);
        $minutes = $value % 60;

        $hours = mb_str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = mb_str_pad($minutes, 2, '0', STR_PAD_LEFT);

        return $hours.':'.$minutes;
    }
}
