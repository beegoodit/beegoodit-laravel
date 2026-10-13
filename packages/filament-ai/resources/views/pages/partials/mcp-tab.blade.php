<div class="space-y-6">
    @if (! \BeegoodIT\FilamentAi\Support\McpAvailability::packagePresent())
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament-ai::ai.mcp.package_missing_title') }}
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ __('filament-ai::ai.mcp.package_missing_body') }}
            </p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">composer require laravel/mcp</pre>
        </x-filament::section>
    @else
        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament-ai::ai.mcp.connection_heading') }}
            </x-slot>
            <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                {{ __('filament-ai::ai.mcp.connection_help') }}
            </p>
            <pre class="overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $this->mcpConnectionSnippet() }}</pre>
        </x-filament::section>

        @if (filled($this->plainMcpToken))
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('filament-ai::ai.mcp.token_created_title') }}
                </x-slot>
                <p class="mb-2 text-sm text-amber-700 dark:text-amber-300">
                    {{ __('filament-ai::ai.mcp.token_created_body') }}
                </p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-3 text-xs text-gray-100">{{ $this->plainMcpToken }}</pre>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                {{ __('filament-ai::ai.mcp.tokens_heading') }}
            </x-slot>

            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label class="mb-1 block text-sm font-medium">{{ __('filament-ai::ai.mcp.token_label') }}</label>
                    <input
                        type="text"
                        wire:model="newMcpTokenLabel"
                        placeholder="{{ __('filament-ai::ai.mcp.token_label_placeholder') }}"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-gray-600 dark:bg-gray-900"
                    />
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-filament::button wire:click="createMcpToken" color="primary">
                        {{ __('filament-ai::ai.mcp.token_create') }}
                    </x-filament::button>
                    <x-filament::button
                        wire:click="createMcpWriteToken"
                        wire:confirm="{{ __('filament-ai::ai.mcp.token_write_confirm') }}"
                        color="danger"
                    >
                        {{ __('filament-ai::ai.mcp.token_create_write') }}
                    </x-filament::button>
                </div>
            </div>
            <p class="mb-4 text-sm text-amber-700 dark:text-amber-300">
                {{ __('filament-ai::ai.mcp.token_write_help') }}
            </p>

            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2">{{ __('filament-ai::ai.mcp.token_label') }}</th>
                            <th class="px-3 py-2">{{ __('filament-ai::ai.mcp.token_created_by') }}</th>
                            <th class="px-3 py-2">{{ __('filament-ai::ai.mcp.token_scope') }}</th>
                            <th class="px-3 py-2">{{ __('filament-ai::ai.mcp.token_active_to') }}</th>
                            <th class="px-3 py-2">{{ __('filament-ai::ai.mcp.token_status') }}</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->mcpTokens as $token)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2">{{ $token->label }}</td>
                                <td class="px-3 py-2">{{ $token->creatorDisplayName() }}</td>
                                <td class="px-3 py-2">{{ $token->scope }}</td>
                                <td class="px-3 py-2">{{ $token->active_to?->toDateString() }}</td>
                                <td class="px-3 py-2">
                                    @if ($token->revoked_at)
                                        {{ __('filament-ai::ai.mcp.token_revoked') }}
                                    @elseif (! $token->isCurrentlyValid())
                                        {{ __('filament-ai::ai.mcp.token_expired') }}
                                    @else
                                        {{ __('filament-ai::ai.mcp.token_active') }}
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    @if ($token->revoked_at === null)
                                        <x-filament::button
                                            wire:click="revokeMcpToken({{ $token->id }})"
                                            color="gray"
                                            size="sm"
                                        >
                                            {{ __('filament-ai::ai.mcp.token_revoke') }}
                                        </x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-gray-500">
                                    {{ __('filament-ai::ai.mcp.tokens_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</div>
