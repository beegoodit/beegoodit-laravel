<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelCookieConsent\Tests\Unit;

use BeegoodIT\LaravelCookieConsent\Consent;
use BeegoodIT\LaravelCookieConsent\Tests\TestCase;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;

class ConsentTest extends TestCase
{
    #[DataProvider('allowsMatrix')]
    public function test_allows_matches_cookie_matrix(?string $cookie, string $category, bool $expected): void
    {
        $this->setConsentCookie($cookie);

        $this->assertSame($expected, Consent::allows($category));
    }

    /**
     * @return array<string, array{0: ?string, 1: string, 2: bool}>
     */
    public static function allowsMatrix(): array
    {
        return [
            'no cookie essential' => [null, Consent::CATEGORY_ESSENTIAL, true],
            'no cookie functional' => [null, Consent::CATEGORY_FUNCTIONAL, false],
            'no cookie analytics' => [null, Consent::CATEGORY_ANALYTICS, false],
            'no cookie marketing' => [null, Consent::CATEGORY_MARKETING, false],
            'essential functional' => [Consent::VALUE_ESSENTIAL, Consent::CATEGORY_FUNCTIONAL, false],
            'essential analytics' => [Consent::VALUE_ESSENTIAL, Consent::CATEGORY_ANALYTICS, false],
            'analytics analytics' => [Consent::VALUE_ANALYTICS, Consent::CATEGORY_ANALYTICS, true],
            'analytics marketing' => [Consent::VALUE_ANALYTICS, Consent::CATEGORY_MARKETING, false],
            'analytics functional' => [Consent::VALUE_ANALYTICS, Consent::CATEGORY_FUNCTIONAL, false],
            'marketing marketing' => [Consent::VALUE_MARKETING, Consent::CATEGORY_MARKETING, true],
            'marketing analytics' => [Consent::VALUE_MARKETING, Consent::CATEGORY_ANALYTICS, false],
            'all functional' => [Consent::VALUE_ALL, Consent::CATEGORY_FUNCTIONAL, true],
            'all analytics' => [Consent::VALUE_ALL, Consent::CATEGORY_ANALYTICS, true],
            'all marketing' => [Consent::VALUE_ALL, Consent::CATEGORY_MARKETING, true],
            'unknown category' => [Consent::VALUE_ALL, 'unknown', false],
        ];
    }

    public function test_available_is_false_until_cookie_exists(): void
    {
        $this->setConsentCookie(null);

        $this->assertFalse(Consent::available());

        $this->setConsentCookie(Consent::VALUE_ESSENTIAL);

        $this->assertTrue(Consent::available());
    }

    public function test_policy_url_is_null_when_empty(): void
    {
        config(['cookie-consent.policy_url_en' => '']);

        $this->assertNull(Consent::policyUrl());
    }

    public function test_policy_url_resolver_overrides_config(): void
    {
        config(['cookie-consent.policy_url_en' => '/from-config']);

        Consent::policyUrlUsing(fn (): string => '/datenschutz');

        $this->assertSame('/datenschutz', Consent::policyUrl());

        Consent::policyUrlUsing(fn (): string => '');

        $this->assertNull(Consent::policyUrl());
    }

    public function test_livewire_defaults_optional_categories_off(): void
    {
        $component = \Livewire\Livewire::test(\BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent::class);

        $component
            ->assertSet('essential', true)
            ->assertSet('functional', false)
            ->assertSet('analytics', false)
            ->assertSet('marketing', false)
            ->assertSee(__('cookie-consent::messages.alert_essential_only'));
    }

    public function test_accept_essential_queues_essential_cookie(): void
    {
        \Livewire\Livewire::test(\BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent::class)
            ->call('acceptEssential')
            ->assertSet('show', false);

        $queued = collect(cookie()->getQueuedCookies())->first(
            fn ($cookie): bool => $cookie->getName() === Consent::cookieName()
        );

        $this->assertNotNull($queued);
        $this->assertSame(Consent::VALUE_ESSENTIAL, $queued->getValue());
    }

    public function test_daisy_view_renders_daisyui_classes(): void
    {
        config(['cookie-consent.view' => 'cookie-consent::livewire.cookie-consent-daisy']);

        \Livewire\Livewire::test(\BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent::class)
            ->assertSee('btn-primary')
            ->assertSee('card relative w-full max-w-3xl', false)
            ->assertSee('bg-base-100')
            ->assertDontSee('modal-box', false)
            ->assertDontSee('bg-zinc-900', false);
    }

    public function test_zinc_settings_close_sits_in_header(): void
    {
        \Livewire\Livewire::test(\BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent::class)
            ->assertSee(__('cookie-consent::messages.settings_close'), false)
            ->assertSeeHtmlInOrder([
                'id="cookie-settings-title"',
                'wire:click="closeSettings"',
            ])
            ->assertDontSee('absolute top-4 right-4 z-10', false);
    }

    public function test_learn_more_hidden_when_policy_url_empty(): void
    {
        \Livewire\Livewire::test(\BeegoodIT\LaravelCookieConsent\Livewire\CookieConsent::class)
            ->assertDontSee(__('cookie-consent::messages.learn_more'));
    }

    protected function setConsentCookie(?string $value): void
    {
        $cookies = $value === null ? [] : [Consent::cookieName() => $value];

        $request = Request::create('/', 'GET', [], $cookies);
        $this->app->instance('request', $request);
    }
}
