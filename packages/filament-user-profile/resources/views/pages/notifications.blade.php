<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Push Notifications Section --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">
                {{ __('filament-user-profile::messages.Push Notifications') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ __('filament-user-profile::messages.Receive push notifications in your browser even when the app is closed.') }}
            </p>

            @if($pushSupported && $pushEnabled)
                {{-- Push notifications are available --}}
                <div x-data="pushNotificationsManager(@js($vapidPublicKey))" class="space-y-4">
                    {{-- Status Display --}}
                    <div class="flex items-center gap-3">
                        <template x-if="status.subscribed">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800 dark:bg-green-800/20 dark:text-green-400">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ __('filament-user-profile::messages.Push notifications enabled') }}
                            </span>
                        </template>
                        <template x-if="!status.subscribed && status.permission === 'granted'">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-3 py-1 text-sm font-medium text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ __('filament-user-profile::messages.Not subscribed') }}
                            </span>
                        </template>
                        <template x-if="status.permission === 'denied'">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-800/20 dark:text-red-400">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ __('filament-user-profile::messages.Notifications blocked') }}
                            </span>
                        </template>
                    </div>

                    {{-- iOS PWA Notice --}}
                    <template x-if="status.iosRequiresPwa">
                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <div class="flex">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="ml-3 text-sm text-blue-700 dark:text-blue-300">
                                    {{ __('filament-user-profile::messages.To enable push notifications on iOS, add this app to your Home Screen first.') }}
                                </p>
                            </div>
                        </div>
                    </template>

                    {{-- Permission Denied Notice --}}
                    <template x-if="status.permission === 'denied'">
                        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                            <p class="text-sm text-red-700 dark:text-red-300">
                                {{ __('filament-user-profile::messages.Notifications are blocked. Please enable them in your browser settings.') }}
                            </p>
                        </div>
                    </template>

                    {{-- Subscribe/Unsubscribe Buttons --}}
                    <div class="flex items-center gap-4">
                        <template x-if="!status.subscribed && status.permission !== 'denied' && !status.iosRequiresPwa">
                            <button type="button" @click="subscribe" :disabled="loading"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 dark:bg-primary-500 dark:hover:bg-primary-600">
                                <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg x-show="!loading" class="h-4 w-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                {{ __('filament-user-profile::messages.Enable Push Notifications') }}
                            </button>
                        </template>

                        <template x-if="status.subscribed">
                            <button type="button" @click="unsubscribe" :disabled="loading"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                {{ __('filament-user-profile::messages.Disable Push Notifications') }}
                            </button>
                        </template>
                    </div>

                    {{-- Error Message --}}
                    <template x-if="error">
                        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                            <p class="text-sm text-red-700 dark:text-red-300" x-text="error"></p>
                        </div>
                    </template>
                </div>
            @elseif($pushSupported && !$pushEnabled)
                {{-- Push not configured --}}
                <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        {{ __('filament-user-profile::messages.Push notifications are not configured. Please contact your administrator.') }}
                    </p>
                </div>
            @else
                {{-- Push not available --}}
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('filament-user-profile::messages.Push notifications are not available.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('pushNotificationsManager', (vapidPublicKey) => ({
                    vapidPublicKey: vapidPublicKey,
                    push: null,
                    status: {
                        supported: false,
                        permission: 'default',
                        subscribed: false,
                        isIos: false,
                        isPwa: false,
                        iosRequiresPwa: false,
                    },
                    loading: false,
                    error: null,

                    async init() {
                        // Check if PushNotifications class is available
                        if (typeof PushNotifications === 'undefined') {
                            console.warn('PushNotifications class not loaded');
                            return;
                        }

                        this.push = new PushNotifications({
                            vapidPublicKey: this.vapidPublicKey,
                        });

                        await this.refreshStatus();
                    },

                    async refreshStatus() {
                        if (!this.push) return;
                        this.status = await this.push.getStatus();
                    },

                    async subscribe() {
                        if (!this.push) return;

                        this.loading = true;
                        this.error = null;

                        try {
                            console.log('[Alpine] Calling push.subscribe()...');
                            await this.push.subscribe();
                            console.log('[Alpine] push.subscribe() resolved. Refreshing status...');
                            await this.refreshStatus();
                            console.log('[Alpine] Status refreshed.');
                        } catch (e) {
                            console.error('[Alpine] Subscription error:', e);
                            this.error = e.message || 'Failed to enable notifications';
                        } finally {
                            console.log('[Alpine] Loading finished.');
                            this.loading = false;
                        }
                    },

                    async unsubscribe() {
                        if (!this.push) return;

                        this.loading = true;
                        this.error = null;

                        try {
                            await this.push.unsubscribe();
                            await this.refreshStatus();
                        } catch (e) {
                            this.error = e.message || 'Failed to disable notifications';
                        } finally {
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
</x-filament-panels::page>