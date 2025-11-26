<x-filament-panels::page>
    <div class="flex flex-col w-full mx-auto space-y-6" wire:cloak>
        @if ($twoFactorEnabled)
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <x-filament::badge color="success">
                        {{ __('filament-user-profile::messages.Enabled') }}
                    </x-filament::badge>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('filament-user-profile::messages.With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                </p>

                {{-- Recovery Codes Section --}}
                <div
                    class="py-6 space-y-6 border shadow-sm rounded-xl border-gray-200 dark:border-white/10"
                    x-data="{ showRecoveryCodes: @js($showRecoveryCodes) }"
                >
                    <div class="px-6 space-y-2">
                        <div class="flex items-center gap-2">
                            <x-filament::icon
                                icon="heroicon-o-lock-closed"
                                class="h-4 w-4"
                            />
                            <h3 class="text-lg font-semibold">{{ __('filament-user-profile::messages.2FA Recovery Codes') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('filament-user-profile::messages.Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
                        </p>
                    </div>

                    <div class="px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <x-filament::button
                                x-show="!showRecoveryCodes"
                                icon="heroicon-o-eye"
                                x-on:click="showRecoveryCodes = true; $wire.set('showRecoveryCodes', true)"
                                aria-expanded="false"
                                aria-controls="recovery-codes-section"
                            >
                                {{ __('filament-user-profile::messages.View Recovery Codes') }}
                            </x-filament::button>

                            <x-filament::button
                                x-show="showRecoveryCodes"
                                icon="heroicon-o-eye-slash"
                                x-on:click="showRecoveryCodes = false; $wire.set('showRecoveryCodes', false)"
                                aria-expanded="true"
                                aria-controls="recovery-codes-section"
                            >
                                {{ __('filament-user-profile::messages.Hide Recovery Codes') }}
                            </x-filament::button>

                            @if (filled($recoveryCodes))
                                <x-filament::button
                                    x-show="showRecoveryCodes"
                                    icon="heroicon-o-arrow-path"
                                    wire:click="regenerateRecoveryCodes"
                                >
                                    {{ __('filament-user-profile::messages.Regenerate Codes') }}
                                </x-filament::button>
                            @endif
                        </div>

                        <div
                            x-show="showRecoveryCodes"
                            x-transition
                            id="recovery-codes-section"
                            class="relative overflow-hidden"
                            x-bind:aria-hidden="!showRecoveryCodes"
                        >
                            <div class="mt-3 space-y-3">
                                @error('recoveryCodes')
                                    <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 p-3 text-sm text-danger-700 dark:text-danger-400">
                                        {{ $message }}
                                    </div>
                                @enderror

                                @if (filled($recoveryCodes))
                                    <div
                                        class="grid gap-1 p-4 font-mono text-sm rounded-lg bg-gray-100 dark:bg-white/5"
                                        role="list"
                                        aria-label="Recovery codes"
                                    >
                                        @foreach($recoveryCodes as $code)
                                            <div
                                                role="listitem"
                                                class="select-text"
                                                wire:loading.class="opacity-50 animate-pulse"
                                            >
                                                {{ $code }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ __('filament-user-profile::messages.Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate Codes above.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-start">
                    <x-filament::button
                        color="danger"
                        icon="heroicon-o-shield-exclamation"
                        wire:click="disable"
                    >
                        {{ __('filament-user-profile::messages.Disable 2FA') }}
                    </x-filament::button>
                </div>
            </div>
        @else
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <x-filament::badge color="danger">
                        {{ __('filament-user-profile::messages.Disabled') }}
                    </x-filament::badge>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('filament-user-profile::messages.When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                </p>

                <x-filament::button
                    icon="heroicon-o-shield-check"
                    wire:click="enable"
                >
                    {{ __('filament-user-profile::messages.Enable 2FA') }}
                </x-filament::button>
            </div>
        @endif
    </div>

    {{-- Two-Factor Setup Modal --}}
    <div
        x-data="{ showModal: @entangle('showModal') }"
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        wire:ignore.self
    >
        <div class="flex items-center justify-center min-h-screen p-4">
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                @click="showModal = false; $wire.closeModal()"
            ></div>

            {{-- Modal Content --}}
            <div
                class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700"
                @click.stop
            >
                {{-- Close Button --}}
                <button
                    type="button"
                    @click="showModal = false; $wire.closeModal()"
                    class="absolute top-4 right-4 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-all"
                    aria-label="{{ __('filament-user-profile::messages.Close') }}"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>

                <div class="p-6 space-y-6">
                    {{-- Header --}}
                    <div class="flex flex-col items-center space-y-4">
                        <div class="p-0.5 w-auto rounded-full border border-gray-100 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm">
                            <div class="p-2.5 rounded-full border border-gray-200 dark:border-gray-600 overflow-hidden bg-gray-100 dark:bg-gray-200 relative">
                                <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-gray-200 dark:divide-gray-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-gray-200 dark:divide-gray-300 justify-around opacity-50">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <x-filament::icon
                                    icon="heroicon-o-qr-code"
                                    class="relative z-20 h-8 w-8 text-gray-900 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <div class="space-y-2 text-center">
                            <h3 class="text-lg font-semibold">{{ $this->modalConfig['title'] }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->modalConfig['description'] }}</p>
                        </div>
                    </div>

                    @if ($showVerificationStep)
                        {{-- Verification Step --}}
                        <div class="space-y-6">
                            <div class="flex flex-col items-center space-y-3">
                                <input
                                    type="text"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    inputmode="numeric"
                                    wire:model.live="code"
                                    x-on:input="
                                        $event.target.value = $event.target.value.replace(/[^0-9]/g, '').slice(0, 6);
                                    "
                                    class="w-full max-w-xs h-14 text-center text-2xl font-semibold tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white"
                                    autocomplete="one-time-code"
                                    placeholder="000000"
                                    autofocus
                                />
                                @error('code')
                                    <p class="text-sm text-danger-600 dark:text-danger-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="flex items-center space-x-3">
                                <x-filament::button
                                    variant="outline"
                                    class="flex-1"
                                    wire:click="resetVerification"
                                >
                                    {{ __('filament-user-profile::messages.Back') }}
                                </x-filament::button>

                                <x-filament::button
                                    class="flex-1"
                                    wire:click="confirmTwoFactor"
                                    x-bind:disabled="$wire.code.length < 6"
                                >
                                    {{ __('filament-user-profile::messages.Confirm') }}
                                </x-filament::button>
                            </div>
                        </div>
                    @else
                        {{-- QR Code Setup Step --}}
                        @error('setupData')
                            <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 p-3 text-sm text-danger-700 dark:text-danger-400">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex justify-center">
                            <div class="relative w-64 overflow-hidden border rounded-lg border-gray-200 dark:border-gray-700 aspect-square">
                                @empty($qrCodeSvg)
                                    <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-700 animate-pulse">
                                        <x-filament::loading-indicator class="h-8 w-8" />
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-full p-4">
                                        {!! $qrCodeSvg !!}
                                    </div>
                                @endempty
                            </div>
                        </div>

                        <div>
                            <x-filament::button
                                :disabled="$errors->has('setupData')"
                                class="w-full"
                                wire:click="showVerificationIfNecessary"
                            >
                                {{ $this->modalConfig['buttonText'] }}
                            </x-filament::button>
                        </div>

                        <div class="space-y-4">
                            <div class="relative flex items-center justify-center w-full">
                                <div class="absolute inset-0 w-full h-px top-1/2 bg-gray-200 dark:bg-gray-600"></div>
                                <span class="relative px-2 text-sm bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                    {{ __('filament-user-profile::messages.or, enter the code manually') }}
                                </span>
                            </div>

                            <div
                                class="flex items-center space-x-2"
                                x-data="{
                                    copied: false,
                                    async copy() {
                                        try {
                                            await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 1500);
                                        } catch (e) {
                                            console.warn('Could not copy to clipboard');
                                        }
                                    }
                                }"
                            >
                                <div class="flex items-stretch w-full border rounded-xl dark:border-gray-700">
                                    @empty($manualSetupKey)
                                        <div class="flex items-center justify-center w-full p-3 bg-gray-100 dark:bg-gray-700">
                                            <x-filament::loading-indicator class="h-4 w-4" />
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $manualSetupKey }}"
                                            class="w-full p-3 bg-transparent outline-none text-gray-900 dark:text-gray-100 border-0"
                                        />

                                        <button
                                            type="button"
                                            @click="copy()"
                                            class="px-3 transition-colors border-l cursor-pointer border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <x-filament::icon
                                                x-show="!copied"
                                                icon="heroicon-o-document-duplicate"
                                                class="h-5 w-5"
                                            />
                                            <x-filament::icon
                                                x-show="copied"
                                                icon="heroicon-o-check"
                                                class="h-5 w-5 text-success-500"
                                            />
                                        </button>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
