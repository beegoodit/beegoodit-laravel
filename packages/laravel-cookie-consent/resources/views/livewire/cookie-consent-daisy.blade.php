<div>
    {{-- Main Banner Backdrop --}}
    <div x-data="{ showBackdrop: @entangle('show') }" x-show="showBackdrop"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-base-content/40 backdrop-blur-sm" style="z-index: 999998 !important;"></div>

    {{-- Main Alert Banner --}}
    <div x-data="{ show: @entangle('show') }" x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-y-full" x-transition:enter-end="transform translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="transform translate-y-0"
        x-transition:leave-end="transform translate-y-full" class="fixed bottom-0 left-0 right-0 px-8 md:px-4 mb-4"
        style="z-index: 999999 !important;">

        <div role="dialog" aria-labelledby="cookie-banner-title" aria-describedby="cookie-banner-desc"
            class="card mx-auto max-w-4xl bg-base-100 shadow-xl border border-base-300 overflow-hidden">

            <div class="card-body p-4 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 text-warning">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z" />
                            <circle cx="8" cy="8" r="1.5" />
                            <circle cx="15" cy="9" r="1.5" />
                            <circle cx="9" cy="15" r="1.5" />
                            <circle cx="16" cy="15" r="1.5" />
                        </svg>
                    </div>
                    <h2 id="cookie-banner-title" class="card-title text-xl sm:text-2xl">
                        {{ __('cookie-consent::messages.alert_title') }}
                    </h2>
                </div>

                <p id="cookie-banner-desc" class="text-sm sm:text-base">
                    {{ __('cookie-consent::messages.alert_text') }}
                    @if ($policyUrl)
                        <a href="{{ $policyUrl }}" class="link link-hover font-medium">
                            {{ __('cookie-consent::messages.learn_more') }}
                        </a>
                    @endif
                </p>
            </div>

            <div class="border-t border-base-300 px-4 py-4 sm:px-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button wire:click="acceptAll" type="button" class="btn btn-primary flex-1">
                        {{ __('cookie-consent::messages.alert_accept') }}
                    </button>
                    <button wire:click="acceptEssential" type="button" class="btn btn-ghost flex-1">
                        {{ __('cookie-consent::messages.alert_essential_only') }}
                    </button>
                    <button wire:click="openSettings" type="button" class="btn btn-outline flex-1">
                        {{ __('cookie-consent::messages.alert_settings') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings overlay: card + bg-base-100 (same as the banner). Do not use daisyUI
         modal/modal-box — theme CSS does not emit those unless the theme already uses them. --}}
    <div x-data="{ showBackdrop: @entangle('showSettings') }" x-show="showBackdrop" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-base-content/40 backdrop-blur-sm" style="z-index: 9999998 !important;"
        wire:click="closeSettings"></div>

    <div x-data="{ showSettings: @entangle('showSettings') }" x-show="showSettings" x-cloak
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 overflow-y-auto" style="z-index: 9999999 !important;" role="dialog"
        aria-labelledby="cookie-settings-title" aria-describedby="cookie-settings-desc">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div
                class="card relative w-full max-w-3xl max-h-[90vh] overflow-y-auto bg-base-100 shadow-xl border border-base-300">
                <button wire:click="closeSettings" type="button"
                    class="btn btn-sm btn-ghost absolute right-2 top-2 z-10"
                    aria-label="{{ __('cookie-consent::messages.settings_close') }}">✕</button>

                <div class="card-body p-4 sm:p-6">
                    <div class="flex items-center gap-3 mb-3 pr-10">
                        <span class="text-warning">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z" />
                            </svg>
                        </span>
                        <h2 id="cookie-settings-title" class="card-title text-2xl">
                            {{ __('cookie-consent::messages.settings_title') }}
                        </h2>
                    </div>
                    <p id="cookie-settings-desc" class="text-sm mb-6">
                        @if ($policyUrl)
                            {!! str_replace(':policyUrl', e($policyUrl), __('cookie-consent::messages.settings_text')) !!}
                        @else
                            {{ __('cookie-consent::messages.settings_text_no_policy') }}
                        @endif
                    </p>

                    <div class="text-center py-2 mb-4">
                        <button wire:click="acceptAll" type="button" class="btn btn-primary">
                            {{ __('cookie-consent::messages.settings_accept_all') }}
                        </button>
                    </div>

                    <div class="flex flex-col gap-4">
                        <label
                            class="flex items-start gap-4 cursor-not-allowed rounded-lg border border-base-300 bg-base-200 p-4">
                            <input type="checkbox" wire:model="essential" disabled checked
                                class="checkbox checkbox-primary mt-0.5">
                            <span>
                                <span class="block font-semibold mb-1">{{ __('cookie-consent::messages.setting_essential') }}</span>
                                <span class="text-sm opacity-80">{{ __('cookie-consent::messages.setting_essential_text') }}</span>
                            </span>
                        </label>

                        <label
                            class="flex items-start gap-4 cursor-pointer rounded-lg border border-base-300 bg-base-100 p-4">
                            <input type="checkbox" wire:model="functional" class="checkbox checkbox-primary mt-0.5">
                            <span>
                                <span class="block font-semibold mb-1">{{ __('cookie-consent::messages.setting_functional') }}</span>
                                <span class="text-sm opacity-80">{{ __('cookie-consent::messages.setting_functional_text') }}</span>
                            </span>
                        </label>

                        <label
                            class="flex items-start gap-4 cursor-pointer rounded-lg border border-base-300 bg-base-100 p-4">
                            <input type="checkbox" wire:model="analytics" class="checkbox checkbox-primary mt-0.5">
                            <span>
                                <span class="block font-semibold mb-1">{{ __('cookie-consent::messages.setting_analytics') }}</span>
                                <span class="text-sm opacity-80">{{ __('cookie-consent::messages.setting_analytics_text') }}</span>
                            </span>
                        </label>

                        <label
                            class="flex items-start gap-4 cursor-pointer rounded-lg border border-base-300 bg-base-100 p-4">
                            <input type="checkbox" wire:model="marketing" class="checkbox checkbox-primary mt-0.5">
                            <span>
                                <span class="block font-semibold mb-1">{{ __('cookie-consent::messages.setting_marketing') }}</span>
                                <span class="text-sm opacity-80">{{ __('cookie-consent::messages.setting_marketing_text') }}</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="border-t border-base-300 px-4 py-4 sm:px-6">
                    <button wire:click="saveSettings" type="button" class="btn btn-neutral w-full">
                        {{ __('cookie-consent::messages.settings_save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
