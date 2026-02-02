<?php

namespace BeegoodIT\LaravelPwa\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationSettings extends Settings
{
    public bool $pwa_deliver_notifications;

    public static function group(): string
    {
        return 'pwa_notifications';
    }
}
