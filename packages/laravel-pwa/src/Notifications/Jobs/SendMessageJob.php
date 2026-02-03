<?php

namespace BeegoodIT\LaravelPwa\Notifications\Jobs;

use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use BeegoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    public function __construct(public Message $message) {}

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new RateLimited('pwa-notifications')];
    }

    public function handle(PushNotificationService $pushService, \BeegoodIT\LaravelPwa\Settings\NotificationSettings $settings): void
    {
        $this->message->load(['broadcast', 'pushSubscription']);

        if (! $this->message->pushSubscription) {
            $this->message->update(['delivery_status' => 'failed', 'error_message' => 'Subscription not found']);

            return;
        }

        // Check for Manual Hold
        if ($this->message->delivery_status->equals(\BeegoodIT\LaravelPwa\States\Messages\OnHold::class)) {
            return;
        }

        // Check for Global Delivery System Status
        if (! $settings->pwa_deliver_notifications) {
            $this->release(60);

            return;
        }

        // Resolve Content
        $content = $this->message->resolveContent();
        $payload = (array) $content;

        // Ensure we have a data key and message_id for tracking
        if (! isset($payload['data'])) {
            $payload['data'] = [];
        }
        $payload['data']['message_id'] = $this->message->id;

        $success = $pushService->send(
            $this->message->pushSubscription,
            $payload
        );

        if ($success) {
            $this->message->update(['delivery_status' => 'sent']);
            if ($this->message->broadcast) {
                $this->message->broadcast()->increment('total_sent');
            }
        } else {
            $this->message->update([
                'delivery_status' => 'failed',
                'error_message' => 'Push service returned false (check logs for details)',
            ]);
        }
    }
}
