<?php

use BeeGoodIT\LaravelCookieConsent\Livewire\CookieConsent;
use Livewire\Livewire;

it('renders cookie consent banner', function () {
    Livewire::test(CookieConsent::class)
        ->assertSee('We use cookies')
        ->assertSee('Accept')
        ->assertSee('Decline');
});

it('hides banner after accepting', function () {
    Livewire::test(CookieConsent::class)
        ->call('accept')
        ->assertSet('show', false);
});

it('hides banner after declining', function () {
    Livewire::test(CookieConsent::class)
        ->call('decline')
        ->assertSet('show', false);
});

it('does not show when disabled in config', function () {
    config(['cookie-consent.enabled' => false]);
    
    $component = Livewire::test(CookieConsent::class);
    
    expect($component->html())->toBe('');
});

