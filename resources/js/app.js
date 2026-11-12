import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { registerSW } from 'virtual:pwa-register';

// Registrasi service worker hanya untuk environment produksi non-lokal.
// Di domain lokal (.test/localhost), SW sering mengunci browser ke cache lama
// sehingga aplikasi terlihat "harus npm run dev" padahal aset build sudah ada.
const isLocalHost = ['localhost', '127.0.0.1'].includes(window.location.hostname) || window.location.hostname.endsWith('.test');
if (import.meta.env.PROD && !isLocalHost && 'serviceWorker' in navigator) {
    registerSW({ immediate: true });
}

// Jika chunk halaman gagal dimuat (biasanya karena aset build lama sudah
// dihapus setelah deploy baru), muat ulang sekali agar mengambil HTML & aset
// terbaru, alih-alih menampilkan layar kosong.
window.addEventListener('vite:preloadError', (event) => {
    event.preventDefault();
    const key = 'ppid-preload-reload-at';
    let last = 0;
    try { last = Number(sessionStorage.getItem(key) || 0); } catch {}
    if (Date.now() - last > 10000) {
        try { sessionStorage.setItem(key, String(Date.now())); } catch {}
        window.location.reload();
    }
});

// Halaman dipulihkan dari bfcache (mis. aplikasi di HP di-resume): segarkan
// agar data & sesi sesuai kondisi server saat ini.
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => `${title} - Pendukung PPID `,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#1e40af',
    },
});
