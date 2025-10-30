<?php

use BeeGoodIT\FilamentI18n\Models\Concerns\HasI18nPreferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns default locale when not set', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $locale = null;
    };
    
    expect($user->getLocale())->toBe(config('app.locale', 'en'));
});

it('returns user locale when set', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $locale = 'de';
    };
    
    expect($user->getLocale())->toBe('de');
});

it('formats time in 12h format', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $time_format = '12h';
    };
    
    $time = new DateTime('2025-10-30 15:30:00');
    expect($user->formatTime($time))->toBe('3:30 PM');
});

it('formats time in 24h format', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $time_format = '24h';
    };
    
    $time = new DateTime('2025-10-30 15:30:00');
    expect($user->formatTime($time))->toBe('15:30');
});

it('detects 12h preference', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $time_format = '12h';
    };
    
    expect($user->prefers12HourFormat())->toBeTrue();
});

it('returns default timezone when not set', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $timezone = null;
    };
    
    expect($user->getTimezone())->toBe(config('app.timezone', 'UTC'));
});

it('formats datetime with user preferences', function () {
    $user = new class extends Model {
        use HasI18nPreferences;
        public $time_format = '12h';
    };
    
    $dateTime = new DateTime('2025-10-30 15:30:00');
    $formatted = $user->formatDateTime($dateTime);
    
    expect($formatted)->toContain('2025-10-30');
    expect($formatted)->toContain('3:30 PM');
});

