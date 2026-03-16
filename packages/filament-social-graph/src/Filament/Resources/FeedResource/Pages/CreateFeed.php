<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeed extends CreateRecord
{
    protected static string $resource = FeedResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['owner']) && is_string($data['owner'])) {
            $parts = explode('|', $data['owner'], 2);
            if (count($parts) === 2) {
                $data['owner_type'] = $parts[0];
                $data['owner_id'] = $parts[1];
            }
            unset($data['owner']);
        }

        return $data;
    }
}
