if (typeof window.PushNotifications === 'undefined') {
    /**
     * Push Notifications Client
     * 
     * Handles browser push notification subscription and management.
     * Requires a service worker and VAPID public key.
     */
    class PushNotifications {
        constructor(options = {}) {
            this.vapidPublicKey = options.vapidPublicKey || null;
            this.serviceWorkerPath = options.serviceWorkerPath || '/sw.js';
            this.subscribeEndpoint = options.subscribeEndpoint || '/api/push-subscriptions';
            this.unsubscribeEndpoint = options.unsubscribeEndpoint || '/api/push-subscriptions';
            this.csrfToken = options.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content;
            
            this.registration = null;
            this.subscription = null;
        }

        /**
         * Check if push notifications are supported.
         */
        isSupported() {
            return 'serviceWorker' in navigator && 
                   'PushManager' in window && 
                   'Notification' in window;
        }

        /**
         * Check if running as installed PWA (for iOS).
         */
        isInstalledPwa() {
            return window.matchMedia('(display-mode: standalone)').matches ||
                   window.navigator.standalone === true;
        }

        /**
         * Check if iOS device.
         */
        isIos() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent);
        }

        /**
         * Get current permission state.
         */
        getPermission() {
            return Notification.permission;
        }

        /**
         * Check if currently subscribed.
         */
        async isSubscribed() {
            if (!this.isSupported()) return false;
            
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                return subscription !== null;
            } catch (e) {
                console.error('Error checking subscription:', e);
                return false;
            }
        }

        /**
         * Request notification permission.
         */
        async requestPermission() {
            if (!this.isSupported()) {
                throw new Error('Push notifications are not supported');
            }

            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        /**
         * Subscribe to push notifications.
         */
        async subscribe() {
            if (!this.vapidPublicKey) {
                throw new Error('VAPID public key is required');
            }

            if (!this.isSupported()) {
                throw new Error('Push notifications are not supported');
            }

            // Request permission first
            const hasPermission = await this.requestPermission();
            if (!hasPermission) {
                throw new Error('Permission denied');
            }

            // Get service worker registration
            console.log('[PushNotifications] Waiting for Service Worker to be ready...');
            this.registration = await navigator.serviceWorker.ready;
            console.log('[PushNotifications] Service Worker ready.');

            // Subscribe to push
            const applicationServerKey = this.urlBase64ToUint8Array(this.vapidPublicKey);
            
            console.log('[PushNotifications] Subscribing to push manager...');
            this.subscription = await this.registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey,
            });
            console.log('[PushNotifications] Subscription successful:', this.subscription);

            // Send subscription to server
            console.log('[PushNotifications] Sending subscription to server...');
            await this.sendSubscriptionToServer(this.subscription);
            console.log('[PushNotifications] Server registration successful.');

            return this.subscription;
        }

        /**
         * Unsubscribe from push notifications.
         */
        async unsubscribe() {
            if (!this.isSupported()) return false;

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    // Remove from server first
                    await this.removeSubscriptionFromServer(subscription);
                    
                    // Then unsubscribe locally
                    await subscription.unsubscribe();
                }

                this.subscription = null;
                return true;
            } catch (e) {
                console.error('Error unsubscribing:', e);
                return false;
            }
        }

        /**
         * Send subscription to server.
         */
        async sendSubscriptionToServer(subscription) {
            const response = await fetch(this.subscribeEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(subscription.toJSON()),
            });

            if (!response.ok) {
                throw new Error('Failed to save subscription');
            }

            return response.json();
        }

        /**
         * Remove subscription from server.
         */
        async removeSubscriptionFromServer(subscription) {
            const response = await fetch(this.unsubscribeEndpoint, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ endpoint: subscription.endpoint }),
            });

            return response.ok;
        }

        /**
         * Convert VAPID key from base64 to Uint8Array.
         */
        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }

            return outputArray;
        }

        /**
         * Get status object for UI display.
         */
        async getStatus() {
            const supported = this.isSupported();
            const permission = supported ? this.getPermission() : 'unsupported';
            const subscribed = await this.isSubscribed();
            const isIos = this.isIos();
            const isPwa = this.isInstalledPwa();

            return {
                supported,
                permission,
                subscribed,
                isIos,
                isPwa,
                // iOS requires PWA installation for push
                iosRequiresPwa: isIos && !isPwa,
            };
        }
    }

    // Export for module systems
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = PushNotifications;
    }

    // Make available globally
    window.PushNotifications = PushNotifications;
}
