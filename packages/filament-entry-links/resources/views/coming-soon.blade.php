<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-entry-links::public.coming_soon_title') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #0f172a; }
        main { max-width: 28rem; padding: 2rem; text-align: center; }
        h1 { font-size: 1.25rem; margin-bottom: 0.75rem; }
        p { color: #475569; margin-bottom: 1.5rem; line-height: 1.5; }
        time { font-weight: 600; color: #0f172a; }
        a { display: inline-block; margin-top: 0.5rem; padding: 0.5rem 1rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 0.375rem; }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
<main>
    <h1>{{ __('filament-entry-links::public.coming_soon_heading') }}</h1>
    <p>
        {{ __('filament-entry-links::public.coming_soon_body') }}
        <time datetime="{{ $activeFrom->toIso8601String() }}">{{ $activeFrom->timezone(config('app.timezone'))->format(__('filament-entry-links::public.datetime_format')) }}</time>
    </p>
    <a href="{{ $homeUrl }}">{{ __('filament-entry-links::public.home_button') }}</a>
</main>
</body>
</html>
