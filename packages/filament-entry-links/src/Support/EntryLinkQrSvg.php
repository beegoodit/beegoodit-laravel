<?php

namespace BeegoodIT\FilamentEntryLinks\Support;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\HtmlString;

final class EntryLinkQrSvg
{
    public static function inlineHtml(string $data, string $ariaLabel, int $size = 220): HtmlString
    {
        $qrCode = new QrCode(
            data: $data,
            size: $size,
            margin: 8,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
        );

        $writer = new SvgWriter;
        $result = $writer->write($qrCode, null, null, [
            SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true,
        ]);

        $svg = $result->getString();

        return new HtmlString(
            '<div class="inline-block rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-950" role="img" aria-label="'.e($ariaLabel).'">'.$svg.'</div>'
        );
    }
}
