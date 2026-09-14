<?php

namespace BeegoodIT\FilamentStreaks\Actions;

use BeegoodIT\FilamentStreaks\Events\StreakBroken;
use BeegoodIT\FilamentStreaks\Models\Notification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Notifications\BrokenNotification;
use BeegoodIT\FilamentStreaks\Support\DurationParser;
use BeegoodIT\FilamentStreaks\Support\StrategyResolver;
use BeegoodIT\FilamentStreaks\Support\StreakPeriodBounds;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class EvaluateStreakPeriods
{
    use AsAction;

    public function __construct(
        private StrategyResolver $strategyResolver,
        private DurationParser $durationParser,
        private StreakPeriodBounds $periodBounds,
    ) {}

    public function handle(?Carbon $asOf = null): int
    {
        $brokenCount = 0;

        Streak::query()
            ->open()
            ->with(['subject', 'scope', 'qualifications'])
            ->each(function (Streak $streak) use ($asOf, &$brokenCount): void {
                if ($this->evaluateStreak($streak, $asOf)) {
                    $brokenCount++;
                }
            });

        return $brokenCount;
    }

    private function evaluateStreak(Streak $streak, ?Carbon $asOf): bool
    {
        $subject = $streak->subject;

        if ($subject === null || ! method_exists($subject, 'getTimezone')) {
            return false;
        }

        $strategyConfig = $this->strategyResolver->config($streak->strategy_identifier);
        $strategy = $this->strategyResolver->resolve($streak->strategy_identifier);
        $timezone = $subject->getTimezone();
        $now = ($asOf ?? Carbon::now())->timezone($timezone);
        $activeTo = $strategy->activeTo($streak->scope);

        if ($activeTo !== null && $now->gt(Carbon::parse($activeTo, $timezone)->endOfDay())) {
            if (($strategyConfig['when_inactive'] ?? 'break') === 'break') {
                $this->closeStreak($streak, 'inactive', silent: true);

                return true;
            }

            return false;
        }

        $periodicity = $strategy->periodicity();
        $from = Carbon::parse($streak->start_at, $timezone)->startOfDay();
        $periods = $periodicity->periodsBetween($from, $now, $timezone);
        $grace = $this->durationParser->toInterval($strategyConfig['breaks']['grace_period'] ?? '2d');

        foreach ($periods as $period) {
            if ($now->lte(Carbon::parse($period['end'])->timezone($timezone)->add($grace))) {
                continue;
            }

            $hasQualification = $streak->qualifications->contains(function ($qualification) use ($period): bool {
                $happenedAt = Carbon::parse($qualification->happened_at);

                return $happenedAt->betweenIncluded($period['start'], $period['end']);
            });

            if ($hasQualification) {
                continue;
            }

            $this->closeStreak(
                streak: $streak,
                periodKey: $period['key'],
                silent: false,
                notify: (bool) ($strategyConfig['breaks']['notifications'] ?? false),
            );

            return true;
        }

        return false;
    }

    private function closeStreak(
        Streak $streak,
        string $periodKey,
        bool $silent,
        bool $notify = false,
    ): void {
        $strategy = $this->strategyResolver->resolve($streak->strategy_identifier);
        $timezone = $streak->subject->getTimezone();

        $streak->update([
            'end_at' => $this->periodBounds->lastQualifiedPeriodEndDate(
                $streak,
                $strategy->periodicity(),
                $timezone,
            ),
        ]);

        event(new StreakBroken($streak->fresh(), $periodKey, $silent));

        if ($silent || ! $notify) {
            return;
        }

        $strategyConfig = $this->strategyResolver->config($streak->strategy_identifier);
        $delay = $this->durationParser->toInterval($strategyConfig['breaks']['notification_delay'] ?? '0m');

        $this->sendBrokenNotification($streak, $periodKey, $delay);
    }

    private function sendBrokenNotification(Streak $streak, string $periodKey, \DateInterval $delay): void
    {
        $alreadySent = Notification::query()
            ->where('streak_id', $streak->getKey())
            ->where('period_key', $periodKey)
            ->where('kind', Notification::KIND_BROKEN)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $notifiable = $streak->subject;

        if ($notifiable !== null && method_exists($notifiable, 'notify')) {
            $notification = new BrokenNotification($streak, $periodKey);

            if ((int) $delay->totalSeconds > 0) {
                $notification->delay(now()->add($delay));
            }

            $notifiable->notify($notification);
        }

        Notification::query()->create([
            'streak_id' => $streak->getKey(),
            'period_key' => $periodKey,
            'kind' => Notification::KIND_BROKEN,
            'sent_at' => (int) $delay->totalSeconds > 0 ? now()->add($delay) : now(),
        ]);
    }
}
