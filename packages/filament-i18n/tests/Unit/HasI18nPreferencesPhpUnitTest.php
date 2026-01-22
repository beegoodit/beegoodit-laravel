<?php

namespace BeegoodIT\FilamentI18n\Tests\Unit;

use BeegoodIT\FilamentI18n\Models\Concerns\HasI18nPreferences;
use BeegoodIT\FilamentI18n\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class HasI18nPreferencesPhpUnitTest extends TestCase
{
    public function test_it_returns_default_locale_when_not_set(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $locale;
        };

        $this->assertEquals(config('app.locale', 'en'), $user->getLocale());
    }

    public function test_it_returns_user_locale_when_set(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $locale = 'de';
        };

        $this->assertEquals('de', $user->getLocale());
    }

    public function test_it_formats_time_in_12h_format(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $time_format = '12h';
        };

        $time = new \DateTime('2025-10-30 15:30:00');
        $this->assertEquals('3:30 PM', $user->formatTime($time));
    }

    public function test_it_formats_time_in_24h_format(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $time_format = '24h';
        };

        $time = new \DateTime('2025-10-30 15:30:00');
        $this->assertEquals('15:30', $user->formatTime($time));
    }

    public function test_it_detects_12h_preference(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $time_format = '12h';
        };

        $this->assertTrue($user->prefers12HourFormat());
    }

    public function test_it_returns_default_timezone_when_not_set(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $timezone;
        };

        $this->assertEquals(config('app.timezone', 'UTC'), $user->getTimezone());
    }

    public function test_it_formats_datetime_with_user_preferences(): void
    {
        $user = new class extends Model
        {
            use HasI18nPreferences;

            public $time_format = '12h';
        };

        $dateTime = new \DateTime('2025-10-30 15:30:00');
        $formatted = $user->formatDateTime($dateTime);

        $this->assertStringContainsString('2025-10-30', $formatted);
        $this->assertStringContainsString('3:30 PM', $formatted);
    }
}
