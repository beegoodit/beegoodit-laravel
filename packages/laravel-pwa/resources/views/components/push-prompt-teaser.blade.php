@props([
    'title' => __('laravel-pwa::teaser.title'),
    'message' => __('laravel-pwa::teaser.message'),
    'buttonText' => __('laravel-pwa::teaser.button'),
    'url' => config('pwa.teaser.url', '/me/notifications'),
    'dismissible' => true,
    'position' => 'inline',
])

<div
    x-data="{
        show: false,
        init() {
            const dismissed = localStorage.getItem('pwa_prompt_dismissed');
            const now = Date.now();
            const duration = {{ config('pwa.teaser.dismiss_duration', 7) }} * 24 * 60 * 60 * 1000;

            if (dismissed && (now - dismissed) < duration) {
                return;
            }

            if ('Notification' in window && Notification.permission === 'default') {
                this.show = true;
            }
        },
        dismiss() {
            localStorage.setItem('pwa_prompt_dismissed', Date.now());
            this.show = false;
        }
    }"
    x-show="show"
    x-cloak
    {{ $attributes->merge([
        'class' => 'pwa-push-teaser' . ($position === 'fixed-bottom' ? ' pwa-push-teaser--fixed-bottom' : '')
    ]) }}
>
    <div class="pwa-push-teaser__inner">
        <div class="pwa-push-teaser__content">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                @if ($title)
                    <h3 class="pwa-push-teaser__title text-lg font-bold leading-tight">{{ $title }}</h3>
                @endif
                @if ($message)
                    <p class="pwa-push-teaser__message mt-1 text-sm opacity-90">{{ $message }}</p>
                @endif
            @endif
        </div>

        <div class="pwa-push-teaser__actions flex items-center gap-3">
            <a href="{{ $url }}" class="pwa-push-teaser__button">
                {{ $buttonText }}
            </a>

            @if ($dismissible)
                <button
                    type="button"
                    @click="dismiss()"
                    class="pwa-push-teaser__dismiss-button"
                    aria-label="{{ __('Dismiss') }}"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
