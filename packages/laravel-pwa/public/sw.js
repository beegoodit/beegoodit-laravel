const CACHE_NAME = 'app-v1';
const urlsToCache = [
  '/build/assets/app.css',
  '/build/assets/app.js',
];

const STATIC_ASSET_EXT = /\.(js|css|png|jpg|jpeg|svg|gif|ico|woff|woff2|ttf|eot)(\?.*)?$/i;

function isStaticAsset(request) {
  if (request.method !== 'GET') {
    return false;
  }
  try {
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
      return false;
    }
    const path = url.pathname;
    if (path === '/' || path === '/favicon.ico') {
      return false;
    }
    return path.startsWith('/build/') || STATIC_ASSET_EXT.test(path);
  } catch {
    return false;
  }
}

// Install event - cache essential resources (e.g. in dev or with hashed assets, precache may 404; SW still activates)
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[ServiceWorker] Caching app shell');
        return cache.addAll(urlsToCache.map(url => new Request(url, { cache: 'reload' })));
      })
      .catch((error) => {
        console.log('[ServiceWorker] Cache failed:', error);
        return Promise.resolve();
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[ServiceWorker] Removing old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - only handle GET same-origin static assets; everything else uses default (network)
self.addEventListener('fetch', (event) => {
  if (!isStaticAsset(event.request)) {
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          return response;
        }

        return fetch(event.request).then(
          (response) => {
            if (!response || !response.ok || (response.type !== 'basic' && response.type !== 'cors')) {
              return response;
            }

            const responseToCache = response.clone();

            // Cache static assets only
            if (event.request.url.match(/\.(js|css|png|jpg|jpeg|svg|gif|ico|woff|woff2|ttf|eot)$/i)) {
              caches.open(CACHE_NAME)
                .then((cache) => {
                  cache.put(event.request, responseToCache);
                });
            }

            return response;
          }
        );
      })
      .catch(() => {
        console.log('[ServiceWorker] Fetch failed');
        return fetch(event.request).catch(
          () => new Response('', { status: 503, statusText: 'Service Unavailable' })
        );
      })
  );
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
  console.log('[ServiceWorker] Push received');

  let data = {
    title: 'Notification',
    body: '',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
  };

  if (event.data) {
    try {
      const payload = event.data.json();
      data = { ...data, ...payload };
    } catch (e) {
      // If not JSON, use as body text
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon,
    badge: data.badge,
    image: data.image,
    tag: data.tag,
    data: data.data || {},
    actions: data.actions || [],
    requireInteraction: data.requireInteraction || false,
    renotify: data.renotify || false,
    silent: data.silent || false,
  };

  // Store URL in data for click handling
  if (data.data?.url) {
    options.data.url = data.data.url;
  }

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Notification click event - handle user interaction
self.addEventListener('notificationclick', (event) => {
  console.log('[ServiceWorker] Notification clicked');

  event.notification.close();

  const action = event.action;
  const notificationData = event.notification.data || {};

  // Determine URL to open
  let urlToOpen = notificationData.url || '/';

  // Handle action buttons if defined
  if (action && notificationData.actions) {
    const actionData = notificationData.actions.find(a => a.action === action);
    if (actionData?.url) {
      urlToOpen = actionData.url;
    }
  }

  const trackingPromise = notificationData.message_id
    ? fetch(`/api/pwa/notifications/${notificationData.message_id}/open`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).catch(err => console.warn('[ServiceWorker] Tracking failed:', err))
    : Promise.resolve();

  event.waitUntil(
    Promise.all([
      trackingPromise,
      clients.matchAll({ type: 'window', includeUncontrolled: true })
        .then((clientList) => {
          // Try to focus an existing window
          for (const client of clientList) {
            if (client.url === urlToOpen && 'focus' in client) {
              return client.focus();
            }
          }
          // Open new window if none found
          if (clients.openWindow) {
            return clients.openWindow(urlToOpen);
          }
        })
    ])
  );
});

// Notification close event - optional tracking
self.addEventListener('notificationclose', (event) => {
  console.log('[ServiceWorker] Notification closed');
});
