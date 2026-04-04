<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('filament-entry-links::public.unavailable_title') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f8fafc; color: #0f172a; }
        main { max-width: 28rem; padding: 2rem; text-align: center; }
        h1 { font-size: 1.25rem; margin-bottom: 0.75rem; }
        p { color: #475569; margin-bottom: 1.5rem; line-height: 1.5; }
        a { display: inline-block; padding: 0.5rem 1rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 0.375rem; }
        a:hover { background: #1d4ed8; }
    </style>
</head>
<body>
<main>
    <h1>{{ __('filament-entry-links::public.unavailable_heading') }}</h1>
    <p>{{ __('filament-entry-links::public.unavailable_body') }}</p>
    <a href="{{ $homeUrl }}">{{ __('filament-entry-links::public.home_button') }}</a>
</main>
</body>
</html>
