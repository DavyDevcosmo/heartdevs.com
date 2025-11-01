<?php

declare(strict_types=1);

namespace He4rt\Provider\Models;

use He4rt\Message\Models\Message;
use He4rt\Provider\Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use src\Models\User;

/**
 * @property string $id
 * @property User $user
 * @property Collection<Token> $tokens
 * @property string $user_id
 * @property string $provider_id
 * @property string $provider
 */
final class Provider extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'providers';

    protected $fillable = [
        'id',
        'user_id',
        'provider',
        'provider_id',
        'email',
    ];

    protected $appends = [
        'messages_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    protected static function newFactory(): ProviderFactory
    {
        return ProviderFactory::new();
    }

    protected function getMessagesCountAttribute(): int
    {
        return $this->messages()->count();
    }
}
