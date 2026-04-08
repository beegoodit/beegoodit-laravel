<?php

namespace BeegoodIT\FilamentOpeningTimes\DataTransferObjects;

use Carbon\CarbonInterface;

final class ScheduleStatusResult
{
    public function __construct(
        public bool $hasActiveSchedule,
        public bool $isOpen,
        public ?CarbonInterface $nextTransition = null,
    ) {}

    /**
     * @return array{has_active_schedule: bool, is_open: bool, next_transition: ?string}
     */
    public function toArray(): array
    {
        return [
            'has_active_schedule' => $this->hasActiveSchedule,
            'is_open' => $this->isOpen,
            'next_transition' => $this->nextTransition?->toIso8601String(),
        ];
    }
}
