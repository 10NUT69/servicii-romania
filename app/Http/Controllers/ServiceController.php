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
    // 1. Paginare variabilă (Logica ta originală)
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

    // 2. Query de bază
    $query = Service::where('status', 'active');

    // Variabilă pentru a ști dacă sortăm după relevanță sau dată
    $isSearch = false; 

    // ... în interiorul metodei index ...

    // 3. CĂUTARE INTELIGENTĂ (FULL-TEXT + RELAȚII)
    if ($request->filled('search')) {
        $isSearch = true;
        $rawSearchTerm = $request->search; // Termenul original pentru LIKE (ex: "Bucuresti")

        // 1. Pregătim termenul pentru Full-Text (ex: "+Bucuresti*")
        $cleanTerm = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $rawSearchTerm);
        $words = explode(' ', $cleanTerm);
        $searchParts = [];
        foreach($words as $word) {
            if (strlen(trim($word)) > 2) {
                $searchParts[] = '+' . $word . '*';
            }
        }
        $fullTextQuery = implode(' ', $searchParts);

        // 2. Construim Query-ul Compus
        $query->where(function($q) use ($fullTextQuery, $rawSearchTerm) {
            
            // A. Căutare în Titlu și Descriere (Prioritară - Full Text)
            if (!empty($fullTextQuery)) {
                $q->whereRaw("MATCH(title, description) AGAINST(? IN BOOLEAN MODE)", [$fullTextQuery]);
            } else {
                // Fallback la LIKE dacă cuvântul e prea scurt
                $q->where('title', 'like', "%{$rawSearchTerm}%")
                  ->orWhere('description', 'like', "%{$rawSearchTerm}%");
            }

            // B. Căutare în Numele Categoriei (ex: caută "Electrician")
            $q->orWhereHas('category', function ($catQ) use ($rawSearchTerm) {
                $catQ->where('name', 'like', "%{$rawSearchTerm}%");
            });

            // C. Căutare în Numele Județului (ex: caută "Bucuresti")
            $q->orWhereHas('county', function ($countyQ) use ($rawSearchTerm) {
                $countyQ->where('name', 'like', "%{$rawSearchTerm}%");
            });
            
            // D. (Opțional) Căutare în Oraș (dacă ai coloana city în services)
            // $q->orWhere('city', 'like', "%{$rawSearchTerm}%");
        });

        // 3. Selectăm scorul de relevanță (Doar pentru partea de text)
        // Notă: Dacă găsește doar în Categorie, relevanța va fi 0, dar anunțul va apărea.
        if (!empty($fullTextQuery)) {
            $query->selectRaw("*, MATCH(title, description) AGAINST(? IN BOOLEAN MODE) as relevance", [$fullTextQuery]);
            // Ordonăm: întâi după relevanță text, apoi cele mai noi
            $query->orderByDesc('relevance')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

    } else {
        // Dacă nu se caută nimic, ordonare standard
        $isSearch = false;
        $query->orderBy('created_at', 'desc');
    }

    // 4. Filtru județ
    if ($request->filled('county')) {
        $query->where('county_id', $request->county);
    }

    // 5. Filtru categorie
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // 6. Total count (trebuie calculat înainte de limit/offset)
    // Notă: La full-text search complex, count() poate fi tricky, dar Laravel se descurcă de obicei.
    $totalCount = $query->count();

    // 7. Sortare și Paginare finală
    if ($isSearch) {
        // Dacă a căutat ceva, cele mai relevante apar primele
        $query->orderByDesc('relevance');
    } else {
        // Dacă navighează normal, cele mai noi apar primele
        $query->orderBy('created_at', 'desc');
    }

    $services = $query
        ->offset($offset)
        ->limit($limit)
        ->get();

    $loadedSoFar = $offset + $services->count();
    $hasMore     = $loadedSoFar < $totalCount;

    // 8. Context SEO
    $currentCategory = $request->attributes->get('currentCategory');
    $currentCounty   = $request->attributes->get('currentCounty');

    if (!$currentCategory && $request->filled('category')) {
        $currentCategory = Category::find($request->category);
    }
    if (!$currentCounty && $request->filled('county')) {
        $currentCounty = County::find($request->county);
    }

    // 9. RĂSPUNS AJAX
    if ($request->ajax() || $request->input('ajax') == 1) {
        $html = view('services.partials.service_cards', ['services' => $services])->render();

        return response()->json([
            'html'        => $html,
            'hasMore'     => $hasMore,
            'total'       => $totalCount,
            'loadedCount' => $services->count(),
        ]);
    }

    // 10. RĂSPUNS NORMAL
    return view('services.index', [
        'services'        => $services,
        'counties'        => County::orderBy('name')->get(),
        'categories'      => Category::orderBy('sort_order', 'asc')->get(),
        'hasMore'         => $hasMore,
        'currentCategory' => $currentCategory,
        'currentCounty'   => $currentCounty,
    ]);
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
