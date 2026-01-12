<?php

namespace BeeGoodIT\LaravelPwa\Console;

use BeeGoodIT\LaravelPwa\Notifications\GenericPushNotification;
use BeeGoodIT\LaravelPwa\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPushNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pwa:send 
                            {user? : The ID of the user to notify}
                            {--title= : The title of the notification}
                            {--body= : The body of the notification}
                            {--all : Send to all subscribers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a push notification to one or all users';

    /**
     * Execute the console command.
     */
    public function handle(PushNotificationService $pushService): int
    {
        $title = $this->option('title') ?: 'Test Notification';
        $body = $this->option('body') ?: 'This is a test push notification from ' . config('app.name');

        $notification = new GenericPushNotification($title, $body);

        if ($this->option('all')) {
            $this->info('Sending push notification to all subscribers...');
            $count = $pushService->sendToAll([
                'title' => $title,
                'body' => $body,
            ]);
            $this->info("Successfully sent to {$count} subscriptions.");
            return self::SUCCESS;
        }

        $userId = $this->argument('user');

        if (!$userId) {
            $this->error('Please provide a user ID or use the --all option.');
            return self::FAILURE;
        }

        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        $user = $userModel::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return self::FAILURE;
        }

        $this->info("Sending push notification to user: {$user->name}...");
        $user->notify($notification);

        $this->info('Notification sent to the queue/channel.');

        return self::SUCCESS;
    }
}
