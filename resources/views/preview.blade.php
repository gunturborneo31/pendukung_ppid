<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - Pendukung PPID</title>
    <meta name="description" content="{{ $article->seo->seo_description ?? $article->excerpt }}">
    <meta property="og:title" content="{{ $article->seo->og_title ?? $article->title }}">
    <meta property="og:description" content="{{ $article->seo->og_description ?? $article->excerpt }}">
    @if($article->seo?->og_image ?? $article->thumbnail)
    <meta property="og:image" content="{{ Storage::url($article->seo?->og_image ?? $article->thumbnail) }}">
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f9fafb; color: #1f2937; }
        .container { max-width: 800px; margin: 0 auto; padding: 2rem 1rem; }
        header { background: #1e40af; color: white; padding: 1rem; text-align: center; margin-bottom: 2rem; }
        header h1 { font-size: 1.25rem; font-weight: 600; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 1rem; }
        h2 { font-size: 2rem; font-weight: 700; color: #111827; margin-bottom: 1rem; line-height: 1.3; }
        .meta { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.5rem; }
        .thumbnail { width: 100%; height: 400px; object-fit: cover; border-radius: 0.5rem; margin-bottom: 2rem; }
        .prose { line-height: 1.8; font-size: 1.05rem; }
        .prose h1, .prose h2, .prose h3 { font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .prose p { margin-bottom: 1rem; }
        .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose blockquote { border-left: 4px solid #1e40af; padding-left: 1rem; color: #4b5563; font-style: italic; }
        .preview-banner { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <header>
        <h1>Pendukung PPID</h1>
    </header>
    <div class="container">
        <div class="preview-banner">⚠️ Ini adalah halaman preview artikel. Belum dipublikasikan secara resmi.</div>

        @if($article->category)
        <span class="badge">{{ $article->category->name }}</span>
        @endif

        <h2>{{ $article->title }}</h2>

        <p class="meta">
            Oleh <strong>{{ $article->author->name }}</strong>
            @if($article->published_at)
            &bull; {{ $article->published_at->translatedFormat('d F Y') }}
            @endif
        </p>

        @if($article->thumbnail)
        <img class="thumbnail" src="{{ Storage::url($article->thumbnail) }}" alt="{{ $article->title }}">
        @endif

        <div class="prose">
            @if(is_array($article->body_web))
                @foreach($article->body_web['content'] ?? [] as $node)
                    @php echo renderTiptapNode($node); @endphp
                @endforeach
            @else
                {!! $article->body_web !!}
            @endif
        </div>
    </div>
</body>
</html>
@php
function renderTiptapNode($node) {
    $type = $node['type'] ?? 'text';
    $content = '';
    foreach ($node['content'] ?? [] as $child) {
        $content .= renderTiptapNode($child);
    }
    $text = htmlspecialchars($node['text'] ?? '');
    foreach ($node['marks'] ?? [] as $mark) {
        if ($mark['type'] === 'bold') $text = "<strong>{$text}</strong>";
        if ($mark['type'] === 'italic') $text = "<em>{$text}</em>";
        if ($mark['type'] === 'link') $text = "<a href='" . htmlspecialchars($mark['attrs']['href'] ?? '#') . "'>{$text}</a>";
    }
    return match($type) {
        'paragraph' => "<p>{$content}{$text}</p>",
        'heading' => "<h" . ($node['attrs']['level'] ?? 2) . ">{$content}{$text}</h" . ($node['attrs']['level'] ?? 2) . ">",
        'bulletList' => "<ul>{$content}</ul>",
        'orderedList' => "<ol>{$content}</ol>",
        'listItem' => "<li>{$content}{$text}</li>",
        'blockquote' => "<blockquote>{$content}</blockquote>",
        'image' => "<img src='" . htmlspecialchars($node['attrs']['src'] ?? '') . "' alt='" . htmlspecialchars($node['attrs']['alt'] ?? '') . "' style='max-width:100%'>",
        'text' => $text,
        default => $content . $text,
    };
}
@endphp
