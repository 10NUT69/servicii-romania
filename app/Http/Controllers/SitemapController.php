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
        // 1) Determinăm "ultima modificare"
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

        // 2) Cache Logic
        $todayKey = 'sitemap.xml:' . now()->toDateString();
        $yesterdayKey = 'sitemap.xml:' . now()->subDay()->toDateString();

        if (Cache::has($todayKey)) {
            return response(Cache::get($todayKey), 200)->header('Content-Type', 'text/xml');
        }

        $yesterdayMetaKey = $yesterdayKey . ':generated_at';
        if (Cache::has($yesterdayKey) && Cache::has($yesterdayMetaKey)) {
            $yesterdayGeneratedAt = Cache::get($yesterdayMetaKey);
            if (!$latestUpdate || $latestUpdate->lte($yesterdayGeneratedAt)) {
                return response(Cache::get($yesterdayKey), 200)->header('Content-Type', 'text/xml');
            }
        }

        // 3) Build & Cache
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

        // 2. Static (Services Index)
        if (Route::has('services.index')) {
            $urls[] = [
                'loc'        => route('services.index'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority'   => '0.9',
            ];
        }

        // 3. Categorii
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

        // 4. SERVICII ACTIVE (FIXED)
        // Trebuie să încărcăm și relațiile (category, county) pentru URL
        $q = Service::query()
            ->with(['category:id,slug', 'county:id,slug']) // Eager loading necesar
            ->select(['id', 'slug', 'category_id', 'county_id', 'updated_at', 'created_at'])
            ->where('status', 'active')
            ->orderBy('id');

        if (in_array(SoftDeletes::class, class_uses_recursive(Service::class))) {
            $q->withoutTrashed();
        } else {
            $q->whereNull('deleted_at');
        }

        $q->chunkById(1000, function ($services) use (&$urls) {
            foreach ($services as $service) {
                // Dacă cumva lipsește categoria sau județul, sărim peste (evităm erori)
                if (!$service->category || !$service->county) {
                    continue;
                }

                // URL CORE: categorie/judet/slug-id
                // Construim manual URL-ul pentru siguranță și performanță
                $correctUrl = url(
                    $service->category->slug . '/' . 
                    $service->county->slug . '/' . 
                    $service->slug . '-' . $service->id
                );

                $urls[] = [
                    'loc'        => $correctUrl,
                    'lastmod'    => ($service->updated_at ?? $service->created_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority'   => '0.7',
                ];
            }
        });

        return view('sitemap', compact('urls'))->render();
    }
}