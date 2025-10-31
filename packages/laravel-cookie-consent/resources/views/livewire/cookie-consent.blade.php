@php
    $locale = app()->getLocale();
    $policyUrl = config("cookie-consent.policy_url_{$locale}", config('cookie-consent.policy_url_en'));
@endphp

<div>
    {{-- Main Alert Banner --}}
    <div x-data="{ show: @entangle('show') }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-y-0"
         x-transition:leave-end="transform translate-y-full"
         role="dialog" 
         aria-labelledby="cookie-banner-title" 
         aria-describedby="cookie-banner-desc"
         class="fixed bottom-0 left-0 right-0 z-50 m-4 max-w-4xl mx-auto rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
        <div class="p-6">
            <h2 id="cookie-banner-title" class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">
                {{ __('cookie-consent::messages.alert_title') }}
            </h2>
            <p id="cookie-banner-desc" class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                {{ __('cookie-consent::messages.alert_text') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3 px-6 pb-6">
            <button wire:click="acceptAll" type="button" 
                    class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 transition-colors">
                {{ __('cookie-consent::messages.alert_accept') }}
            </button>
            <button wire:click="acceptEssential" type="button" 
                    class="inline-flex items-center justify-center rounded-lg bg-zinc-100 px-4 py-2.5 text-sm font-medium text-zinc-900 hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700 transition-colors">
                {{ __('cookie-consent::messages.alert_essential_only') }}
            </button>
            <button wire:click="openSettings" type="button" 
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-transparent px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                {{ __('cookie-consent::messages.alert_settings') }}
            </button>
        </div>
    </div>

    {{-- Settings Modal --}}
    <div x-data="{ showSettings: @entangle('showSettings') }" 
         x-show="showSettings" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         role="dialog" 
         aria-labelledby="cookie-settings-title" 
         aria-describedby="cookie-settings-desc"
         class="fixed inset-0 z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
                <button wire:click="closeSettings" class="absolute top-4 right-4 rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300" type="button">
                    <span class="sr-only">{{ __('cookie-consent::messages.settings_close') }}</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <div class="p-6">
                    <h2 id="cookie-settings-title" class="text-2xl font-semibold text-zinc-900 dark:text-white mb-3">
                        {{ __('cookie-consent::messages.settings_title') }}
                    </h2>
                    <p id="cookie-settings-desc" class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                        {!! str_replace(':policyUrl', $policyUrl, __('cookie-consent::messages.settings_text')) !!}
                    </p>
                    
                    <div class="mb-6 text-center">
                        <button wire:click="acceptAll" type="button" 
                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 transition-colors">
                            {{ __('cookie-consent::messages.settings_accept_all') }}
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        {{-- Essential Cookies (Always On) --}}
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <label class="flex items-start gap-3 cursor-not-allowed">
                                <input type="checkbox" wire:model="essential" disabled checked 
                                       class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700">
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ __('cookie-consent::messages.setting_essential') }}
                                    </span>
                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('cookie-consent::messages.setting_essential_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Functional Cookies (Always On) --}}
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <label class="flex items-start gap-3 cursor-not-allowed">
                                <input type="checkbox" wire:model="functional" disabled checked 
                                       class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-700">
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ __('cookie-consent::messages.setting_functional') }}
                                    </span>
                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('cookie-consent::messages.setting_functional_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Analytics Cookies (Toggleable) --}}
                        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="analytics" 
                                       class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ __('cookie-consent::messages.setting_analytics') }}
                                    </span>
                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('cookie-consent::messages.setting_analytics_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Marketing Cookies (Toggleable) --}}
                        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="marketing" 
                                       class="mt-1 h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800">
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ __('cookie-consent::messages.setting_marketing') }}
                                    </span>
                                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('cookie-consent::messages.setting_marketing_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-zinc-200 p-6 dark:border-zinc-700">
                    <button wire:click="saveSettings" type="button" 
                            class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 transition-colors">
                        {{ __('cookie-consent::messages.settings_save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Backdrop Overlay --}}
    <div x-data="{ showBackdrop: @entangle('showSettings') }" 
         x-show="showBackdrop" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"
         @click="$wire.closeSettings()"></div>
</div>
