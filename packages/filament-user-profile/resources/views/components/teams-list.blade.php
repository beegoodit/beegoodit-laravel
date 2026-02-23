@props([
    'teams',
])

<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($teams as $team)
            @php
                $role = $team->pivot?->role;
                $hasRoleWithColor = $role && method_exists($role, 'getColor') && method_exists($role, 'getLabel');
                $color = $hasRoleWithColor ? $role->getColor() : 'gray';
                $roleLabel = $hasRoleWithColor
                    ? $role->getLabel()
                    : ($role ? match (true) {
                        $role instanceof \BackedEnum => $role->value,
                        $role instanceof \UnitEnum => $role->name,
                        is_scalar($role) => (string) $role,
                        default => null,
                    } : null);
            @endphp
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm shadow-sm transition-all hover:shadow-md">
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $team->name }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $team->slug }}
                    </span>
                </div>

                @if($roleLabel !== null)
                    <x-filament::badge :color="$color">
                        {{ $roleLabel }}
                    </x-filament::badge>
                @endif
            </div>
        @endforeach
    </div>

    @if($teams->isEmpty())
        <div class="p-8 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700 text-center">
            <flux:text>
                {{ __('No teams joined yet.') }}
            </flux:text>
        </div>
    @endif
</div>
