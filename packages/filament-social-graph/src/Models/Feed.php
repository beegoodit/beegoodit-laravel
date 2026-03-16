<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read \Illuminate\Database\Eloquent\Model|null $owner Polymorphic owner (e.g. Team).
 */
class Feed extends Model
{
    use HasFactory;
    use HasUuids;

    protected static function newFactory(): FeedFactory
    {
        return FeedFactory::new();
    }

    protected $fillable = [
        'owner_type',
        'owner_id',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function feedItems(): HasMany
    {
        return $this->hasMany(FeedItem::class)->orderByDesc('created_at');
    }

    public function feedSubscriptionRules(): HasMany
    {
        return $this->hasMany(FeedSubscriptionRule::class);
    }

    public static function firstOrCreateForOwner(Model $owner): self
    {
        return self::query()
            ->firstOrCreate(
                [
                    'owner_type' => $owner->getMorphClass(),
                    'owner_id' => $owner->getKey(),
                ]
            );
    }
}
