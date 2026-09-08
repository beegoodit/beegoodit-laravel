<?php

declare(strict_types=1);

use BeegoodIT\Pdf\Contracts\RendererContract;
use BeegoodIT\Pdf\Strategies\FakeStrategy;

it('binds fake strategy when fake config is enabled', function (): void {
    expect(app(RendererContract::class))->toBeInstanceOf(FakeStrategy::class);
});

it('returns pdf-ish bytes from the fake strategy', function (): void {
    $pdf = app(RendererContract::class)->htmlToPdf('<html>hello</html>');

    expect($pdf)
        ->toStartWith('%PDF')
        ->toContain('FakeStrategy');
});
