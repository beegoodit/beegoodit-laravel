<?php

declare(strict_types=1);

namespace BeegoodIT\DateTimePicker\Support;

use Carbon\CarbonInterface;

final class DateTimeLabel
{
    public static function formatDay(CarbonInterface $date, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return str_starts_with($locale, 'de')
            ? $date->format('d.m.Y')
            : $date->format('M j, Y');
    }

    public static function formatMonth(CarbonInterface $date, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $date->copy()->locale($locale)->translatedFormat('F Y');
    }
}
