<?php

namespace BeegoodIT\LaravelPwa\Channels;

use BeegoodIT\LaravelPwa\Messages\WebPushMessage;
use BeegoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification): void
    {
        if (! $this->pushService->isEnabled()) {
            return;
        }

        // Get subscriptions via the routeNotificationFor method
        $subscriptions = $notifiable->routeNotificationFor('webPush', $notification);

        if (! $subscriptions || $subscriptions->isEmpty()) {
            return;
        }

        // Get the message from the notification
        $message = $notification->toWebPush($notifiable);

        if (! $message instanceof WebPushMessage) {
            $message = (new WebPushMessage)->title($message);
        }

        $payload = $message->toArray();

        foreach ($subscriptions as $subscription) {
            $this->pushService->send($subscription, $payload);
        }
    }
}
