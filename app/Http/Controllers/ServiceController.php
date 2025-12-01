<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use App\Models\County;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    // INDEX
    public function index(Request $request)
    {
        $query = Service::where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

        return view('services.index', [
            'services'   => $query->orderBy('created_at', 'desc')->paginate(24)->withQueryString(),
            'counties'   => County::all(),
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
        ]);
    }

    // INDEX LOCATION
    public function indexLocation($categorySlug, $countySlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $county   = County::where('slug', $countySlug)->firstOrFail();

        $services = Service::where('status', 'active')
            ->where('category_id', $category->id)
            ->where('county_id', $county->id)
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('services.index', [
            'services'    => $services,
            'counties'    => County::all(),
            'categories'  => Category::orderBy('sort_order', 'asc')->get(),
            'currentCategory' => $category,
            'currentCounty'   => $county,
        ]);
    }

    // SHOW
    public function show($category, $county, $slug, $id)
    {
        $service = Service::with(['category', 'county', 'user'])->findOrFail($id);

        $correctSlug = $service->smart_slug;
        if ($slug !== $correctSlug) {
            return redirect()->to($service->public_url, 301);
        }

        $service->increment('views');
        return view('services.show', compact('service'));
    }

    // CREATE
    public function create()
    {
        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
        ]);
    }

    // STORE (LOGICA NUME MODIFICATĂ)
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

        // =========================================================
        // 1. CALCULARE NUME (LOGICA CERUTĂ)
        // =========================================================
        
        // Pasul A: Verificăm dacă a introdus manual un nume
        $calculatedName = $request->input('name'); 

        // Pasul B: Dacă NU a introdus nume, prelucrăm emailul
        if (empty($calculatedName) && $request->filled('email')) {
            $emailParts = explode('@', $request->input('email'));
            $rawName = $emailParts[0]; // ex: ion.popescu sau maria_123

            // Spargem textul la prima apariție a unui caracter special (., _, -) sau cifră
            // Regex: [\.\_\-\d] înseamnă punct, underscore, cratimă sau cifră
            $nameParts = preg_split('/[\.\_\-\d]/', $rawName);

            // Luăm prima parte (ex: din "ion.popescu" luăm "ion")
            if (!empty($nameParts[0])) {
                $calculatedName = ucfirst($nameParts[0]);
            } else {
                // Caz de rezervă: dacă emailul începe cu cifră (ex: 123ion), luăm tot și curățăm
                $calculatedName = ucfirst(preg_replace('/[^A-Za-z0-9]/', '', $rawName));
            }
        }

        // Fallback final
        if (empty($calculatedName)) {
            $calculatedName = 'Vizitator';
        }

        // =========================================================
        // 2. LOGICA USER
        // =========================================================
        $userId = null;

        if (Auth::check()) {
            $userId = Auth::id();
        } elseif ($request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name'      => $calculatedName, // Salvăm numele curat și în User
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
            ]);
            Auth::login($user);
            $userId = $user->id;
        }

        // =========================================================
        // 3. SALVARE SERVICIU
        // =========================================================
        $service = new Service();
        $service->user_id     = $userId;
        
        // Dacă e anonim, salvăm numele în contact_name
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

        $words = Str::of($validated['title'])->explode(' ')->take(5)->implode(' ');
        $baseSlug = Str::slug($words);
        $uniqueSlug = $baseSlug;
        $i = 2;
        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }
        $service->slug = $uniqueSlug;
        $service->status = 'active';
        
        $savedImages = [];
        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            $seoBaseName = $baseSlug; 

            foreach ($request->file('images') as $image) {
                if (count($savedImages) >= 10) break;
                
                $name = $seoBaseName . '-' . Str::random(6) . '.jpg';
                $path = storage_path('app/public/services/' . $name);
                
                if (!file_exists(dirname($path))) mkdir(dirname($path), 0755, true);

                $manager->read($image->getRealPath())
                        ->scaleDown(1600)
                        ->toJpeg(75)
                        ->save($path);
                        
                $savedImages[] = $name;
            }
        }

        $service->images = $savedImages; 
        $service->save();

        return redirect()->to($service->public_url)
                         ->with('success', 'Anunțul a fost publicat!');
    }

    // EDIT
    public function edit($id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        return view('services.edit', [
            'service'    => $service,
            'categories' => Category::all(),
            'counties'   => County::all(),
        ]);
    }

    // UPDATE
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

        $finalImages = $service->images;
        if (is_string($finalImages)) $finalImages = json_decode($finalImages, true);
        if (!is_array($finalImages)) $finalImages = [];

        unset($validated['images']);
        $service->fill($validated);

        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            $countyName = County::find($validated['county_id'])->name ?? 'romania';
            $seoBaseName = Str::slug($validated['title'] . '-' . $countyName);

            foreach ($request->file('images') as $image) {
                if (count($finalImages) >= 10) break;

                $name = $seoBaseName . '-' . Str::random(6) . '.jpg';
                $path = storage_path('app/public/services/' . $name);

                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }

                $manager->read($image->getRealPath())
                    ->scaleDown(1600)
                    ->toJpeg(75)
                    ->save($path);

                $finalImages[] = $name;
            }
        }

        $service->images = $finalImages;
        $service->save();

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Modificat cu succes!');
    }

    // DELETE IMAGE
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

    // DELETE SERVICE
    public function destroy($id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) $images = [];

        foreach ($images as $img) {
            if (!$img) continue;
            $path = storage_path("app/public/services/" . $img);
            try {
                if (file_exists($path)) @unlink($path); 
            } catch (\Throwable $e) {
                Log::error("Delete error: " . $e->getMessage());
            }
        }
        
        $service->delete();
        return response()->json(['status' => 'deleted']);
    }

    // RENEW
    public function renew($id)
    {
        $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $service->status = 'active';
        $service->created_at = now();
        $service->save();
        return back()->with('success', 'Reînnoit!');
    }
}