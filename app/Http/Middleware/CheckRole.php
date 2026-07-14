<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if ($user->isPlatformAdmin() && in_array('platform_admin', $roles, true)) {
            return $next($request);
        }

        if ($user->isPlatformAdmin()) {
            abort(403, 'Accès réservé aux espaces plateforme.');
        }

        if ($user->isSuperAdmin() && in_array('super_admin', $roles, true)) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($role === 'super_admin' && $user->isSuperAdmin()) {
                return $next($request);
            }

            if ($role === 'admin' && $user->isAdmin()) {
                return $next($request);
            }

            if ($role === 'manager' && $user->isManager()) {
                return $next($request);
            }

            if ($role === 'moderateur' && $user->isModerateur()) {
                return $next($request);
            }
        }

        abort(403, 'Accès non autorisé.');
    }
}
