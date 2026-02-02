<?php

namespace BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource\Pages;

use App\Models\User;
use BeegoodIT\LaravelPwa\Filament\Resources\BroadcastResource;
use BeegoodIT\LaravelPwa\Models\Notifications\Broadcast;
use BeegoodIT\LaravelPwa\Notifications\Jobs\ProcessBroadcastJob;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class CreateBroadcast extends CreateRecord
{
    protected static string $resource = BroadcastResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title_input')
                    ->label(__('laravel-pwa::broadcast.fields.title.label'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('laravel-pwa::broadcast.fields.title.placeholder')),

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
            ->columns(1);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $broadcast = Broadcast::create([
            'trigger_type' => 'manual',
            'target_ids' => $data['target_type'] === 'users' ? $data['users'] : null,
            'payload' => [
                'title' => $data['title_input'],
                'body' => $data['body'],
                'data' => [
                    'url' => $data['action_url'] ?: null,
                ],
            ],
            'status' => 'pending',
        ]);

        dispatch(new ProcessBroadcastJob($broadcast))
            ->onQueue(config('pwa.notifications.queue', 'default'));

        return $broadcast;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('laravel-pwa::broadcast.notifications.success.title');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
