<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar que el usuario esté autenticado
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Obtener el rol del usuario
        $userRole = $request->user()->role->name ?? null;

        if (!$userRole) {
            return response()->json(['error' => 'User has no role assigned'], 403);
        }

        // Verificar que el usuario tenga uno de los roles permitidos
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Unauthorized. Required roles: ' . implode(', ', $roles),
                'user_role' => $userRole
            ], 403);
        }

        return $next($request);
    }
}
