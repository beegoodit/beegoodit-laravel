<?php

namespace BeegoodIT\FilamentStreaks\Events;

use BeegoodIT\FilamentStreaks\Models\Streak;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreakAtRisk
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Streak $streak,
        public string $periodKey,
    ) {}
}
