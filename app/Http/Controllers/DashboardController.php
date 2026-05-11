<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function approvedNews()
    {
        $articles = Article::whereIn('status', ['approved', 'published'])
            ->with(['author', 'editor', 'category'])
            ->latest('published_at')
            ->paginate(15);

        return Inertia::render('ApprovedNews/Index', [
            'articles' => $articles,
        ]);
    }

    public function index()
    {
        $user = auth()->user();
        $overallStats = [
            'total' => Article::count(),
            'draft' => Article::where('status', 'draft')->count(),
            'submitted' => Article::where('status', 'submitted')->count(),
            'returned' => Article::where('status', 'returned')->count(),
            'approved' => Article::where('status', 'approved')->count(),
            'published' => Article::where('status', 'published')->count(),
        ];

        $monthlyTrend = collect(range(5, 0))
            ->map(function (int $offset) {
                $date = Carbon::now()->subMonths($offset);

                return [
                    'label' => $date->translatedFormat('M'),
                    'total' => Article::whereBetween('created_at', [
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
                    ->with(['author', 'category'])
                    ->latest()
                    ->take(5)
                    ->get(),
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
                    ->with(['author', 'category'])
                    ->latest('published_at')
                    ->take(5)
                    ->get(),
            ]);
        }

        return Inertia::render('Dashboard/Index', $data);
    }
}
