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

        // Ștergere imagini
        if ($service->images) {
            foreach ($service->images as $img) {
                if (Storage::exists($img)) {
                    Storage::delete($img);
                }
            }
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Anunțul a fost șters.');
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
        $ids = (array) $request->ids; // convertim în array chiar dacă e doar un ID

        if (!$ids || count($ids) === 0) {
            return back()->with('error', 'Selectează cel puțin un anunț.');
        }

        switch ($action) {

            // --------------------
            // ȘTERGERE ÎN MASĂ
            // --------------------
            case 'delete':

                foreach ($ids as $id) {
                    $service = Service::find($id);
                    if (!$service) continue;

                    // Ștergere imagini
                    if ($service->images) {
                        foreach ($service->images as $img) {
                            if (Storage::exists($img)) {
                                Storage::delete($img);
                            }
                        }
                    }

                    $service->delete();
                }

                return back()->with('success', 'Anunțurile selectate au fost șterse.');

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
}
