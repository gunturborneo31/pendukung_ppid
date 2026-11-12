<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Larang browser menyimpan halaman HTML (shell Blade/Inertia) di cache.
 *
 * Tanpa ini, saat tab/PWA dibuka kembali (session restore, back/forward,
 * aplikasi di HP di-resume) browser menampilkan HTML lama dari cache-nya
 * sendiri walau header-nya `no-cache`. HTML lama itu mereferensikan aset
 * build lama (app-XXXX.js) yang sudah terhapus oleh `npm run build`,
 * sehingga Vue gagal dimuat: layar kosong, hanya tombol
 * "Aktifkan Notifikasi" (yang dirender langsung oleh Blade) yang terlihat.
 */
class PreventHtmlCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (str_contains($contentType, 'text/html') || $request->header('X-Inertia')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
