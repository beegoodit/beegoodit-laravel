<?php

use BeegoodIT\FilamentTimeline\Components\TimelineWidget;
use BeegoodIT\FilamentTimeline\Data\TimelineEntry;
use BeegoodIT\FilamentTimeline\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

// Mock context for Subject
class SubjectModel extends Model
{
    protected $guarded = [];
}

uses(TestCase::class);

it('groups timeline entries by year correctly', function () {
    $subject = new SubjectModel(['id' => 1]);

    // Create a mock widget that returns specific entries
    $widget = new class extends TimelineWidget
    {
        public Collection $mockEntries;

        public function getEntries(): Collection
        {
            return $this->mockEntries;
        }
    };

    $widget->subject = $subject;
    $widget->mockEntries = collect([
        new TimelineEntry(title: 'Item 1', occurredAt: Carbon::parse('2024-01-01')),
        new TimelineEntry(title: 'Item 2', occurredAt: Carbon::parse('2024-06-01')),
        new TimelineEntry(title: 'Item 3', occurredAt: Carbon::parse('2025-01-01')),
        new TimelineEntry(title: 'Origin', occurredAt: null),
    ]);

    $grouped = $widget->getGroupedEntries();

    expect($grouped)->toHaveCount(3)
        ->and($grouped->has('2024'))->toBeTrue()
        ->and($grouped->has('2025'))->toBeTrue()
        ->and($grouped->has('Start'))->toBeTrue(); // 'Start' is the default translation for 'origin' in lang/en/messages.php

    expect($grouped->get('2024'))->toHaveCount(2)
        ->and($grouped->get('2025'))->toHaveCount(1)
        ->and($grouped->get('Start'))->toHaveCount(1);
});
