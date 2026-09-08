<?php

declare(strict_types=1);

use BeegoodIT\Pdf\Support\LogoDataUri;

it('accepts null and blank logos', function (): void {
    expect(LogoDataUri::normalize(null))->toBeNull()
        ->and(LogoDataUri::normalize(''))->toBeNull()
        ->and(LogoDataUri::normalize('   '))->toBeNull();
});

it('accepts a valid base64 png data uri', function (): void {
    $uri = 'data:image/png;base64,'.base64_encode('fake-png-bytes');

    expect(LogoDataUri::normalize($uri))->toBe($uri);
});

it('accepts svg+xml base64 data uris', function (): void {
    $uri = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>');

    expect(LogoDataUri::normalize($uri))->toBe($uri);
});

it('rejects http and file urls', function (string $value): void {
    LogoDataUri::normalize($value);
})->with([
    'https://example.com/logo.png',
    'http://example.com/logo.png',
    'file:///tmp/logo.png',
    'data:text/html;base64,'.base64_encode('<script>alert(1)</script>'),
    'data:image/png;base64,!!!',
    'javascript:alert(1)',
])->throws(InvalidArgumentException::class);
