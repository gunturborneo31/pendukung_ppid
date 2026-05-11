<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SitemapController;
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
        Route::get('/editor/contributors', [\App\Http\Controllers\UserController::class, 'index'])->name('editor.contributors');
        Route::get('/editor/contributors/create', [\App\Http\Controllers\UserController::class, 'create'])->name('editor.contributors.create');
        Route::post('/editor/contributors', [\App\Http\Controllers\UserController::class, 'store'])->name('editor.contributors.store');
        Route::get('/editor/contributors/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->name('editor.contributors.edit');
        Route::put('/editor/contributors/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('editor.contributors.update');
        Route::delete('/editor/contributors/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('editor.contributors.destroy');
    });

    // Rekap (editor + leader)
    Route::middleware(['role:contributor,editor,leader'])->group(function () {
        Route::get('/news/approved', [DashboardController::class, 'approvedNews'])->name('news.approved');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export/excel', [RekapController::class, 'exportExcel'])->name('rekap.excel');
        Route::get('/rekap/export/pdf', [RekapController::class, 'exportPdf'])->name('rekap.pdf');
    });

    // Media upload
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});

