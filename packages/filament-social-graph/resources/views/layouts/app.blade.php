<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }} - {{ __('filament-social-graph::feed.title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
