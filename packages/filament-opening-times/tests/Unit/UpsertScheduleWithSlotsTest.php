<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Actions\UpsertScheduleWithSlots;
use BeegoodIT\FilamentOpeningTimes\Tests\Fixtures\TestOpenable;
use BeegoodIT\FilamentOpeningTimes\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UpsertScheduleWithSlotsTest extends TestCase
{
    public function test_adjacent_active_windows_allowed(): void
    {
        $openable = TestOpenable::query()->create();

        UpsertScheduleWithSlots::run(
            null,
            $openable->getMorphClass(),
            (string) $openable->getKey(),
            'UTC',
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-06-30 23:59:59'),
            [],
        );

        $second = UpsertScheduleWithSlots::run(
            null,
            $openable->getMorphClass(),
            (string) $openable->getKey(),
            'UTC',
            Carbon::parse('2026-07-01 00:00:00'),
            Carbon::parse('2026-12-31'),
            [],
        );

        $this->assertNotNull($second->getKey());
    }

    public function test_overlapping_active_window_rejected(): void
    {
        $this->expectException(ValidationException::class);

        $openable = TestOpenable::query()->create();

        UpsertScheduleWithSlots::run(
            null,
            $openable->getMorphClass(),
            (string) $openable->getKey(),
            'UTC',
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-12-31'),
            [],
        );

        UpsertScheduleWithSlots::run(
            null,
            $openable->getMorphClass(),
            (string) $openable->getKey(),
            'UTC',
            Carbon::parse('2026-06-01'),
            Carbon::parse('2027-01-01'),
            [],
        );
    }
}
