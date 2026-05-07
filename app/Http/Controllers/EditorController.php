<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EditorController extends Controller
{
    public function inbox()
    {
        $articles = Article::whereIn('status', ['submitted', 'returned'])
            ->with(['author', 'category'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Editor/Inbox', [
            'articles' => $articles,
        ]);
    }

    public function show(Article $article)
    {
        return Inertia::render('Editor/Show', [
            'article' => $article->load(['author', 'category', 'seo', 'media', 'activityLogs.user']),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body_web' => 'nullable|array',
            'excerpt' => 'nullable|string|max:500',
            'caption_ig' => 'nullable|string|max:2200',
            'hashtags_ig' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $article->update(array_merge($data, ['editor_id' => auth()->id()]));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'editor_updated',
            'notes' => 'Artikel diedit oleh editor',
        ]);

        return redirect()->route('editor.show', $article)
            ->with('message', 'Artikel berhasil diperbarui.');
    }

    public function approve(Request $request, Article $article)
    {
        $article->update([
            'status' => 'published',
            'editor_id' => auth()->id(),
            'published_at' => now(),
            'editor_notes' => null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'approved',
            'notes' => 'Artikel disetujui dan dipublikasikan',
        ]);

        return redirect()->route('editor.inbox')
            ->with('message', 'Artikel berhasil dipublikasikan.');
    }

    public function returnArticle(Request $request, Article $article)
    {
        $request->validate([
            'editor_notes' => 'required|string|max:1000',
        ]);

        $article->update([
            'status' => 'returned',
            'editor_id' => auth()->id(),
            'editor_notes' => $request->editor_notes,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'returned',
            'notes' => $request->editor_notes,
        ]);

        return redirect()->route('editor.inbox')
            ->with('message', 'Artikel dikembalikan ke kontributor.');
    }
}
