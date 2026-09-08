<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Actions\Din5008;

use BeegoodIT\Pdf\Data\Din5008\FirstPageLayoutData;
use BeegoodIT\Pdf\Support\Din5008Debug;
use BeegoodIT\Pdf\Support\Din5008Metrics;
use BeegoodIT\Pdf\Support\PageFooter;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildFirstPageLayoutAction
{
    use AsAction;

    public function handle(
        string $form = 'A',
        bool $noMarkers = false,
        string $briefkopfHtml = '',
        string $rucksendeangabeHtml = '',
        string $vermerkHtml = '',
        string $anschriftHtml = '',
        string $informationsblockHtml = '',
        string $textfeldHtml = '',
        ?string $pageFooterHtml = null,
        ?string $locale = null,
        ?bool $debugLayout = null,
    ): FirstPageLayoutData {
        $form = strtoupper($form) === 'B' ? 'B' : 'A';
        $pageFooterHtml ??= PageFooter::make($locale);
        $debugLayout = Din5008Debug::enabled($debugLayout);

        $html = view('laravel-pdf-renderer::din5008.first-page', [
            'form' => $form,
            'noMarkers' => $noMarkers,
            'debugLayout' => $debugLayout,
            'briefkopfHtml' => $briefkopfHtml,
            'rucksendeangabeHtml' => $rucksendeangabeHtml,
            'vermerkHtml' => $vermerkHtml,
            'anschriftHtml' => $anschriftHtml,
            'informationsblockHtml' => $informationsblockHtml,
            'textfeldHtml' => $textfeldHtml,
        ])->render();

        // Absolute DIN zones are measured from the paper edge — zero top/side chrome.
        $printOptions = array_merge(
            PageFooter::printFooterOptions($pageFooterHtml, debugLayout: $debugLayout),
            [
                'paperWidth' => Din5008Metrics::PAPER_WIDTH_IN,
                'paperHeight' => Din5008Metrics::PAPER_HEIGHT_IN,
                'preferCSSPageSize' => false,
            ],
        );

        return FirstPageLayoutData::from([
            'html' => $html,
            'printOptions' => $printOptions,
        ]);
    }
}
