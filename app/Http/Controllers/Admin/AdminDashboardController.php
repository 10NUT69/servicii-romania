<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. KPI-uri Generale
        $userCount    = User::count();
        $serviceCount = Service::count();
        $totalVisits  = Visit::count();
        // Calculăm vizitatori unici (după IP)
        $uniqueVisitors = Visit::distinct('ip')->count('ip');

        // 2. Vizite pe perioade (KPI Cards)
        $todayVisits     = Visit::whereDate('created_at', now()->toDateString())->count();
        $yesterdayVisits = Visit::whereDate('created_at', now()->subDay()->toDateString())->count();
        
        // 3. Grafic Principal (Ultimele 30 zile - Line Chart)
        $dailyStats = Visit::selectRaw('DATE(created_at) as date, COUNT(*) as visits, COUNT(DISTINCT ip) as unique_ips')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 4. Top Pagini (Tabel)
        $topPages = Visit::select('url', DB::raw('count(*) as total'))
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 5. Top Țări (Listă)
        $topCountries = Visit::select('country', DB::raw('count(*) as total'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 6. Browsere (Doughnut)
        $browsers = Visit::select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 7. Dispozitive (Pie/List)
        $devices = Visit::select('device', DB::raw('count(*) as total'))
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        // 8. Ultimii 10 Vizitatori (Live Feed)
        $recentVisits = Visit::latest()->limit(10)->get();

        // 9. Surse Trafic (Referrers)
        // Curățăm refererii pentru a grupa google.com, google.ro etc. sub "Google"
        $trafficSources = Visit::selectRaw("
            CASE 
                WHEN referer LIKE '%google%' THEN 'Google Search'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer LIKE '%instagram%' THEN 'Instagram'
                WHEN referer LIKE '%bing%' THEN 'Bing'
                WHEN referer IS NULL OR referer = '' THEN 'Direct / Unknown'
                ELSE 'Other Sites'
            END as source,
            COUNT(*) as total
        ")
        ->groupBy('source')
        ->orderByDesc('total')
        ->limit(6)
        ->get();

        return view('admin.dashboard', compact(
            'userCount', 'serviceCount', 'totalVisits', 'uniqueVisitors',
            'todayVisits', 'yesterdayVisits', 'dailyStats',
            'topPages', 'topCountries', 'browsers', 'devices',
            'recentVisits', 'trafficSources'
        ));
    }
}