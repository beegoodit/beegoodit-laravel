<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        {{-- Profile Card --}}
        <div class="flex">
            <x-filament::section class="flex-1 flex flex-col">
                <div class="flex flex-col h-full">
                    <div class="flex items-center gap-4 grow min-h-[7rem] sm:min-h-[8rem]">
                        <div class="p-3 bg-amber-100 rounded-lg dark:bg-amber-900/30 shrink-0">
                            <x-heroicon-o-user class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">
                                {{ __('filament-user-profile::messages.Profile Information') }}</h2>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                {{ __('filament-user-profile::messages.Update your name and email address') }}
                            </p>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex">
                        <x-filament::button tag="a"
                            href="{{ \BeeGoodIT\FilamentUserProfile\Filament\Pages\Profile::getUrl() }}" color="gray"
                            outlined size="sm" class="w-full sm:w-auto">
                            {{ __('filament-user-profile::messages.Edit Profile') }}
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::section>
        </div>

        {{-- Password Card --}}
        <div class="flex">
            <x-filament::section class="flex-1 flex flex-col">
                <div class="flex flex-col h-full">
                    <div class="flex items-center gap-4 grow min-h-[7rem] sm:min-h-[8rem]">
                        <div class="p-3 bg-amber-100 rounded-lg dark:bg-amber-900/30 shrink-0">
                            <x-heroicon-o-key class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">{{ __('filament-user-profile::messages.Update Password') }}
                            </h2>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                {{ __('filament-user-profile::messages.Ensure your account is using a strong password') }}
                            </p>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex">
                        <x-filament::button tag="a"
                            href="{{ \BeeGoodIT\FilamentUserProfile\Filament\Pages\Password::getUrl() }}" color="gray"
                            outlined size="sm" class="w-full sm:w-auto">
                            {{ __('filament-user-profile::messages.Change Password') }}
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::section>
        </div>

        {{-- Appearance Card --}}
        <div class="flex">
            <x-filament::section class="flex-1 flex flex-col">
                <div class="flex flex-col h-full">
                    <div class="flex items-center gap-4 grow min-h-[7rem] sm:min-h-[8rem]">
                        <div class="p-3 bg-amber-100 rounded-lg dark:bg-amber-900/30 shrink-0">
                            <x-heroicon-o-paint-brush class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">
                                {{ __('filament-user-profile::messages.Appearance Settings') }}</h2>
                            <p class="text-sm text-gray-400 dark:text-gray-500">
                                {{ __('filament-user-profile::messages.Manage your theme and language') }}
                            </p>
                        </div>
                    </div>
                </div>
                <x-slot name="footer">
                    <div class="flex">
                        <x-filament::button tag="a"
                            href="{{ \BeeGoodIT\FilamentUserProfile\Filament\Pages\Appearance::getUrl() }}" color="gray"
                            outlined size="sm" class="w-full sm:w-auto">
                            {{ __('filament-user-profile::messages.Manage Appearance') }}
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>