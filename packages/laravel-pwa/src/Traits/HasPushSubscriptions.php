<?php

namespace BeeGoodIT\LaravelPwa\Traits;

use BeeGoodIT\LaravelPwa\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasPushSubscriptions
{
    /**
     * Get all push subscriptions for the user.
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(
            config('pwa.subscription_model', PushSubscription::class),
            'user_id'
        );
    }

    /**
     * Route notifications for the web push channel.
     */
    public function routeNotificationForWebPush(): Collection
    {
        return $this->pushSubscriptions;
    }

    /**
     * Check if user has any push subscriptions.
     */
    public function hasPushSubscriptions(): bool
    {
        return $this->pushSubscriptions()->exists();
    }

    /**
     * Subscribe to push notifications.
     */
    public function subscribeToPush(array $subscription): PushSubscription
    {
        $model = config('pwa.subscription_model', PushSubscription::class);

        return $model::updateOrCreate(
            ['endpoint' => $subscription['endpoint']],
            [
                'user_id' => $this->id,
                'public_key' => $subscription['keys']['p256dh'],
                'auth_token' => $subscription['keys']['auth'],
                'content_encoding' => $subscription['contentEncoding'] ?? 'aesgcm',
            ]
        );
    }

    /**
     * Unsubscribe from push notifications by endpoint.
     */
    public function unsubscribeFromPush(string $endpoint): bool
    {
        return $this->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete() > 0;
    }
}
