import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { registerSW } from 'virtual:pwa-register';

// Registrasi service worker Workbox (precache aset + cache API) agar aplikasi
// benar-benar bisa dipakai offline saat diinstal sebagai PWA. Tanpa panggilan
// ini, `vite-plugin-pwa` hanya menghasilkan file sw.js tanpa pernah mendaftarkannya,
// karena project ini pakai Blade/Inertia (bukan index.html) sehingga auto-inject
// registrasi bawaan plugin tidak berjalan.
if ('serviceWorker' in navigator) {
    registerSW({ immediate: true });
}

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
