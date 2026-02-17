<?php

namespace BeegoodIT\FilamentTimeline\Components;

use BeegoodIT\FilamentTimeline\Services\TimelineAggregator;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class TimelineWidget extends Widget
{
    protected string $view = 'filament-timeline::livewire.timeline-widget';

    protected int|string|array $columnSpan = 'full';

    public ?Model $subject = null;

    public string $direction = 'vertical';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'entries' => $this->getEntries(),
        ];
    }

    /**
     * @return Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>
     */
    public function getEntries(): Collection
    {
        if (! $this->subject instanceof \Illuminate\Database\Eloquent\Model) {
            return collect();
        }

        $entries = resolve(TimelineAggregator::class)->for($this->subject);

        return $this->direction === 'horizontal'
            ? $entries->reverse()->values()
            : $entries;
    }

    /**
     * @return Collection<string, Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry>>
     */
    public function getGroupedEntries(): Collection
    {
        /** @var Collection<int, \BeegoodIT\FilamentTimeline\Data\TimelineEntry> $entries */
        $entries = $this->getEntries();

        return $entries->groupBy(fn ($entry) => $entry->occurredAt
            ? (string) $entry->occurredAt->year
            : __('filament-timeline::messages.origin'));
    }

    #[On('timeline-refresh')]
    public function refresh(): void
    {
        \Illuminate\Support\Sleep::usleep(500000); // 500ms for visual feedback
    }
}
