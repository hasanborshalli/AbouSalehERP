<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
         $user = $request->user();

        if (!$user) {
            abort(403);
        }

        // Normalize (optional)
        $userRole = strtolower((string) $user->role);
        $roles = array_map('strtolower', $roles);

        if (!in_array($userRole, $roles, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}