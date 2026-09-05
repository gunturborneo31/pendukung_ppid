<?php

namespace App\Http\Middleware;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $notifications = [
            'pending_verification' => 0,
            'revision_notes' => 0,
        ];

        if ($user) {
            if ($user->role === 'editor') {
                $notifications['pending_verification'] = Article::where('status', 'submitted')->count();
            }

            if ($user->role === 'contributor') {
                $notifications['revision_notes'] = Article::where('author_id', $user->id)
                    ->where('status', 'returned')
                    ->whereNotNull('editor_notes')
                    ->count();
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'opd_id' => $user->opd_id,
                    'opd' => $user->opd ? ['id' => $user->opd->id, 'name' => $user->opd->name] : null,
                    'has_cross_opd_access' => $user->hasCrossOpdAccess(),
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notifications' => $notifications,
        ]);
    }
}
