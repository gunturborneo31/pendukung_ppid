<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Article;

// API route untuk mengambil daftar editor beserta bidangnya
Route::get('/editors', function (Request $request) {
    return User::where('role', 'editor')
        ->select('id', 'name', 'field')
        ->get();
});

// API untuk mengambil artikel terakhir milik user (untuk upload file pendukung setelah submit)
Route::get('/my-latest-article', function (Request $request) {
    $article = Article::where('author_id', $request->user()->id)->latest()->first();
    return $article ? $article->only(['id', 'title']) : response()->json(['error' => 'Not found'], 404);
});
