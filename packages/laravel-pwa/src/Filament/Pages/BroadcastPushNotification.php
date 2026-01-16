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
                    ->label(__('laravel-pwa::broadcast.fields.target_type.label'))
                    ->options([
                        'all' => __('laravel-pwa::broadcast.fields.target_type.options.all'),
                        'users' => __('laravel-pwa::broadcast.fields.target_type.options.users'),
                    ])
                    ->required()
                    ->live(),

                Select::make('users')
                    ->label(__('laravel-pwa::broadcast.fields.users.label'))
                    ->multiple()
                    ->searchable()
                    ->options(User::query()->pluck('name', 'id'))
                    ->required()
                    ->visible(fn (Get $get): bool => $get('target_type') === 'users'),

                TextInput::make('title_input')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('laravel-pwa::broadcast.fields.title.placeholder')),

                Textarea::make('body')
                    ->label(__('laravel-pwa::broadcast.fields.body.label'))
                    ->required()
                    ->maxLength(500)
                    ->rows(4)
                    ->placeholder(__('laravel-pwa::broadcast.fields.body.placeholder')),

                TextInput::make('action_url')
                    ->label(__('laravel-pwa::broadcast.fields.action_url.label'))
                    ->url()
                    ->placeholder(__('laravel-pwa::broadcast.fields.action_url.placeholder')),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $pushService = resolve(PushNotificationService::class);

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
            ->title(__('laravel-pwa::broadcast.notifications.success.title'))
            ->body(__('laravel-pwa::broadcast.notifications.success.body', ['count' => $count]))
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
        return __('laravel-pwa::broadcast.navigation_label');
    }
}

