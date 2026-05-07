<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $data = [];

        if ($user->role === 'contributor') {
            $data = [
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
            ];
        } elseif ($user->role === 'editor') {
            $data = [
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
            ];
        } elseif ($user->role === 'leader') {
            $data = [
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
            ];
        }

        return Inertia::render('Dashboard/Index', $data);
    }
}
