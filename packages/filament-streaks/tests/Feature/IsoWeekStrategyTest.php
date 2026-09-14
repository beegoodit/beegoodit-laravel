<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Strategies\IsoWeekStrategy;
use Illuminate\Support\Carbon;

it('builds iso week period keys', function (): void {
    $strategy = new IsoWeekStrategy;
    $instant = Carbon::parse('2026-03-19 12:00:00', 'Europe/Berlin');

    $period = $strategy->periodContaining($instant, 'Europe/Berlin');

    expect($period['key'])->toBe('2026-W12')
        ->and($period['start']->timezone('Europe/Berlin')->toDateString())->toBe('2026-03-16')
        ->and($period['end']->timezone('Europe/Berlin')->toDateString())->toBe('2026-03-22');
});

it('lists iso week periods between two dates', function (): void {
    $strategy = new IsoWeekStrategy;
    $from = Carbon::parse('2026-03-01', 'UTC');
    $to = Carbon::parse('2026-03-31', 'UTC');

    $periods = $strategy->periodsBetween($from, $to, 'UTC');

    expect($periods)->not->toBeEmpty()
        ->and($periods->first()['key'])->toStartWith('2026-W');
});
