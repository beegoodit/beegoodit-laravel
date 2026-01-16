<?php

namespace BeeGoodIT\LaravelPwa\Filament\Pages;

use App\Models\User;
use BeeGoodIT\LaravelPwa\Services\PushNotificationService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification as FilamentNotification;

use Filament\Pages\Page;
use UnitEnum;
use App\Filament\Traits\HasModelTranslations;

class BroadcastPushNotification extends Page
{
    use HasModelTranslations;
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected string $view = 'laravel-pwa::filament.pages.broadcast-push-notification';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'target_type' => 'all',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('target_type')
                    ->label(__('Target'))
                    ->options([
                        'all' => __('All Users'),
                        'users' => __('Specific Users'),
                    ])
                    ->required()
                    ->live(),

                Select::make('users')
                    ->label(__('Users'))
                    ->multiple()
                    ->searchable()
                    ->options(User::query()->pluck('name', 'id'))
                    ->required()
                    ->visible(fn (Get $get) => $get('target_type') === 'users'),

                TextInput::make('title_input')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('e.g. New Event Published!')),

                Textarea::make('body')
                    ->label(__('Body'))
                    ->required()
                    ->maxLength(500)
                    ->rows(4)
                    ->placeholder(__('e.g. A new tournament is available in your city.')),

                TextInput::make('action_url')
                    ->label(__('Action URL (optional)'))
                    ->url()
                    ->placeholder('https://...'),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $pushService = app(PushNotificationService::class);

        $payload = [
            'title' => $data['title_input'],
            'body' => $data['body'],
            'data' => [
                'url' => $data['action_url'] ?: null,
            ],
        ];

        if ($data['target_type'] === 'all') {
            $count = $pushService->sendToAll($payload);
        } else {
            $count = $pushService->sendToUsers($data['users'], $payload);
        }

        FilamentNotification::make()
            ->title('Push Sent')
            ->body("Successfully sent push notification to {$count} subscriptions.")
            ->success()
            ->send();

        $this->form->fill([
            'target_type' => $data['target_type'],
            'users' => $data['users'] ?? [],
        ]);
    }

    protected static UnitEnum|string|null $navigationGroup = 'navigation.groups.settings';

    public static function getNavigationLabel(): string
    {
        return 'Push Broadcast';
    }
}

