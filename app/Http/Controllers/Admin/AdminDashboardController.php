<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Visit;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Statistici de bază
        $userCount    = User::count();
        $serviceCount = Service::count();
        $totalVisits  = Visit::count();

        // Vizite pe perioade
        $todayVisits     = Visit::whereDate('created_at', now()->toDateString())->count();
        $yesterdayVisits = Visit::whereDate('created_at', now()->subDay()->toDateString())->count();
        $weekVisits      = Visit::where('created_at', '>=', now()->subDays(7))->count();
        $monthVisits     = Visit::where('created_at', '>=', now()->subDays(30))->count();

        // Sursa traficului
        $trafficSources = Visit::selectRaw("
            CASE 
                WHEN referer LIKE '%google%'   THEN 'Google'
                WHEN referer LIKE '%bing%'     THEN 'Bing'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer IS NULL OR referer = '' THEN 'Direct'
                ELSE 'Other'
            END as source,
            COUNT(*) as total
        ")
        ->groupBy('source')
        ->orderByDesc('total')
        ->get();

        // Vizite pe oră (grafic)
        $hourly = Visit::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Vizite pe zile (ultimele 7 zile)
        $daily = Visit::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Returnăm TOATE datele către view
        return view('admin.dashboard', [
            'userCount'       => $userCount,
            'serviceCount'    => $serviceCount,
            'totalVisits'     => $totalVisits,
            'todayVisits'     => $todayVisits,
            'yesterdayVisits' => $yesterdayVisits,
            'weekVisits'      => $weekVisits,
            'monthVisits'     => $monthVisits,
            'trafficSources'  => $trafficSources,
            'hourly'          => $hourly,
            'daily'           => $daily,
        ]);
    }
}
