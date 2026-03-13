@php
    $locale = app()->getLocale();
    $content = $policy->content[$locale] ?? $policy->content[config('app.fallback_locale')] ?? reset($policy->content);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full legal-accept-page">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-legal::messages.Legal Acceptance Required') }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
        }
    </script>
    <style>
        :root {
            --legal-bg: var(--color-gray-50, #f9fafb);
            --legal-card-bg: var(--color-white, #ffffff);
            --legal-text-main: var(--color-gray-900, #111827);
            --legal-text-muted: var(--color-gray-600, #4b5563);
            --legal-border: var(--color-gray-200, #e5e7eb);
            --legal-prose-bg: var(--color-gray-50, #f9fafb);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --legal-bg: var(--color-gray-900, #111827);
                --legal-card-bg: var(--color-gray-800, #1f2937);
                --legal-text-main: var(--color-gray-50, #f9fafb);
                --legal-text-muted: var(--color-gray-400, #9ca3af);
                --legal-border: var(--color-gray-700, #374151);
                --legal-prose-bg: var(--color-gray-900, #111827);
            }
        }

        .legal-accept-page {
            background-color: var(--legal-bg);
            color: var(--legal-text-main);
        }

        .legal-card {
            background-color: var(--legal-card-bg);
            border-color: var(--legal-border);
        }

        .legal-prose-container {
            background-color: var(--legal-prose-bg);
            border-color: var(--legal-border);
        }

        /* Fit one viewport; 100svh = visible area when browser chrome is shown (avoids S26-style bugs) */
        .legal-accept-viewport {
            height: 100vh;
            height: 100svh;
        }
    </style>
</head>

<body class="min-h-full legal-accept-page overflow-hidden">
    <main class="legal-accept-viewport flex flex-col overflow-hidden p-4 sm:p-6 lg:p-8 sm:max-w-4xl sm:mx-auto gap-6">
        <header class="text-center flex-none">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight" style="color: var(--legal-text-main)">
                {{ __('filament-legal::messages.Please review and accept our updated Privacy Policy') }}
            </h1>
            <p class="mt-2 text-sm" style="color: var(--legal-text-muted)">
                {{ __('filament-legal::messages.Version') }}: {{ $policy->version }}
                ({{ $policy->published_at?->format('Y-m-d') }})
            </p>
        </header>

        <article class="legal-card legal-prose-container prose prose-sm dark:prose-invert max-w-none p-4 border rounded-lg shadow sm:rounded-lg flex-1 min-h-0 overflow-y-auto">
            {!! $content !!}
        </article>

        <form action="{{ route('filament-legal.submit-acceptance') }}" method="POST" class="flex-none">
            @csrf
            <div class="flex items-center justify-end">
                <button type="submit"
                    class="w-full sm:w-auto flex justify-center rounded-md border border-transparent py-2.5 px-6 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
                    style="background-color: var(--color-primary-600, #f59e0b); --tw-ring-color: var(--color-primary-500, #f59e0b);">
                    {{ __('filament-legal::messages.I Accept and Continue') }}
                </button>
            </div>
        </form>
    </main>
</body>

</html>