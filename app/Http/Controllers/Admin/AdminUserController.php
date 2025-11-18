<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    // LISTA UTILIZATORILOR
    public function index()
    {
        $users = User::withCount('services')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    // ACTIVARE / DEZACTIVARE USER
    public function toggle($id)
    {
        $user = User::findOrFail($id);

        // Protecție: nu îți poți dezactiva propriul cont
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Nu poți dezactiva propriul cont.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status utilizator actualizat.');
    }

    // ȘTERGERE USER + ANUNȚURI + POZE
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Protecție: nu poți să te ștergi singur :)
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Nu poți șterge propriul cont.');
        }

        // Ștergem toate anunțurile userului
        $services = Service::where('user_id', $user->id)->get();

        foreach ($services as $service) {

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

        // Ștergem utilizatorul
        $user->delete();

        return back()->with('success', 'Utilizatorul și toate anunțurile sale au fost șterse.');
    }
}
