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

        if (!$user || !$user->organization_id) {
            abort(403, 'Organisation manquante.');
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
