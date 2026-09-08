import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import { existsSync, copyFileSync } from 'node:fs';
import { resolve } from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            // Registrasi manual lewat `virtual:pwa-register` di resources/js/app.js,
            // karena Laravel/Inertia memakai Blade (bukan index.html) sehingga
            // auto-inject script dari plugin ini tidak akan pernah jalan.
            injectRegister: null,
            // Laravel Vite plugin mengubah `base` Vite jadi '/build/', sehingga
            // secara default plugin ini akan menaruh sw.js & manifest.webmanifest
            // di public/build/ dengan scope '/build/' (tidak bisa meng-cache halaman
            // di luar folder itu). Override agar file dihasilkan di public/ (root)
            // dan scope-nya '/' sehingga bisa mengontrol seluruh aplikasi.
            outDir: 'public',
            base: '/',
            buildBase: '/',
            scope: '/',
            includeAssets: ['favicon.ico', 'robots.txt'],
            manifest: {
                name: 'Pendukung PPID',
                short_name: 'PPID',
                description: 'Aplikasi Pendukung PPID',
                theme_color: '#1e40af',
                background_color: '#ffffff',
                display: 'standalone',
                icons: [
                    { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
                // Jangan precache junction 'storage' (symlink ke storage/app/public,
                // berisi file upload user yang dinamis & bisa besar/berkembang terus).
                globIgnores: ['storage/**'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-cache',
                            expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: /\/api\/.*/i,
                        handler: 'NetworkFirst',
                        options: { cacheName: 'api-cache', expiration: { maxEntries: 50, maxAgeSeconds: 300 } },
                    },
                ],
            },
        }),
        // vite-plugin-pwa selalu menulis manifest.webmanifest lewat mekanisme
        // emitFile Rollup, yang mengikuti build.outDir Laravel (public/build/),
        // walau opsi `outDir` PWA di atas sudah diarahkan ke public/. Akibatnya
        // sw.js mem-precache '/manifest.webmanifest' (mengikuti scope '/'),
        // padahal file fisiknya ada di '/build/manifest.webmanifest' -> 404 saat
        // instalasi SW. Salin filenya ke public/ root supaya kedua lokasi valid.
        {
            name: 'copy-manifest-to-public-root',
            apply: 'build',
            closeBundle() {
                const src = resolve(process.cwd(), 'public/build/manifest.webmanifest');
                const dest = resolve(process.cwd(), 'public/manifest.webmanifest');
                if (existsSync(src)) {
                    copyFileSync(src, dest);
                }
            },
        },
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
