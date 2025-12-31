@php
    $locale = app()->getLocale();
    $content = $policy->content[$locale] ?? $policy->content[config('app.fallback_locale')] ?? reset($policy->content);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-legal::messages.Legal Acceptance Required') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-4xl">
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                {{ __('filament-legal::messages.Please review and accept our updated Privacy Policy') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ __('filament-legal::messages.Version') }}: {{ $policy->version }}
                ({{ $policy->published_at?->format('Y-m-d') }})
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-4xl">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 space-y-6">
                <div class="prose prose-sm max-w-none overflow-y-auto max-h-[60vh] p-4 border rounded-lg bg-gray-50">
                    {!! nl2br(e($content)) !!}
                </div>

                <form action="{{ route('filament-legal.submit-acceptance') }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-end space-x-4">
                        <button type="submit"
                            class="flex justify-center rounded-md border border-transparent bg-primary-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                            style="background-color: #f59e0b;">
                            {{ __('filament-legal::messages.I Accept and Continue') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>