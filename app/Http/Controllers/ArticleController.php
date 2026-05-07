<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('author_id', auth()->id())
            ->with(['category', 'editor'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Articles/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(ArticleRequest $request)
    {
        $data = $request->validated();
        $seoData = $data['seo'] ?? [];
        unset($data['seo']);

        $data['author_id'] = auth()->id();
        $data['status'] = 'draft';

        if (isset($data['thumbnail']) && $request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $article = Article::create($data);

        if (!empty($seoData)) {
            $article->seo()->create($seoData);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'created',
            'notes' => 'Artikel dibuat',
        ]);

        return redirect()->route('articles.index')
            ->with('message', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article)
    {
        $this->authorizeArticle($article);

        return Inertia::render('Articles/Edit', [
            'article' => $article->load(['seo', 'media', 'category']),
            'categories' => Category::all(),
        ]);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $this->authorizeArticle($article);

        $data = $request->validated();
        $seoData = $data['seo'] ?? [];
        unset($data['seo']);

        if (isset($data['thumbnail']) && $request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $article->update($data);

        if (!empty($seoData)) {
            $article->seo()->updateOrCreate(['article_id' => $article->id], $seoData);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'updated',
            'notes' => 'Artikel diperbarui',
        ]);

        return redirect()->route('articles.edit', $article)
            ->with('message', 'Artikel berhasil disimpan.');
    }

    public function submit(Article $article)
    {
        $this->authorizeArticle($article);

        if (!in_array($article->status, ['draft', 'returned'])) {
            return back()->with('error', 'Artikel tidak dapat disubmit.');
        }

        $article->update(['status' => 'submitted']);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'submitted',
            'notes' => 'Artikel disubmit untuk review',
        ]);

        return redirect()->route('articles.index')
            ->with('message', 'Artikel berhasil disubmit.');
    }

    private function authorizeArticle(Article $article): void
    {
        if ($article->author_id !== auth()->id()) {
            abort(403);
        }
    }
}
