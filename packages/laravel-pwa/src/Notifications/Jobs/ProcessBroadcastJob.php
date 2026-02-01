<?php

namespace BeegoodIT\LaravelPwa\Notifications\Jobs;

use BeegoodIT\LaravelPwa\Models\Notifications\Broadcast;
use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use BeegoodIT\LaravelPwa\Models\Notifications\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Broadcast $broadcast) {}

    public function handle(): void
    {
        $this->broadcast->update(['status' => 'processing']);

        // Determine recipients based on trigger_type
        $subscriptions = PushSubscription::query();

        if ($this->broadcast->target_ids && count($this->broadcast->target_ids) > 0) {
            $subscriptions->whereIn('user_id', $this->broadcast->target_ids);
        }

        $totalRecipients = $subscriptions->count();
        $this->broadcast->update(['total_recipients' => $totalRecipients]);

        if ($totalRecipients === 0) {
            $this->broadcast->update(['status' => 'completed']);

            return;
        }

        $jobs = [];
        foreach ($subscriptions->cursor() as $subscription) {
            $message = Message::create([
                'broadcast_id' => $this->broadcast->id,
                'push_subscription_id' => $subscription->id,
                'content' => $this->broadcast->payload,
                'delivery_status' => 'pending',
            ]);

            $jobs[] = new SendMessageJob($message);
        }

        // Use batches for better tracking if possible, or just dispatch them
        // To stay simple and support rate limiting, we dispatch them individually
        // with the configured queue.
        foreach ($jobs as $job) {
            dispatch($job)->onQueue(config('pwa.notifications.queue', 'default'));
        }

        $this->broadcast->update(['status' => 'completed']);
    }
}
