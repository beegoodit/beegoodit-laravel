<?php

namespace BeegoodIT\FilamentStreaks\Notifications;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Streak $streak,
        public string $periodKey,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $webPushClass = 'BeegoodIT\\LaravelPwa\\Messages\\WebPushMessage';

        return array_values(array_filter(
            config('filament-streaks.notification_channels', ['mail']),
            fn (string $channel): bool => $channel !== 'webPush' || class_exists($webPushClass),
        ));
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('filament-streaks::notification.mail.broken_subject'))
            ->line(__('filament-streaks::notification.mail.broken_body', [
                'strategy' => $this->streak->strategy_identifier,
                'period' => $this->periodKey,
            ]));
    }

    public function toWebPush(object $notifiable): object
    {
        $webPushClass = 'BeegoodIT\\LaravelPwa\\Messages\\WebPushMessage';

        return (new $webPushClass)
            ->title(__('filament-streaks::notification.push.broken_title'))
            ->body(__('filament-streaks::notification.push.broken_body', [
                'strategy' => $this->streak->strategy_identifier,
                'period' => $this->periodKey,
            ]))
            ->url(url('/player'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'streak_id' => $this->streak->getKey(),
            'strategy_identifier' => $this->streak->strategy_identifier,
            'period_key' => $this->periodKey,
            'kind' => 'broken',
        ];
    }
}
