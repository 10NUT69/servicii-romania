<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 1. Middleware-ul global de statistici (TrackVisit)
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisit::class,
        ]);

        // 2. Alias middleware (le păstrăm exact cum le ai)
        $middleware->alias([
            'admin.access'    => \App\Http\Middleware\AdminAccess::class,
            'pretty.throttle' => \App\Http\Middleware\PrettyThrottle::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ✅ Interceptăm 429 (Too Many Requests) și facem redirect + mesaj frumos
        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            \Illuminate\Http\Request $request
        ) {
            // Dacă request-ul e AJAX/JSON, păstrăm 429 dar cu mesaj clar
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '⏳ Ai atins limita de publicare. Te rugăm încearcă din nou în câteva minute.'
                ], 429);
            }

            // Mesaj diferit guest vs auth
            if ($request->user()) {
                return redirect('/contul-meu?tab=anunturi')
                    ->with('error', '⏳ Ai atins limita de publicare. Te rugăm încearcă din nou în câteva minute.');
            }

            // Guest: îl trimitem înapoi la formular și păstrăm input-ul
            return back()->withInput()->with(
                'error',
                '🛡️ Pentru a preveni spamul, utilizatorii neînregistrați pot publica 1 anunț la 5 minute. Încearcă din nou în câteva minute sau creează un cont.'
            );
        });

    })->create();
