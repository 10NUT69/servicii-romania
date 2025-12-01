<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Service;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite (adauga / scoate).
     */
    public function toggle(Request $request)
    {
        // Daca nu e logat, nu salvam in DB
        if (!auth()->check()) {
            return response()->json(['status' => 'guest'], 401);
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $serviceId = $request->service_id;

        $fav = Favorite::where('user_id', auth()->id())
                       ->where('service_id', $serviceId)
                       ->first();

        if ($fav) {
            $fav->delete();
            return response()->json(['status' => 'removed']);
        }

        Favorite::create([
            'user_id'    => auth()->id(),
            'service_id' => $serviceId,
        ]);

        return response()->json(['status' => 'added']);
    }

  
}