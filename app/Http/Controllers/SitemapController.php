<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];

        // 1. Pagina principală
        $urls[] = [
            'loc'        => url('/'),
            'lastmod'    => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ];

        // 2. Exemple pagini statice (dacă ai)
        $staticPages = [
            ['loc' => route('services.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
            // ['loc' => route('about'), 'changefreq' => 'yearly', 'priority' => '0.4'],
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

        // 4. Anunțuri / servicii
        $services = Service::whereNull('deleted_at')->get(); // sau ce condiții ai tu

        foreach ($services as $service) {
            $urls[] = [
                'loc'        => $service->public_url, // tu deja îl ai în model :)
                'lastmod'    => optional($service->updated_at ?? $service->created_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
        }

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
