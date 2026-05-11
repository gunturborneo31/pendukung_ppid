<?php

namespace App\Http\Controllers;

use App\Models\Article;

class PreviewController extends Controller
{
    public function show(string $token)
    {
        $article = Article::where('preview_token', $token)
            ->with(['author', 'category', 'editor', 'seo', 'media'])
            ->firstOrFail();

        return view('preview', compact('article'));
    }
}
