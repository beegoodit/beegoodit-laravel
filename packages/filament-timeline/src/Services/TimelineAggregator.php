<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentTimeline\Services;

use BeegoodIT\FilamentTimeline\Contracts\ProvidesTimeline;
use BeegoodIT\FilamentTimeline\Contracts\ProvidesTimelineEvent;
use BeegoodIT\FilamentTimeline\Contracts\TimelineProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TimelineAggregator
{
    /** @var array<int, class-string<TimelineProvider>> */
    protected array $providers = [];

    /**
     * @param  array<int, class-string<TimelineProvider>>  $providers
     */
    public function registerProviders(array $providers): void
    {
        $this->providers = array_unique(array_merge($this->providers, $providers));
    }

    /**
     * @return Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>
     */
    public function for(Model $subject): Collection
    {
        $entries = collect();

        // 1. Direct Model Implementation (Plural)
        if ($subject instanceof ProvidesTimeline) {
            $entries = $entries->merge($subject->toTimeline());
        }

        // 2. Direct Model Implementation (Singular)
        if ($subject instanceof ProvidesTimelineEvent) {
            $entries->push($subject->toTimelineEvent());
        }

        // 3. Registered Discovery Providers
        foreach ($this->providers as $providerClass) {
            /** @var TimelineProvider $provider */
            $provider = resolve($providerClass);
            $entries = $entries->merge($provider->discover($subject));
        }

        return $entries->sortByDesc('occurredAt')->values();
    }
}
