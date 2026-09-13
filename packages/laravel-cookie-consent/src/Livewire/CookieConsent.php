<?php

declare(strict_types=1);

namespace BeegoodIT\LaravelCookieConsent\Livewire;

use BeegoodIT\LaravelCookieConsent\Consent;
use Livewire\Component;

class CookieConsent extends Component
{
    public bool $show = true;

    public bool $showSettings = false;

    public bool $consentGiven = false;

    public bool $essential = true;

    public bool $functional = false;

    public bool $analytics = false;

    public bool $marketing = false;

    public function mount(): void
    {
        if ($this->isIgnoredPath() || $this->hasConsented()) {
            $this->show = false;
            $this->consentGiven = $this->hasConsented();
        }
    }

    public function acceptAll(): void
    {
        $this->setConsent(Consent::VALUE_ALL);
        $this->show = false;
        $this->showSettings = false;
        $this->consentGiven = true;
    }

    public function acceptEssential(): void
    {
        $this->setConsent(Consent::VALUE_ESSENTIAL);
        $this->show = false;
        $this->showSettings = false;
        $this->consentGiven = true;
    }

    public function openSettings(): void
    {
        $this->showSettings = true;
    }

    public function closeSettings(): void
    {
        $this->showSettings = false;
    }

    public function saveSettings(): void
    {
        $this->setConsent($this->buildConsentValue());
        $this->show = false;
        $this->showSettings = false;
        $this->consentGiven = true;
    }

    protected function buildConsentValue(): string
    {
        if ($this->analytics && $this->marketing) {
            return Consent::VALUE_ALL;
        }

        if ($this->analytics) {
            return Consent::VALUE_ANALYTICS;
        }

        if ($this->marketing) {
            return Consent::VALUE_MARKETING;
        }

        return Consent::VALUE_ESSENTIAL;
    }

    protected function hasConsented(): bool
    {
        return Consent::available();
    }

    protected function isIgnoredPath(): bool
    {
        $patterns = config('cookie-consent.ignored_paths', []);

        if (! is_array($patterns) || $patterns === []) {
            return false;
        }

        return request()->is(...array_values($patterns));
    }

    protected function setConsent(string $value): void
    {
        $lifetime = config('cookie-consent.cookie_lifetime', config('cookie-consent.cookie_expiration_days', 365));
        $minutes = is_numeric($lifetime) ? (int) $lifetime * 24 * 60 : 365 * 24 * 60;

        cookie()->queue(
            Consent::cookieName(),
            $value,
            $minutes
        );
    }

    public function render()
    {
        if (config('cookie-consent.enabled', true) === false || $this->consentGiven) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $view = config('cookie-consent.view', 'cookie-consent::livewire.cookie-consent');

        if (! is_string($view) || $view === '') {
            $view = 'cookie-consent::livewire.cookie-consent';
        }

        return view($view, [
            'policyUrl' => Consent::policyUrl(),
        ]);
    }
}
