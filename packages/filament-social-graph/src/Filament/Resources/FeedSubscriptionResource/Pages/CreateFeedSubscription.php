<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedSubscription extends CreateRecord
{
    protected static string $resource = FeedSubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (config('filament-social-graph.tenancy.enabled')) {
            $tenant = Filament::getTenant();
            if ($tenant !== null) {
                $data['team_id'] = $tenant->getKey();
            }
        }

        return $data;
    }
}
