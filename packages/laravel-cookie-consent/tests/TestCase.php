<?php

namespace BeeGoodIT\LaravelCookieConsent\Tests;

use BeeGoodIT\LaravelCookieConsent\LaravelCookieConsentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LaravelCookieConsentServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cookie-consent.enabled', true);
        $app['config']->set('cookie-consent.cookie_name', '__cookie_consent');
        $app['config']->set('cookie-consent.cookie_lifetime', 365);
        $app['config']->set('cookie-consent.position', 'bottom');
    }
}

