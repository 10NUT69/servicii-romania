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

    // CREATE
    public function create()
    {
        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
        ]);
    }

    // STORE (ADĂUGARE)
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

        $userId = null;
        if (Auth::check()) {
            $userId = Auth::id();
        } elseif ($request->filled('email') && $request->filled('password')) {
            // Logică creare user (identică cu ce aveai)
            if ($request->filled('name')) {
                $finalName = $request->name;
            } else {
                $rawName = explode('@', $request->email)[0];
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $rawName);
                $finalName = ucfirst(strtolower($cleanName));
            }
            $user = User::create([
                'name'     => $finalName,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            Auth::login($user);
            $userId = $user->id;
        }

        $service = new Service();
        $service->user_id     = $userId;
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

        // =========================================================
        // 🔥 ARTIFICIU SEO: GENERARE LINK (SLUG) COMPLEX 🔥
        // Structură: "primele-3-cuvinte-titlu-categoria-judet"
        // =========================================================
        
        // 1. Luăm primele 3 cuvinte din titlu
        $words = Str::of($validated['title'])->explode(' ')->take(3)->implode(' ');
        
        // 2. Luăm numele categoriei și al județului
        $categoryName = Category::find($validated['category_id'])->name;
        $countyName = County::find($validated['county_id'])->name;

        // 3. Generăm slug-ul de bază
        $seoString = $words . ' ' . $categoryName . ' ' . $countyName;
        $baseSlug = Str::slug($seoString);
        
        // 4. Verificăm unicitatea
        $uniqueSlug = $baseSlug;
        $i = 2;
        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }
        $service->slug = $uniqueSlug;
        
        $service->status = 'active';
        
        // --- LOGICĂ IMAGINI (Rămâne neschimbată) ---
        $savedImages = [];
        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            // Folosim același string SEO și pentru numele imaginilor
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

        return redirect()->route('services.show', [$service->id, $service->slug])
                         ->with('success', 'Anunțul a fost publicat!');
    }
    // SHOW
    public function show($id, $slug)
    {
        $service = Service::where('id', $id)->where('slug', $slug)->firstOrFail();
        $service->increment('views');
        return view('services.show', compact('service'));
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

        // Păstrăm imaginile vechi
        $finalImages = $service->images;
        // Asigurăm compatibilitatea dacă vine ca string sau array
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
            // Reindexăm array-ul
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
        
        // Decodare sigură
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) $images = [];

        // Ștergem fișierele fizice
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