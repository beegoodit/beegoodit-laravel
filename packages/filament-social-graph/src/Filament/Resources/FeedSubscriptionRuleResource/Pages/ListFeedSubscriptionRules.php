<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedSubscriptionRules extends ListRecords
{
    protected static string $resource = FeedSubscriptionRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
