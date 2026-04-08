<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Support\SlotValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SlotValidatorTest extends TestCase
{
    public function test_same_day_overlap_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SlotValidator::validate([
            ['day_of_week' => 1, 'opens_at' => '09:00:00', 'closes_at' => '13:00:00'],
            ['day_of_week' => 1, 'opens_at' => '12:00:00', 'closes_at' => '17:00:00'],
        ]);
    }

    public function test_overnight_splits_and_allows_non_overlapping_morning_tail(): void
    {
        SlotValidator::validate([
            ['day_of_week' => 0, 'opens_at' => '22:00:00', 'closes_at' => '02:00:00'],
        ]);

        $this->assertTrue(true);
    }

    public function test_overnight_tail_overlap_on_next_day_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SlotValidator::validate([
            ['day_of_week' => 0, 'opens_at' => '22:00:00', 'closes_at' => '04:00:00'],
            ['day_of_week' => 1, 'opens_at' => '03:00:00', 'closes_at' => '05:00:00'],
        ]);
    }
}
