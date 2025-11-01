<?php

declare(strict_types=1);

namespace He4rt\Feedback\Models;

use He4rt\Feedback\Database\Factories\FeedbackFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use src\Models\User;

final class Feedback extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'feedbacks';

    protected $fillable = [
        'id',
        'sender_id',
        'target_id',
        'type',
        'message',
    ];

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    protected static function newFactory(): FeedbackFactory
    {
        return FeedbackFactory::new();
    }
}
