<?php

namespace BeegoodIT\LaravelPwa\Console;

use BeegoodIT\LaravelPwa\Settings\NotificationSettings;
use Illuminate\Console\Command;

class ResumeDeliverySystemCommand extends Command
{
    protected $signature = 'pwa:notifications:resume';

    protected $description = 'Resume PWA notification delivery';

    public function handle(NotificationSettings $settings): int
    {
        $settings->pwa_deliver_notifications = true;
        $settings->save();

        $this->info('PWA notification delivery is resumed.');

        return self::SUCCESS;
    }
}
