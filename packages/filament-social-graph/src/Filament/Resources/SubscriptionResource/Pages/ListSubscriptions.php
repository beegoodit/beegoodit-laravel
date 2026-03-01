<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\SubscriptionResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\SubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
