<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\SubscriptionResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\SubscriptionResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

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
