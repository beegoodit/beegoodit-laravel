<?php

declare(strict_types=1);

use BeegoodIT\Pdf\Support\Din5008Metrics;

it('converts millimetres to inches', function (): void {
    expect(Din5008Metrics::mmToInches(25.4))->toBe(1.0)
        ->and(Din5008Metrics::mmToInches(Din5008Metrics::FOLGESEITE_HEADER_MM))
        ->toBe(Din5008Metrics::mmToInches(Din5008Metrics::FORM_A_BRIEFKOPF_MM));
});

it('resolves briefkopf height by form', function (): void {
    expect(Din5008Metrics::briefkopfMm('A'))->toBe(Din5008Metrics::FORM_A_BRIEFKOPF_MM)
        ->and(Din5008Metrics::briefkopfMm('b'))->toBe(Din5008Metrics::FORM_B_BRIEFKOPF_MM);
});

it('exposes css variables for every core metric', function (): void {
    $vars = Din5008Metrics::cssVariables();

    expect($vars)
        ->toHaveKey('--din-paper-width')
        ->toHaveKey('--din-briefkopf-a')
        ->toHaveKey('--din-briefkopf-b')
        ->toHaveKey('--din-folgeseite-header')
        ->toHaveKey('--din-content-left')
        ->toHaveKey('--din-content-right')
        ->toHaveKey('--din-footer-band')
        ->and($vars['--din-briefkopf-a'])->toBe(Din5008Metrics::FORM_A_BRIEFKOPF_MM.'mm')
        ->and($vars['--din-folgeseite-header'])->toBe(Din5008Metrics::FOLGESEITE_HEADER_MM.'mm');
});
