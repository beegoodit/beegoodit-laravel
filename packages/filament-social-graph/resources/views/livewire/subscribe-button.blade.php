<div>
    @auth
    <button wire:click="toggle" type="button"
        class="{{ $this->isSubscribed()
            ? 'rounded-md bg-primary-600 px-3 py-1 text-sm font-medium text-white hover:bg-primary-700'
            : 'rounded-md border border-gray-300 bg-white px-3 py-1 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
        {{ $this->isSubscribed() ? __('filament-social-graph::feed_subscription.unsubscribe') : __('filament-social-graph::feed_subscription.subscribe') }}
    </button>
    @endauth
</div>
