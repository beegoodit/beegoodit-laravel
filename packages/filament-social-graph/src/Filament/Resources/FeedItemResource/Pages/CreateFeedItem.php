<?php

namespace BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource\Pages;

use BeegoodIT\FilamentSocialGraph\Filament\Resources\FeedItemResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFeedItem extends CreateRecord
{
    protected static string $resource = FeedItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Flatten nested actor from MorphToSelect (actor => [actor_type, actor_id])
        if (isset($data['actor']) && is_array($data['actor'])) {
            $data['actor_type'] = $data['actor']['actor_type'] ?? $data['actor_type'] ?? null;
            $data['actor_id'] = $data['actor']['actor_id'] ?? $data['actor_id'] ?? null;
            unset($data['actor']);
        }

        if (config('filament-social-graph.tenancy.enabled')) {
            $tenant = Filament::getTenant();
            if ($tenant !== null) {
                $data['team_id'] = $tenant->getKey();
                if (empty($data['actor_type']) || empty($data['actor_id'])) {
                    $data['actor_type'] = $tenant::class;
                    $data['actor_id'] = $tenant->getKey();
                }
            }
        }

        if (empty($data['actor_type']) || empty($data['actor_id'])) {
            throw ValidationException::withMessages([
                'data.actor' => __('filament-social-graph::feed_item.actor_required_hint'),
            ]);
        }

        return $data;
    }
}
