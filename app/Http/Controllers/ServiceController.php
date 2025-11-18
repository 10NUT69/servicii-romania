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

class ServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTĂ ANUNȚURI
    |--------------------------------------------------------------------------
    */
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

        return view('services.index', [
            'services' => $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString(),
            'counties' => County::all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (CU CREARE AUTOMATĂ CONT + SLUG)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:120',
            'password'    => 'nullable|string|min:6',
            'images.*'    => 'image|max:4096',
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREARE AUTOMATĂ CONT DACĂ USERUL NU ESTE LOGAT
        |--------------------------------------------------------------------------
        */
        $user = auth()->user();

        if (!$user && $request->email) {

            if ($request->password) {

                $existing = User::where('email', $request->email)->first();

                if ($existing) {

                    if (Hash::check($request->password, $existing->password)) {
                        Auth::login($existing);
                        $user = $existing;

                    } else {
                        return back()->withErrors([
                            'email' => 'Există deja un cont cu acest email, dar parola introdusă este greșită.'
                        ])->withInput();
                    }

                } else {
                    // Generăm numele din email (ex: VladPopa23@gmail.com → Vladpopa23)
                    $rawName = explode('@', $request->email)[0];
                    $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $rawName);
                    $finalName = ucfirst(strtolower($cleanName));

                    $user = User::create([
                        'name'     => $finalName,
                        'email'    => $request->email,
                        'password' => Hash::make($request->password),
                    ]);

                    Auth::login($user);
                }
            }
        }

        // Acum $user este sigur setat dacă e logat sau abia creat
        if (!$user) {
            return back()->withErrors([
                'email' => 'Pentru a publica un anunț, trebuie să adaugi email + parolă pentru a crea un cont.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREARE ANUNȚ
        |--------------------------------------------------------------------------
        */
        $service = new Service();
        $service->fill($validated);
        $service->user_id = $user->id;

        // SLUG
        $categorySlug = Str::slug(Category::find($validated['category_id'])->name);
        $countySlug   = Str::slug(County::find($validated['county_id'])->name);
        $titleSlug    = Str::slug($validated['title']);

        $baseSlug = "$categorySlug-$countySlug-$titleSlug";
        $uniqueSlug = $baseSlug;
        $i = 2;

        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }

        $service->slug = $uniqueSlug;
        $service->expires_at = now()->addDays(30);
        $service->status = 'active';
        $service->save();

        /*
        |--------------------------------------------------------------------------
        | IMAGINI
        |--------------------------------------------------------------------------
        */
        $manager = new ImageManager(new Driver());
        $savedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if (count($savedImages) >= 10) break;

                $name = uniqid() . '.jpg';
                $path = storage_path('app/public/services/' . $name);

                $manager->read($image->getRealPath())
                    ->scaleDown(1600)
                    ->toJpeg(75)
                    ->save($path);

                $savedImages[] = $name;
            }
        }

        $service->images = $savedImages;
        $service->save();

        return redirect()->route('services.show', [$service->id, $service->slug]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW (ID + SLUG)
    |--------------------------------------------------------------------------
    */
    public function show($id, $slug)
    {
        $service = Service::where('id', $id)
            ->where('slug', $slug)
            ->firstOrFail();

        $service->increment('views');

        return view('services.show', compact('service'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('services.edit', [
            'service'    => $service,
            'categories' => Category::all(),
            'counties'   => County::all(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
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
        ]);

        $service->update($validated);

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Modificat cu succes!');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($service->images) {
            foreach ($service->images as $img) {
                $file = storage_path("app/public/services/$img");
                if (file_exists($file)) unlink($file);
            }
        }

        $service->delete();

        return response()->json([
            'status' => 'deleted'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RENEW
    |--------------------------------------------------------------------------
    */
    public function renew($id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $service->status = 'active';
        $service->save();

        return back()->with('success', 'Reînnoit cu succes!');
    }
}
