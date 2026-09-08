<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Actions\Din5008;

use BeegoodIT\Pdf\Data\Din5008\FolgeseiteLayoutData;
use BeegoodIT\Pdf\Support\Din5008Debug;
use BeegoodIT\Pdf\Support\Din5008Metrics;
use BeegoodIT\Pdf\Support\LogoDataUri;
use BeegoodIT\Pdf\Support\PageFooter;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildFolgeseiteLayoutAction
{
    use AsAction;

    public function handle(
        string $bodyHtml,
        ?string $logoDataUri = null,
        ?string $pageFooterHtml = null,
        ?string $locale = null,
        ?bool $debugLayout = null,
    ): FolgeseiteLayoutData {
        $pageFooterHtml ??= PageFooter::make($locale);
        $debugLayout = Din5008Debug::enabled($debugLayout);
        $logoDataUri = LogoDataUri::normalize($logoDataUri);

        $html = view('laravel-pdf-renderer::din5008.folgeseite', [
            'bodyHtml' => $bodyHtml,
            'logoDataUri' => $logoDataUri,
            'debugLayout' => $debugLayout,
        ])->render();

        // Logo lives in fixed .briefkopf (DIN mm). Chrome footer only for live page spans.
        $printOptions = array_merge(
            PageFooter::printFooterOptions($pageFooterHtml, debugLayout: $debugLayout),
            [
                'paperWidth' => Din5008Metrics::PAPER_WIDTH_IN,
                'paperHeight' => Din5008Metrics::PAPER_HEIGHT_IN,
                'preferCSSPageSize' => false,
            ],
        );

        return FolgeseiteLayoutData::from([
            'html' => $html,
            'printOptions' => $printOptions,
        ]);
    }
}
