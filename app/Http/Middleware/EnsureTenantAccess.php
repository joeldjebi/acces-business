<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isPlatformAdmin()) {
            abort(403, 'Utilisez la console plateforme.');
        }

        if (!$user || !$user->organization_id) {
            abort(403, 'Organisation manquante.');
        }

        if (!$user->organization || !in_array($user->organization->status, ['active', 'trialing'], true)) {
            abort(403, 'Organisation suspendue ou inactive.');
        }

        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if (!$parameter instanceof Model) {
                continue;
            }

            if (!array_key_exists('organization_id', $parameter->getAttributes())) {
                continue;
            }

            if ((int) $parameter->getAttribute('organization_id') !== (int) $user->organization_id) {
                abort(404);
            }
        }

        return $next($request);
    }
}
