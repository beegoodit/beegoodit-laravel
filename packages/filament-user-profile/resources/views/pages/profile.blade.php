<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-8">
        {{-- Avatar Upload Section --}}
        @php
            $user = auth()->user();
            $supportsAvatar = $user instanceof \Filament\Models\Contracts\HasAvatar && method_exists($user, 'getAvatarUrl');
        @endphp

        @if($supportsAvatar)
            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div 
                        class="relative group"
                        x-data="{ 
                            isDragging: false,
                            handleDrop(event) {
                                this.isDragging = false;
                                const files = event.dataTransfer.files;
                                if (files.length > 0) {
                                    @this.upload('avatarUpload', files[0], () => {
                                        @this.call('updateAvatar');
                                    });
                                }
                            }
                        }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        :class="isDragging ? 'ring-2 ring-primary-500' : ''"
                    >
                        <span class="relative flex h-24 w-24 shrink-0 overflow-hidden rounded-full border-2 border-dashed border-gray-300 dark:border-gray-600 transition-colors hover:border-gray-400 dark:hover:border-gray-500">
                            @php
                                $avatarUrl = $user->getAvatarUrl();
                            @endphp
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ __('filament-user-profile::messages.Avatar') }}" class="h-full w-full object-cover" wire:loading.remove wire:target="avatarUpload" />
                            @else
                                <span class="flex h-full w-full items-center justify-center rounded-full bg-neutral-200 text-lg font-semibold text-black dark:bg-neutral-700 dark:text-white" wire:loading.remove wire:target="avatarUpload">
                                    {{ method_exists($user, 'initials') ? $user->initials() : substr($user->name, 0, 2) }}
                                </span>
                            @endif
                            
                            <span wire:loading wire:target="avatarUpload" class="flex h-full w-full items-center justify-center bg-zinc-100 dark:bg-zinc-800">
                                <svg class="animate-spin h-8 w-8 text-zinc-600 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </span>

                        @if ($avatarUrl)
                            <button 
                                type="button"
                                wire:click="removeAvatar"
                                class="absolute -top-1 -right-1 h-6 w-6 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 transition-colors"
                                title="{{ __('filament-user-profile::messages.Remove avatar') }}"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <div class="mb-2">
                            <input 
                                type="file" 
                                accept="image/jpeg,image/png,image/gif,image/webp" 
                                class="hidden"
                                id="avatar-upload"
                                x-on:change="$wire.upload('avatarUpload', $event.target.files[0], () => { $wire.call('updateAvatar'); })"
                            />
                            <x-filament::button 
                                type="button" 
                                x-on:click="document.getElementById('avatar-upload').click()"
                            >
                                {{ $avatarUrl ? __('filament-user-profile::messages.Change picture') : __('filament-user-profile::messages.Upload picture') }}
                            </x-filament::button>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('filament-user-profile::messages.Click to upload or drag and drop') }}<br>
                            {{ __('filament-user-profile::messages.JPG, PNG, GIF or WebP (max. 2MB)') }}
                        </p>

                        @error('avatarUpload')
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror

                        @if (session('status') === 'avatar-updated')
                            <p class="text-sm text-success-600 dark:text-success-400">
                                {{ __('filament-user-profile::messages.Avatar updated successfully.') }}
                            </p>
                        @elseif (session('status') === 'avatar-removed')
                            <p class="text-sm text-success-600 dark:text-success-400">
                                {{ __('filament-user-profile::messages.Avatar removed successfully.') }}
                            </p>
                        @elseif (session('status') === 'avatar-update-failed' || session('status') === 'avatar-remove-failed')
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ __('filament-user-profile::messages.Failed to update avatar. Please try again.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Profile Information Form --}}
        {{ $this->form }}

        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
            <div class="mt-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('filament-user-profile::messages.Your email address is unverified.') }}
                    <button type="button" wire:click="resendVerificationNotification" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        {{ __('filament-user-profile::messages.Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-success-600 dark:text-success-400">
                        {{ __('filament-user-profile::messages.A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        @if (session('status') === 'profile-updated')
            <p class="mt-2 text-sm font-medium text-success-600 dark:text-success-400">
                {{ __('filament-user-profile::messages.Profile updated successfully.') }}
            </p>
        @endif

        <div class="flex items-center gap-4 mt-4">
            <x-filament::button type="submit">
                {{ __('filament-user-profile::messages.Save') }}
            </x-filament::button>
        </div>
    </form>

    {{-- Delete User Account Section --}}
    <div class="mt-10 space-y-6 border-t border-gray-200 dark:border-gray-700 pt-8">
        <div class="relative mb-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('filament-user-profile::messages.Delete account') }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('filament-user-profile::messages.Delete your account and all of its resources') }}</p>
        </div>

        <x-filament::button
            type="button"
            color="danger"
            wire:click="openDeleteModal"
        >
            {{ __('filament-user-profile::messages.Delete account') }}
        </x-filament::button>

        {{-- Delete User Modal --}}
        <div
            x-data="{ showModal: @entangle('showDeleteModal') }"
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
                    @click="showModal = false; $wire.closeDeleteModal()"
                ></div>

                {{-- Modal Content --}}
                <div
                    class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700"
                    @click.stop
                >
                    {{-- Close Button --}}
                    <button
                        type="button"
                        @click="showModal = false; $wire.closeDeleteModal()"
                        class="absolute top-4 right-4 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-all"
                        aria-label="{{ __('filament-user-profile::messages.Close') }}"
                    >
                        <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                    </button>

                    @php
                        $user = auth()->user();
                        $isOAuthOnly = method_exists($user, 'isOAuthOnly') ? $user->isOAuthOnly() : is_null($user->password);
                        // Check for OAuth accounts directly
                        $hasOAuthAccounts = false;
                        if (method_exists($user, 'oauthAccounts')) {
                            try {
                                $hasOAuthAccounts = $user->oauthAccounts()->exists();
                            } catch (\Exception $e) {
                                // Fall through
                            }
                        }
                        // Also check socialite_users table
                        if (!$hasOAuthAccounts && \Illuminate\Support\Facades\Schema::hasTable('socialite_users')) {
                            $hasOAuthAccounts = \Illuminate\Support\Facades\DB::table('socialite_users')
                                ->where('user_id', $user->id)
                                ->exists();
                        }
                        // Only show OAuth UI if user is OAuth-only AND has OAuth accounts
                        $showOAuthUI = $isOAuthOnly && $hasOAuthAccounts;
                    @endphp

                    @if($showOAuthUI)
                        {{-- OAuth User: Confirmation Checkbox --}}
                        <form wire:submit="initiateDeleteAccount" class="p-6 space-y-6">
                            {{-- Header --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ __('filament-user-profile::messages.Are you sure you want to delete your account?') }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ __('filament-user-profile::messages.Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.') }}
                                </p>
                                <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 p-4">
                                    <p class="text-sm font-medium text-danger-800 dark:text-danger-200">
                                        {{ __('filament-user-profile::messages.You will be redirected to re-authenticate with your OAuth provider to confirm this action.') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Confirmation Checkbox --}}
                            <div>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model="confirmDelete"
                                        required
                                        class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('filament-user-profile::messages.I understand that this action cannot be undone and all my data will be permanently deleted.') }}
                                    </span>
                                </label>
                                @error('confirmDelete')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Actions --}}
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                <x-filament::button
                                    type="button"
                                    color="gray"
                                    @click="showModal = false; $wire.closeDeleteModal()"
                                >
                                    {{ __('filament-user-profile::messages.Cancel') }}
                                </x-filament::button>

                                <x-filament::button
                                    type="submit"
                                    color="danger"
                                >
                                    {{ __('filament-user-profile::messages.Continue to Delete') }}
                                </x-filament::button>
                                </div>
                        </form>
                    @elseif(!$isOAuthOnly)
                        {{-- Password User: Password Field --}}
                        <form wire:submit="deleteUser" class="p-6 space-y-6">
                            {{-- Header --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ __('filament-user-profile::messages.Are you sure you want to delete your account?') }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('filament-user-profile::messages.Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                                </p>
                            </div>

                            {{-- Password Input --}}
                            <div>
                                <label for="delete-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('filament-user-profile::messages.Password') }}
                                </label>
                                <input
                                    id="delete-password"
                                    type="password"
                                    wire:model="deletePassword"
                                    required
                                    autocomplete="current-password"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:focus:border-primary-400 dark:focus:ring-primary-400"
                                />
                                @error('deletePassword')
                                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Actions --}}
                            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                <x-filament::button
                                    type="button"
                                    color="gray"
                                    @click="showModal = false; $wire.closeDeleteModal()"
                                >
                                    {{ __('filament-user-profile::messages.Cancel') }}
                                </x-filament::button>

                                <x-filament::button
                                    type="submit"
                                    color="danger"
                                >
                                    {{ __('filament-user-profile::messages.Delete account') }}
                                </x-filament::button>
                                </div>
                        </form>
                    @else
                        {{-- Fallback: Should not happen, but show error message --}}
                        <div class="p-6 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ __('filament-user-profile::messages.Error') }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('filament-user-profile::messages.Unable to determine account type. Please refresh the page and try again.') }}
                                </p>
                            </div>
                            <div class="flex justify-end">
                                <x-filament::button
                                    type="button"
                                    color="gray"
                                    @click="showModal = false; $wire.closeDeleteModal()"
                                >
                                    {{ __('filament-user-profile::messages.Close') }}
                                </x-filament::button>
                            </div>
                        </div>
                    @endif
                </div>
                </div>
            </div>
        </div>
</x-filament-panels::page>

