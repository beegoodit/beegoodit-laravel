<?php

namespace BeegoodIT\FilamentSocialGraph\Models;

use BeegoodIT\FilamentSocialGraph\Database\Factories\FeedSubscriptionRuleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
