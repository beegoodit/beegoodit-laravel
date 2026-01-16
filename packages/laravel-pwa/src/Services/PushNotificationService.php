<?php

namespace BeeGoodIT\LaravelPwa\Services;

use BeeGoodIT\LaravelPwa\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    protected ?WebPush $webPush = null;

    /**
     * Get the WebPush instance.
     */
    protected function getWebPush(): WebPush
    {
        if (!$this->webPush instanceof \Minishlink\WebPush\WebPush) {
            $vapidConfig = config('pwa.push.vapid');

            $auth = [
                'VAPID' => [
                    'subject' => $vapidConfig['subject'],
                    'publicKey' => $vapidConfig['public_key'],
                    'privateKey' => $vapidConfig['private_key'],
                ],
            ];

            $this->webPush = new WebPush($auth);
            $this->webPush->setReuseVAPIDHeaders(true);
        }

        return $this->webPush;
    }

    /**
     * Check if push notifications are properly configured.
     */
    public function isConfigured(): bool
    {
        $vapid = config('pwa.push.vapid');

        return !empty($vapid['public_key']) && !empty($vapid['private_key']);
    }

    /**
     * Check if push notifications are enabled.
     */
    public function isEnabled(): bool
    {
        return config('pwa.push.enabled', true) && $this->isConfigured();
    }

    /**
     * Send a push notification to a subscription.
     */
    public function send(PushSubscription $subscription, array $payload): bool
    {
        if (!$this->isEnabled()) {
            Log::warning('Push notifications are not enabled or configured.');

            return false;
        }

        try {
            $webPushSubscription = Subscription::create($subscription->toWebPush());

            Log::debug('Sending push notification', [
                'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
                'vapid_subject' => config('pwa.push.vapid.subject'),
                'vapid_public_key_length' => strlen((string) config('pwa.push.vapid.public_key', '')),
            ]);

            $report = $this->getWebPush()->sendOneNotification(
                $webPushSubscription,
                json_encode($payload)
            );

            if ($report->isSuccess()) {
                return true;
            }

            // Handle expired subscriptions
            if ($report->isSubscriptionExpired()) {
                $subscription->delete();
                Log::info('Deleted expired push subscription.', ['endpoint' => $subscription->endpoint]);
            } else {
                Log::warning('Push notification failed.', [
                    'reason' => $report->getReason(),
                    'endpoint' => $subscription->endpoint,
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Push notification error.', [
                'message' => $e->getMessage(),
                'endpoint' => $subscription->endpoint,
            ]);

            return false;
        }
    }

    /**
     * Send push notification to a user (all their subscriptions).
     */
    public function sendToUser($user, array $payload): int
    {
        if (!method_exists($user, 'pushSubscriptions')) {
            Log::warning('User model does not have pushSubscriptions relationship.');

            return 0;
        }

        $sent = 0;
        foreach ($user->pushSubscriptions as $subscription) {
            if ($this->send($subscription, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send push notification to all subscriptions.
     */
    public function sendToAll(array $payload): int
    {
        $model = config('pwa.subscription_model', PushSubscription::class);
        $sent = 0;

        foreach ($model::cursor() as $subscription) {
            if ($this->send($subscription, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Send push notification to specific users.
     */
    public function sendToUsers(array $userIds, array $payload): int
    {
        $model = config('pwa.subscription_model', PushSubscription::class);
        $sent = 0;

        foreach ($model::whereIn('user_id', $userIds)->cursor() as $subscription) {
            if ($this->send($subscription, $payload)) {
                $sent++;
            }
        }

        return $sent;
    }


    /**
     * Get the VAPID public key for JavaScript.
     */
    public function getVapidPublicKey(): ?string
    {
        return config('pwa.push.vapid.public_key');
    }
}
