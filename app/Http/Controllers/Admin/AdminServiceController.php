<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminServiceController extends Controller
{
    // LISTA DE ANUNȚURI
    public function index()
    {
        $services = Service::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    // ==========================================================
    // ȘTERGERE ANUNȚ INDIVIDUAL
    // ==========================================================
    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        // 1. Ștergem pozele fizice de pe server
        $this->deleteImages($service);

        // 2. Ștergem anunțul din baza de date
        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Anunțul și imaginile au fost șterse.');
    }

    // ==========================================================
    // ACTIVARE / DEZACTIVARE UN ANUNȚ
    // ==========================================================
    public function toggle($id)
    {
        $service = Service::findOrFail($id);

        // Inversăm is_active
        $service->is_active = !$service->is_active;

        // Sincronizăm și coloana STATUS
        $service->status = $service->is_active ? 'active' : 'pending';

        $service->save();

        return back()->with('success', 'Status actualizat.');
    }

    // ==========================================================
    // BULK ACTION — activate / deactivate / delete
    // ==========================================================
    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = (array) $request->ids; 

        if (!$ids || count($ids) === 0) {
            return back()->with('error', 'Selectează cel puțin un anunț.');
        }

        switch ($action) {

            // --------------------
            // ȘTERGERE ÎN MASĂ
            // --------------------
            case 'delete':
                
                // Luăm anunțurile unul câte unul pentru a le șterge pozele
                $services = Service::whereIn('id', $ids)->get();

                foreach ($services as $service) {
                    // Ștergem pozele fizice
                    $this->deleteImages($service);
                    // Ștergem din DB
                    $service->delete();
                }

                return back()->with('success', 'Anunțurile selectate au fost șterse definitiv.');

            // --------------------
            // ACTIVARE ÎN MASĂ
            // --------------------
            case 'activate':

                Service::whereIn('id', $ids)->update([
                    'is_active' => 1,
                    'status' => 'active'
                ]);

                return back()->with('success', 'Anunțurile selectate au fost activate.');

            // --------------------
            // DEZACTIVARE ÎN MASĂ
            // --------------------
            case 'deactivate':

                Service::whereIn('id', $ids)->update([
                    'is_active' => 0,
                    'status' => 'pending'
                ]);

                return back()->with('success', 'Anunțurile selectate au fost dezactivate.');

            // --------------------
            default:
                return back()->with('error', 'Acțiune invalidă.');
        }
    }

    // ==========================================================
    // HELPER: Funcție pentru ștergerea fizică a imaginilor
    // ==========================================================
    private function deleteImages(Service $service)
    {
        // Verificăm dacă anunțul are imagini
        if (!empty($service->images) && is_array($service->images)) {
            foreach ($service->images as $imageName) {
                // Construim calea absolută către fișier
                // Aici presupunem că pozele sunt în storage/app/public/services/
                $path = storage_path('app/public/services/' . $imageName);

                // Dacă fișierul există, îl ștergem
                if (file_exists($path)) {
                    @unlink($path); // @ ascunde erorile dacă fișierul e blocat
                }
            }
        }
    }
}