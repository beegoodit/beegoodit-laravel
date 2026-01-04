<x-filament-panels::page.simple>
    <div class="flex flex-col items-center justify-center min-h-[400px] space-y-8">
        <div class="p-3 bg-primary-50 dark:bg-primary-500/10 rounded-full">
            <x-filament::icon icon="heroicon-o-shield-check" class="h-10 w-10 text-primary-600 dark:text-primary-400" />
        </div>

        <div class="space-y-2 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ __('filament-user-profile::messages.Two-Factor Authentication') }}
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                @if ($usingRecoveryCode)
                    {{ __('filament-user-profile::messages.Please confirm access to your account by entering one of your emergency recovery codes.') }}
                @else
                    {{ __('filament-user-profile::messages.Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                @endif
            </p>
        </div>

        <form wire:submit="confirm" class="w-full max-w-sm space-y-6">
            {{ $this->form }}

            <x-filament::button type="submit" class="w-full">
                {{ __('filament-user-profile::messages.Confirm') }}
            </x-filament::button>

            <div class="text-center">
                <button type="button" wire:click="toggleRecoveryCode"
                    class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                    @if ($usingRecoveryCode)
                        {{ __('filament-user-profile::messages.Use an authentication code') }}
                    @else
                        {{ __('filament-user-profile::messages.Use a recovery code') }}
                    @endif
                </button>
            </div>
        </form>

        <div class="text-center">
            <x-filament::link :href="route('filament.portal.auth.logout')" color="gray" size="sm">
                {{ __('filament-user-profile::messages.Log out') }}
            </x-filament::link>
        </div>
    </div>
</x-filament-panels::page.simple>