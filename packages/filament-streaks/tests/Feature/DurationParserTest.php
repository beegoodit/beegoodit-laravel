<?php

declare(strict_types=1);

use BeegoodIT\FilamentStreaks\Support\DurationParser;

it('parses supported duration strings', function (): void {
    $parser = new DurationParser;

    expect($parser->toSeconds('5m'))->toBe(300)
        ->and($parser->toSeconds('1h'))->toBe(3600)
        ->and($parser->toSeconds('2d'))->toBe(172800);
});

it('rejects invalid duration strings', function (): void {
    $parser = new DurationParser;

    expect(fn () => $parser->toSeconds('2w'))->toThrow(InvalidArgumentException::class);
});
