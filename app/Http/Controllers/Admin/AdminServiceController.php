<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class AdminServiceController extends Controller
{
    // ==========================================================
    // 1. LISTA ANUNȚURI
    // ==========================================================
    public function index(Request $request)
    {
        // withTrashed() este obligatoriu ca să vedem și ce e în "Coș"
        $query = Service::withTrashed()->with(['user', 'category']);

        // Căutare
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('id', $s)
                  ->orWhereHas('user', function($u) use ($s) {
                      $u->where('email', 'like', "%$s%");
                  });
            });
        }

        // Filtrare Status
        if ($request->filled('status')) {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } elseif ($request->status === 'active') {
                $query->where('status', 'active')->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->where('status', '!=', 'active')->whereNull('deleted_at');
            }
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.services.index', compact('services'));
    }
	// ==========================================================
// 1B. EDIT FORM (Admin) - titlu, descriere, categorie
// ==========================================================
public function edit($id)
{
    $service = Service::withTrashed()
        ->with(['category', 'county', 'user'])
        ->findOrFail($id);

    // Luăm categorii pentru dropdown
    $categories = Category::orderBy('sort_order', 'asc')->get();

    return view('admin.services.edit', compact('service', 'categories'));
}

// ==========================================================
// 1C. UPDATE (Admin) - titlu, descriere, categorie
// ==========================================================
public function update(Request $request, $id)
{
    $service = Service::withTrashed()->findOrFail($id);

    $data = $request->validate([
        'title'       => 'required|string|max:255',
        'description' => 'required|string|max:10000',
        'category_id' => 'required|exists:categories,id',
    ]);

    // Update doar ce vrei tu (fără slug/status/is_active etc.)
    $service->title = $data['title'];
    $service->description = $data['description'];
    $service->category_id = $data['category_id'];
    $service->save();

    return back()->with('success', 'Anunț actualizat.');
}

// ==========================================================
// 1D. DELETE IMAGE (Admin) - șterge din DB + din server
// ==========================================================
public function deleteImage(Request $request, $id)
{
    $service = Service::withTrashed()->findOrFail($id);

    $request->validate([
        'image' => 'required|string',
    ]);

    $imageName = $request->input('image');

    $images = $service->images;
    if (is_string($images)) $images = json_decode($images, true);
    if (!is_array($images)) $images = [];

    // imaginea trebuie să existe în array
    if (!in_array($imageName, $images, true)) {
        return back()->with('error', 'Imaginea nu a fost găsită în acest anunț.');
    }

    // scoatem din array
    $images = array_values(array_filter($images, fn($img) => $img !== $imageName));

    // ștergem fizic fișierul (storage/app/public/services/)
    $path = storage_path("app/public/services/" . $imageName);
    if (file_exists($path)) {
        @unlink($path);
    }

    // salvăm în DB: dacă rămâne gol, punem null (ca la logica ta)
    $service->images = count($images) ? $images : null;
    $service->save();

    return back()->with('success', 'Imagine ștearsă.');
}

    // ==========================================================
    // 2. BULK ACTIONS (LOGICA PRINCIPALĂ CERUTĂ)
    // ==========================================================
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $rawIds = $request->input('ids'); // Vine ca string "1,2,5" din JS

        if (empty($rawIds)) {
            return back()->with('error', 'Nu ai selectat nimic.');
        }

        $ids = explode(',', $rawIds);

        // Luăm TOATE serviciile (inclusiv cele șterse)
        $services = Service::withTrashed()->whereIn('id', $ids)->get();
        $count = 0;

        foreach ($services as $service) {
            switch ($action) {
                
                // A. DEZACTIVEAZĂ (Doar status)
                case 'deactivate':
                    if (!$service->trashed()) {
                        $service->is_active = 0;
                        $service->status = 'pending';
                        $service->save();
                        $count++;
                    }
                    break;

                // B. ACTIVEAZĂ
                case 'activate':
                    if (!$service->trashed()) {
                        $service->is_active = 1;
                        $service->status = 'active';
                        $service->save();
                        $count++;
                    }
                    break;

                // C. SOFT DELETE (Mută în Coș + Șterge Poze)
                case 'soft_delete':
                    if (!$service->trashed()) {
                        $this->deleteImages($service); // Ștergem pozele fizic
                        $service->images = null;       // Golim coloana images
                        $service->save();
                        $service->delete();            // Soft Delete (deleted_at)
                        $count++;
                    }
                    break;

                // D. FORCE DELETE (Șterge Definitiv)
                case 'force_delete':
                    $this->deleteImages($service);     // Ștergem pozele (safety check)
                    $service->forceDelete();           // Ștergem rândul din SQL
                    $count++;
                    break;
            }
        }

        return back()->with('success', "Acțiunea '{$action}' a fost aplicată pe {$count} anunțuri.");
    }

    // ==========================================================
    // 3. ȘTERGERE INDIVIDUALĂ (Butoanele de pe rând)
    // ==========================================================
    public function destroy(Request $request, $id)
    {
        $service = Service::withTrashed()->findOrFail($id);
        
        // Verificăm dacă s-a cerut explicit Force Delete (din butonul roșu plin)
        $force = $request->has('force') && $request->force == '1';

        // CAZ: FORCE DELETE (Definitiv)
        if ($force || $service->trashed()) {
            $this->deleteImages($service);
            $service->forceDelete();
            return back()->with('success', 'Anunț șters DEFINITIV.');
        }

        // CAZ: SOFT DELETE (Coș)
        $this->deleteImages($service);
        $service->images = null;
        $service->save();
        $service->delete();

        return back()->with('success', 'Anunț mutat în coș.');
    }

    // ==========================================================
    // 4. TOGGLE INDIVIDUAL
    // ==========================================================
    public function toggle($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->status = $service->is_active ? 'active' : 'pending';
        $service->save();

        return back()->with('success', 'Status actualizat.');
    }

    // ==========================================================
    // HELPER IMAGINI
    // ==========================================================
    private function deleteImages($service)
    {
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        
        if (is_array($images)) {
            foreach ($images as $img) {
                if (empty($img)) continue;
                $path = storage_path("app/public/services/" . $img);
                if (file_exists($path)) @unlink($path);
            }
        }
    }
}