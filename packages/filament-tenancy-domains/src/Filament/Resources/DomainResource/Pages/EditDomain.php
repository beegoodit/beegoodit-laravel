<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource\Pages;

use BeegoodIT\FilamentTenancyDomains\Filament\Resources\DomainResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDomain extends EditRecord
{
    protected static string $resource = DomainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('verify')
                ->label(__('filament-tenancy-domains::domains.verify_now'))
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->action(function (): void {
                    if ($this->record->verify()) {
                        Notification::make()
                            ->title(__('filament-tenancy-domains::domains.verification_success'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('filament-tenancy-domains::domains.verification_failed'))
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                })
                ->visible(function (): bool {
                    try {
                        return $this->record->type !== 'platform' && ! $this->record->is_verified;
                    } catch (\Error) {
                        return false;
                    }
                }),
            Actions\DeleteAction::make()
                ->after(fn () => $this->redirect(static::$resource::getUrl('index'))),
        ];
    }
}
