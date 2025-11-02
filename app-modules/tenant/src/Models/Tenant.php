<?php

declare(strict_types=1);

namespace He4rt\Tenant\Models;

use He4rt\Tenant\Database\Factories\TenantFactory;
use He4rt\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'active',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
