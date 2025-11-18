<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $userCount = User::count();
        $serviceCount = Service::count();

        return view('admin.dashboard', compact('userCount', 'serviceCount'));
    }
}
