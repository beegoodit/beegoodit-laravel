@php
    $progress = max(0, min(100, (float) $getState()));
@endphp

<div
    class="h-2 w-full min-w-24 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-valuenow="{{ (int) round($progress) }}"
>
    <div
        class="h-full rounded-full bg-primary-600 transition-all dark:bg-primary-500"
        style="width: {{ $progress }}%"
    ></div>
</div>
