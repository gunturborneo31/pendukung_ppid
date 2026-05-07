<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        foreach ($roles as $role) {
            // Support comma-separated roles like 'editor,leader'
            $allowedRoles = array_map('trim', explode(',', $role));
            if (in_array($userRole, $allowedRoles)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Required role: ' . implode(' or ', $roles));
    }
}
