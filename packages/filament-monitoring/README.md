# Filament Monitoring Patterns

This directory serves as a **Reference Implementation** for integrating observability, error tracking, and analytics into Filament applications. These patterns are used in the `foosbeaver` application.

## Core Monitoring Trio

### 1. Observability: SigNoz (OpenTelemetry)

We use **OpenTelemetry** for vendor-neutral tracing and metrics.

#### Prerequisites
- A SigNoz instance with an OTLP collector reachable at `:4318`.

#### Configuration (`.env`)
```env
OTEL_SERVICE_NAME=your-app-name
OTEL_RESOURCE_ATTRIBUTES="deployment.environment=${APP_ENV}-${USER}"
OTEL_EXPORTER_OTLP_ENDPOINT=http://your-signoz-collector:4318
OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf
OTEL_PHP_AUTOLOAD_ENABLED=true
```

---

### 2. Error Tracking: GlitchTip (Sentry SDK)

GlitchTip is a Sentry-compatible open-source error tracking platform.

#### Registration (`bootstrap/app.php`)
```php
->withExceptions(function (Exceptions $exceptions) {
    \Sentry\Laravel\Integration::handles($exceptions);
})
```

#### Verification
```bash
php artisan sentry:test
```

---

### 3. Privacy-Focused Analytics: Umami

#### Configuration (`config/services.php`)
```php
'umami' => [
    'url' => env('UMAMI_URL'),
    'website_id' => env('UMAMI_WEBSITE_ID'),
],
```

#### Public Pages (`resources/views/partials/head.blade.php`)
```blade
@if(config('services.umami.url') && config('services.umami.website_id'))
    <script async src="{{ config('services.umami.url') }}/script.js" data-website-id="{{ config('services.umami.website_id') }}"></script>
@endif
```

#### Filament Panels (`app/Providers/Filament/*PanelProvider.php`)
```php
->renderHook(
    \Filament\View\PanelsRenderHook::HEAD_END,
    fn (): string => config('services.umami.url') && config('services.umami.website_id')
        ? '<script async src="'.config('services.umami.url').'/script.js" data-website-id="'.config('services.umami.website_id').'"></script>'
        : '',
)
```

## Testing Patterns

Verify your integrations using Pest tests:

```php
test('umami script is injected into public pages', function () {
    Config::set('services.umami.url', 'https://analytics.example.com');
    Config::set('services.umami.website_id', 'testing-123');

    $response = $this->get(route('home'));
    
    $response->assertStatus(200);
    $response->assertSee('https://analytics.example.com/script.js');
    $response->assertSee('data-website-id="testing-123"', false);
});
```

## Best Practices
- **Environment Isolation**: Always use `${APP_ENV}-${USER}` in development to avoid pollution in dashboards.
- **Privacy First**: Umami is used for non-invasive analytics (no cookies).
- **Graceful Failure**: All injections are wrapped in `config()` checks.
