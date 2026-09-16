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
