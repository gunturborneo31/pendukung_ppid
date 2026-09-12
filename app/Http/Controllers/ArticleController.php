<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Jobs\AnalyzeArticleSeoJob;
use App\Models\Article;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Media;
use App\Models\Opd;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function __construct(protected FirebaseNotificationService $notifier)
    {
    }

    public function index()
    {
        $selectColumns = [
            'id',
            'title',
            'status',
            'author_id',
            'editor_id',
            'category_id',
            'opd_id',
            'editor_notes',
            'preview_token',
            'published_at',
            'created_at',
        ];

        if (Article::hasUploadProofsColumn()) {
            $selectColumns[] = 'upload_proofs';
        }

        $articles = Article::where('author_id', auth()->id())
            ->select($selectColumns)
            ->with([
                'author:id,name,field',
                'category:id,name',
                'editor:id,name,field',
                'opd:id,name',
            ])
            ->latest()
            ->paginate(15);

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        return Inertia::render('Articles/Create', [
            'categories' => Category::all(),
            'opds' => $this->contributorAccessibleOpds($user),
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
        $this->enforceContributorOpdAccess($data);
        if (!Article::hasUploadProofsColumn()) {
            unset($data['upload_proofs']);
        }
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
        // Dipindahkan ke job antrean supaya panggilan HTTP eksternal tidak memblokir
        // request penyimpanan artikel oleh kontributor.
        AnalyzeArticleSeoJob::dispatch($article->id);
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
            'opds' => $this->contributorAccessibleOpds(auth()->user()),
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
            'opds' => [],
            'isEditor' => true,
            'updateUrl' => route('editor.update', $article),
        ]);
    }

    public function uploadProofs(Article $article)
    {
        $this->authorizeUploadProofsView($article);

        $user = auth()->user();
        $canEdit = $user->role === 'uploader';

        return Inertia::render('Articles/UploadProofs', [
            'article' => $article->load(['author', 'category', 'editor']),
            'updateUrl' => route('upload-proofs.update', $article),
            'backUrl' => $user->role === 'editor'
                ? route('editor.show', $article)
                : ($user->role === 'contributor'
                    ? route('articles.edit', $article)
                    : ($user->role === 'superadmin' ? route('dashboard') : route('news.approved'))),
            'canEdit' => $canEdit,
        ]);
    }

    public function updateUploadProofs(Request $request, Article $article)
    {
        $this->authorizeUploadProofsEdit($article);

        if (!Article::hasUploadProofsColumn()) {
            return back()->with('error', 'Kolom upload_proofs belum tersedia. Jalankan migrasi terbaru.');
        }

        $validated = $request->validate([
            'upload_proofs' => 'nullable|array',
            'upload_proofs.instagram' => 'nullable|string|max:255',
            'upload_proofs.facebook' => 'nullable|string|max:255',
            'upload_proofs.youtube' => 'nullable|string|max:255',
            'upload_proofs.x' => 'nullable|string|max:255',
            'upload_proofs.tiktok' => 'nullable|string|max:255',
            'upload_proofs.website' => 'nullable|string|max:255',
        ]);

        $article->update([
            'upload_proofs' => $validated['upload_proofs'] ?? [],
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'article_id' => $article->id,
            'action' => 'upload_proofs_updated',
            'notes' => 'Bukti tayang upload diperbarui',
        ]);

        return redirect()->route('upload-proofs.show', $article)
            ->with('message', 'Bukti tayang upload berhasil disimpan.');
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
        $this->enforceContributorOpdAccess($data);
        if (!Article::hasUploadProofsColumn()) {
            unset($data['upload_proofs']);
        }

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

        // Beri tahu semua editor bahwa ada artikel baru yang perlu diverifikasi.
        $this->notifier->sendToUsers(
            User::where('role', 'editor')->get(),
            'Artikel Baru Perlu Diverifikasi',
            "{$article->title} dikirim oleh {$article->author->name} dan menunggu review.",
            ['type' => 'verification', 'article_id' => (string) $article->id]
        );

        return redirect()->route('articles.index')
            ->with('message', 'Artikel berhasil disubmit.');
    }

    private function authorizeArticle(Article $article): void
    {
        if ($article->author_id !== auth()->id()) {
            abort(403);
        }
    }

    private function authorizeUploadProofsView(Article $article): void
    {
        $user = auth()->user();

        if (in_array($user->role, ['editor', 'leader', 'superadmin', 'uploader'], true)) {
            return;
        }

        $this->authorizeArticle($article);
    }

    private function authorizeUploadProofsEdit(Article $article): void
    {
        $this->authorizeUploadProofsView($article);

        if (auth()->user()->role !== 'uploader') {
            abort(403, 'Hanya uploader yang dapat mengubah bukti tayang.');
        }
    }

    private function contributorAccessibleOpds(User $user)
    {
        if ($user->role !== 'contributor') {
            return [];
        }

        $opdIds = $user->accessibleOpdIds();
        if (empty($opdIds)) {
            return [];
        }

        return Opd::whereIn('id', $opdIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function enforceContributorOpdAccess(array &$data): void
    {
        $user = auth()->user();
        if ($user->role !== 'contributor') {
            return;
        }

        $allowedOpdIds = $user->accessibleOpdIds();
        if (empty($allowedOpdIds)) {
            throw ValidationException::withMessages([
                'opd_id' => 'Kontributor belum memiliki akses OPD. Hubungi admin.',
            ]);
        }

        if (empty($data['opd_id'])) {
            $data['opd_id'] = $allowedOpdIds[0];

            return;
        }

        if (!in_array((int) $data['opd_id'], $allowedOpdIds, true)) {
            throw ValidationException::withMessages([
                'opd_id' => 'OPD yang dipilih tidak termasuk akses kontributor.',
            ]);
        }
    }
}
