<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e40af">
    <title inertia>{{ config('app.name', 'Pendukung PPID ') }}</title>
    <link rel="icon" type="image/png" href="/image/logo_mahulu.png">
    <link rel="apple-touch-icon" href="/image/logo_mahulu.png">
    <link rel="manifest" href="/manifest.webmanifest">
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $firebaseConfig = [
            'apiKey' => env('VITE_FIREBASE_API_KEY', env('FIREBASE_API_KEY', '')),
            'authDomain' => env('VITE_FIREBASE_AUTH_DOMAIN', env('FIREBASE_AUTH_DOMAIN', '')),
            'projectId' => env('VITE_FIREBASE_PROJECT_ID', env('FIREBASE_PROJECT_ID', '')),
            'storageBucket' => env('VITE_FIREBASE_STORAGE_BUCKET', env('FIREBASE_STORAGE_BUCKET', '')),
            'messagingSenderId' => env('VITE_FIREBASE_MESSAGING_SENDER_ID', env('FIREBASE_MESSAGING_SENDER_ID', '')),
            'appId' => env('VITE_FIREBASE_APP_ID', env('FIREBASE_APP_ID', '')),
        ];
    @endphp
    <script>
        window.__PPID_FIREBASE_CONFIG__ = @json($firebaseConfig);
        window.__PPID_FIREBASE_VAPID_KEY__ = @json(env('VITE_FIREBASE_VAPID_KEY', env('FIREBASE_VAPID_KEY', '')));
        window.__PPID_IS_AUTHENTICATED__ = @json(Auth::check());
        window.__PPID_REQUEST_PUSH_PERMISSION__ = @json((bool) session('request_push_permission', false));
    </script>
    @inertiaHead
</head>
<body class="font-sans antialiased bg-gray-50">
    @inertia

    @if (Auth::check())
        <button
            id="ppid-enable-push"
            type="button"
            class="fixed bottom-4 right-4 z-50 rounded-full bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg hover:bg-blue-700"
        >
            Aktifkan Notifikasi
        </button>
    @endif

    <script type="module">
        async function initFirebaseMessaging() {
            const firebaseConfig = window.__PPID_FIREBASE_CONFIG__ || {};
            const vapidKey = window.__PPID_FIREBASE_VAPID_KEY__ || '';
            const isAuthenticated = !!window.__PPID_IS_AUTHENTICATED__;

            if (
                !firebaseConfig.apiKey ||
                !firebaseConfig.messagingSenderId ||
                !firebaseConfig.appId ||
                !('Notification' in window) ||
                !('serviceWorker' in navigator) ||
                !isAuthenticated ||
                !vapidKey
            ) {
                return;
            }

            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js');
            const { getMessaging, getToken, onMessage } = await import('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js');

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);

            async function registerPushToken() {
                // Scope khusus (bukan '/') supaya SW Firebase ini tidak bentrok
                // dengan SW Workbox (vite-plugin-pwa) yang mengontrol scope root '/'.
                // Ini pola resmi yang direkomendasikan Firebase.
                const registration = await navigator.serviceWorker.register('/firebase-service-worker.js', {
                    scope: '/firebase-cloud-messaging-push-scope',
                });
                const token = await getToken(messaging, {
                    vapidKey,
                    serviceWorkerRegistration: registration,
                });

                if (!token) {
                    return;
                }

                await fetch('/fcm-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ fcm_token: token }),
                });
            }

            async function enablePushNotifications() {
                const permission = Notification.permission === 'granted'
                    ? 'granted'
                    : await Notification.requestPermission();

                if (permission !== 'granted') {
                    return;
                }

                await registerPushToken();
            }

            const shouldRequestPushPermission = !!window.__PPID_REQUEST_PUSH_PERMISSION__;
            const enableButton = document.getElementById('ppid-enable-push');
            if (enableButton) {
                enableButton.addEventListener('click', () => {
                    enablePushNotifications().catch((error) => {
                        console.error('Gagal mendaftarkan token FCM:', error);
                    });
                });
            }

            if (shouldRequestPushPermission && Notification.permission === 'default') {
                setTimeout(() => {
                    enablePushNotifications().catch((error) => {
                        console.error('Gagal mendaftarkan token FCM:', error);
                    });
                }, 100);
            }

            if (Notification.permission === 'granted') {
                registerPushToken().catch((error) => {
                    console.error('Gagal mendaftarkan token FCM:', error);
                });
            }

            onMessage(messaging, (payload) => {
                const title = payload?.notification?.title || 'Notifikasi PPID';
                const body = payload?.notification?.body || '';

                if (Notification.permission === 'granted') {
                    new Notification(title, { body, icon: '/image/logo_mahulu.png' });
                }
            });
        }

        initFirebaseMessaging();
    </script>
</body>
</html>
