<?php

namespace BeegoodIT\FilamentSocialGraph\Livewire;

use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialSubscriptions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class SubscribeButton extends Component
{
    public Model $feedOwner;

    public function mount(Model $feedOwner): void
    {
        $this->feedOwner = $feedOwner;
    }

    public function toggle(): void
    {
        $subscriber = auth()->user();
        if (! $subscriber || ! in_array(HasSocialSubscriptions::class, class_uses_recursive($subscriber), true)) {
            return;
        }

        if ($subscriber->isSubscribedTo($this->feedOwner)) {
            $subscriber->unsubscribeFrom($this->feedOwner);
        } else {
            $subscriber->subscribeTo($this->feedOwner);
        }

        $this->dispatch('subscriptionUpdated');
    }

    public function isSubscribed(): bool
    {
        $subscriber = auth()->user();
        if (! $subscriber || ! in_array(HasSocialSubscriptions::class, class_uses_recursive($subscriber), true)) {
            return false;
        }

        return $subscriber->isSubscribedTo($this->feedOwner);
    }

    public function render(): View
    {
        return view('filament-social-graph::livewire.subscribe-button');
    }
}
