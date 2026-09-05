<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function approvedNews()
    {
        $user = auth()->user();

        $articles = Article::whereIn('status', ['approved', 'published'])
            ->visibleTo($user)
            ->with(['author', 'editor', 'category', 'opd'])
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('ApprovedNews/Index', [
            'articles' => $articles,
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $scopedArticles = Article::query()->visibleTo($user);

        $overallStats = [
            'total' => (clone $scopedArticles)->count(),
            'draft' => (clone $scopedArticles)->where('status', 'draft')->count(),
            'submitted' => (clone $scopedArticles)->where('status', 'submitted')->count(),
            'returned' => (clone $scopedArticles)->where('status', 'returned')->count(),
            'approved' => (clone $scopedArticles)->where('status', 'approved')->count(),
            'published' => (clone $scopedArticles)->where('status', 'published')->count(),
        ];

        $monthlyTrend = collect(range(5, 0))
            ->map(function (int $offset) use ($user) {
                $date = Carbon::now()->subMonths($offset);

                return [
                    'label' => $date->translatedFormat('M'),
                    'total' => Article::query()->visibleTo($user)->whereBetween('created_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])->count(),
                ];
            })
            ->values();

        $data = [
            'overallStats' => $overallStats,
            'statusChart' => [
                ['key' => 'draft', 'label' => 'Draft', 'value' => $overallStats['draft']],
                ['key' => 'submitted', 'label' => 'Submitted', 'value' => $overallStats['submitted']],
                ['key' => 'returned', 'label' => 'Returned', 'value' => $overallStats['returned']],
                ['key' => 'approved', 'label' => 'Approved', 'value' => $overallStats['approved']],
                ['key' => 'published', 'label' => 'Published', 'value' => $overallStats['published']],
            ],
            'monthlyTrend' => $monthlyTrend,
        ];

        if ($user->role === 'contributor') {
            $data = array_merge($data, [
                'stats' => [
                    'total' => Article::where('author_id', $user->id)->count(),
                    'draft' => Article::where('author_id', $user->id)->where('status', 'draft')->count(),
                    'submitted' => Article::where('author_id', $user->id)->where('status', 'submitted')->count(),
                    'published' => Article::where('author_id', $user->id)->where('status', 'published')->count(),
                    'returned' => Article::where('author_id', $user->id)->where('status', 'returned')->count(),
                ],
                'recentArticles' => Article::where('author_id', $user->id)
                    ->with('category')
                    ->latest()
                    ->take(5)
                    ->get(),
            ]);
        } elseif ($user->role === 'editor') {
            $data = array_merge($data, [
                'stats' => [
                    'inbox' => Article::whereIn('status', ['submitted', 'returned'])->count(),
                    'approved' => Article::where('editor_id', $user->id)->where('status', 'approved')->count(),
                    'published' => Article::where('status', 'published')->count(),
                ],
                'inbox' => Article::whereIn('status', ['submitted'])
                    ->with(['author', 'category', 'opd'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'opdSummary' => $this->opdSummary(),
            ]);
        } elseif ($user->role === 'leader') {
            $data = array_merge($data, [
                'stats' => [
                    'total' => Article::count(),
                    'published' => Article::where('status', 'published')->count(),
                    'draft' => Article::where('status', 'draft')->count(),
                    'submitted' => Article::where('status', 'submitted')->count(),
                ],
                'recentPublished' => Article::where('status', 'published')
                    ->with(['author', 'category', 'opd'])
                    ->latest('published_at')
                    ->take(5)
                    ->get(),
                'opdSummary' => $this->opdSummary(),
            ]);
        } elseif ($user->role === 'superadmin') {
            $data = array_merge($data, [
                'stats' => [
                    'total_opd' => Opd::count(),
                    'active_opd' => Opd::where('is_active', true)->count(),
                    'total_editors' => User::where('role', 'editor')->count(),
                    'total_leaders' => User::where('role', 'leader')->count(),
                ],
                'opdSummary' => $this->opdSummary(),
            ]);
        }

        return Inertia::render('Dashboard/Index', $data);
    }

    /**
     * Dashboard khusus editor/leader untuk memantau status artikel di seluruh OPD.
     */
    public function opdOverview()
    {
        return Inertia::render('Dashboard/OpdOverview', [
            'opds' => $this->opdSummary(),
        ]);
    }

    /**
     * Ringkasan status artikel per OPD, di-cache singkat karena hanya agregat
     * dan berubah relatif jarang dibanding trafik baca dashboard.
     */
    private function opdSummary(): array
    {
        return Cache::remember('dashboard.opd_summary', now()->addMinutes(5), function () {
            return Opd::where('is_active', true)
                ->withCount([
                    'articles as total_count',
                    'articles as draft_count' => fn ($q) => $q->where('status', 'draft'),
                    'articles as submitted_count' => fn ($q) => $q->where('status', 'submitted'),
                    'articles as returned_count' => fn ($q) => $q->where('status', 'returned'),
                    'articles as approved_count' => fn ($q) => $q->where('status', 'approved'),
                    'articles as published_count' => fn ($q) => $q->where('status', 'published'),
                    'contributors as contributors_count',
                ])
                ->orderBy('name')
                ->get()
                ->toArray();
        });
    }
}

