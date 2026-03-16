<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedSubscriptions extends ListRecords
{
    protected static string $resource = FeedSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
