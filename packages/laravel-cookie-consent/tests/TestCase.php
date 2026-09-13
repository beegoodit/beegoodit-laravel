<?php

namespace BeegoodIT\LaravelCookieConsent\Tests;

use BeegoodIT\LaravelCookieConsent\LaravelCookieConsentServiceProvider;
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
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cookie-consent.cookie_name', '__cookie_consent');
        $app['config']->set('cookie-consent.cookie_lifetime', 365);
        $app['config']->set('cookie-consent.position', 'bottom');
        $app['config']->set('cookie-consent.policy_url_en', '');
        $app['config']->set('cookie-consent.policy_url_de', '');
        $app['config']->set('cookie-consent.ignored_paths', [
            'admin',
            'admin/*',
            'livewire/*',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        \BeegoodIT\LaravelCookieConsent\Consent::policyUrlUsing(null);
    }
}
