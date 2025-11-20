<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visit;

class TrackVisit
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        /* =======================================================
         * EXCLUDERI — NU CONTORIZĂM:
         * =======================================================
         */

        // 1. Nu logăm admin (evită falsificarea statisticilor)
        if ($request->is('admin/*')) {
            return $response;
        }

        // 2. Nu logăm bots / crawlers
        if ($this->isBot($request->userAgent())) {
            return $response;
        }

        // 3. Nu logăm AJAX (toggle favorite, etc.)
        if ($request->ajax()) {
            return $response;
        }

        // 4. Nu logăm asset-uri
        if ($request->is('images/*') || $request->is('storage/*') || $request->is('css/*') || $request->is('js/*')) {
            return $response;
        }


        /* =======================================================
         * DETECTARE DEVICE, BROWSER, GEO-IP, REFERER
         * =======================================================
         */
        $ua      = $request->userAgent();
        $device  = $this->detectDevice($ua);
        $browser = $this->detectBrowser($ua);

        // Procesare referer
        $referer = $request->headers->get('referer');
        if ($referer) {
            $referer = parse_url($referer, PHP_URL_HOST) ?: null;
            $referer = str_replace(['www.', 'http://', 'https://'], '', $referer);
        }

        // Geo-IP (nu va funcționa pe localhost → e normal)
        $country = null;
        $city = null;

        try {
            if ($request->ip() && $request->ip() !== '127.0.0.1' && $request->ip() !== '::1') {
                $geo = @json_decode(file_get_contents("http://ip-api.com/json/{$request->ip()}"), true);
                $country = $geo['country'] ?? null;
                $city    = $geo['city'] ?? null;
            }
        } catch (\Exception $e) {}

        /* =======================================================
         * SALVARE VIZITĂ
         * =======================================================
         */

        Visit::create([
            'url'        => '/' . ltrim($request->path(), '/'),
            'ip'         => $request->ip(),
            'user_agent' => $ua,
            'referer'    => $referer,
            'country'    => $country,
            'city'       => $city,
            'device'     => $device,
            'browser'    => $browser,
            'user_id'    => auth()->check() && auth()->user()->is_admin ? null : auth()->id(), // exclude admin
        ]);

        return $response;
    }

    /* =======================================================
     * DETECT BOT
     * ======================================================= */
    private function isBot($userAgent)
    {
        if (!$userAgent) return true;

        $bots = [
            'bot', 'crawl', 'slurp', 'spider', 'curl',
            'facebookexternalhit', 'google', 'bing', 'yandex',
            'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'uptimerobot',
            'python-requests', 'scrapy', 'wget'
        ];

        $ua = strtolower($userAgent);

        foreach ($bots as $bot) {
            if (strpos($ua, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /* =======================================================
     * DETECTARE DEVICE
     * ======================================================= */
    private function detectDevice($ua)
    {
        if (preg_match('/mobile|iphone|android/i', $ua)) return 'mobile';
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        return 'desktop';
    }

    /* =======================================================
     * DETECTARE BROWSER
     * ======================================================= */
    private function detectBrowser($ua)
    {
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        return 'Other';
    }
}
