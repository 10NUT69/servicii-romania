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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    // INDEX (ACUM SUPORTĂ AJAX INFINITE SCROLL ȘI FILTRARE INSTANT)
    public function index(Request $request)
    {
        // 1. Configurare Paginare Variabilă
        $page = $request->get('page', 1);
        $perPageFirst = 10; // Primele 10 anunțuri
        $perPageNext = 8;   // Următoarele seturi de 8

        if ($page == 1) {
            $limit = $perPageFirst;
            $offset = 0;
        } else {
            $limit = $perPageNext;
            // Matematica: Primele 10 + (Pagina curentă - 2) * 8
            $offset = $perPageFirst + (($page - 2) * $perPageNext);
        }

        $query = Service::where('status', 'active');

        // 2. FILTRE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->filled('county')) {
            $query->where('county_id', $request->county);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // 3. Execuție Query
        // Calculăm totalul pentru a ști dacă mai avem pagini (hasMore)
        $totalCount = $query->count(); 
        
        $services = $query
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        // Calculăm dacă mai există rezultate după acest batch
        $loadedSoFar = $offset + $services->count();
        $hasMore = $loadedSoFar < $totalCount;

        // 4. RĂSPUNS AJAX
        if ($request->ajax()) {
            $html = view('services.partials.service_cards', ['services' => $services])->render();

            return response()->json([
                'html'        => $html,
                'hasMore'     => $hasMore,
                'total'       => $totalCount,
                // 🔥 pentru empty state din JS
                'loadedCount' => $services->count(),
            ]);
        }

        // 5. Răspuns Initial (Blade)
        return view('services.index', [
            'services'        => $services,
            'counties'        => County::all(),
            'categories'      => Category::orderBy('sort_order', 'asc')->get(),
            'hasMore'         => $hasMore,
            // pentru paginile SEO (categorie / categorie + județ)
            'currentCategory' => $request->attributes->get('currentCategory'),
            'currentCounty'   => $request->attributes->get('currentCounty'),
        ]);
    }

    // INDEX LOCATION – /{category} și /{category}/{county}
    public function indexLocation(Request $request, $categorySlug, $countySlug = null)
    {
        // 1. Găsim categoria după slug (ex: electrician)
        $category = Category::where('slug', $categorySlug)->firstOrFail();

        // 2. Dacă există județ în URL, îl găsim după slug (ex: arges)
        $county = null;
        if ($countySlug) {
            $county = County::where('slug', $countySlug)->firstOrFail();
        }

        // 3. Injectăm în request ID-urile ca și cum ar fi venit din filtre
        $request->merge([
            'category' => $category->id,
            'county'   => $county ? $county->id : null,
        ]);

        // 4. Setăm în request "currentCategory / currentCounty" pentru view
        $request->attributes->set('currentCategory', $category);
        if ($county) {
            $request->attributes->set('currentCounty', $county);
        }

        // 5. Refolosim toată logica din index() (paginare, AJAX etc.)
        return $this->index($request);
    }

    // SHOW (MODIFICAT PENTRU SOFT DELETE SEO)
    public function show($category, $county, $slug, $id)
    {
        // 1. Folosim withTrashed() ca să găsim și anunțurile șterse (SEO)
        $service = Service::withTrashed()->with(['category', 'county', 'user'])->findOrFail($id);

        $correctSlug = $service->smart_slug;
        if ($slug !== $correctSlug) {
            return redirect()->to($service->public_url, 301);
        }

        // 2. Incrementăm view-uri DOAR dacă anunțul NU este șters
        if (!$service->trashed()) {
            $service->increment('views');
        }

        return view('services.show', compact('service'));
    }

    // CREATE (ORIGINAL)
    public function create()
    {
        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
        ]);
    }

    // STORE (ADAPTAT - QUEUE PUBLISH)
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
            $rules['email'] = 'required|email|unique:users,email|max:120';
            $rules['password'] = 'required|string|min:6';
        }

        $messages = [
            'images.*.max' => 'Una dintre imagini este prea mare (max 15MB).',
            'images.*.uploaded' => 'Eroare la încărcare server.',
        ];

        $validated = $request->validate($rules, $messages);

        // 1. CALCULARE NUME
        $calculatedName = $request->input('name');
        if (empty($calculatedName) && $request->filled('email')) {
            $emailParts = explode('@', $request->input('email'));
            $rawName = $emailParts[0];
            $nameParts = preg_split('/[\.\_\-\d]/', $rawName);
            if (!empty($nameParts[0])) {
                $calculatedName = ucfirst($nameParts[0]);
            } else {
                $calculatedName = ucfirst(preg_replace('/[^A-Za-z0-9]/', '', $rawName));
            }
        }
        if (empty($calculatedName)) {
            $calculatedName = 'Vizitator';
        }

        // 2. LOGICA USER
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

        // 3. SALVARE SERVICIU (fără procesare imagini în request)
        $service = new Service();
        $service->user_id = $userId;
        if (!$userId) {
            $service->contact_name = $calculatedName;
        }

        $service->title       = $validated['title'];
        $service->description = $validated['description'];
        $service->category_id = $validated['category_id'];
        $service->county_id   = $validated['county_id'];
        $service->phone       = $validated['phone'];
        $service->price_value = $request->price_value;
        $service->price_type  = $validated['price_type'];
        $service->currency    = $validated['currency'];

        if ($request->filled('email')) {
            $service->email = $request->email;
        }

        // slug logic (păstrată 1:1)
        $words = Str::of($validated['title'])->explode(' ')->take(5)->implode(' ');
        $baseSlug = Str::slug($words);
        $uniqueSlug = $baseSlug;
        $i = 2;
        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }
        $service->slug = $uniqueSlug;

        // NOUA LOGICĂ: intră în așteptare
        $service->status = 'pending';
        $service->queued_at = now();

        // încă nu avem imaginile finale
        $service->images = [];
        $service->images_tmp = [];
        $service->fail_reason = null;

        // salvăm ca să avem ID pentru folderul tmp
        $service->save();

        // 4. Upload RAW în tmp (fără Intervention)
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

        // 5. Trimitem job-ul care procesează imaginile și activează anunțul
        PublishServiceJob::dispatch($service->id)->onQueue('services');

        // 6. Redirect + mesaj (conform noua logică)
        if (Auth::check()) {
            return redirect('/contul-meu?tab=anunturi')
                ->with('success', '✅ Anunțul tău a fost trimis spre procesare și va apărea în câteva momente.');
        }

        return redirect('/')
            ->with('success', '✅ Anunțul tău a fost trimis spre procesare și va apărea în câteva momente.');
    }

    // EDIT (ORIGINAL)
    public function edit($id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        return view('services.edit', [
            'service'    => $service,
            'categories' => Category::all(),
            'counties'   => County::all(),
        ]);
    }

    // UPDATE (ORIGINAL - STRICT CODUL TAU)
    public function update(Request $request, $id)
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

    // Păstrăm imaginile existente (nu procesăm aici)
    unset($validated['images']);
    $service->fill($validated);

    // Dacă s-a urcat email, îl păstrăm (similar cu store)
    if ($request->filled('email')) {
        $service->email = $request->email;
    }

    // NU schimbăm slug aici (ca în codul tău original)

    // Dacă sunt imagini noi -> le urcăm RAW în tmp + job
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

        // salvăm tmp list (o să fie procesată în job)
        $service->images_tmp  = $tmpNames;
        $service->queued_at   = now();
        $service->fail_reason = null;

        $service->save();

        // procesăm asincron (poze noi se adaugă la cele existente până la max 10)
        ProcessServiceImagesJob::dispatch($service->id)->onQueue('services');

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', '✅ Modificările au fost salvate. Imaginile se procesează și vor apărea în câteva momente.');
    }

    // fără imagini -> update instant (rapid)
    $service->save();

    return redirect('/contul-meu?tab=anunturi')
        ->with('success', 'Modificat cu succes!');
}


    // DELETE IMAGE (ORIGINAL)
    public function deleteImage(Request $request, $id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $imageName = $request->input('image');

        $currentImages = $service->images;
        if (is_string($currentImages)) $currentImages = json_decode($currentImages, true);
        if (!is_array($currentImages)) $currentImages = [];

        $key = array_search($imageName, $currentImages);

        if ($key !== false) {
            $path = storage_path('app/public/services/' . $imageName);
            if (file_exists($path)) unlink($path);

            unset($currentImages[$key]);
            $service->images = array_values($currentImages);
            $service->save();

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // DESTROY (REPARAT - FĂRĂ STATUS UPDATE)
    public function destroy($id)
    {
        try {
            $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            // 1. Ștergem imaginile fizic
            $images = $service->images; // Laravel face cast automat la array datorită Modelului

            // Măsură de siguranță extra: dacă e null sau string ciudat
            if (is_null($images)) $images = [];
            elseif (is_string($images)) $images = json_decode($images, true) ?? [];

            if (is_array($images)) {
                foreach ($images as $img) {
                    if (empty($img)) continue;
                    $path = storage_path("app/public/services/" . $img);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            // 2. Doar golim imaginile din DB (statusul îl lăsăm așa cum e)
            $service->images = null;
            $service->save();

            // 3. Executăm Soft Delete (Asta e tot ce contează pentru a ascunde anunțul)
            $service->delete();

            return response()->json(['status' => 'deleted']);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // RENEW (ORIGINAL)
    public function renew($id)
    {
        try {
            $service = Service::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Actualizăm data creării pentru a urca anunțul primul în listă
            $service->created_at = now();

            // Opțional: Dacă anunțul expirase sau era inactiv, îl reactivăm
            if ($service->status !== 'active') {
                $service->status = 'active';
            }

            $service->save();

            // Returnăm JSON pentru Javascript-ul din frontend
            return response()->json([
                'status'  => 'success',
                'message' => 'Anunțul a fost reactualizat cu succes!'
            ]);

        } catch (\Exception $e) {
            // În caz de eroare
            return response()->json([
                'status'  => 'error',
                'message' => 'Eroare la actualizare.'
            ], 500);
        }
    }
}
