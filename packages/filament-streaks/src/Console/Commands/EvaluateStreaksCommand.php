<?php

namespace BeegoodIT\FilamentStreaks\Console\Commands;

use BeegoodIT\FilamentStreaks\Actions\EvaluateStreakPeriods;
use Illuminate\Console\Command;

class EvaluateStreaksCommand extends Command
{
    protected $signature = 'filament-streaks:evaluate';

    protected $description = 'Evaluate open streak periods and break runs past grace with no qualifications';

    public function handle(EvaluateStreakPeriods $evaluateStreakPeriods): int
    {
        $count = $evaluateStreakPeriods->handle();

        $this->info("Broke {$count} streak(s).");

        return self::SUCCESS;
    }
}
