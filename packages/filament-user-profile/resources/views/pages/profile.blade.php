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
                        <span class="relative flex h-24 w-24 shrink-0 overflow-hidden rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 transition-colors hover:border-gray-400 dark:hover:border-gray-500">
                            @php($avatarUrl = $user->getAvatarUrl())
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ __('Avatar') }}" class="h-full w-full object-cover" wire:loading.remove wire:target="avatarUpload" />
                            @else
                                <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-lg font-semibold text-black dark:bg-neutral-700 dark:text-white" wire:loading.remove wire:target="avatarUpload">
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
                                title="{{ __('Remove avatar') }}"
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
                                {{ $avatarUrl ? __('Change picture') : __('Upload picture') }}
                            </x-filament::button>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Click to upload or drag and drop') }}<br>
                            {{ __('JPG, PNG, GIF or WebP (max. 2MB)') }}
                        </p>

                        @error('avatarUpload')
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror

                        @if (session('status') === 'avatar-updated')
                            <p class="text-sm text-success-600 dark:text-success-400">
                                {{ __('Avatar updated successfully.') }}
                            </p>
                        @elseif (session('status') === 'avatar-removed')
                            <p class="text-sm text-success-600 dark:text-success-400">
                                {{ __('Avatar removed successfully.') }}
                            </p>
                        @elseif (session('status') === 'avatar-update-failed' || session('status') === 'avatar-remove-failed')
                            <p class="text-sm text-danger-600 dark:text-danger-400">
                                {{ __('Failed to update avatar. Please try again.') }}
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
                    {{ __('Your email address is unverified.') }}
                    <button type="button" wire:click="resendVerificationNotification" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-medium text-success-600 dark:text-success-400">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        @if (session('status') === 'profile-updated')
            <p class="mt-2 text-sm font-medium text-success-600 dark:text-success-400">
                {{ __('Profile updated successfully.') }}
            </p>
        @endif

        <div class="flex items-center gap-4 mt-4">
            <x-filament::button type="submit">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

