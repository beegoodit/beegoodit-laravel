@php
    use Illuminate\Support\Carbon;

    $createdAt = filled($conversationCreatedAt ?? null)
        ? Carbon::parse($conversationCreatedAt)->timezone(config('app.timezone'))
        : null;
    $updatedAt = filled($conversationUpdatedAt ?? null)
        ? Carbon::parse($conversationUpdatedAt)->timezone(config('app.timezone'))
        : null;
    $attachments = $this->conversationAttachments();
@endphp

<div class="filament-ai-assistant-sidebar-panel space-y-6">
    <section class="space-y-3">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ __('filament-ai::ai.chat.sidebar.meta') }}
        </h3>
        <dl class="space-y-3 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('filament-ai::ai.chat.sidebar.created_at') }}
                </dt>
                <dd
                    class="mt-0.5 text-gray-950 dark:text-gray-100"
                    @if ($createdAt) title="{{ $createdAt->toDateTimeString() }}" @endif
                >
                    {{ $createdAt?->diffForHumans() ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('filament-ai::ai.chat.sidebar.updated_at') }}
                </dt>
                <dd
                    class="mt-0.5 text-gray-950 dark:text-gray-100"
                    @if ($updatedAt) title="{{ $updatedAt->toDateTimeString() }}" @endif
                >
                    {{ $updatedAt?->diffForHumans() ?? '—' }}
                </dd>
            </div>
        </dl>
    </section>

    <section class="space-y-3">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ __('filament-ai::ai.chat.sidebar.attachments') }}
        </h3>

        @if ($attachments === [])
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('filament-ai::ai.chat.sidebar.attachments_empty') }}
            </p>
        @else
            <ul class="space-y-2 text-sm text-gray-950 dark:text-gray-100">
                @foreach ($attachments as $attachment)
                    <li class="flex items-start gap-2 break-all">
                        <x-filament::icon
                            icon="heroicon-m-paper-clip"
                            class="mt-0.5 h-4 w-4 shrink-0 text-gray-400"
                        />
                        <span>{{ $attachment['name'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
