<?php

namespace BeegoodIT\FilamentUserProfile\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

class EmailSignInCodeNotification extends Notification
{
    public function __construct(#[SensitiveParameter] public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = app()->getLocale();

        return (new MailMessage)
            ->subject(__('filament-user-profile::auth.email_code.subject', [], $locale))
            ->line(__('filament-user-profile::auth.email_code.line', [], $locale))
            ->line(e($this->code))
            ->line(__('filament-user-profile::auth.email_code.expires', ['minutes' => 10], $locale));
    }
}
