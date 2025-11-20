<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrackVisit
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 0. Nu logăm erorile de tip 500
        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 500) {
            return $response;
        }

        /*
         |=======================================================
         |   EXCLUDERI
         |=======================================================
         */

        // 1. Nu logăm admin
        if ($request->is('admin') || $request->is('admin/*')) {
            return $response;
        }

        // 2. Nu logăm AJAX
        if ($request->ajax()) {
            return $response;
        }

        // 3. Nu logăm asset-uri
        if (
            $request->is('images/*') ||
            $request->is('storage/*') ||
            $request->is('css/*') ||
            $request->is('js/*') ||
            $request->is('vendor/*')
        ) {
            return $response;
        }

        // 4. Nu logăm boți
        $ua = $request->userAgent() ?? '';
        if ($this->isBot($ua)) {
            return $response;
        }

        /*
         |=======================================================
         |   COLECTARE DATE (Local - Rapid)
         |=======================================================
         */

        // Device & Browser (procesare locală, foarte rapidă)
        $device  = $this->detectDevice($ua);
        $browser = $this->detectBrowser($ua);

        // Referer clean
        $referer = $request->headers->get('referer');
        if ($referer) {
            $host = parse_url($referer, PHP_URL_HOST);
            $referer = $host ? str_replace('www.', '', $host) : null;
        }

        $ip = $request->ip();

        /*
         |=======================================================
         |   SALVARE RAPIDĂ
         |=======================================================
         */
        try {
            Visit::create([
                'url'        => '/' . ltrim($request->path(), '/'),
                'ip'         => $ip,
                'user_agent' => $ua,
                'referer'    => $referer,
                'device'     => $device,
                'browser'    => $browser,
                // Aici lăsăm NULL. Comanda programată le va completa peste o oră.
                'country'    => null,
                'city'       => null,
                // Verificăm userul
                'user_id'    => (Auth::check() && Auth::user()->is_admin) ? null : Auth::id(),
            ]);
        } catch (\Throwable $e) {
            // Logăm erorile silențios ca să nu deranjăm userul
            Log::error('Visit tracking error: ' . $e->getMessage());
        }

        return $response;
    }

    private function isBot(string $ua): bool
    {
        if (empty($ua) || $ua === '-' || $ua === 'unknown') return false;
        
        $ua = strtolower($ua);
        $bots = [
            'bot', 'crawl', 'spider', 'slurp', 'curl', 'python', 'wget', 
            'scrapy', 'facebook', 'google', 'bing', 'yandex', 'ahrefs', 
            'semrush', 'mj12', 'dotbot', 'uptime'
        ];

        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) return true;
        }
        return false;
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/mobile|iphone|android/i', $ua)) return 'mobile';
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        $ua = strtolower($ua);
        if (strpos($ua, 'chrome') !== false && strpos($ua, 'edge') === false) return 'Chrome';
        if (strpos($ua, 'firefox') !== false) return 'Firefox';
        if (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) return 'Safari';
        if (strpos($ua, 'edge') !== false) return 'Edge';
        return 'Other';
    }
}