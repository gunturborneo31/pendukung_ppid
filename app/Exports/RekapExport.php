<?php

namespace App\Exports;

use App\Models\Article;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Article::with(['author', 'category', 'editor']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return ['#', 'Judul', 'Kategori', 'Penulis', 'Editor', 'Status', 'Platform', 'Tanggal Buat', 'Tanggal Publikasi'];
    }

    public function map($article): array
    {
        return [
            $article->id,
            $article->title,
            $article->category?->name ?? '-',
            $article->author?->name ?? '-',
            $article->editor?->name ?? '-',
            $article->status,
            $article->target_platform,
            $article->created_at->format('d/m/Y H:i'),
            $article->published_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
