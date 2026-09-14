<?php

namespace BeegoodIT\FilamentStreaks\Actions;

use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use BeegoodIT\FilamentStreaks\Jobs\ProcessStreakHistoryRun;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateStreakHistoryRun
{
    use AsAction;

    public function handle(
        CarbonInterface $from,
        CarbonInterface $to,
        ?Authenticatable $requestedBy = null,
        bool $sync = false,
    ): OperationRun {
        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        if ($to->lt($from)) {
            throw new \InvalidArgumentException('The end date must be on or after the start date.');
        }

        $run = OperationRun::query()->create([
            'type' => OperationRun::TYPE_STREAK_HISTORY,
            'params' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'status' => OperationRunStatus::Queued,
            'requested_by' => $requestedBy?->getAuthIdentifier(),
        ]);

        if ($sync) {
            ProcessStreakHistoryRun::dispatchSync($run);
        } else {
            ProcessStreakHistoryRun::dispatch($run);
        }

        return $run->fresh() ?? $run;
    }
}
