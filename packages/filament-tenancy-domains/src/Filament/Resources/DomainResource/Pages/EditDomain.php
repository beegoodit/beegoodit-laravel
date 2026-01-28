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
                ->action(function () {
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
                ->visible(fn () => $this->record->type !== 'platform' && !$this->record->is_verified),
            Actions\DeleteAction::make(),
        ];
    }
}
