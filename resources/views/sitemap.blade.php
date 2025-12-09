<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // cache "sitemap.xml" timp de 1 zi
        $xml = Cache::remember('sitemap.xml', now()->addDay(), function () {
            $urls = [];

            // 1. Pagina principală
            $urls[] = [
                'loc'        => url('/'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ];

            // 2. Pagini statice
            $staticPages = [
                ['loc' => route('services.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                // ['loc' => route('page.about'), 'changefreq' => 'yearly', 'priority' => '0.4'],
                // ['loc' => route('page.contact'), 'changefreq' => 'yearly', 'priority' => '0.4'],
            ];

            foreach ($staticPages as $page) {
                $urls[] = array_merge([
                    'lastmod' => now()->toAtomString(),
                ], $page);
            }

            // 3. Categorii
            $categories = Category::all();

            foreach ($categories as $category) {
                $urls[] = [
                    'loc'        => route('category.index', ['category' => $category->slug]),
                    'lastmod'    => optional($category->updated_at ?? $category->created_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            }

            // 4. Servicii
            $services = Service::whereNull('deleted_at')->get();

            foreach ($services as $service) {
                $urls[] = [
                    'loc'        => $service->public_url,
                    'lastmod'    => optional($service->updated_at ?? $service->created_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }

            // 🔥 rendăm view-ul O SINGURĂ DATĂ și salvăm XML-ul ca string în cache
            return view('sitemap', compact('urls'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
