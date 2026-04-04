@extends(config('filament-entry-links.public_layout'))

@section('title', __('filament-entry-links::public.coming_soon_title'))

@section('content')
    <div class="container mx-auto px-4 py-16 sm:py-24 max-w-lg text-center">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">{{ __('filament-entry-links::public.coming_soon_heading') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
            {{ __('filament-entry-links::public.coming_soon_body') }}
            <time datetime="{{ $activeFrom->toIso8601String() }}"
                class="font-semibold text-gray-900 dark:text-white">{{ $activeFrom->timezone(config('app.timezone'))->format(__('filament-entry-links::public.datetime_format')) }}</time>
        </p>
        <a href="{{ $homeUrl }}"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-colors shadow-md hover:shadow-lg">{{ __('filament-entry-links::public.home_button') }}</a>
    </div>
@endsection
