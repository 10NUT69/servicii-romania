<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PublicCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Cache doar pentru GET/HEAD
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $response;
        }

        // Dacă există sesiune sau CSRF cookie -> nu cache (user logat / formulare)
        if ($request->hasCookie('laravel_session') || $request->hasCookie('XSRF-TOKEN')) {
            return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        // Excluderi clare (sensibile)
        if ($request->is(
            'login',
            'register',
            'forgot-password',
            'reset-password/*',
            'confirm-password',
            'verify-email*',
            'contul-meu*',
            'adauga-anunt*',
            'anunt/*/edit',
            'panou-secret-mb*',
            'api/*'
        )) {
            return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        // Public pages: cache 5 min
        return $response->header('Cache-Control', 'public, max-age=300');
    }
}

