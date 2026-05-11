@php
    use Illuminate\Support\Facades\Storage;

    $media = collect($article->media ?? []);
    $instagramMedia = $media->filter(fn ($item) => ($item->alt_text ?? null) === 'ig_media')->values();
    $supportingFiles = $media->filter(fn ($item) => ($item->alt_text ?? null) !== 'ig_media')->values();

    function previewStorageUrl(?string $path): string
    {
        if (!$path) {
            return '#';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::url($path);
    }

    function mediaExtension(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $parsedPath = parse_url($path, PHP_URL_PATH) ?: $path;
        return strtolower((string) pathinfo($parsedPath, PATHINFO_EXTENSION));
    }

    function supportingPreviewKind($item): string
    {
        $ext = mediaExtension($item->path ?? null);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v'];

        if (in_array($ext, $imageExtensions, true)) {
            return 'image';
        }

        if (in_array($ext, $videoExtensions, true)) {
            return 'video';
        }

        return 'file';
    }

    function renderTiptapNode($node): string
    {
        $type = $node['type'] ?? 'text';
        $content = '';

        foreach ($node['content'] ?? [] as $child) {
            $content .= renderTiptapNode($child);
        }

        $text = htmlspecialchars($node['text'] ?? '');

        foreach ($node['marks'] ?? [] as $mark) {
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

        return match ($type) {
            'paragraph' => "<p>{$content}{$text}</p>",
            'heading' => '<h' . (int) ($node['attrs']['level'] ?? 2) . '>' . $content . $text . '</h' . (int) ($node['attrs']['level'] ?? 2) . '>',
            'bulletList' => "<ul>{$content}</ul>",
            'orderedList' => "<ol>{$content}</ol>",
            'listItem' => "<li>{$content}{$text}</li>",
            'blockquote' => "<blockquote>{$content}</blockquote>",
            'image' => "<img src='" . htmlspecialchars($node['attrs']['src'] ?? '') . "' alt='" . htmlspecialchars($node['attrs']['alt'] ?? '') . "' style='max-width:100%'>",
            'hardBreak' => '<br>',
            'text' => $text,
            default => $content . $text,
        };
    }

    function renderTiptapPlainText($node): string
    {
        $type = $node['type'] ?? 'text';
        $content = '';

        foreach ($node['content'] ?? [] as $child) {
            $content .= renderTiptapPlainText($child);
        }

        $text = $node['text'] ?? '';

        return match ($type) {
            'paragraph', 'heading', 'blockquote', 'listItem' => trim($content . ' ' . $text),
            'bulletList', 'orderedList' => trim($content),
            'hardBreak' => "\n",
            default => trim($content . ' ' . $text),
        };
    }

    $bodyHtml = '';
    $bodyPlainText = '';

    if (is_array($article->body_web)) {
        $bodyHtml = '';
        $bodyPlainText = '';
        foreach ($article->body_web['content'] ?? [] as $node) {
            $bodyHtml .= renderTiptapNode($node);
            $bodyPlainText .= renderTiptapPlainText($node) . "\n";
        }
    } else {
        $bodyHtml = (string) ($article->body_web ?? '');
        $bodyPlainText = strip_tags($bodyHtml);
    }

    $seo = $article->seo;
    $thumbnailUrl = $article->thumbnail ? previewStorageUrl($article->thumbnail) : null;
    $ogImageUrl = $seo?->og_image ? previewStorageUrl($seo->og_image) : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - Pendukung PPID Bappelitbangda Mahulu</title>
    <meta name="description" content="{{ $seo?->seo_description ?? $article->excerpt }}">
    <meta property="og:title" content="{{ $seo?->og_title ?? $article->title }}">
    <meta property="og:description" content="{{ $seo?->og_description ?? $article->excerpt }}">
    @if($ogImageUrl || $thumbnailUrl)
    <meta property="og:image" content="{{ $ogImageUrl ?? $thumbnailUrl }}">
    @endif
    <link rel="icon" type="image/png" href="/image/logo_mahulu.png">
    <link rel="apple-touch-icon" href="/image/logo_mahulu.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); color: #0f172a; }
        a { color: inherit; text-decoration: none; }
        .page { max-width: 1120px; margin: 0 auto; padding: 18px 14px 28px; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
        .brand { font-weight: 700; color: #1e3a8a; letter-spacing: .2px; }
        .banner { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; padding: 10px 12px; border-radius: 10px; margin-bottom: 14px; font-size: 13px; }
        .hero { background: transparent; border: 0; border-bottom: 1px solid #dbe3ee; border-radius: 0; box-shadow: none; padding: 8px 0 14px; margin-bottom: 10px; }
        .hero-top { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; background: #eef2ff; color: #3730a3; font-size: 12px; padding: 6px 10px; border-radius: 999px; font-weight: 600; }
        .status { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 6px 10px; border-radius: 999px; font-weight: 700; text-transform: capitalize; }
        .status.submitted { background: #fffbeb; color: #b45309; }
        .status.returned { background: #fef2f2; color: #dc2626; }
        .status.approved { background: #eef2ff; color: #4338ca; }
        .status.published { background: #ecfdf5; color: #047857; }
        h1 { font-size: clamp(28px, 4vw, 44px); line-height: 1.15; margin-bottom: 10px; letter-spacing: -.02em; }
        .meta { color: #64748b; font-size: 14px; line-height: 1.75; margin-bottom: 18px; }
        .grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 12px; }
        .stack { display: grid; gap: 10px; }
        .card { background: transparent; border: 0; border-bottom: 1px solid #dbe3ee; border-radius: 0; box-shadow: none; padding: 10px 0 14px; }
        .card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
        .card-title { font-size: 15px; font-weight: 700; color: #0f172a; }
        .card-sub { font-size: 12px; color: #64748b; margin-top: 2px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 8px 12px; font-size: 12px; font-weight: 700; background: white; cursor: pointer; transition: .15s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(15, 23, 42, .08); }
        .btn-primary { border-color: #c7d2fe; color: #3730a3; background: #eef2ff; }
        .btn-emerald { border-color: #a7f3d0; color: #047857; background: #ecfdf5; }
        .btn-slate { color: #334155; }
        .details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 10px; }
        .detail { background: #ffffffb5; border: 1px solid #e7edf5; border-radius: 10px; padding: 10px; }
        .label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: #64748b; margin-bottom: 6px; font-weight: 700; }
        .value { font-size: 14px; color: #0f172a; line-height: 1.7; white-space: pre-line; word-break: break-word; }
        .copy-inline { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
        .copy-inline .value { flex: 1; }
        .copy-wrap { display: flex; gap: 8px; align-items: flex-start; }
        .copy-wrap .value { flex: 1; }
        .content { line-height: 1.75; font-size: 15px; color: #1e293b; }
        .content h1, .content h2, .content h3, .content h4 { font-weight: 700; margin: 1.1em 0 .5em; }
        .content p { margin-bottom: .9em; }
        .content ul, .content ol { padding-left: 1.4em; margin-bottom: .9em; }
        .content blockquote { border-left: 4px solid #6366f1; padding-left: 1rem; color: #475569; font-style: italic; margin: 1rem 0; }
        .content img { max-width: 100%; border-radius: 14px; margin: .5rem 0; }
        .plain-info { display: grid; gap: 6px; font-size: 14px; color: #334155; }
        .plain-info-row { line-height: 1.65; }
        .plain-info-label { font-weight: 700; color: #0f172a; }
        .media-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .media-item { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; }
        .media-preview { aspect-ratio: 1 / 1; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .media-preview img, .media-preview video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .media-foot { padding: 10px 12px; display: flex; gap: 8px; align-items: center; justify-content: space-between; }
        .media-name { font-size: 12px; font-weight: 600; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .media-meta { font-size: 11px; color: #64748b; }
        .footer-note { margin-top: 10px; font-size: 12px; color: #94a3b8; text-align: center; }
        .copy-feedback { position: fixed; right: 16px; bottom: 16px; background: #0f172a; color: white; padding: 10px 12px; border-radius: 999px; font-size: 12px; box-shadow: 0 10px 24px rgba(15, 23, 42, .25); opacity: 0; transform: translateY(8px); pointer-events: none; transition: .2s ease; }
        .copy-feedback.show { opacity: 1; transform: translateY(0); }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
            .details { grid-template-columns: 1fr; }
            .media-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="brand" style="display:flex; align-items:center; gap:10px;">
                <img src="/image/logo_mahulu.png" alt="Logo Mahulu" style="width:32px; height:32px; object-fit:contain;">
                <span>Pendukung PPID Bappelitbangda Mahulu</span>
            </div>
            <span class="badge">Preview Artikel</span>
        </div>

        <div class="banner">⚠️ Ini halaman preview. Konten dapat disalin atau diunduh sebelum dipublikasikan.</div>

        <section class="hero">
            <div class="hero-top">
                @if($article->category)
                    <span class="badge">{{ $article->category->name }}</span>
                @endif
                <span class="status {{ $article->status }}">{{ $article->status }}</span>
            </div>

            <h1>{{ $article->title }}</h1>

            <p class="meta">
                Oleh <strong>{{ $article->author->name }}</strong>
                @if($article->editor)
                    &bull; Editor: <strong>{{ $article->editor->name }}</strong>
                    @if($article->editor->field)
                        ({{ $article->editor->field }})
                    @endif
                @endif
                @if($article->published_at)
                    &bull; {{ $article->published_at->translatedFormat('d F Y') }}
                @endif
            </p>

        </section>

        <div class="grid">
            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Detail Artikel</div>
                            <div class="card-sub">Informasi dasar dan ringkasan isi</div>
                        </div>
                    </div>

                    <div class="details">
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Judul</span>
                                {{ $article->title }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($article->title), 'Judul disalin')">Copy</button>
                        </div>
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Tanggal</span>
                                {{ $article->published_at ? $article->published_at->translatedFormat('d F Y') : '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js(optional($article->published_at)->translatedFormat('d F Y') ?? ''), 'Tanggal disalin')">Copy</button>
                        </div>
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Kategori</span>
                                {{ $article->category->name ?? 'Tanpa Kategori' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($article->category->name ?? ''), 'Kategori disalin')">Copy</button>
                        </div>
                        <div class="plain-info" style="margin-top: 10px;">
                        <div class="plain-info-row">
                            <span class="plain-info-label">Penulis:</span> {{ $article->author->name }}
                        </div>
                        <div class="plain-info-row">
                            <span class="plain-info-label">Editor:</span> {{ $article->editor->name ?? '-' }}
                        </div>
                        <div class="plain-info-row">
                            <span class="plain-info-label">Status:</span> {{ $article->status }}
                        </div>
                    </div>
                    </div>

                    
                </section>

                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Thumbnail</div>
                            <div class="card-sub">Gambar utama artikel</div>
                        </div>
                        @if($thumbnailUrl)
                        <div class="actions">
                            <a class="btn btn-primary" href="{{ $thumbnailUrl }}" target="_blank">Lihat</a>
                            <a class="btn btn-emerald" href="{{ $thumbnailUrl }}" download="{{ basename($article->thumbnail) }}">Unduh</a>
                        </div>
                        @endif
                    </div>
                    @if($thumbnailUrl)
                        <img src="{{ $thumbnailUrl }}" alt="{{ $article->title }}" style="width:100%; border-radius:16px; max-height: 460px; object-fit:cover; border:1px solid #e2e8f0;">
                    @else
                        <div class="detail">Tidak ada thumbnail.</div>
                    @endif
                </section>

                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Konten Website</div>
                            <div class="card-sub">Isi lengkap untuk halaman web</div>
                        </div>
                    </div>
                    <div class="detail" style="margin-bottom: 12px;">
                        <div class="copy-inline">
                            <div class="value">
                                <span class="label">Copy Isi Konten</span>
                                Gunakan tombol di kanan untuk menyalin teks isi website.
                            </div>
                            <button class="btn btn-primary" type="button" onclick="copyText(@js($bodyPlainText), 'Isi website disalin')">Copy</button>
                        </div>
                    </div>
                    <div class="content">
                        {!! $bodyHtml ?: '<p style="color:#94a3b8;">Tidak ada konten web.</p>' !!}
                    </div>
                </section>

                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Instagram</div>
                            <div class="card-sub">Caption, hashtag, dan media Instagram</div>
                        </div>
                    </div>
                    <div class="stack" style="gap: 12px;">
                        @if($instagramMedia->count())
                            <div class="media-grid">
                                @foreach($instagramMedia as $item)
                                    <div class="media-item">
                                        <div class="media-preview">
                                            @if(($item->type ?? '') === 'video')
                                                <video src="{{ previewStorageUrl($item->path) }}" controls></video>
                                            @else
                                                <img src="{{ previewStorageUrl($item->path) }}" alt="{{ $item->description ?? 'Media Instagram' }}">
                                            @endif
                                        </div>
                                        <div class="media-foot">
                                            <div style="min-width:0; flex:1;">
                                                <div class="media-name">{{ basename($item->path) }}</div>
                                                <div class="media-meta">{{ $item->type ?? 'media' }}</div>
                                            </div>
                                            <div class="actions">
                                                <a class="btn btn-slate" href="{{ previewStorageUrl($item->path) }}" target="_blank">Lihat</a>
                                                <a class="btn btn-emerald" href="{{ previewStorageUrl($item->path) }}" download="{{ basename($item->path) }}">Unduh</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="detail">Belum ada media Instagram.</div>
                        @endif

                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Caption</span>
                                {{ $article->caption_ig ?: '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($article->caption_ig ?? ''), 'Caption IG disalin')">Copy</button>
                        </div>
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Hashtags</span>
                                {{ $article->hashtags_ig ?: '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($article->hashtags_ig ?? ''), 'Hashtags IG disalin')">Copy</button>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Riwayat</div>
                            <div class="card-sub">Aktivitas artikel (teks biasa)</div>
                        </div>
                    </div>
                    <div class="plain-info">
                        <div class="plain-info-row">
                            <span class="plain-info-label">Dibuat:</span> {{ $article->created_at?->translatedFormat('d F Y H:i') ?? '-' }}
                        </div>
                        <div class="plain-info-row">
                            <span class="plain-info-label">Diperbarui:</span> {{ $article->updated_at?->translatedFormat('d F Y H:i') ?? '-' }}
                        </div>
                    </div>
                </section>
            </div>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">SEO Detail</div>
                            <div class="card-sub">Seluruh metadata SEO yang tersimpan</div>
                        </div>
                    </div>
                    <div class="stack" style="gap: 12px;">
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">SEO Title</span>
                                {{ $seo?->seo_title ?? '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($seo?->seo_title ?? ''), 'SEO title disalin')">Copy</button>
                        </div>
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Meta Description</span>
                                {{ $seo?->seo_description ?? '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($seo?->seo_description ?? ''), 'Meta description disalin')">Copy</button>
                        </div>
                        <div class="detail copy-wrap">
                            <div class="value">
                                <span class="label">Keywords</span>
                                {{ $seo?->seo_keywords ?? '-' }}
                            </div>
                            <button class="btn btn-slate" type="button" onclick="copyText(@js($seo?->seo_keywords ?? ''), 'Keywords disalin')">Copy</button>
                        </div>
                         <section class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">File Pendukung</div>
                            <div class="card-sub">Semua file tambahan yang diunggah</div>
                        </div>
                    </div>
                    <div class="stack" style="gap: 12px;">
                        @if($supportingFiles->count())
                            @foreach($supportingFiles as $item)
                                @php($previewKind = supportingPreviewKind($item))
                                <div class="media-item">
                                    <div class="media-preview" style="aspect-ratio: 16 / 10;">
                                        @if($previewKind === 'video')
                                            <video src="{{ previewStorageUrl($item->path) }}" controls></video>
                                        @elseif($previewKind === 'image')
                                            <img src="{{ previewStorageUrl($item->path) }}" alt="{{ $item->description ?? 'File pendukung' }}">
                                        @else
                                            <div style="display:flex; align-items:center; justify-content:center; width:100%; height:100%; color:#64748b;">
                                                <div style="font-size:48px; line-height:1;">📄</div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="media-foot">
                                        <div style="min-width:0; flex:1;">
                                            <div class="media-name">{{ basename($item->path) }}</div>
                                            <div class="media-meta">{{ $item->description ?: $item->type ?: 'file' }}</div>
                              m          </div>
                                        <div class="actions">
                                            @if($previewKind === 'video' || $previewKind === 'image')
                                            <a class="btn btn-slate" href="{{ previewStorageUrl($item->path) }}" target="_blank">Lihat</a>
                                            @endif
                                            <a class="btn btn-emerald" href="{{ previewStorageUrl($item->path) }}" download="{{ basename($item->path) }}">Unduh</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="detail">Belum ada file pendukung.</div>
                        @endif
                    </div>
                </section>
                        @if($ogImageUrl)
                        <div class="detail">
                            <span class="label">OG Image</span>
                            <img src="{{ $ogImageUrl }}" alt="OG Image" style="width:100%; border-radius:14px; border:1px solid #e2e8f0; margin-top:6px;">
                            <div class="actions" style="margin-top:10px;">
                                <a class="btn btn-primary" href="{{ $ogImageUrl }}" target="_blank">Lihat</a>
                                <a class="btn btn-emerald" href="{{ $ogImageUrl }}" download>Unduh</a>
                            </div>
                        </div>
                        @endif
                        
                    </div>
                </section>

               

            </div>
        </div>

        <div class="footer-note">Semua bagian di atas bisa disalin teksnya atau diunduh file/gambarnya.</div>
    </div>

    <div id="copyFeedback" class="copy-feedback">Tersalin</div>

    <script>
        function copyText(text, message) {
            if (!text) {
                showCopyFeedback('Tidak ada teks untuk disalin');
                return;
            }

            const value = String(text);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(value)
                    .then(() => showCopyFeedback(message || 'Tersalin'))
                    .catch(() => fallbackCopy(value, message));
                return;
            }

            fallbackCopy(value, message);
        }

        function fallbackCopy(text, message) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                showCopyFeedback(message || 'Tersalin');
            } catch (error) {
                showCopyFeedback('Gagal menyalin');
            }
            document.body.removeChild(textarea);
        }

        let feedbackTimer = null;
        function showCopyFeedback(message) {
            const el = document.getElementById('copyFeedback');
            if (!el) return;
            el.textContent = message;
            el.classList.add('show');
            clearTimeout(feedbackTimer);
            feedbackTimer = setTimeout(() => el.classList.remove('show'), 1400);
        }
    </script>
</body>
</html>
