<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging.js";

  // Tempelkan firebaseConfig dari Step 1 di sini
  const firebaseConfig = {
      apiKey: "PASTE_API_KEY_KAMU",
      authDomain: "pendukung-ppid.firebaseapp.com",
      projectId: "pendukung-ppid",
      storageBucket: "pendukung-ppid.appspot.com",
      messagingSenderId: "PASTE_SENDER_ID",
      appId: "PASTE_APP_ID"
  };

  const app = initializeApp(firebaseConfig);
  const messaging = getMessaging(app);

  async function requestPermission() {
      try {
          const permission = await Notification.requestPermission();
          if (permission === 'granted') {
              const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
              
              // Tempelkan VAPID Key dari Step 2 di sini
              const currentToken = await getToken(messaging, { 
                  vapidKey: 'PASTE_VAPID_KEY_KAMU',
                  serviceWorkerRegistration: registration 
              });

              if (currentToken) {
                  // Kirim token ke backend Laravel
                  await fetch('/api/save-fcm-token', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                      },
                      body: JSON.stringify({ fcm_token: currentToken })
                  });
              }
          }
      } catch (error) {
          console.error('Gagal mendapatkan token FCM:', error);
      }
  }

  requestPermission();

  // Menerima pesan saat aplikasi sedang dibuka
  onMessage(messaging, (payload) => {
      alert(`${payload.notification.title}\n${payload.notification.body}`);
  });
</script>