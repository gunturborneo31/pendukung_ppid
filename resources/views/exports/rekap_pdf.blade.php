<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Artikel PPID</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        .header h1 { font-size: 18px; color: #1e40af; }
        .header p { font-size: 10px; color: #6b7280; }
        .stats { display: flex; gap: 10px; margin-bottom: 15px; }
        .stat-card { flex: 1; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 8px; text-align: center; }
        .stat-card .number { font-size: 20px; font-weight: bold; color: #1e40af; }
        .stat-card .label { font-size: 9px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e40af; color: white; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .status-published { color: #059669; font-weight: bold; }
        .status-draft { color: #6b7280; }
        .status-submitted { color: #d97706; }
        .status-returned { color: #dc2626; }
        .footer { margin-top: 20px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ARTIKEL - PENDUKUNG PPID</h1>
        <p>Digenerate pada: {{ now()->format('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Judul Artikel</th>
                <th>Kategori</th>
                <th>Penulis</th>
                <th>Editor</th>
                <th>Status</th>
                <th>Platform</th>
                <th>Tgl Buat</th>
                <th>Tgl Publikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($articles as $i => $article)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ Str::limit($article->title, 40) }}</td>
                <td>{{ $article->category?->name ?? '-' }}</td>
                <td>{{ $article->author?->name ?? '-' }}</td>
                <td>{{ $article->editor?->name ?? '-' }}</td>
                <td class="status-{{ $article->status }}">{{ $article->status }}</td>
                <td>{{ $article->target_platform }}</td>
                <td>{{ $article->created_at->format('d/m/Y') }}</td>
                <td>{{ $article->published_at?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total: {{ $stats['total'] }} artikel | Dipublikasikan: {{ $stats['published'] }} artikel</p>
        <p>Dokumen ini dibuat otomatis oleh sistem Pendukung PPID</p>
    </div>
</body>
</html>
