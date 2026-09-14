<?php

namespace BeegoodIT\FilamentStreaks\Console\Commands;

use BeegoodIT\FilamentStreaks\Actions\CreateStreakHistoryRun;
use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class HistoryStreaksCommand extends Command
{
    protected $signature = 'filament-streaks:history {from} {to} {--sync : Run in the current process instead of queuing}';

    protected $description = 'Queue (or sync) a streak history replay over a date range';

    public function handle(): int
    {
        $from = Carbon::parse($this->argument('from'))->startOfDay();
        $to = Carbon::parse($this->argument('to'))->endOfDay();
        $sync = (bool) $this->option('sync');

        $run = CreateStreakHistoryRun::run(
            from: $from,
            to: $to,
            requestedBy: null,
            sync: $sync,
        );

        if ($sync) {
            $status = $run->status === OperationRunStatus::Succeeded ? 'succeeded' : $run->status->value;

            $this->info("History replay {$status} for {$from->toDateString()} → {$to->toDateString()} (run {$run->getKey()}).");

            if ($run->status === OperationRunStatus::Failed) {
                $this->error($run->error ?? 'Unknown error');

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        $this->info("Queued history run {$run->getKey()} for {$from->toDateString()} → {$to->toDateString()}.");

        return self::SUCCESS;
    }
}
