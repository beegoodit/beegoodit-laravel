<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Concerns;

use BeegoodIT\DateTimePicker\Enums\DateTimeRangeKind;
use BeegoodIT\DateTimePicker\Support\DateTimeRange;
use Carbon\Carbon;

trait InteractsWithDateTimePicker
{
    public bool $dateTimePickerOpen = false;

    abstract protected function dateTimePickerTimezone(): string;

    abstract protected function getDateTimePickerStart(): ?string;

    abstract protected function getDateTimePickerEnd(): ?string;

    abstract protected function setDateTimePickerRange(Carbon $start, Carbon $end): void;

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateTimePickerDefaultRange(): array
    {
        $timezone = $this->dateTimePickerTimezone();
        $start = Carbon::now($timezone)->subMonthNoOverflow()->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }

    public function toggleDateTimePicker(): void
    {
        $this->dateTimePickerOpen = ! $this->dateTimePickerOpen;
    }

    public function closeDateTimePicker(): void
    {
        $this->dateTimePickerOpen = false;
    }

    public function dateTimePickerPrevious(): void
    {
        $this->shiftDateTimePicker(-1);
    }

    public function dateTimePickerNext(): void
    {
        $this->shiftDateTimePicker(1);
    }

    public function dateTimePickerLabel(): string
    {
        return $this->dateTimePickerRange()->label();
    }

    public function dateTimePickerYear(): int
    {
        return $this->dateTimePickerAnchor()->year;
    }

    public function dateTimePickerMonth(): int
    {
        return $this->dateTimePickerAnchor()->month;
    }

    public function dateTimePickerGoToMonth(int $year, int $month): void
    {
        $month = max(1, min(12, $month));
        $timezone = $this->dateTimePickerTimezone();
        $anchor = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        $this->setDateTimePickerRange($anchor, $anchor->copy()->endOfMonth());
    }

    public function dateTimePickerGoToQuarter(int $year, int $quarter, bool $close = true): void
    {
        $quarter = max(1, min(4, $quarter));
        $timezone = $this->dateTimePickerTimezone();
        $startMonth = (($quarter - 1) * 3) + 1;
        $start = Carbon::create($year, $startMonth, 1, 0, 0, 0, $timezone)->startOfMonth();

        $this->setDateTimePickerRange($start, $start->copy()->addMonthsNoOverflow(2)->endOfMonth());

        if ($close) {
            $this->closeDateTimePicker();
        }
    }

    public function dateTimePickerGoToYear(int $year, bool $close = true): void
    {
        $timezone = $this->dateTimePickerTimezone();
        $start = Carbon::create($year, 1, 1, 0, 0, 0, $timezone)->startOfDay();

        $this->setDateTimePickerRange($start, $start->copy()->endOfYear());

        if ($close) {
            $this->closeDateTimePicker();
        }
    }

    public function dateTimePickerGoToDay(int $year, int $month, int $day, bool $close = true): void
    {
        $month = max(1, min(12, $month));
        $timezone = $this->dateTimePickerTimezone();
        $daysInMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->daysInMonth;
        $day = max(1, min($daysInMonth, $day));
        $date = Carbon::create($year, $month, $day, 0, 0, 0, $timezone)->startOfDay();

        $this->setDateTimePickerRange($date, $date->copy()->endOfDay());

        if ($close) {
            $this->closeDateTimePicker();
        }
    }

    public function dateTimePickerGoToToday(): void
    {
        $today = Carbon::now($this->dateTimePickerTimezone())->startOfDay();

        $this->setDateTimePickerRange($today, $today->copy()->endOfDay());
        $this->closeDateTimePicker();
    }

    public function dateTimePickerReset(): void
    {
        [$start, $end] = $this->dateTimePickerDefaultRange();

        $this->setDateTimePickerRange($start, $end);
        $this->closeDateTimePicker();
    }

    protected function shiftDateTimePicker(int $steps): void
    {
        $range = $this->dateTimePickerRange();
        $start = $range->start;
        $end = $range->end;

        match ($range->kind) {
            DateTimeRangeKind::Day => $this->setDateTimePickerRange(
                $start->copy()->addDays($steps)->startOfDay(),
                $start->copy()->addDays($steps)->endOfDay(),
            ),
            DateTimeRangeKind::Year => $this->dateTimePickerGoToYear($start->year + $steps, close: false),
            DateTimeRangeKind::Quarter => $this->dateTimePickerGoToQuarter(
                $start->copy()->addMonthsNoOverflow($steps * 3)->year,
                (int) ceil($start->copy()->addMonthsNoOverflow($steps * 3)->month / 3),
                close: false,
            ),
            DateTimeRangeKind::Month => $this->dateTimePickerGoToMonth(
                $start->copy()->addMonthsNoOverflow($steps)->year,
                $start->copy()->addMonthsNoOverflow($steps)->month,
            ),
            DateTimeRangeKind::Custom => $this->setDateTimePickerRange(
                $start->copy()->addDays($steps * ((int) $start->diffInDays($end) + 1))->startOfDay(),
                $end->copy()->addDays($steps * ((int) $start->diffInDays($end) + 1))->startOfDay(),
            ),
        };
    }

    protected function dateTimePickerRange(): DateTimeRange
    {
        [$start, $end] = $this->dateTimePickerBounds();

        return DateTimeRange::fromBounds($start, $end, $this->dateTimePickerTimezone());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateTimePickerBounds(): array
    {
        $timezone = $this->dateTimePickerTimezone();
        $startRaw = $this->getDateTimePickerStart();
        $endRaw = $this->getDateTimePickerEnd();

        $start = filled($startRaw)
            ? Carbon::parse((string) $startRaw, $timezone)->startOfDay()
            : $this->dateTimePickerAnchor()->startOfDay();
        $end = filled($endRaw)
            ? Carbon::parse((string) $endRaw, $timezone)->startOfDay()
            : $start->copy()->endOfMonth()->startOfDay();

        return [$start, $end];
    }

    protected function dateTimePickerAnchor(): Carbon
    {
        $timezone = $this->dateTimePickerTimezone();
        $start = $this->getDateTimePickerStart();

        if (filled($start)) {
            return Carbon::parse((string) $start, $timezone)->startOfMonth();
        }

        return Carbon::now($timezone)->subMonthNoOverflow()->startOfMonth();
    }
}
