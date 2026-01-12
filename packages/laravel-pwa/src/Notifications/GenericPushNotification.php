<?php

namespace BeeGoodIT\LaravelPwa\Notifications;

use BeeGoodIT\LaravelPwa\Messages\WebPushMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GenericPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected string $title,
        protected string $body,
        protected ?string $actionUrl = null,
        protected ?string $icon = null
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['webPush'];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        $message = (new WebPushMessage)
            ->title($this->title)
            ->body($this->body);

        if ($this->actionUrl) {
            $message->action('Open', $this->actionUrl);
            $message->data(['url' => $this->actionUrl]);
        }

        if ($this->icon) {
            $message->icon($this->icon);
        }

        return $message;
    }
}
