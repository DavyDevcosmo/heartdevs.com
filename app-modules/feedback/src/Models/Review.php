<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use He4rt\Feedback\Enum\ReviewTypeEnum;
use Heart\User\Infrastructure\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Review extends Model
{
    use HasUuids;

    protected $table = 'feedback_reviews';

    protected $fillable = [
        'id',
        'feedback_id',
        'staff_id',
        'status',
        'reason',
        'received_at',
    ];

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    protected function casts(): array
    {
        return [
            'status' => ReviewTypeEnum::class,
            'received_at' => 'timestamp',
        ];
    }
}
