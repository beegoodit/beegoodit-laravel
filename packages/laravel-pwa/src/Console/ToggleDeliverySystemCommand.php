<?php

namespace BeegoodIT\LaravelPwa\Console;

use BeegoodIT\LaravelPwa\Settings\NotificationSettings;
use Illuminate\Console\Command;

class ToggleDeliverySystemCommand extends Command
{
    protected $signature = 'pwa:toggle-system {status?}';

    protected $description = 'Toggle the global PWA notification delivery system';

    public function handle(NotificationSettings $settings): int
    {
        $status = $this->argument('status');

        if ($status === null) {
            $newStatus = ! $settings->pwa_deliver_notifications;
        } else {
            $newStatus = (in_array($status, ['on', 'open', 'enabled', '1']));
        }

        $settings->pwa_deliver_notifications = $newStatus;
        $settings->save();

        $stateLabel = $newStatus ? '<fg=green>ENABLED</>' : '<fg=red>DISABLED</>';
        $this->info("PWA Notification Delivery is now {$stateLabel}.");

        if ($newStatus) {
            $this->info('Pending notifications will be processed by workers.');
        } else {
            $this->warn('Incoming notifications will be recorded as pending and re-queued by workers until delivery is enabled.');
        }

        return 0;
    }
}
