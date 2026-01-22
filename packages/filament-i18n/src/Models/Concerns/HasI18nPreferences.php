<?php

namespace BeegoodIT\FilamentI18n\Models\Concerns;

trait HasI18nPreferences
{
    /**
     * Get the user's preferred locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?? config('app.locale', 'en');
    }

    /**
     * Get the user's preferred timezone.
     */
    public function getTimezone(): string
    {
        return $this->timezone ?? config('app.timezone', 'UTC');
    }

    /**
     * Get the user's preferred time format (12h or 24h).
     */
    public function getTimeFormat(): string
    {
        return $this->time_format ?? '24h';
    }

    /**
     * Check if user prefers 12-hour time format.
     */
    public function prefers12HourFormat(): bool
    {
        return $this->getTimeFormat() === '12h';
    }

    /**
     * Format a time according to user's preference.
     */
    public function formatTime(\DateTimeInterface $time): string
    {
        return $this->prefers12HourFormat()
            ? $time->format('g:i A')  // 3:45 PM
            : $time->format('H:i');   // 15:45
    }

    /**
     * Format a date-time according to user's preference.
     */
    public function formatDateTime(\DateTimeInterface $dateTime): string
    {
        $dateFormat = 'Y-m-d';
        $timeFormat = $this->prefers12HourFormat() ? 'g:i A' : 'H:i';

        return $dateTime->format($dateFormat.' '.$timeFormat);
    }
}
