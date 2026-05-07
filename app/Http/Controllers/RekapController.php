<?php

namespace App\Http\Controllers;

use App\Exports\RekapExport;
use App\Models\Article;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with(['author', 'category', 'editor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $articles = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Article::count(),
            'draft' => Article::where('status', 'draft')->count(),
            'submitted' => Article::where('status', 'submitted')->count(),
            'returned' => Article::where('status', 'returned')->count(),
            'approved' => Article::where('status', 'approved')->count(),
            'published' => Article::where('status', 'published')->count(),
        ];

        return Inertia::render('Rekap/Index', [
            'articles' => $articles,
            'stats' => $stats,
            'filters' => $request->only(['status', 'category_id', 'author_id', 'date_from', 'date_to']),
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new RekapExport($request->all()), 'rekap-ppid-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = Article::with(['author', 'category', 'editor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $articles = $query->latest()->get();

        $stats = [
            'total' => $articles->count(),
            'published' => $articles->where('status', 'published')->count(),
        ];

        $pdf = Pdf::loadView('exports.rekap_pdf', compact('articles', 'stats'));

        return $pdf->download('rekap-ppid-' . now()->format('Y-m-d') . '.pdf');
    }
}
