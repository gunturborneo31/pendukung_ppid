importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

// Tempelkan firebaseConfig dari Step 1 di sini
const firebaseConfig = {
    apiKey: "PASTE_API_KEY_KAMU",
    authDomain: "pendukung-ppid.firebaseapp.com",
    projectId: "pendukung-ppid",
    storageBucket: "pendukung-ppid.appspot.com",
    messagingSenderId: "PASTE_SENDER_ID",
    appId: "PASTE_APP_ID"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Menangani notifikasi di background (saat app ditutup / HP di-lock)
messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title || 'Notifikasi PPID';
    const notificationOptions = {
        body: payload.notification.body || '',
        icon: '/icon-192.png',
        badge: '/icon-192.png',
        data: {
            url: payload.data?.url || '/'
        }
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Aksi klik notifikasi
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const urlToOpen = event.notification.data.url;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            for (let client of windowClients) {
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