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
        // Cache "sitemap.xml" timp de 1 zi (pentru performanță maximă pe i5)
        $xml = Cache::remember('sitemap.xml', now()->addDay(), function () {
            $urls = [];

            // 1. Pagina principală
            $urls[] = [
                'loc'        => url('/'),
                'lastmod'    => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ];

            // 2. Pagini statice (Asigură-te că rutele astea există în web.php!)
            // Dacă route('services.index') nu există, comentează linia ca să nu dea eroare 500
            if (\Route::has('services.index')) {
                 $urls[] = [
                    'loc' => route('services.index'), 
                    'lastmod' => now()->toAtomString(), 
                    'changefreq' => 'daily', 
                    'priority' => '0.9'
                ];
            }

            // 3. Categorii
            $categories = Category::all();
            foreach ($categories as $category) {
                $urls[] = [
                    'loc'        => route('category.index', ['category' => $category->slug]),
                    // Folosim created_at dacă updated_at e null
                    'lastmod'    => ($category->updated_at ?? $category->created_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }

            // 4. Servicii / Anunțuri
            // IMPORTANT: Luăm doar anunțurile ACTIVE (nu draft, nu processing)
            $services = Service::where('status', 'active')
                        ->whereNull('deleted_at')
                        ->latest()
                        ->get();

            foreach ($services as $service) {
                // Verificam daca modelul are atributul public_url, altfel construim manual
                $link = $service->public_url ?? url('/anunt/' . $service->slug);

                $urls[] = [
                    'loc'        => $link,
                    'lastmod'    => ($service->updated_at ?? $service->created_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.7',
                ];
            }

            // Rendăm view-ul și îl transformăm în string pentru a fi salvat în cache
            return view('sitemap', compact('urls'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}