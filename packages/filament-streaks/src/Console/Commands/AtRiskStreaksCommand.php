<?php

namespace BeegoodIT\FilamentStreaks\Console\Commands;

use BeegoodIT\FilamentStreaks\Actions\NotifyAtRiskStreaks;
use Illuminate\Console\Command;

class AtRiskStreaksCommand extends Command
{
    protected $signature = 'filament-streaks:at-risk';

    protected $description = 'Send at-risk notifications for open streaks within the lead window';

    public function handle(NotifyAtRiskStreaks $notifyAtRiskStreaks): int
    {
        $count = $notifyAtRiskStreaks->handle();

        $this->info("Sent {$count} at-risk notification(s).");

        return self::SUCCESS;
    }
}
