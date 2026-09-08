<?php

declare(strict_types=1);

use BeegoodIT\Pdf\Actions\Din5008\BuildFirstPageLayoutAction;
use BeegoodIT\Pdf\Actions\Din5008\BuildFolgeseiteLayoutAction;
use BeegoodIT\Pdf\Support\Din5008Metrics;
use BeegoodIT\Pdf\Support\PageFooter;

it('builds form A first page with markers by default', function (): void {
    $layout = BuildFirstPageLayoutAction::run(
        briefkopfHtml: 'Logo',
        anschriftHtml: 'Firma<br>Ort',
        textfeldHtml: '<p>Hello</p>',
        locale: 'de',
    );

    expect($layout->html)
        ->not->toContain('form="B"')
        ->not->toContain('<din-5008 no-markers')
        ->toContain('<din-5008')
        ->toContain('class="briefkopf">Logo')
        ->toContain('class="anschriftzone">Firma')
        ->toContain('class="textfeld"><p>Hello</p>')
        ->and($layout->printOptions['marginTop'])->toBe(0.0)
        ->and($layout->printOptions['displayHeaderFooter'])->toBeTrue()
        ->and($layout->printOptions['footerTemplate'])->toContain('text-align:right')
        ->and($layout->printOptions['footerTemplate'])->toContain('Seite');
});

it('builds form B first page and can disable markers', function (): void {
    $layout = BuildFirstPageLayoutAction::run(
        form: 'B',
        noMarkers: true,
        textfeldHtml: 'Body',
        locale: 'en',
    );

    expect($layout->html)
        ->toContain('form="B"')
        ->toContain('no-markers')
        ->and($layout->printOptions['footerTemplate'])->toContain('Page');
});

it('builds folgeseite with fixed briefkopf logo zone and zero top chrome margin', function (): void {
    $logo = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg"></svg>');

    $layout = BuildFolgeseiteLayoutAction::run(
        bodyHtml: '<h1>Report</h1>',
        logoDataUri: $logo,
        locale: 'de',
    );

    expect($layout->html)
        ->toContain('<h1>Report</h1>')
        ->toContain('din-5008-folgeseite')
        ->toContain('class="briefkopf"')
        ->toContain($logo)
        ->toContain('--din-folgeseite-header')
        ->toContain('class="textfeld body"')
        ->and($layout->printOptions['marginTop'])->toBe(0.0)
        ->and($layout->printOptions['marginLeft'])->toBe(0.0)
        ->and($layout->printOptions['marginBottom'])
        ->toBe(Din5008Metrics::mmToInches(Din5008Metrics::FOOTER_BAND_MM))
        ->and($layout->printOptions['footerTemplate'])->toContain('text-align:right')
        ->and($layout->printOptions['footerTemplate'])->toContain('pageNumber');
});

it('rejects an invalid folgeseite logo data uri', function (): void {
    BuildFolgeseiteLayoutAction::run(
        bodyHtml: '<p>Report</p>',
        logoDataUri: 'https://evil.example/logo.png',
    );
})->throws(InvalidArgumentException::class);

it('builds a right-aligned page footer string', function (): void {
    expect(PageFooter::make('de'))
        ->toContain('Seite')
        ->toContain('pageNumber')
        ->toContain('totalPages');
});

it('outlines din zones when debug layout is enabled', function (): void {
    $first = BuildFirstPageLayoutAction::run(
        textfeldHtml: 'Body',
        locale: 'de',
        debugLayout: true,
    );

    $folge = BuildFolgeseiteLayoutAction::run(
        bodyHtml: '<p>Report</p>',
        locale: 'de',
        debugLayout: true,
    );

    expect($first->html)
        ->toContain('debug-layout')
        ->toContain('[debug-layout] .briefkopf')
        ->and($first->printOptions['footerTemplate'])->toContain('border:2px solid #3b82f6')
        ->and($folge->html)->toContain('debug-layout')
        ->and($folge->html)->toContain('[debug-layout] .briefkopf')
        ->and($folge->html)->toContain('[debug-layout] .textfeld')
        ->and($folge->printOptions['footerTemplate'])->toContain('border:2px solid #3b82f6');
});
