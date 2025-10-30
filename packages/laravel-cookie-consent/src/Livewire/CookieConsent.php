<?php

namespace BeeGoodIT\LaravelCookieConsent\Livewire;

use Livewire\Component;

class CookieConsent extends Component
{
    public bool $show = true;

    public function mount(): void
    {
        // Check if user has already consented
        if ($this->hasConsented()) {
            $this->show = false;
        }
    }

    public function accept(): void
    {
        $this->setConsent(true);
        $this->show = false;
    }

    public function decline(): void
    {
        $this->setConsent(false);
        $this->show = false;
    }

    protected function hasConsented(): bool
    {
        return request()->cookie(config('cookie-consent.cookie_name')) !== null;
    }

    protected function setConsent(bool $accepted): void
    {
        cookie()->queue(
            config('cookie-consent.cookie_name'),
            $accepted ? 'accepted' : 'declined',
            config('cookie-consent.cookie_lifetime') * 24 * 60 // Convert days to minutes
        );
    }

    public function render()
    {
        if (! config('cookie-consent.enabled') || ! $this->show) {
            return '';
        }

        return view('cookie-consent::livewire.cookie-consent');
    }
}

