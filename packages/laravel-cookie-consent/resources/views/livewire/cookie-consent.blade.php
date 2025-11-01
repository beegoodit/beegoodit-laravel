<div>
    @php
        $locale = app()->getLocale();
        $policyUrl = config("cookie-consent.policy_url_{$locale}", config('cookie-consent.policy_url_en'));
    @endphp

    {{-- Main Alert Banner --}}
    <div x-data="{ show: @entangle('show') }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-y-full"
         x-transition:enter-end="transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-y-0"
         x-transition:leave-end="transform translate-y-full"
         class="fixed bottom-0 left-0 right-0 z-50 px-4 mb-0 sm:mb-4">
        
        <div role="dialog" 
             aria-labelledby="cookie-banner-title" 
             aria-describedby="cookie-banner-desc"
             class="mx-auto max-w-4xl rounded-t-2xl sm:rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 overflow-hidden">
        
            <div class="p-4 sm:p-6">
            {{-- Title with Cookie Icon --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z"/>
                        <circle cx="8" cy="8" r="1.5"/>
                        <circle cx="15" cy="9" r="1.5"/>
                        <circle cx="9" cy="15" r="1.5"/>
                        <circle cx="16" cy="15" r="1.5"/>
                    </svg>
                </div>
                <h2 id="cookie-banner-title" class="text-xl sm:text-2xl font-bold text-zinc-900 dark:text-white">
                    {{ __('cookie-consent::messages.alert_title') }}
                </h2>
            </div>
            
            <p id="cookie-banner-desc" class="text-sm sm:text-base text-zinc-600 dark:text-zinc-400">
                {{ __('cookie-consent::messages.alert_text') }}
                <a href="{{ $policyUrl }}" class="underline hover:text-zinc-900 dark:hover:text-white font-medium transition">
                    {{ __('cookie-consent::messages.learn_more') }}
                </a>
            </p>
        </div>
        
        {{-- Buttons Section with border separator --}}
        <div class="border-t border-zinc-200 dark:border-zinc-700 px-4 py-4 sm:px-6 sm:py-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <button wire:click="acceptAll" type="button" 
                        class="flex-1 inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 shadow-sm transition-all">
                    {{ __('cookie-consent::messages.alert_accept') }}
                </button>
                <button wire:click="acceptEssential" type="button" 
                        class="flex-1 inline-flex items-center justify-center rounded-lg bg-zinc-100 px-5 py-3 text-sm font-semibold text-zinc-900 hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700 transition-all">
                    {{ __('cookie-consent::messages.alert_essential_only') }}
                </button>
                <button wire:click="openSettings" type="button" 
                        class="flex-1 inline-flex items-center justify-center rounded-lg border-2 border-zinc-300 bg-transparent px-5 py-3 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-all">
                    {{ __('cookie-consent::messages.alert_settings') }}
                </button>
            </div>
            </div>
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
         class="fixed inset-0 z-[100] overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl max-h-[90vh] overflow-y-auto rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                
                {{-- Close Button --}}
                <button wire:click="closeSettings" 
                        class="absolute top-4 right-4 z-10 rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-500 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-all" 
                        type="button"
                        aria-label="{{ __('cookie-consent::messages.settings_close') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                {{-- Modal Header (Sticky on scroll) --}}
                <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 rounded-t-xl z-10">
                    <div class="flex items-center gap-3 mb-3 pr-10">
                        <svg class="w-8 h-8 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v2h-2zm0 4h2v6h-2z"/>
                            <circle cx="8" cy="8" r="1.5"/>
                            <circle cx="15" cy="9" r="1.5"/>
                            <circle cx="9" cy="15" r="1.5"/>
                            <circle cx="16" cy="15" r="1.5"/>
                        </svg>
                        <h2 id="cookie-settings-title" class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ __('cookie-consent::messages.settings_title') }}
                        </h2>
                    </div>
                    <p id="cookie-settings-desc" class="text-sm text-zinc-600 dark:text-zinc-400">
                        {!! str_replace(':policyUrl', $policyUrl, __('cookie-consent::messages.settings_text')) !!}
                    </p>
                </div>
                
                {{-- Modal Content --}}
                <div class="p-4 sm:p-6 space-y-6">
                    {{-- Accept All Quick Action --}}
                    <div class="text-center py-2">
                        <button wire:click="acceptAll" type="button" 
                                class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-8 py-3 text-sm font-semibold text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 shadow-sm transition-all">
                            {{ __('cookie-consent::messages.settings_accept_all') }}
                        </button>
                    </div>
                    
                    {{-- Cookie Categories --}}
                    <div class="space-y-4">
                        {{-- Essential Cookies (Always On) --}}
                        <div class="rounded-xl border-2 border-zinc-200 bg-zinc-50 p-4 sm:p-5 dark:border-zinc-700 dark:bg-zinc-800">
                            <label class="flex items-start gap-4 cursor-not-allowed">
                                <input type="checkbox" wire:model="essential" disabled checked 
                                       class="mt-0.5 h-5 w-5 rounded border-zinc-300 text-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-700 cursor-not-allowed">
                                <div class="flex-1">
                                    <span class="block text-base font-semibold text-zinc-900 dark:text-white mb-1">
                                        {{ __('cookie-consent::messages.setting_essential') }}
                                    </span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                        {{ __('cookie-consent::messages.setting_essential_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Functional Cookies (Always On) --}}
                        <div class="rounded-xl border-2 border-zinc-200 bg-zinc-50 p-4 sm:p-5 dark:border-zinc-700 dark:bg-zinc-800">
                            <label class="flex items-start gap-4 cursor-not-allowed">
                                <input type="checkbox" wire:model="functional" disabled checked 
                                       class="mt-0.5 h-5 w-5 rounded border-zinc-300 text-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-700 cursor-not-allowed">
                                <div class="flex-1">
                                    <span class="block text-base font-semibold text-zinc-900 dark:text-white mb-1">
                                        {{ __('cookie-consent::messages.setting_functional') }}
                                    </span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                        {{ __('cookie-consent::messages.setting_functional_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Analytics Cookies (Toggleable) --}}
                        <div class="rounded-xl border-2 border-zinc-200 bg-white p-4 sm:p-5 hover:border-amber-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-amber-600 transition-colors">
                            <label class="flex items-start gap-4 cursor-pointer">
                                <input type="checkbox" wire:model="analytics" 
                                       class="mt-0.5 h-5 w-5 rounded border-zinc-300 text-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-800 cursor-pointer">
                                <div class="flex-1">
                                    <span class="block text-base font-semibold text-zinc-900 dark:text-white mb-1">
                                        {{ __('cookie-consent::messages.setting_analytics') }}
                                    </span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                        {{ __('cookie-consent::messages.setting_analytics_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        {{-- Marketing Cookies (Toggleable) --}}
                        <div class="rounded-xl border-2 border-zinc-200 bg-white p-4 sm:p-5 hover:border-amber-300 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-amber-600 transition-colors">
                            <label class="flex items-start gap-4 cursor-pointer">
                                <input type="checkbox" wire:model="marketing" 
                                       class="mt-0.5 h-5 w-5 rounded border-zinc-300 text-amber-500 focus:ring-amber-500 dark:border-zinc-600 dark:bg-zinc-800 cursor-pointer">
                                <div class="flex-1">
                                    <span class="block text-base font-semibold text-zinc-900 dark:text-white mb-1">
                                        {{ __('cookie-consent::messages.setting_marketing') }}
                                    </span>
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                        {{ __('cookie-consent::messages.setting_marketing_text') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                {{-- Modal Footer (Sticky) --}}
                <div class="sticky bottom-0 border-t border-zinc-200 bg-white p-4 sm:p-6 dark:border-zinc-700 dark:bg-zinc-900 rounded-b-xl">
                    <button wire:click="saveSettings" type="button" 
                            class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-3 text-base font-semibold text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 shadow-sm transition-all">
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
         class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
         @click="$wire.closeSettings()"></div>
</div>
