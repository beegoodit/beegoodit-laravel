<?php

use BeeGoodIT\LaravelCookieConsent\Livewire\CookieConsent;
use Livewire\Livewire;

it('renders cookie consent banner with 3 buttons', function () {
    Livewire::test(CookieConsent::class)
        ->assertSee('We use cookies')
        ->assertSee('Accept all cookies')
        ->assertSee('Accept only necessary cookies')
        ->assertSee('Adjust your preferences');
});

it('hides banner after accepting all', function () {
    Livewire::test(CookieConsent::class)
        ->call('acceptAll')
        ->assertSet('show', false)
        ->assertSet('showSettings', false);
});

it('hides banner after accepting essential only', function () {
    Livewire::test(CookieConsent::class)
        ->call('acceptEssential')
        ->assertSet('show', false)
        ->assertSet('showSettings', false);
});

it('opens settings modal', function () {
    Livewire::test(CookieConsent::class)
        ->call('openSettings')
        ->assertSet('showSettings', true)
        ->assertSee('Cookie Preferences')
        ->assertSee('Essential Cookies')
        ->assertSee('Functional Cookies')
        ->assertSee('Analytics Cookies')
        ->assertSee('Marketing Cookies');
});

it('closes settings modal', function () {
    Livewire::test(CookieConsent::class)
        ->set('showSettings', true)
        ->call('closeSettings')
        ->assertSet('showSettings', false);
});

it('saves granular settings', function () {
    Livewire::test(CookieConsent::class)
        ->set('analytics', true)
        ->set('marketing', false)
        ->call('saveSettings')
        ->assertSet('show', false)
        ->assertSet('showSettings', false);
});

it('essential and functional are always enabled', function () {
    Livewire::test(CookieConsent::class)
        ->assertSet('essential', true)
        ->assertSet('functional', true);
});

it('analytics and marketing are disabled by default', function () {
    Livewire::test(CookieConsent::class)
        ->assertSet('analytics', false)
        ->assertSet('marketing', false);
});

it('does not show when disabled in config', function () {
    config(['cookie-consent.enabled' => false]);
    
    $component = Livewire::test(CookieConsent::class);
    
    expect($component->html())->toBe('');
});

