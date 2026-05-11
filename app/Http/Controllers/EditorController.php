<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\ActivityLog;
use App\Models\Media;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EditorController extends Controller
{
    public function approved()
    {
        $articles = Article::where('editor_id', auth()->id())
            ->whereIn('status', ['approved', 'published'])
            ->with(['author', 'category', 'editor'])
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('Editor/Approved', [
            'articles' => $articles,
        ]);
    }

    public function inbox()
    {
        $articles = Article::whereIn('status', ['submitted', 'returned'])
            ->with(['author', 'category', 'editor'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Editor/Inbox', [
            'articles' => $articles,
        ]);
    }

    public function show(Article $article)
    {
        $article = $article->load(['author', 'category', 'seo', 'media', 'activityLogs.user']);
        $article->body_web_html = $this->renderBodyWebHtml($article->body_web);

        return Inertia::render('Editor/Show', [
            'article' => $article,
        ]);
    }

    private function renderBodyWebHtml($bodyWeb): string
    {
        if (empty($bodyWeb)) {
            return '';
        }

        if (is_string($bodyWeb)) {
            $decoded = json_decode($bodyWeb, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $bodyWeb = $decoded;
            } else {
                return $bodyWeb;
            }
        }

        if (!is_array($bodyWeb)) {
            return '';
        }

        return $this->renderTiptapNodes($bodyWeb['content'] ?? []);
    }

    private function renderTiptapNodes(array $nodes): string
    {
        $html = '';

        foreach ($nodes as $node) {
            $type = $node['type'] ?? 'text';
            $content = $this->renderTiptapNodes($node['content'] ?? []);
            $text = htmlspecialchars($node['text'] ?? '');

            foreach (($node['marks'] ?? []) as $mark) {
                if (($mark['type'] ?? '') === 'bold') {
                    $text = "<strong>{$text}</strong>";
                }
                if (($mark['type'] ?? '') === 'italic') {
                    $text = "<em>{$text}</em>";
                }
                if (($mark['type'] ?? '') === 'link') {
                    $href = htmlspecialchars($mark['attrs']['href'] ?? '#');
                    $text = "<a href=\"{$href}\" target=\"_blank\" rel=\"noopener noreferrer\">{$text}</a>";
                }
            }

            if ($type === 'paragraph') {
                $html .= "<p>{$content}{$text}</p>";
            } elseif ($type === 'heading') {
                $level = (int) ($node['attrs']['level'] ?? 2);
                if ($level < 1 || $level > 6) {
                    $level = 2;
                }
                $html .= "<h{$level}>{$content}{$text}</h{$level}>";
            } elseif ($type === 'bulletList') {
                $html .= "<ul>{$content}</ul>";
            } elseif ($type === 'orderedList') {
                $html .= "<ol>{$content}</ol>";
            } elseif ($type === 'listItem') {
                $html .= "<li>{$content}{$text}</li>";
            } elseif ($type === 'blockquote') {
                $html .= "<blockquote>{$content}</blockquote>";
            } elseif ($type === 'image') {
                $src = htmlspecialchars($node['attrs']['src'] ?? '');
                $alt = htmlspecialchars($node['attrs']['alt'] ?? '');
                $html .= "<img src=\"{$src}\" alt=\"{$alt}\" style=\"max-width:100%\">";
            } elseif ($type === 'hardBreak') {
                $html .= '<br>';
            } elseif ($type === 'horizontalRule') {
                $html .= '<hr>';
            } elseif ($type === 'text') {
                $html .= $text;
            } else {
                $html .= $content . $text;
            }
        }

        return $html;
    }

    public function update(ArticleRequest $request, Article $article)
    {
        $data = $request->validated();
        $seoData = $data['seo'] ?? [];
        $igMediaDeleteIds = $data['ig_media_delete_ids'] ?? [];
        $supportingFileDeleteIds = $data['supporting_file_delete_ids'] ?? [];
        $supportingDescriptions = $data['supporting_descriptions'] ?? [];
        unset($data['seo']);
        unset($data['ig_media']);
        unset($data['ig_media_delete_ids']);
        unset($data['supporting_files']);
        unset($data['supporting_file_delete_ids']);
        unset($data['supporting_descriptions']);

        if (isset($data['thumbnail']) && $request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        $article->update(array_merge($data, ['editor_id' => auth()->id()]));

        if (!empty($seoData)) {
            $article->seo()->updateOrCreate(['article_id' => $article->id], $seoData);
        }

        if (!empty($igMediaDeleteIds)) {
            Media::where('article_id', $article->id)
                ->whereIn('id', $igMediaDeleteIds)
                ->delete();
        }

        if (!empty($supportingFileDeleteIds)) {
            Media::where('article_id', $article->id)
                ->whereIn('id', $supportingFileDeleteIds)
                ->delete();
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
