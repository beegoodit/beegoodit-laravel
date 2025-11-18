<x-filament-panels::page>
    <div>
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ __('Theme') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('Choose your preferred theme appearance') }}
            </p>
            <div x-data="themeToggle()" class="w-full">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Theme') }}
                    </label>
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-800" role="group">
                        <button type="button" @click="setTheme('light')" :class="theme === 'light' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'" class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ __('Light') }}
                        </button>
                        <button type="button" @click="setTheme('dark')" :class="theme === 'dark' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'" class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            {{ __('Dark') }}
                        </button>
                        <button type="button" @click="setTheme('system')" :class="theme === 'system' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'" class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ __('System') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <form wire:submit="submit">
            {{ $this->form }}
        </form>
    </div>
</x-filament-panels::page>

@once
@push('scripts')
<script>
if (typeof window.themeToggle === 'undefined') {
    window.themeToggle = function() {
        return {
            theme: localStorage.getItem('theme') || 'system',
            systemPreferenceListener: null,
            init() {
                this.applyTheme(this.theme);
                if (this.theme === 'system') {
                    this.watchSystemPreference();
                }
            },
            setTheme(theme) {
                this.theme = theme;
                localStorage.setItem('theme', theme);
                this.applyTheme(theme);
                if (theme === 'system') {
                    this.watchSystemPreference();
                }
            },
            applyTheme(theme) {
                const html = document.documentElement;
                const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && isSystemDark)) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            },
            watchSystemPreference() {
                if (this.systemPreferenceListener) {
                    window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.systemPreferenceListener);
                }
                this.systemPreferenceListener = (e) => {
                    if (this.theme === 'system') {
                        this.applyTheme('system');
                    }
                };
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.systemPreferenceListener);
            }
        };
    };
}
</script>
@endpush
@endonce

