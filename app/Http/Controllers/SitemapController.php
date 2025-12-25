<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\SoftDeletes;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // 1) Determinăm "ultima modificare" relevantă (categorii + servicii active)
        $latestCategory = Category::query()
            ->select(['updated_at', 'created_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        $serviceQuery = Service::query()->where('status', 'active');
        if (in_array(SoftDeletes::class, class_uses_recursive(Service::class))) {
            $serviceQuery->withoutTrashed();
        }

        $latestService = $serviceQuery
            ->select(['updated_at', 'created_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        $latestUpdate = max(
            optional($latestCategory?->updated_at ?? $latestCategory?->created_at),
            optional($latestService?->updated_at ?? $latestService?->created_at),
        );

        // 2) Chei: azi / ieri
        $todayKey = 'sitemap.xml:' . now()->toDateString();
        $yesterdayKey = 'sitemap.xml:' . now()->subDay()->toDateString();

        // 3) Dacă avem deja sitemap-ul de azi, îl returnăm direct (max 1 generare/zi)
        if (Cache::has($todayKey)) {
            return response(Cache::get($todayKey), 200)->header('Content-Type', 'text/xml');
        }

        // 4) Dacă NU avem azi, dar avem ieri și NU există modificări după momentul generării de ieri,
        //    returnăm sitemap-ul de ieri (zero regenerare).
        $yesterdayMetaKey = $yesterdayKey . ':generated_at';

        if (Cache::has($yesterdayKey) && Cache::has($yesterdayMetaKey)) {
            $yesterdayGeneratedAt = Cache::get($yesterdayMetaKey); // Carbon string
            // dacă nu există update-uri după generarea de ieri => nu regenerăm azi
            if (!$latestUpdate || $latestUpdate->lte($yesterdayGeneratedAt)) {
                return response(Cache::get($yesterdayKey), 200)->header('Content-Type', 'text/xml');
            }
        }

        // 5) Altfel, generăm sitemap-ul (o singură dată azi) și îl cache-uim până mâine.
        $xml = $this->buildSitemapXml();

        Cache::put($todayKey, $xml, now()->addDay());
        Cache::put($todayKey . ':generated_at', now(), now()->addDay());

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private function buildSitemapXml(): string
    {
        $urls = [];

        // 1. Home
        $urls[] = [
            'loc'        => url('/'),
            'lastmod'    => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ];

        // 2. Static
        if (Route::has('services.index')) {
            $urls[] = [
                'loc'        => route('services.index'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority'   => '0.9',
            ];
        }

        // 3. Categorii (chunk)
        Category::query()
            ->select(['id', 'slug', 'updated_at', 'created_at'])
            ->orderBy('id')
            ->chunkById(1000, function ($categories) use (&$urls) {
                foreach ($categories as $category) {
                    $urls[] = [
                        'loc'        => route('category.index', ['category' => $category->slug]),
                        'lastmod'    => ($category->updated_at ?? $category->created_at)->toAtomString(),
                        'changefreq' => 'daily',
                        'priority'   => '0.8',
                    ];
                }
            });

        // 4. Servicii active (chunk) - link manual, ca în codul tău care funcționa
        $q = Service::query()
            ->select(['id', 'slug', 'updated_at', 'created_at'])
            ->where('status', 'active')
            ->orderBy('id');

        if (in_array(SoftDeletes::class, class_uses_recursive(Service::class))) {
            $q->withoutTrashed();
        } else {
            $q->whereNull('deleted_at');
        }

        $q->chunkById(1000, function ($services) use (&$urls) {
            foreach ($services as $service) {
                $urls[] = [
                    'loc'        => url('/anunt/' . $service->slug),
                    'lastmod'    => ($service->updated_at ?? $service->created_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority'   => '0.7',
                ];
            }
        });

        return view('sitemap', compact('urls'))->render();
    }
}
