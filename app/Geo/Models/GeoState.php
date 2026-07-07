<?php

declare(strict_types=1);

namespace App\Geo\Models;

use App\Geo\Support\GeoSearch;
use App\Geo\Support\GeoSushiStore;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sushi\Sushi;

/**
 * @property int $id
 * @property int $country_id
 * @property string $country_code
 * @property string|null $state_code
 * @property string $name
 */
#[WithoutIncrementing]
final class GeoState extends Model
{
    use Sushi;

    protected $keyType = 'int';

    /**
     * @var array<string, string>
     */
    protected $schema = [
        'id' => 'integer',
        'country_id' => 'integer',
        'country_code' => 'string',
        'state_code' => 'string',
        'name' => 'string',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getRows(): array
    {
        return GeoSushiStore::states();
    }

    /**
     * @return BelongsTo<GeoCountry, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(GeoCountry::class, 'country_id');
    }

    /**
     * @return HasMany<GeoCity, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(GeoCity::class, 'state_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    protected function scopeMatching(Builder $query, string $term): void
    {
        $operator = GeoSearch::likeOperator($query);

        $query->where('name', $operator, sprintf('%%%s%%', $term));
    }
}
