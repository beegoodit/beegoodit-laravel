<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedItem extends CreateRecord
{
    protected static string $resource = FeedItemResource::class;

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
