<?php

namespace BeegoodIT\LaravelPwa\Console;

use BeegoodIT\LaravelPwa\Settings\NotificationSettings;
use Illuminate\Console\Command;

class PauseDeliverySystemCommand extends Command
{
    protected $signature = 'pwa:notifications:pause';

    protected $description = 'Pause PWA notification delivery';

    public function handle(NotificationSettings $settings): int
    {
        $settings->pwa_deliver_notifications = false;
        $settings->save();

        $this->warn('PWA notification delivery is paused. Incoming messages stay pending until resume.');

        return self::SUCCESS;
    }
}
