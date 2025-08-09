<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !in_array(optional($user->role)->name, $roles, true)) {
            abort(403); // Forbidden
        }

        return $next($request);
    }
}
