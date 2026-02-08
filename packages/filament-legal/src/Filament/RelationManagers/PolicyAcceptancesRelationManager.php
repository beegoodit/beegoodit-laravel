<?php

namespace BeegoodIT\FilamentLegal\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PolicyAcceptancesRelationManager extends RelationManager
{
    protected static string $relationship = 'acceptances';

    protected static ?string $recordTitleAttribute = 'ip_address';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-legal::messages.Policy Acceptances');
    }

    protected static \BackedEnum|string|null $icon = 'heroicon-o-shield-check';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('filament-legal::messages.User'))
                    ->searchable()
                    ->sortable()
                    ->hidden(fn ($livewire): bool => $livewire->getOwnerRecord() instanceof \App\Models\User),
                TextColumn::make('policy.type')
                    ->label(__('filament-legal::messages.Policy Type'))
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'privacy' => __('filament-legal::messages.Privacy Policy'),
                        'terms' => __('filament-legal::messages.Terms of Service'),
                        'imprint' => __('filament-legal::messages.Imprint'),
                        'cookie' => __('filament-legal::messages.Cookie Policy'),
                        default => ucfirst((string) $state),
                    })
                    ->sortable()
                    ->hidden(fn ($livewire): bool => $livewire->getOwnerRecord() instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy),
                TextColumn::make('policy.version')
                    ->label(__('filament-legal::messages.Version'))
                    ->sortable()
                    ->hidden(fn ($livewire): bool => $livewire->getOwnerRecord() instanceof \BeegoodIT\FilamentLegal\Models\LegalPolicy),
                TextColumn::make('ip_address')
                    ->label(__('filament-legal::messages.IP Address'))
                    ->searchable(),
                TextColumn::make('accepted_at')
                    ->label(__('filament-legal::messages.Accepted At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->url(
                        fn ($record): string => $this->getOwnerRecord() instanceof \App\Models\User
                        ? \BeegoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource::getUrl('edit', ['record' => $record->legal_policy_id])
                        : route('filament.admin.resources.users.edit', ['record' => $record->user_id])
                    ),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }
}
