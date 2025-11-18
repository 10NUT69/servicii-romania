<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * AJAX — Update Name, Email, Password
     */
    public function ajaxUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:120',
            'password' => 'nullable|min:6'
        ]);

        // Update name + email
        $user->name  = $request->name;
        $user->email = $request->email;

        // Update password ONLY if provided
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'status'  => 'ok',
            'message' => 'Profil actualizat cu succes!'
        ]);
    }
}
