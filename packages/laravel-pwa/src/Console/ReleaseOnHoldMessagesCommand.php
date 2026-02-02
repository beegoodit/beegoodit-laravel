<?php

namespace BeegoodIT\LaravelPwa\Console;

use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob;
use Illuminate\Console\Command;

class ReleaseOnHoldMessagesCommand extends Command
{
    protected $signature = 'pwa:release-on-hold';

    protected $description = 'Bulk release all messages currently on hold';

    public function handle(): int
    {
        $onHoldCount = Message::where('delivery_status', 'on_hold')->count();

        if ($onHoldCount === 0) {
            $this->info('No messages on hold found.');

            return 0;
        }

        if (! $this->confirm("Are you sure you want to release {$onHoldCount} messages from hold?")) {
            return 0;
        }

        $this->info("Releasing {$onHoldCount} messages...");

        Message::where('delivery_status', 'on_hold')->chunk(100, function ($messages): void {
            foreach ($messages as $message) {
                $message->release();

                dispatch(new SendMessageJob($message))
                    ->onQueue(config('pwa.notifications.queue', 'default'));
            }
        });

        $this->info('All selected messages have been re-queued for delivery.');

        return 0;
    }
}
