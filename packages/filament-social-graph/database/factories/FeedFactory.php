<?php

namespace BeegoodIT\FilamentSocialGraph\Database\Factories;

use BeegoodIT\FilamentSocialGraph\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BeegoodIT\FilamentSocialGraph\Models\Feed>
 */
class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition(): array
    {
        $ownerModels = config('filament-social-graph.owner_models', config('filament-social-graph.entity_models', []));
        $ownerType = $ownerModels[0] ?? \App\Models\Team::class;

        return [
            'owner_type' => $ownerType,
            'owner_id' => Str::uuid(),
        ];
    }

    public function forOwner(\Illuminate\Database\Eloquent\Model $owner): static
    {
        return $this->state(fn (): array => [
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
        ]);
    }
}
