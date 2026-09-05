<?php

namespace App\Http\Controllers;

use App\Exports\RekapExport;
use App\Models\Article;
use App\Models\Opd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Article::with(['author', 'category', 'editor', 'opd']);
        $this->scopeToUser($query, $user, $request);

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

        $statsQuery = Article::query();
        $this->scopeToUser($statsQuery, $user, $request);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'draft' => (clone $statsQuery)->where('status', 'draft')->count(),
            'submitted' => (clone $statsQuery)->where('status', 'submitted')->count(),
            'returned' => (clone $statsQuery)->where('status', 'returned')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'published' => (clone $statsQuery)->where('status', 'published')->count(),
        ];

        return Inertia::render('Rekap/Index', [
            'articles' => $articles,
            'stats' => $stats,
            'opds' => $user->hasCrossOpdAccess() ? Opd::orderBy('name')->get() : [],
            'filters' => $request->only(['status', 'category_id', 'author_id', 'date_from', 'date_to', 'opd_id']),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->all();
        $user = auth()->user();
        if (!$user->hasCrossOpdAccess()) {
            $filters['author_id'] = $user->id;
        } elseif ($request->filled('opd_id')) {
            $filters['opd_id'] = $request->opd_id;
        }

        return Excel::download(new RekapExport($filters), 'rekap-ppid-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $query = Article::with(['author', 'category', 'editor', 'opd']);
        $this->scopeToUser($query, $user, $request);

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

    /**
     * Kontributor hanya melihat artikel miliknya sendiri; editor & leader lintas OPD
     * (dengan filter opd_id opsional).
     */
    private function scopeToUser($query, $user, Request $request): void
    {
        if (!$user->hasCrossOpdAccess()) {
            $query->where('author_id', $user->id);

            return;
        }

        if ($request->filled('opd_id')) {
            $query->where('opd_id', $request->opd_id);
        }
    }
}

