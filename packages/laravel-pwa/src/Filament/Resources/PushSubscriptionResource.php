<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources;

use BeegoodIT\LaravelPwa\Filament\Resources\PushSubscriptionResource\Pages;
use BeegoodIT\LaravelPwa\Messages\WebPushMessage;
use BeegoodIT\LaravelPwa\Models\Notifications\PushSubscription;
use BeegoodIT\LaravelPwa\Services\PushNotificationService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PushSubscriptionResource extends Resource
{
    protected static ?string $model = PushSubscription::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function getModelLabel(): string
    {
        return __('laravel-pwa::notifications.subscriptions.resource_label');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getPluralModelLabel(): string
    {
        return __('laravel-pwa::notifications.subscriptions.resource_label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('laravel-pwa::notifications.subscriptions.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('laravel-pwa::notifications.nav.group');
    }

    public static function getNavigationSort(): ?int
    {
        return 40;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('laravel-pwa::broadcast.fields.user.label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('endpoint')
                    ->label(__('laravel-pwa::broadcast.fields.endpoint.label'))
                    ->limit(30)
                    ->copyable()
                    ->tooltip(fn ($record) => $record->endpoint),

                TextColumn::make('content_encoding')
                    ->label(__('laravel-pwa::broadcast.fields.encoding.label'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('laravel-pwa::broadcast.fields.created_at.label'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('test_notification')
                    ->label(__('laravel-pwa::broadcast.buttons.test_notification'))
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->action(function (PushSubscription $record, PushNotificationService $service) {
                        $payload = (new WebPushMessage)
                            ->title('🧪 Test Notification')
                            ->body('Your PWA push subscription is working correctly!')
                            ->icon('/icons/icon-192x192.png')
                            ->data(['url' => '/'])
                            ->toArray();

                        $success = $service->send($record, $payload);

                        if ($success) {
                            Notification::make()
                                ->title(__('laravel-pwa::broadcast.notifications.test_sent.title'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('laravel-pwa::broadcast.notifications.test_failed.title'))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPushSubscriptions::route('/'),
        ];
    }
}
