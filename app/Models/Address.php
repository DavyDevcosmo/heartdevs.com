<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table(name: 'addresses')]
final class Address extends Model
{
    use HasFactory;
    use HasUuids;

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
