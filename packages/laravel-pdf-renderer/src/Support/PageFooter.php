<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

/** DIN-style right-aligned page footer HTML for Chromium header/footer templates. */
final class PageFooter
{
    public static function make(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return __('laravel-pdf-renderer::messages.page_footer', [
            'current' => '<span class="pageNumber"></span>',
            'total' => '<span class="totalPages"></span>',
        ], $locale);
    }

    /**
     * Folgeseite / first-page: chrome footer only (page numbers). Side/top margins stay 0
     * so absolute/fixed DIN zones are measured from the paper edge.
     *
     * @return array{displayHeaderFooter: bool, headerTemplate: string, footerTemplate: string, marginTop: float, marginBottom: float, marginLeft: float, marginRight: float}
     */
    public static function printFooterOptions(string $footerHtml, ?float $marginBottomIn = null, bool $debugLayout = false): array
    {
        $marginBottomIn ??= Din5008Metrics::mmToInches(Din5008Metrics::FOOTER_BAND_MM);
        $sidePad = Din5008Metrics::mmToInches(Din5008Metrics::CONTENT_LEFT_MM).'in';
        $rightPad = Din5008Metrics::mmToInches(Din5008Metrics::CONTENT_RIGHT_MM).'in';
        $bandHeight = number_format(max($marginBottomIn - 0.08, 0.2), 2, '.', '').'in';
        $debugBorder = $debugLayout ? 'border:2px solid #3b82f6;box-sizing:border-box;' : '';

        return [
            'displayHeaderFooter' => true,
            'headerTemplate' => '<div></div>',
            'footerTemplate' => '<div style="width:100%;box-sizing:border-box;margin:0;padding:0 '.$rightPad.' 0 '.$sidePad.';font-family:DejaVu Sans,sans-serif;font-size:9px;">'
                .'<div style="box-sizing:border-box;width:100%;height:'.$bandHeight.';max-height:'.$bandHeight.';'
                .'overflow:hidden;text-align:right;color:#6b7280;line-height:'.$bandHeight.';'.$debugBorder.'">'
                .$footerHtml
                .'</div></div>',
            'marginTop' => 0.0,
            'marginBottom' => $marginBottomIn,
            'marginLeft' => 0.0,
            'marginRight' => 0.0,
        ];
    }
}
