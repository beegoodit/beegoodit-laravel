<?php

namespace Beegoodit\FilamentConnect\Filament\RelationManagers;

use Beegoodit\FilamentConnect\Facades\Connect;
use Beegoodit\FilamentConnect\Models\ApiAccount;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ConnectRelationManager extends RelationManager
{
    protected static string $relationship = 'apiAccounts';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service')
                    ->label(__('Service'))
                    ->options(collect(Connect::getServices())->mapWithKeys(fn($class, $name) => [$name => (new $class)->getName()]))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('credentials', [])),

                TextInput::make('name')
                    ->label(__('Account Name'))
                    ->placeholder('e.g. Primary Tournament.io Account')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label(__('Active'))
                    ->default(true),

                \Filament\Schemas\Components\Group::make()
                    ->schema(function (callable $get) {
                        $serviceName = $get('service');
                        if (!$serviceName) {
                            return [];
                        }

                        $serviceClass = Connect::getService($serviceName);
                        if (!$serviceClass) {
                            return [];
                        }

                        return (new $serviceClass)->getFormSchema();
                    })
                    ->statePath('credentials')
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),

                TextColumn::make('service')
                    ->label(__('Service'))
                    ->formatStateUsing(function ($state) {
                        $serviceClass = Connect::getService($state);
                        return $serviceClass ? (new $serviceClass)->getName() : $state;
                    }),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                Action::make('testConnection')
                    ->label(__('Test Connection'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (ApiAccount $record): void {
                        $serviceClass = Connect::getService($record->service);
                        if (!$serviceClass) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Service not found'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $success = (new $serviceClass)->validate($record->credentials);

                        if ($success) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Connection successful!'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title(__('Connection failed.'))
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
