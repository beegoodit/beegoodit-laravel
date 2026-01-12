<?php

namespace BeeGoodIT\LaravelPwa\Filament\Pages;

use BeeGoodIT\LaravelPwa\Services\PushNotificationService;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;

class BroadcastPushNotification extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected string $view = 'laravel-pwa::filament.pages.broadcast-push-notification';

    public string $title_input = '';

    public string $body = '';

    public string $action_url = '';

    public function submit(): void
    {
        $this->validate([
            'title_input' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'action_url' => 'nullable|url',
        ]);

        $pushService = app(PushNotificationService::class);

        $count = $pushService->sendToAll([
            'title' => $this->title_input,
            'body' => $this->body,
            'data' => [
                'url' => $this->action_url ?: null,
            ],
        ]);

        FilamentNotification::make()
            ->title('Broadcast Sent')
            ->body("Successfully sent push notification to {$count} subscriptions.")
            ->success()
            ->send();

        $this->reset(['title_input', 'body', 'action_url']);
    }

    public static function getNavigationLabel(): string
    {
        return 'Push Broadcast';
    }
}
