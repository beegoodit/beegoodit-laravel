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
        $data = $this->ensureSubscribableTypeAndId($data);

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
     * @return array<string, mixed>
     */
    protected function ensureSubscribableTypeAndId(array $data): array
    {
        $subscribable = $data['subscribable'] ?? null;
        if (is_string($subscribable) && str_contains($subscribable, '|')) {
            [$type, $id] = explode('|', $subscribable, 2);
            if ($type !== '' && $id !== '') {
                $data['subscribable_type'] = $type;
                $data['subscribable_id'] = $id;
            }
        }

        return $data;
    }
}
