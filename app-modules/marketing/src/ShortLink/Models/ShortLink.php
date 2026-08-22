<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Models;

use Carbon\CarbonInterface;
use He4rt\Identity\User\Models\User;
use He4rt\Marketing\Database\Factories\ShortLinkFactory;
use He4rt\Marketing\ShortLink\Casts\AsTagList;
use He4rt\Marketing\ShortLink\Casts\AsUtmParameters;
use He4rt\Marketing\ShortLink\Enums\ShortLinkStatus;
use He4rt\Marketing\ShortLink\Observers\ShortLinkObserver;
use He4rt\Marketing\ShortLink\ValueObjects\TagList;
use He4rt\Marketing\ShortLink\ValueObjects\UtmParameters;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $slug
 * @property string $base_slug
 * @property string $destination_url
 * @property UtmParameters $utm
 * @property TagList $tags
 * @property bool $active
 * @property CarbonInterface|null $expires_at
 * @property int $clicks_count
 * @property int $human_clicks_count
 * @property string|null $created_by
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property-read ShortLinkStatus $status
 */
#[ObservedBy(classes: ShortLinkObserver::class)]
#[Table(name: 'marketing_short_links')]
#[UseFactory(factoryClass: ShortLinkFactory::class)]
final class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /** @var array<string, mixed> */
    protected $attributes = [
        'active' => true,
        'clicks_count' => 0,
        'human_clicks_count' => 0,
    ];

    /** @return HasMany<ShortLinkClick, $this> */
    public function clicks(): HasMany
    {
        return $this->hasMany(ShortLinkClick::class, 'short_link_id');
    }

    /** @return HasMany<ShortLinkDestination, $this> */
    public function destinations(): HasMany
    {
        return $this->hasMany(ShortLinkDestination::class, 'short_link_id');
    }

    /**
     * O destino vigente: a única linha de histórico com o intervalo ainda aberto.
     *
     * @return HasOne<ShortLinkDestination, $this>
     */
    public function currentDestination(): HasOne
    {
        return $this->hasOne(ShortLinkDestination::class, 'short_link_id')
            ->whereNull('valid_until')
            ->latest('valid_from');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Só links que podem, de fato, redirecionar agora.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    protected function scopeRedirectable(Builder $query): Builder
    {
        return $query
            ->where('active', operator: true)
            ->where(static fn (Builder $query): Builder => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>', now()));
    }

    /**
     * Estado derivado, nunca persistido.
     *
     * @return Attribute<ShortLinkStatus, never>
     */
    protected function status(): Attribute
    {
        return Attribute::get($this->resolveStatus(...));
    }

    /** @return array<string, mixed> */
    protected function casts(): array
    {
        return [
            'utm' => AsUtmParameters::class,
            'tags' => AsTagList::class,
            'active' => 'boolean',
            'expires_at' => 'datetime',
            'clicks_count' => 'integer',
            'human_clicks_count' => 'integer',
        ];
    }

    /**
     * Precedência: desligar manualmente (ou remover) ganha da data de expiração —
     * um link com `active = false` e `expires_at` no futuro é `Disabled`, não `Active`.
     */
    private function resolveStatus(): ShortLinkStatus
    {
        if ($this->deleted_at !== null || $this->active === false) {
            return ShortLinkStatus::Disabled;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return ShortLinkStatus::Expired;
        }

        return ShortLinkStatus::Active;
    }
}
