<?php

namespace BeegoodIT\FilamentLegal\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'version',
        'content',
        'is_active',
        'published_at',
        'owner_id',
        'owner_type',
    ];

    public function owner(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(PolicyAcceptance::class);
    }

    public static function getActive(string $type, ?Model $owner = null): ?self
    {
        return self::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->when($owner, fn ($query) => $query->forOwner($owner), fn ($query) => $query->forPlatform())
            ->orderByDesc('version')
            ->first();
    }

    protected function scopeForPlatform(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('owner_id')->whereNull('owner_type');
    }

    protected function scopeForOwner(\Illuminate\Database\Eloquent\Builder $query, Model $owner): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('owner_id', $owner->getKey())
            ->where('owner_type', $owner->getMorphClass());
    }

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
