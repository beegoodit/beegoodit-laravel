<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedSubscription extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'feed_subscriptions';

    protected $fillable = [
        'subscriber_type',
        'subscriber_id',
        'feed_owner_type',
        'feed_owner_id',
        'team_id',
    ];

    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber');
    }

    public function feedOwner(): MorphTo
    {
        return $this->morphTo('feed_owner');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo($this->getTeamModel());
    }

    protected function getTeamModel(): string
    {
        return config('filament-social-graph.tenancy.team_model', \App\Models\Team::class);
    }

    protected static function newFactory(): FeedSubscriptionFactory
    {
        return FeedSubscriptionFactory::new();
    }
}
