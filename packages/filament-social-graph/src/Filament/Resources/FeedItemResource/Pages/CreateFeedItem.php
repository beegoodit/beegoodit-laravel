<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use BeegoodIT\FilamentSocialGraph\Models\Feed;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFeedItem extends CreateRecord
{
    protected static string $resource = FeedItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $owner = $this->resolveOwnerFromData($data);
        if ($owner === null) {
            throw ValidationException::withMessages([
                'data.owner' => __('filament-social-graph::feed_item.owner_required_hint'),
            ]);
        }

        $feed = Feed::firstOrCreateForOwner($owner);
        $data['feed_id'] = $feed->getKey();
        unset($data['owner']);

        if (config('filament-social-graph.tenancy.enabled')) {
            $tenant = Filament::getTenant();
            if ($tenant !== null) {
                $data['team_id'] = $tenant->getKey();
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveOwnerFromData(array $data): ?\Illuminate\Database\Eloquent\Model
    {
        $owner = $data['owner'] ?? null;
        if ($owner instanceof \Illuminate\Database\Eloquent\Model) {
            return $owner;
        }
        if (is_string($owner) && str_contains($owner, '|')) {
            [$ownerType, $ownerId] = explode('|', $owner, 2);
            if (! empty($ownerType) && ! empty($ownerId)) {
                return $ownerType::query()->find($ownerId);
            }
        }
        $ownerType = is_array($owner) ? ($owner['owner_type'] ?? null) : ($data['owner_type'] ?? null);
        $ownerId = is_array($owner) ? ($owner['owner_id'] ?? null) : ($data['owner_id'] ?? null);
        if (! empty($ownerType) && ! empty($ownerId)) {
            return $ownerType::query()->find($ownerId);
        }
        if (config('filament-social-graph.tenancy.enabled')) {
            $tenant = Filament::getTenant();
            if ($tenant !== null) {
                return $tenant;
            }
        }

        return null;
    }
}
