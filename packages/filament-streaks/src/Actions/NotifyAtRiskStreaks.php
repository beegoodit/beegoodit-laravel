<?php

namespace BeegoodIT\FilamentStreaks\Actions;

use BeegoodIT\FilamentStreaks\Events\StreakAtRisk;
use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Notifications\AtRiskNotification;
use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use BeegoodIT\FilamentStreaks\Support\StreakAtRiskEvaluator;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class NotifyAtRiskStreaks
{
    use AsAction;

    public function __construct(
        private StrategyResolver $strategyResolver,
        private StreakAtRiskEvaluator $atRiskEvaluator,
    ) {}

    public function handle(?Carbon $asOf = null): int
    {
        $notifiedCount = 0;

        Streak::query()
            ->open()
            ->with(['subject', 'scope', 'qualifications'])
            ->each(function (Streak $streak) use ($asOf, &$notifiedCount): void {
                if ($this->notifyIfAtRisk($streak, $asOf)) {
                    $notifiedCount++;
                }
            });

        return $notifiedCount;
    }

    private function notifyIfAtRisk(Streak $streak, ?Carbon $asOf): bool
    {
        $subject = $streak->subject;

        if ($subject === null || ! method_exists($subject, 'getTimezone')) {
            return false;
        }

        $strategyConfig = $this->strategyResolver->config($streak->strategy_identifier);

        if (! ($strategyConfig['at_risk']['notifications'] ?? false)) {
            return false;
        }

        if (($strategyConfig['open_on'] ?? 'first_event') === 'at_risk' && $streak->wasRecentlyCreated) {
            return false;
        }

        if (! $this->atRiskEvaluator->isAtRisk($streak, $asOf)) {
            return false;
        }

        $period = $this->atRiskEvaluator->currentPeriod($streak, $asOf);

        if ($period === null) {
            return false;
        }

        $alreadySent = Notification::query()
            ->where('streak_id', $streak->getKey())
            ->where('period_key', $period['key'])
            ->where('kind', Notification::KIND_AT_RISK)
            ->exists();

        if ($alreadySent) {
            return false;
        }

        event(new StreakAtRisk($streak, $period['key']));

        if (method_exists($subject, 'notify')) {
            $subject->notify(new AtRiskNotification($streak, $period['key']));
        }

        Notification::query()->create([
            'streak_id' => $streak->getKey(),
            'period_key' => $period['key'],
            'kind' => Notification::KIND_AT_RISK,
            'sent_at' => now(),
        ]);

        return true;
    }
}
