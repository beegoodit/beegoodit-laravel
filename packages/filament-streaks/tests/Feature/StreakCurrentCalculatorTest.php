<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Models\Qualification;
use BeegoodIT\FilamentStreaks\Models\Streak;
use BeegoodIT\FilamentStreaks\Support\StreakCurrentCalculator;
use BeegoodIT\FilamentStreaks\Tests\Fixtures\User;
use Illuminate\Support\Carbon;

it('counts consecutive qualified periods ending at now', function (): void {
    Carbon::setTestNow('2026-06-21 12:00:00');

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'current-count@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-02',
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-03 18:00:00', 'UTC'),
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-10 18:00:00', 'UTC'),
    ]);

    // Sun 21 Jun = ISO W25 (15–21), empty → skip; W24 + W23 consecutive → 2.
    $count = app(StreakCurrentCalculator::class)->calculate($streak->fresh(['qualifications']));

    expect($count)->toBe(2);
});

it('does not inflate the count for an empty current period', function (): void {
    // Tue 9 Jun 2026 = current ISO week Mon 8–Sun 14. Prior week Mon 1–Sun 7 has a qual.
    Carbon::setTestNow('2026-06-09 12:00:00');

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'empty-current@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-02',
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-03 18:00:00', 'UTC'),
    ]);

    $count = app(StreakCurrentCalculator::class)->calculate($streak->fresh(['qualifications']));

    expect($count)->toBe(1);
});

it('stops at the first gap when walking backward from now', function (): void {
    Carbon::setTestNow('2026-06-21 12:00:00');

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'gap-count@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-01',
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-03 18:00:00', 'UTC'),
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-17 18:00:00', 'UTC'),
    ]);

    $count = app(StreakCurrentCalculator::class)->calculate($streak->fresh(['qualifications']));

    expect($count)->toBe(1);
});

it('returns zero when the previous finished period was missed', function (): void {
    // Qual only in W23; now in W25 with empty current — W24 is a finished gap → 0.
    Carbon::setTestNow('2026-06-16 12:00:00');

    $user = User::query()->create([
        'name' => 'Player',
        'email' => 'missed-previous@example.test',
        'password' => bcrypt('secret'),
        'timezone' => 'UTC',
    ]);

    $streak = Streak::factory()->create([
        'subject_type' => User::class,
        'subject_id' => $user->getKey(),
        'start_at' => '2026-06-02',
    ]);

    Qualification::factory()->create([
        'streak_id' => $streak->getKey(),
        'happened_at' => Carbon::parse('2026-06-03 18:00:00', 'UTC'),
    ]);

    $count = app(StreakCurrentCalculator::class)->calculate($streak->fresh(['qualifications']));

    expect($count)->toBe(0);
});
