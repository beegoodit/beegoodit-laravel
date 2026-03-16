<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedSubscriptionRuleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class FeedSubscriptionRule extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'feed_subscription_rules';

    protected $fillable = [
        'feed_id',
        'scope',
        'auto_subscribe',
        'unsubscribable',
        'team_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_subscribe' => 'boolean',
            'unsubscribable' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (FeedSubscriptionRule $rule): void {
            $rule->syncTeamIdFromFeedOwner();
        });
    }

    /**
     * Set team_id from the feed's owner when the owner is the tenancy team model.
     * Ensures scope resolvers (e.g. team_members) use the feed-owning team, not Filament tenant.
     */
    protected function syncTeamIdFromFeedOwner(): void
    {
        if (! Schema::hasColumn($this->getTable(), 'team_id')) {
            return;
        }

        $feed = $this->getFeedForSync();
        if ($feed === null) {
            $this->team_id = null;

            return;
        }

        $owner = $feed->getRelationValue('owner') ?? $feed->owner;
        if ($owner === null || ! $this->ownerIsTenancyTeam($owner)) {
            $this->team_id = null;

            return;
        }

        $this->team_id = $owner->getKey();
    }

    /**
     * Resolve the feed for this rule (the one that will be saved).
     * When feed_id was just changed (e.g. on update), use the new feed_id, not the previously loaded relation.
     */
    protected function getFeedForSync(): ?Feed
    {
        if ($this->feed_id === null) {
            return null;
        }

        $loadedFeed = $this->relationLoaded('feed') ? $this->feed : null;
        if ($loadedFeed !== null && (string) $loadedFeed->getKey() === (string) $this->feed_id) {
            $loadedFeed->loadMissing('owner');

            return $loadedFeed;
        }

        $feed = Feed::query()->find($this->feed_id);
        if ($feed !== null) {
            $feed->loadMissing('owner');
        }

        return $feed;
    }

    protected function ownerIsTenancyTeam(Model $owner): bool
    {
        if (! config('filament-social-graph.tenancy.enabled', false)) {
            return false;
        }

        $teamModel = config('filament-social-graph.tenancy.team_model');
        if (! is_string($teamModel)) {
            return false;
        }

        return $owner instanceof $teamModel;
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function feedSubscriptions(): HasMany
    {
        return $this->hasMany(FeedSubscription::class, 'subscription_rule_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo($this->getTeamModel());
    }

    protected function getTeamModel(): string
    {
        return config('filament-social-graph.tenancy.team_model', \App\Models\Team::class);
    }

    protected static function newFactory(): FeedSubscriptionRuleFactory
    {
        return FeedSubscriptionRuleFactory::new();
    }
}
