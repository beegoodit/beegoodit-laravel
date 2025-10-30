{{-- PWA Meta Tags --}}
<meta name="theme-color" content="{{ config('app.theme_color', '#000000') }}" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}" />
<link rel="manifest" href="/manifest.json" />

{{-- Icons --}}
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/icons/icon-180x180.png">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/icon-180x180.png">
<link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192x192.png">
<link rel="apple-touch-icon" sizes="512x512" href="/icons/icon-512x512.png">

{{-- Service Worker Registration --}}
@push('scripts')
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('✅ ServiceWorker registered:', registration.scope);
      })
      .catch((error) => {
        console.error('❌ ServiceWorker registration failed:', error);
      });
  });
}
</script>
@endpush

