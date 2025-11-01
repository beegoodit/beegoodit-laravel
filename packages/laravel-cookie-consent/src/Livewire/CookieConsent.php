<?php

namespace BeeGoodIT\LaravelCookieConsent\Livewire;

use Livewire\Component;

class CookieConsent extends Component
{
    public bool $show = true;
    public bool $showSettings = false;
    public bool $consentGiven = false; // Track if consent was just given in this session
    
    // Cookie categories
    public bool $essential = true;
    public bool $functional = true;
    public bool $analytics = false;
    public bool $marketing = false;

    public function mount(): void
    {
        // Check if user has already consented
        if ($this->hasConsented()) {
            $this->show = false;
            $this->consentGiven = true;
        }
    }

    public function acceptAll(): void
    {
        $this->setConsent('all');
        $this->show = false;
        $this->showSettings = false;
        $this->consentGiven = true;
    }

    public function acceptEssential(): void
    {
        $this->setConsent('essential');
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
        $value = $this->buildConsentValue();
        $this->setConsent($value);
        $this->show = false;
        $this->showSettings = false;
        $this->consentGiven = true;
    }

    protected function buildConsentValue(): string
    {
        if ($this->analytics && $this->marketing) {
            return 'all';
        } elseif ($this->analytics) {
            return 'analytics';
        } elseif ($this->marketing) {
            return 'marketing';
        }
        
        return 'essential';
    }

    protected function hasConsented(): bool
    {
        return request()->cookie(config('cookie-consent.cookie_name')) !== null;
    }

    protected function setConsent(string $value): void
    {
        cookie()->queue(
            config('cookie-consent.cookie_name'),
            $value,
            config('cookie-consent.cookie_lifetime') * 24 * 60 // Convert days to minutes
        );
    }

    public function render()
    {
        // Don't render anything if consent already given or disabled
        if (! config('cookie-consent.enabled') || $this->consentGiven) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        return view('cookie-consent::livewire.cookie-consent');
    }
}

