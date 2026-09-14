<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Tests\Fixtures\FakeStrategy;
use BeegoodIT\FilamentStreaks\Tests\TestCase;
use Illuminate\Support\Carbon;

uses(TestCase::class)->in('Feature');

beforeEach(function (): void {
    FakeStrategy::reset();
    Carbon::setTestNow();
});

afterEach(function (): void {
    FakeStrategy::reset();
});
