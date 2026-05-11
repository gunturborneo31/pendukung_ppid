<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('author_id', auth()->id())
            ->with(['author', 'category', 'editor'])
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
        $supportingDescriptions = $data['supporting_descriptions'] ?? [];
        unset($data['seo']);
        unset($data['ig_media']);
        unset($data['supporting_files']);
        unset($data['supporting_descriptions']);

        $data['author_id'] = auth()->id();
        // Only set status to draft if not provided
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        if (isset($data['thumbnail']) && $request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $article = Article::create($data);

        if (!empty($seoData)) {
            $article->seo()->create($seoData);
        }

        if ($request->hasFile('ig_media')) {
            foreach ($request->file('ig_media') as $file) {
                $mime = (string) $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('media', 'public');

                Media::create([
                    'article_id' => $article->id,
                    'uploader_id' => auth()->id(),
                    'type' => $type,
                    'path' => $path,
                    'alt_text' => 'ig_media',
                    'size' => $file->getSize(),
                    'description' => 'Media Instagram',
                ]);
            }
        }

        if ($request->hasFile('supporting_files')) {
            foreach ($request->file('supporting_files') as $index => $file) {
                $mime = (string) $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('media', 'public');

                Media::create([
                    'article_id' => $article->id,
                    'uploader_id' => auth()->id(),
                    'type' => $type,
                    'path' => $path,
                    'alt_text' => 'supporting_file',
                    'size' => $file->getSize(),
                    'description' => $supportingDescriptions[$index] ?? null,
                ]);
            }
        }

        // --- AI SEO ANALYSIS INTEGRATION ---
        try {
            $seoApiUrl = 'https://public-seo-ai.example.com/analyze'; // Replace with actual public SEO AI endpoint
            $response = \Http::post($seoApiUrl, [
                'title' => $article->title,
                'body' => is_array($article->body_web) ? implode(' ', $article->body_web) : $article->body_web,
            ]);
            if ($response->successful()) {
                $analysis = $response->json();
                $article->seo()->updateOrCreate([], [
                    'seo_title' => $analysis['seo_title'] ?? $article->title,
                    'seo_description' => $analysis['seo_description'] ?? null,
                    'seo_keywords' => $analysis['seo_keywords'] ?? null,
                    'og_title' => $analysis['og_title'] ?? null,
                    'og_description' => $analysis['og_description'] ?? null,
                    'og_image' => $analysis['og_image'] ?? null,
                    'canonical_url' => $analysis['canonical_url'] ?? null,
                    'robots_index' => $analysis['robots_index'] ?? true,
                    'robots_follow' => $analysis['robots_follow'] ?? true,
                ]);
            }
        } catch (\Exception $e) {
            // Optionally log the error or ignore
        }
        // --- END AI SEO ANALYSIS ---

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
            'article' => $article->load(['seo', 'media', 'category', 'editor']),
            'categories' => Category::all(),
            'isEditor' => false,
            'updateUrl' => route('articles.update', $article),
        ]);
    }

    public function editForEditor(Article $article)
    {
        if (auth()->user()->role !== 'editor') {
            abort(403);
        }

        return Inertia::render('Articles/Edit', [
            'article' => $article->load(['seo', 'media', 'category', 'editor']),
            'categories' => Category::all(),
            'isEditor' => true,
            'updateUrl' => route('editor.update', $article),
        ]);
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $this->authorizeArticle($article);

        $data = $request->validated();
        $seoData = $data['seo'] ?? [];
        unset($data['ig_media_delete_ids']);
        unset($data['supporting_file_delete_ids']);
        unset($data['seo']);
        unset($data['ig_media']);

        // Saat kontributor mengedit, artikel kembali ke draft agar editor tidak mengakses versi lama.
        $data['status'] = 'draft';
        $data['editor_notes'] = null;

        if (isset($data['thumbnail']) && $request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $article->update($data);

        if (!empty($seoData)) {
            $article->seo()->updateOrCreate(['article_id' => $article->id], $seoData);
        }

        if ($request->hasFile('ig_media')) {
            foreach ($request->file('ig_media') as $file) {
                $mime = (string) $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('media', 'public');

                Media::create([
                    'article_id' => $article->id,
                    'uploader_id' => auth()->id(),
                    'type' => $type,
                    'path' => $path,
                    'alt_text' => 'ig_media',
                    'size' => $file->getSize(),
                    'description' => 'Media Instagram',
                ]);
            }
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

    public function destroy(Article $article)
    {
        $this->authorizeArticle($article);

        if (!in_array($article->status, ['draft', 'returned'])) {
            return back()->with('error', 'Artikel ini tidak bisa dihapus.');
        }

        $article->delete();

        return redirect()->route('articles.index')
            ->with('message', 'Artikel berhasil dihapus.');
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
