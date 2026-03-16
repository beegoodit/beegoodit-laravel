<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedSubscriptionRuleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedSubscriptionRule extends CreateRecord
{
    protected static string $resource = FeedSubscriptionRuleResource::class;

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
