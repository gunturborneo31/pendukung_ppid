<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Preview (public)
Route::get('/preview/{token}', [PreviewController::class, 'show'])->name('preview');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contributor
    Route::middleware(['role:contributor'])->group(function () {
        Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
        Route::post('/articles/{article}/submit', [ArticleController::class, 'submit'])->name('articles.submit');
    });

    // Editor
    Route::middleware(['role:editor'])->group(function () {
        Route::get('/editor/inbox', [EditorController::class, 'inbox'])->name('editor.inbox');
        Route::get('/editor/approved', [EditorController::class, 'approved'])->name('editor.approved');
        Route::get('/editor/articles/{article}', [EditorController::class, 'show'])->name('editor.show');
        Route::get('/editor/articles/{article}/edit-full', [ArticleController::class, 'editForEditor'])->name('editor.articles.editFull');
        Route::put('/editor/articles/{article}', [EditorController::class, 'update'])->name('editor.update');
        Route::post('/editor/articles/{article}/approve', [EditorController::class, 'approve'])->name('editor.approve');
        Route::post('/editor/articles/{article}/return', [EditorController::class, 'returnArticle'])->name('editor.return');

        // Manajemen kontributor
        Route::get('/editor/contributors', [UserController::class, 'index'])->name('editor.contributors');
        Route::get('/editor/contributors/create', [UserController::class, 'create'])->name('editor.contributors.create');
        Route::post('/editor/contributors', [UserController::class, 'store'])->name('editor.contributors.store');
        Route::get('/editor/contributors/{user}/edit', [UserController::class, 'edit'])->name('editor.contributors.edit');
        Route::put('/editor/contributors/{user}', [UserController::class, 'update'])->name('editor.contributors.update');
        Route::delete('/editor/contributors/{user}', [UserController::class, 'destroy'])->name('editor.contributors.destroy');
    });

    // Rekap (editor + leader)
    Route::middleware(['role:contributor,editor,leader'])->group(function () {
        Route::get('/news/approved', [DashboardController::class, 'approvedNews'])->name('news.approved');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export/excel', [RekapController::class, 'exportExcel'])->name('rekap.excel');
        Route::get('/rekap/export/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
    });

    // Dashboard status seluruh OPD (editor & leader, akses lintas OPD)
    Route::middleware(['role:editor,leader'])->group(function () {
        Route::get('/dashboard/opd', [DashboardController::class, 'opdOverview'])->name('dashboard.opd');
    });

    // Superadmin: kelola daftar OPD serta akun editor & leader
    Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/opds', [OpdController::class, 'index'])->name('opds.index');
        Route::get('/opds/create', [OpdController::class, 'create'])->name('opds.create');
        Route::post('/opds', [OpdController::class, 'store'])->name('opds.store');
        Route::get('/opds/{opd}/edit', [OpdController::class, 'edit'])->name('opds.edit');
        Route::put('/opds/{opd}', [OpdController::class, 'update'])->name('opds.update');
        Route::delete('/opds/{opd}', [OpdController::class, 'destroy'])->name('opds.destroy');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Superadmin juga dapat mengelola akun kontributor.
        Route::get('/contributors', [UserController::class, 'index'])->name('contributors');
        Route::get('/contributors/create', [UserController::class, 'create'])->name('contributors.create');
        Route::post('/contributors', [UserController::class, 'store'])->name('contributors.store');
        Route::get('/contributors/{user}/edit', [UserController::class, 'edit'])->name('contributors.edit');
        Route::put('/contributors/{user}', [UserController::class, 'update'])->name('contributors.update');
        Route::delete('/contributors/{user}', [UserController::class, 'destroy'])->name('contributors.destroy');
    });

    // Media upload
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    // FCM token (push notification) - dipanggil frontend setelah Firebase Messaging aktif
    Route::post('/fcm-token', [FcmTokenController::class, 'store'])->name('fcm-token.store');
    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy'])->name('fcm-token.destroy');
});

Route::get('/firebase-service-worker.js', function () {
    $firebaseConfig = [
        'apiKey' => env('VITE_FIREBASE_API_KEY', env('FIREBASE_API_KEY', '')),
        'authDomain' => env('VITE_FIREBASE_AUTH_DOMAIN', env('FIREBASE_AUTH_DOMAIN', '')),
        'projectId' => env('VITE_FIREBASE_PROJECT_ID', env('FIREBASE_PROJECT_ID', '')),
        'storageBucket' => env('VITE_FIREBASE_STORAGE_BUCKET', env('FIREBASE_STORAGE_BUCKET', '')),
        'messagingSenderId' => env('VITE_FIREBASE_MESSAGING_SENDER_ID', env('FIREBASE_MESSAGING_SENDER_ID', '')),
        'appId' => env('VITE_FIREBASE_APP_ID', env('FIREBASE_APP_ID', '')),
    ];

    return response()->view('firebase-messaging-sw', ['firebaseConfig' => $firebaseConfig])
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('firebase.service-worker');
