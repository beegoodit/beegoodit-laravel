<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Actions\NotifyAtRiskStreaks;
use BeegoodIT\FilamentStreaks\Models\Notification as StreakNotification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as LaravelNotification;

beforeEach(function (): void {
    config(['filament-streaks.strategies.test_strategy.at_risk.notifications' => true]);
    LaravelNotification::fake();
});

afterEach(function (): void {
    config()->offsetUnset('filament-streaks.strategies.test_strategy.at_risk.notifications');
});

it('creates one at_risk log within lead when there is no qualification', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'at-risk@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-01',
    ]);

    $asOf = Carbon::parse('2026-06-21 18:00:00', 'UTC');

    expect(NotifyAtRiskStreaks::run($asOf))->toBe(1)
        ->and(StreakNotification::query()
            ->where('streak_id', $streak->getKey())
            ->where('kind', StreakNotification::KIND_AT_RISK)
            ->count())->toBe(1);
});

it('does not duplicate at_risk logs on a second run', function (): void {
    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'at-risk-dedupe@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-01',
    ]);

    $asOf = Carbon::parse('2026-06-21 18:00:00', 'UTC');

    NotifyAtRiskStreaks::run($asOf);
    NotifyAtRiskStreaks::run($asOf);

    expect(StreakNotification::query()->where('kind', StreakNotification::KIND_AT_RISK)->count())->toBe(1);
});
