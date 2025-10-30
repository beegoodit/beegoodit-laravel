<div x-data="{ show: @entangle('show') }" 
     x-show="show" 
     x-transition
     class="fixed {{ config('cookie-consent.position') === 'top' ? 'top-0' : 'bottom-0' }} left-0 right-0 z-50 p-4 bg-zinc-900 dark:bg-zinc-950 text-white shadow-lg border-t {{ config('cookie-consent.position') === 'top' ? 'border-b' : 'border-t' }} border-zinc-700">
    <div class="container mx-auto max-w-7xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex-1 text-sm">
                <p class="mb-2 font-semibold">{{ __('cookie-consent::messages.title', ['default' => 'We use cookies']) }}</p>
                <p class="text-zinc-300 dark:text-zinc-400">
                    {{ __('cookie-consent::messages.description', ['default' => 'We use cookies to improve your experience on our site. By using our site, you agree to our use of cookies.']) }}
                    <a href="/privacy" class="underline hover:text-white">
                        {{ __('cookie-consent::messages.learn_more', ['default' => 'Learn more']) }}
                    </a>
                </p>
            </div>
            
            <div class="flex gap-2 shrink-0">
                <button wire:click="decline" 
                        class="px-4 py-2 text-sm font-medium text-zinc-300 hover:text-white transition">
                    {{ __('cookie-consent::messages.decline', ['default' => 'Decline']) }}
                </button>
                <button wire:click="accept" 
                        class="px-4 py-2 text-sm font-medium bg-white text-zinc-900 rounded-lg hover:bg-zinc-100 transition">
                    {{ __('cookie-consent::messages.accept', ['default' => 'Accept']) }}
                </button>
            </div>
        </div>
    </div>
</div>

