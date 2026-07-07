<?php

declare(strict_types=1);

namespace App\Geo\Models;

use App\Geo\Support\GeoSearch;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $state_id
 * @property string $country_code
 * @property string $name
 */
#[Table(name: 'geo_cities')]
#[WithoutIncrementing]
#[WithoutTimestamps]
final class GeoCity extends Model
{
    protected $fillable = [
        'id',
        'state_id',
        'country_code',
        'name',
        'synced_at',
    ];

    /**
     * @return BelongsTo<GeoState, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(GeoState::class, 'state_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    protected function scopeMatching(Builder $query, string $term): void
    {
        $operator = GeoSearch::likeOperator($query);

        $query->where('name', $operator, sprintf('%%%s%%', $term));
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'state_id' => 'integer',
            'synced_at' => 'immutable_datetime',
        ];
    }
}
