<?php

declare(strict_types=1);

namespace App\Geo\Models;

use App\Geo\Support\GeoSushiStore;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sushi\Sushi;

/**
 * @property int $id
 * @property string $iso2
 * @property string|null $iso3
 * @property string $name
 */
#[WithoutIncrementing]
final class GeoCountry extends Model
{
    use Sushi;

    protected $keyType = 'int';

    /**
     * @var array<string, string>
     */
    protected $schema = [
        'id' => 'integer',
        'iso2' => 'string',
        'iso3' => 'string',
        'name' => 'string',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getRows(): array
    {
        return GeoSushiStore::countries();
    }

    /**
     * @return HasMany<GeoState, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(GeoState::class, 'country_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    protected function scopeMatching(Builder $query, string $term): void
    {
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query->where(static function (Builder $query) use ($operator, $term): void {
            $query->where('name', $operator, sprintf('%%%s%%', $term))
                ->orWhere('iso2', $operator, $term.'%');
        });
    }
}
