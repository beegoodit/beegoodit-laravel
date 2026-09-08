<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

/** DIN 5008 A4 millimetre constants and Chromium print helpers. */
final class Din5008Metrics
{
    public const PAPER_WIDTH_MM = 210.0;

    public const PAPER_HEIGHT_MM = 297.0;

    public const PAPER_WIDTH_IN = 8.27;

    public const PAPER_HEIGHT_IN = 11.69;

    /** Form A briefkopf / Folgeseite header band. */
    public const FORM_A_BRIEFKOPF_MM = 27.0;

    public const FORM_B_BRIEFKOPF_MM = 45.0;

    public const FOLGESEITE_HEADER_MM = self::FORM_A_BRIEFKOPF_MM;

    public const CONTENT_LEFT_MM = 25.0;

    public const CONTENT_RIGHT_MM = 20.0;

    public const FOOTER_BAND_MM = 15.0;

    public const ANSCHRIFT_WIDTH_MM = 80.0;

    public const INFOBLOCK_LEFT_MM = 125.0;

    public const INFOBLOCK_MAX_WIDTH_MM = 75.0;

    public const LOGO_MAX_WIDTH_MM = 40.0;

    public const LOGO_MAX_HEIGHT_MM = 20.0;

    public static function mmToInches(float $mm): float
    {
        return round($mm / 25.4, 4);
    }

    public static function briefkopfMm(string $form): float
    {
        return strtoupper($form) === 'B'
            ? self::FORM_B_BRIEFKOPF_MM
            : self::FORM_A_BRIEFKOPF_MM;
    }

    /**
     * CSS custom properties for DIN layout Blade (single source of truth with PHP constants).
     *
     * @return array<string, string>
     */
    public static function cssVariables(): array
    {
        return [
            '--din-paper-width' => self::PAPER_WIDTH_MM.'mm',
            '--din-paper-height' => self::PAPER_HEIGHT_MM.'mm',
            '--din-briefkopf-a' => self::FORM_A_BRIEFKOPF_MM.'mm',
            '--din-briefkopf-b' => self::FORM_B_BRIEFKOPF_MM.'mm',
            '--din-folgeseite-header' => self::FOLGESEITE_HEADER_MM.'mm',
            '--din-content-left' => self::CONTENT_LEFT_MM.'mm',
            '--din-content-right' => self::CONTENT_RIGHT_MM.'mm',
            '--din-footer-band' => self::FOOTER_BAND_MM.'mm',
            '--din-anschrift-width' => self::ANSCHRIFT_WIDTH_MM.'mm',
            '--din-infoblock-left' => self::INFOBLOCK_LEFT_MM.'mm',
            '--din-infoblock-max-width' => self::INFOBLOCK_MAX_WIDTH_MM.'mm',
            '--din-logo-max-width' => self::LOGO_MAX_WIDTH_MM.'mm',
            '--din-logo-max-height' => self::LOGO_MAX_HEIGHT_MM.'mm',
        ];
    }
}
