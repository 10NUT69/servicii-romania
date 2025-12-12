<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class PrettyThrottle
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ThrottleRequestsException|TooManyRequestsHttpException $e) {

            $msg = '⏳ Pentru a preveni spam-ul, am limitat publicarea anunțurilor. Te rugăm să încerci din nou în câteva minute.';

            if ($request->user()) {
                return redirect('/contul-meu?tab=anunturi')->with('error', $msg);
            }

            return redirect('/')->with('error', $msg);
        }
    }
}
