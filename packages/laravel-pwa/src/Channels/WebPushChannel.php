<?php

namespace BeegoodIT\LaravelPwa\Channels;

use BeegoodIT\LaravelPwa\Messages\WebPushMessage;
use BeegoodIT\LaravelPwa\Models\Notifications\Broadcast;
use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use BeegoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

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

        // Resolve the message content
        $webPushMessage = $notification->toWebPush($notifiable);

        if (! $webPushMessage instanceof WebPushMessage) {
            $webPushMessage = (new WebPushMessage)->title($webPushMessage);
        }

        $payload = $webPushMessage->toArray();

        // 1. Find or create the Broadcast (The "Group")
        $broadcast = $this->resolveBroadcast($notification, $payload);

        foreach ($subscriptions as $subscription) {
            // 2. Create the Message record (The "Delivery")
            $message = Message::create([
                'broadcast_id' => $broadcast->id,
                'push_subscription_id' => $subscription->id,
                'notification_type' => $notification::class,
                'data' => ['serialized' => serialize($notification)],
                'delivery_status' => 'pending',
            ]);

            dispatch(new \BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob($message))
                ->onQueue(config('pwa.notifications.queue', 'default'));
        }
    }

    protected function resolveBroadcast(Notification $notification, array $payload): Broadcast
    {
        // Check if the notification already has a broadcast_id in its data
        if (isset($payload['data']['broadcast_id'])) {
            return Broadcast::findOrFail($payload['data']['broadcast_id']);
        }

        // Otherwise, create a "System" broadcast for grouping
        $type = $notification::class;
        Str::afterLast($type, '\\');

        return Broadcast::firstOrCreate(
            ['trigger_type' => $type, 'status' => 'automated'],
            [
                'id' => Str::uuid(),
                'payload' => null, // Personalized contentResolved on-demand
            ]
        );
    }

    protected function getNotificationData(Notification $notification): array
    {
        // Basic resolution of public properties to store as data for re-hydration
        // In a more complex setup, we might use serialization
        $data = [];
        $reflection = new \ReflectionClass($notification);
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (! $property->isStatic()) {
                $data[$property->getName()] = $property->getValue($notification);
            }
        }

        return $data;
    }
}
