<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Actions\CreateStreakHistoryRun;
use BeegoodIT\FilamentStreaks\Actions\ReplayStreakHistory;
use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use BeegoodIT\FilamentStreaks\Jobs\ProcessStreakHistoryRun;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\StreakEvent;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;

it('queues a history run by default', function (): void {
    Bus::fake();

    $user = User::query()->create([
        'name' => 'Admin',
        'email' => 'history-queue@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
        'is_admin' => true,
    ]);

    $run = CreateStreakHistoryRun::run(
        from: Carbon::parse('2026-01-01'),
        to: Carbon::parse('2026-03-31'),
        requestedBy: $user,
    );

    expect($run->type)->toBe(OperationRun::TYPE_STREAK_HISTORY)
        ->and($run->status)->toBe(OperationRunStatus::Queued)
        ->and($run->params)->toMatchArray([
            'from' => '2026-01-01',
            'to' => '2026-03-31',
        ])
        ->and($run->requested_by)->toBe($user->getKey());

    Bus::assertDispatched(ProcessStreakHistoryRun::class, function (ProcessStreakHistoryRun $job) use ($run): bool {
        return $job->run->is($run);
    });
});

it('runs history synchronously when sync is true', function (): void {
    $run = CreateStreakHistoryRun::run(
        from: Carbon::parse('2026-06-01'),
        to: Carbon::parse('2026-06-30'),
        sync: true,
    );

    expect($run->fresh()->status)->toBe(OperationRunStatus::Succeeded)
        ->and($run->fresh()->result)->toHaveKeys(['from', 'to', 'evaluated_as_of', 'strategies', 'record_calls', 'streaks_broken']);
});

it('replays through configured strategies and returns a result summary', function (): void {
    Carbon::setTestNow('2026-06-21 12:00:00');

    $result = ReplayStreakHistory::run(
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-21'),
    );

    expect($result['strategies'])->toBe(1)
        ->and($result['from'])->toBe('2026-06-01')
        ->and($result['to'])->toBe('2026-06-21')
        ->and($result['evaluated_as_of'])->toBe('2026-06-21')
        ->and($result)->toHaveKeys(['record_calls', 'streaks_broken']);
});

it('does not close a live streak when history to is in the future', function (): void {
    Carbon::setTestNow('2026-09-13 12:00:00');

    $user = User::query()->create([
        'name' => 'Live Player',
        'email' => 'live-history@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    StreakEvent::query()->create([
        'name' => 'This week',
        'occurred_at' => Carbon::parse('2026-09-09 18:00:00', 'UTC'),
    ]);

    $result = ReplayStreakHistory::run(
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-12-31'),
    );

    $streak = Streak::query()
        ->where('subject_id', $user->getKey())
        ->first();

    expect($result['evaluated_as_of'])->toBe('2026-09-13')
        ->and($streak)->not->toBeNull()
        ->and($streak->isOpen())->toBeTrue()
        ->and($streak->end_at)->toBeNull()
        ->and($streak->start_at->toDateString())->toBe('2026-09-07');
});

it('marks the run failed when replay throws', function (): void {
    config(['filament-streaks.strategies' => [
        'broken' => [
            'strategy' => 'Not\\A\\Real\\StrategyClass',
            'open_on' => 'first_event',
        ],
    ]]);

    $run = OperationRun::factory()->create();

    (new ProcessStreakHistoryRun($run))->handle(app(ReplayStreakHistory::class));

    $run->refresh();

    expect($run->status)->toBe(OperationRunStatus::Failed)
        ->and($run->error)->not->toBeNull()
        ->and($run->finished_at)->not->toBeNull();
});
