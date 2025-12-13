<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use App\Models\County;
use App\Models\User;
use App\Jobs\PublishServiceJob;
use App\Jobs\ProcessServiceImagesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX – LISTA DE ANUNȚURI (HOME + AJAX + SEO)
    |--------------------------------------------------------------------------
    | - Rulată pe: /
    | - Folosită și de rutele SEO prin indexLocation()
    | - Suportă:
    |     - căutare text (search)
    |     - filtrare categorie (category_id)
    |     - filtrare județ (county_id)
    |     - infinite scroll AJAX (page + ajax=1)
    */
 public function index(Request $request)
    {
        // 1. Paginare
        $page         = (int) $request->get('page', 1);
        $perPageFirst = 10;
        $perPageNext  = 8;

        if ($page === 1) {
            $limit  = $perPageFirst;
            $offset = 0;
        } else {
            $limit  = $perPageNext;
            $offset = $perPageFirst + (($page - 2) * $perPageNext);
        }

        // 2. Query de bază (Optimizat cu 'with')
        $query = Service::with(['category', 'county'])->where('status', 'active');

        $hasRelevance = false; 

        // 3. CĂUTARE AVANSATĂ
        if ($request->filled('search')) {
            $rawInput = $request->search;

            // A. ELIMINARE DIACRITICE (Protecție anti-autocorrect telefon)
            // Transformăm "Încălzire" -> "Incalzire" ca să se potrivească cu regulile noastre
            $diacritics = ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'];
            $normalized = ['a', 'a', 'i', 's', 't', 'a', 'a', 'i', 's', 't'];
            $rawSearchTerm = str_replace($diacritics, $normalized, $rawInput);

            // B. Curățare caractere speciale
            $cleanTerm = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $rawSearchTerm);
            $words = explode(' ', $cleanTerm);
            
            $searchParts = []; 
            $rootsList   = []; 
            
            foreach($words as $word) {
                $word = trim($word);
                $wordLower = mb_strtolower($word);
                $len = mb_strlen($word);

                if ($len > 2) {
                    $root = $word;

                    // --- REGULI PENTRU CATEGORIILE TALE (SOLIDE) ---
                    // Scriem regulile fără diacritice, pentru că am curățat inputul mai sus
                    if (str_starts_with($wordLower, 'electr')) { $root = 'electr'; } 
                    elseif (str_starts_with($wordLower, 'instal')) { $root = 'instal'; }
                    elseif (str_starts_with($wordLower, 'constr')) { $root = 'constr'; }
                    elseif (str_starts_with($wordLower, 'amenaj')) { $root = 'amenaj'; }
                    elseif (str_starts_with($wordLower, 'acoperi')) { $root = 'acoperi'; }
                    elseif (str_starts_with($wordLower, 'parchet')) { $root = 'parchet'; }
                    elseif (str_starts_with($wordLower, 'termopan')) { $root = 'termopan'; }
                    elseif (str_starts_with($wordLower, 'frigo')) { $root = 'frigo'; }
                    elseif (str_starts_with($wordLower, 'curat')) { $root = 'curat'; }
                    elseif (str_starts_with($wordLower, 'mobil')) { $root = 'mobil'; }
                    elseif (str_starts_with($wordLower, 'faian')) { $root = 'faian'; }
                    elseif (str_starts_with($wordLower, 'zugrav')) { $root = 'zugrav'; }
                    elseif (str_starts_with($wordLower, 'monta')) { $root = 'monta'; } // montator, montaj
                    elseif (str_starts_with($wordLower, 'peisag')) { $root = 'peisag'; } // peisagistica
                    elseif (str_starts_with($wordLower, 'gradin')) { $root = 'gradin'; } // gradinarit
                    elseif (str_starts_with($wordLower, 'auto')) { $root = 'auto'; } // servicii auto
                    else {
                        // Fallback generic
                        if ($len > 5 && str_ends_with($wordLower, 'uri')) {
                            $root = mb_substr($word, 0, -3);
                        } elseif ($len > 4 && str_ends_with($wordLower, 'i')) {
                            $root = mb_substr($word, 0, -1);
                            if (str_ends_with(mb_strtolower($root), 'ien')) {
                                $root = mb_substr($root, 0, -3);
                            }
                        } elseif ($len > 5 && str_ends_with($wordLower, 'ele')) {
                            $root = mb_substr($word, 0, -3);
                        } elseif ($len > 8) {
                            $root = mb_substr($word, 0, 7);
                        }
                    }

                    if (mb_strlen($root) < 3) { $root = $word; }

                    $searchParts[] = '+' . $root . '*'; 
                    $rootsList[]   = $root;            
                }
            }
            
            $fullTextQuery = implode(' ', $searchParts);

            // --- CONSTRUCȚIA QUERY-ULUI ---
            $query->where(function($q) use ($fullTextQuery, $rawSearchTerm, $rootsList) {
                
                // 1. Căutare Full Text în Titlu/Descriere (Aici trimitem rădăcina: +electr*)
                if (!empty($fullTextQuery)) {
                    $q->whereRaw("MATCH(title, description) AGAINST(? IN BOOLEAN MODE)", [$fullTextQuery]);
                } else {
                    $q->where('title', 'like', "%{$rawSearchTerm}%")
                      ->orWhere('description', 'like', "%{$rawSearchTerm}%");
                }

                // 2. Căutare în Categorii/Județe (Folosind rădăcina curățată)
                if (!empty($rootsList)) {
                    $q->orWhereHas('category', function ($catQ) use ($rootsList) {
                        $catQ->where(function($subQ) use ($rootsList) {
                            foreach ($rootsList as $root) {
                                // Caută dacă numele categoriei conține rădăcina (ex: "electr" în "Electrician")
                                $subQ->orWhere('name', 'like', "%{$root}%");
                            }
                        });
                    });

                    $q->orWhereHas('county', function ($countyQ) use ($rootsList) {
                        $countyQ->where(function($subQ) use ($rootsList) {
                            foreach ($rootsList as $root) {
                                $subQ->orWhere('name', 'like', "%{$root}%");
                            }
                        });
                    });
                } else {
                    // Fallback
                     $q->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$rawSearchTerm}%"))
                       ->orWhereHas('county', fn($c) => $c->where('name', 'like', "%{$rawSearchTerm}%"));
                }
            });

            // Calcul Relevanță
            if (!empty($fullTextQuery)) {
                $query->selectRaw("*, MATCH(title, description) AGAINST(? IN BOOLEAN MODE) as relevance", [$fullTextQuery]);
                $hasRelevance = true;
            }
        }

        // 4. Filtre Standard
        if ($request->filled('county')) {
            $query->where('county_id', $request->county);
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // 5. Sortare
        $totalCount = $query->count();

        if ($hasRelevance) {
            $query->orderByDesc('relevance')->orderByDesc('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 6. Fetch
        $services = $query->offset($offset)->limit($limit)->get();
        $loadedSoFar = $offset + $services->count();
        $hasMore     = $loadedSoFar < $totalCount;

        // 7. View Data & Response
        if ($request->ajax() || $request->input('ajax') == 1) {
            $html = view('services.partials.service_cards', ['services' => $services])->render();
            return response()->json([
                'html'        => $html,
                'hasMore'     => $hasMore,
                'total'       => $totalCount,
                'loadedCount' => $services->count(),
            ]);
        }

        // Pentru load normal (SSR)
        // Optimizare: cache sau doar interogare simplă
        $categories = Category::orderBy('sort_order', 'asc')->get();
        $counties   = County::orderBy('name')->get();

        $currentCategory = $request->filled('category') ? $categories->firstWhere('id', $request->category) : null;
        $currentCounty   = $request->filled('county')   ? $counties->firstWhere('id', $request->county)   : null;

        return view('services.index', compact('services', 'categories', 'counties', 'hasMore', 'currentCategory', 'currentCounty'));
    }
    /*
    |--------------------------------------------------------------------------
    | INDEX LOCATION – RUTE SEO
    |--------------------------------------------------------------------------
    | /{category}             -> doar categorie (ex: /instalator)
    | /{category}/{county}    -> categorie + județ (ex: /instalator/botosani)
    |
    | Aici NU facem filtrarea efectivă. Doar:
    |  - găsim categoria + județul după slug
    |  - injectăm ID-urile în $request ca și cum ar veni din filtre
    |  - setăm currentCategory / currentCounty pentru view
    |  - apelăm index($request)
    */
    public function indexLocation(Request $request, string $categorySlug, ?string $countySlug = null)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        $county = null;
        if ($countySlug !== null) {
            $county = County::where('slug', $countySlug)->firstOrFail();
        }

        // Simulăm filtrele ca și cum ar fi trimise din formular
        $request->merge([
            'category' => $category->id,
            'county'   => $county ? $county->id : null,
        ]);

        // Trimitem în view și obiectele complete (pentru SEO, titluri etc.)
        $request->attributes->set('currentCategory', $category);
        if ($county) {
            $request->attributes->set('currentCounty', $county);
        }

        // Refolosim LOGICA din index()
        return $this->index($request);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW – PAGINA DE DETALIU ANUNȚ
    |--------------------------------------------------------------------------
    | RUTA: /{category}/{county}/{slug-smart}-{id}
    */
    public function show(string $categorySlug, string $countySlug, string $slug, int $id)
    {
        // Găsim inclusiv anunțurile șterse (pentru SEO / 301)
        $service = Service::withTrashed()
            ->with(['category', 'county', 'user'])
            ->findOrFail($id);

        // Dacă slug-ul din URL nu corespunde cu slug-ul actual -> redirect 301 la URL-ul canonic
        $correctSlug = $service->smart_slug;   // presupunem că ai accessor în Model
        if ($slug !== $correctSlug) {
            return redirect()->to($service->public_url, 301);
        }

        // Incrementăm view-uri doar pentru anunțuri active
        if (!$service->trashed()) {
            $service->increment('views');
        }

        return view('services.show', compact('service'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE – FORMULAR ADĂUGARE ANUNȚ
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::orderBy('name')->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE – SALVARE ANUNȚ (CU JOB PENTRU IMAGINI + PUBLICARE)
    |--------------------------------------------------------------------------
    | - Creează user dacă e vizitator (cu email + parolă)
    | - Salvează anunțul cu status "pending"
    | - Salvează imaginile RAW într-un folder temporar
    | - Pornește job-ul PublishServiceJob care:
    |       • procesează imaginile
    |       • actualizează statusul în "active"
    */
    public function store(Request $request)
    {
        $rules = [
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'phone'       => 'required|string|max:30',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'name'        => 'nullable|string|max:255',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',
        ];

        if (!Auth::check() && $request->filled('email') && $request->filled('password')) {
            $rules['email']    = 'required|email|unique:users,email|max:120';
            $rules['password'] = 'required|string|min:6';
        }

        $messages = [
            'images.*.max'      => 'Una dintre imagini este prea mare (max 15MB).',
            'images.*.uploaded' => 'Eroare la încărcare server.',
        ];

        $validated = $request->validate($rules, $messages);

        // 1. Calcul nume afișat (dacă nu a completat)
        $calculatedName = $request->input('name');
        if (empty($calculatedName) && $request->filled('email')) {
            $emailParts = explode('@', $request->input('email'));
            $rawName    = $emailParts[0];
            $nameParts  = preg_split('/[\.\_\-\d]/', $rawName);
            if (!empty($nameParts[0])) {
                $calculatedName = ucfirst($nameParts[0]);
            } else {
                $calculatedName = ucfirst(preg_replace('/[^A-Za-z0-9]/', '', $rawName));
            }
        }
        if (empty($calculatedName)) {
            $calculatedName = 'Vizitator';
        }

        // 2. User (logat / creare user nou)
        $userId = null;

        if (Auth::check()) {
            $userId = Auth::id();
        } elseif ($request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name'     => $calculatedName,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            Auth::login($user);
            $userId = $user->id;
        }

        // 3. Creăm anunțul
        $service            = new Service();
        $service->user_id   = $userId;
        $service->title     = $validated['title'];
        $service->description = $validated['description'];
        $service->category_id = $validated['category_id'];
        $service->county_id   = $validated['county_id'];
        $service->phone       = $validated['phone'];
        $service->price_value = $request->price_value;
        $service->price_type  = $validated['price_type'];
        $service->currency    = $validated['currency'];

        if (!$userId) {
            $service->contact_name = $calculatedName;
        }
        if ($request->filled('email')) {
            $service->email = $request->email;
        }

        // Slug: primele 5 cuvinte din titlu
        $words      = Str::of($validated['title'])->explode(' ')->take(5)->implode(' ');
        $baseSlug   = Str::slug($words);
        $uniqueSlug = $baseSlug;
        $i          = 2;
        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }
        $service->slug = $uniqueSlug;

        // Status + info pentru job
        $service->status      = 'pending';
        $service->queued_at   = now();
        $service->images      = [];
        $service->images_tmp  = [];
        $service->fail_reason = null;

        $service->save();

        // 4. Upload RAW în folder temporar
        $tmpNames = [];
        if ($request->hasFile('images')) {
            $tmpDir = storage_path("app/services-tmp/{$service->id}");
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            foreach ($request->file('images') as $image) {
                if (count($tmpNames) >= 10) break;

                $tmpName = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $image->move($tmpDir, $tmpName);
                $tmpNames[] = $tmpName;
            }
        }

        $service->images_tmp = $tmpNames;
        $service->save();

        // 5. Job-ul care procesează imaginile + activează anunțul
        PublishServiceJob::dispatch($service->id)->onQueue('services');

        // 6. Redirect
        if (Auth::check()) {
            return redirect('/contul-meu?tab=anunturi')
                ->with('success', '✅ Anunțul tău a fost trimis spre procesare și va apărea în câteva momente.');
        }

        return redirect('/')
            ->with('success', '✅ Anunțul tău a fost trimis spre procesare și va apărea în câteva momente.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT – FORMULAR EDITARE ANUNȚ
    |--------------------------------------------------------------------------
    */
    public function edit(int $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('services.edit', [
            'service'    => $service,
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::orderBy('name')->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE – EDITARE ANUNȚ
    |--------------------------------------------------------------------------
    | - Update instant pentru text & preț
    | - Dacă urcăm imagini noi:
    |      • se pun RAW în tmp
    |      • job-ul ProcessServiceImagesJob le procesează și le adaugă
    */
    public function update(Request $request, int $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:120',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',
        ]);

        // Nu procesăm imaginile aici
        unset($validated['images']);
        $service->fill($validated);

        if ($request->filled('email')) {
            $service->email = $request->email;
        }

        // Dacă avem imagini noi -> merg în tmp + job
        if ($request->hasFile('images')) {
            $tmpDir = storage_path("app/services-tmp/{$service->id}");
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $tmpNames = [];

            foreach ($request->file('images') as $image) {
                if (count($tmpNames) >= 10) break;

                $tmpName = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $image->move($tmpDir, $tmpName);
                $tmpNames[] = $tmpName;
            }

            $service->images_tmp  = $tmpNames;
            $service->queued_at   = now();
            $service->fail_reason = null;

            $service->save();

            ProcessServiceImagesJob::dispatch($service->id)->onQueue('services');

            return redirect('/contul-meu?tab=anunturi')
                ->with('success', '✅ Modificările au fost salvate. Imaginile se procesează și vor apărea în câteva momente.');
        }

        // Fără imagini noi -> doar salvezi
        $service->save();

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Modificat cu succes!');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE IMAGE – ȘTERGERE O SINGURĂ IMAGINE DIN ANUNȚ
    |--------------------------------------------------------------------------
    */
    public function deleteImage(Request $request, int $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $imageName = $request->input('image');

        $currentImages = $service->images;
        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true);
        }
        if (!is_array($currentImages)) {
            $currentImages = [];
        }

        $key = array_search($imageName, $currentImages, true);

        if ($key !== false) {
            $path = storage_path('app/public/services/' . $imageName);
            if (file_exists($path)) {
                @unlink($path);
            }

            unset($currentImages[$key]);
            $service->images = array_values($currentImages);
            $service->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY – ȘTERGERE ANUNȚ (SOFT DELETE + ȘTERGERE IMAGINI)
    |--------------------------------------------------------------------------
    */
    public function destroy(int $id)
    {
        try {
            $service = Service::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $images = $service->images;

            if (is_null($images)) {
                $images = [];
            } elseif (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }

            if (is_array($images)) {
                foreach ($images as $img) {
                    if (empty($img)) continue;
                    $path = storage_path("app/public/services/" . $img);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $service->images = null;
            $service->save();

            $service->delete(); // soft delete

            return response()->json(['status' => 'deleted']);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RENEW – REACTUALIZARE ANUNȚ (URCĂ ÎN LISTĂ)
    |--------------------------------------------------------------------------
    */
    public function renew(int $id)
    {
        try {
            $service = Service::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $service->created_at = now();

            if ($service->status !== 'active') {
                $service->status = 'active';
            }

            $service->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Anunțul a fost reactualizat cu succes!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Eroare la actualizare.',
            ], 500);
        }
    }
}
