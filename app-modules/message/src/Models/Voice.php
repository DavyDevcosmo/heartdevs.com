<?php

declare(strict_types=1);

namespace He4rt\Message\Models;

use He4rt\Provider\Model\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Voice extends Model
{
    protected $table = 'voice_messages';

    protected $fillable = [
        'provider_id',
        'season_id',
        'channel_name',
        'state',
        'obtained_experience',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
