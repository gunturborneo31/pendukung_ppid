importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: @json($firebaseConfig['apiKey'] ?? ''),
    authDomain: @json($firebaseConfig['authDomain'] ?? ''),
    projectId: @json($firebaseConfig['projectId'] ?? ''),
    storageBucket: @json($firebaseConfig['storageBucket'] ?? ''),
    messagingSenderId: @json($firebaseConfig['messagingSenderId'] ?? ''),
    appId: @json($firebaseConfig['appId'] ?? ''),
};

if (firebaseConfig.apiKey && firebaseConfig.messagingSenderId && firebaseConfig.appId) {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
        const notificationTitle = payload?.notification?.title || 'Notifikasi PPID';
        const notificationOptions = {
            body: payload?.notification?.body || '',
            icon: '/image/logo_mahulu.png',
            badge: '/image/logo_mahulu.png',
            data: {
                url: payload?.data?.url || '/dashboard',
            },
        };

        self.registration.showNotification(notificationTitle, notificationOptions);
    });
}

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const urlToOpen = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (const client of windowClients) {
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});
