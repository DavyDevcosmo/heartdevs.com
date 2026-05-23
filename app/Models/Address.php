<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Address extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'addresses';

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'country',
        'state',
        'city',
        'zip_code',
    ];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
