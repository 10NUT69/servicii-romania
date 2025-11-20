@extends('admin.layout')

@section('content')
<!-- Adăugăm CSS pentru steaguri dacă API-ul returnează coduri de țară (ex: RO, US) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css">

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Overview Analitic</h1>
            <p class="text-sm text-gray-500">Raport detaliat pentru {{ config('app.name') }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3">
            <span class="bg-white px-4 py-2 rounded-lg shadow-sm text-sm text-gray-600 font-medium border border-gray-200">
                <i class="far fa-clock mr-2"></i> {{ now()->format('d M Y, H:i') }}
            </span>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 1: KPI WIDGETS (Densitate mare) -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        <!-- Widget 1: Vizite Azi -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-start justify-between relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Vizite Astăzi</p>
                <h3 class="text-3xl font-extrabold text-gray-800 mt-1">{{ number_format($todayVisits) }}</h3>
                <div class="mt-1 flex items-center text-xs">
                    <span class="{{ $todayVisits >= $yesterdayVisits ? 'text-green-500' : 'text-red-500' }} font-bold flex items-center">
                        @if($todayVisits >= $yesterdayVisits)
                            <i class="fas fa-arrow-up mr-1"></i>
                        @else
                            <i class="fas fa-arrow-down mr-1"></i>
                        @endif
                        vs Ieri ({{ $yesterdayVisits }})
                    </span>
                </div>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>

        <!-- Widget 2: Vizitatori Unici -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">IP-uri Unice</p>
                <h3 class="text-3xl font-extrabold text-gray-800 mt-1">{{ number_format($uniqueVisitors) }}</h3>
                <p class="text-xs text-gray-400 mt-1">Total All-Time</p>
            </div>
            <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                <i class="fas fa-fingerprint text-xl"></i>
            </div>
        </div>

        <!-- Widget 3: Total Pagini Văzute -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-start justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Afișări</p>
                <h3 class="text-3xl font-extrabold text-gray-800 mt-1">{{ number_format($totalVisits) }}</h3>
                <p class="text-xs text-gray-400 mt-1">Hits</p>
            </div>
            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                <i class="fas fa-eye text-xl"></i>
            </div>
        </div>

        <!-- Widget 4: Utilizatori & Servicii -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex flex-col justify-between">
            <div class="flex justify-between items-center border-b border-gray-100 pb-2 mb-2">
                <span class="text-xs text-gray-500 font-semibold">UTILIZATORI</span>
                <span class="text-lg font-bold text-indigo-600">{{ number_format($userCount) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500 font-semibold">ANUNȚURI</span>
                <span class="text-lg font-bold text-orange-600">{{ number_format($serviceCount) }}</span>
            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 2: GRAFIC PRINCIPAL & DEVICES -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <!-- Main Chart (2/3 width) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
            <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-area text-blue-500 mr-2"></i> Trafic ultimele 30 zile
            </h3>
            <div class="relative h-72 w-full">
                <canvas id="mainChart"></canvas>
            </div>
        </div>

        <!-- Devices & Systems (1/3 width) -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <h3 class="text-base font-bold text-gray-800 mb-4">Dispozitive</h3>
            
            <div class="flex-1 flex items-center justify-center relative h-48 mb-4">
                <canvas id="deviceChart"></canvas>
            </div>

            <!-- Custom Legend -->
            <div class="space-y-3 mt-auto">
                @foreach($devices as $dev)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        @php
                            $icon = match(strtolower($dev->device)) {
                                'desktop' => 'fa-desktop',
                                'mobile' => 'fa-mobile-alt',
                                'tablet' => 'fa-tablet-alt',
                                default => 'fa-laptop'
                            };
                        @endphp
                        <i class="fas {{ $icon }} text-gray-400 w-6"></i>
                        <span class="text-gray-600 capitalize">{{ $dev->device }}</span>
                    </div>
                    <span class="font-bold text-gray-800">{{ $dev->total }}</span>
                </div>
                <!-- Progress Bar mic -->
                <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ ($dev->total / $totalVisits) * 100 }}%"></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 3: TABELE DETALIATE -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- 1. TOP PAGINI -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-700">Top Pagini</h3>
                <i class="fas fa-link text-gray-400"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topPages as $page)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex justify-between mb-1">
                                    <span class="text-gray-700 font-medium truncate max-w-[200px]" title="{{ $page->url }}">{{ $page->url }}</span>
                                    <span class="text-gray-900 font-bold">{{ $page->total }}</span>
                                </div>
                                <!-- Visual Bar -->
                                @php $percent = ($page->total / $totalVisits) * 100; @endphp
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. TOP ȚĂRI -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-700">Geolocație</h3>
                <i class="fas fa-globe-europe text-gray-400"></i>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($topCountries as $country)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <!-- Încercăm să afișăm steagul dacă country e cod ISO (ex: RO) -->
                                    <!-- Dacă e nume complet, va arăta doar iconița default -->
                                    @if(strlen($country->country) == 2)
                                        <span class="fi fi-{{ strtolower($country->country) }} rounded shadow-sm"></span>
                                    @else
                                        <i class="fas fa-map-marker-alt text-gray-300"></i>
                                    @endif
                                    <span class="text-gray-700 font-medium">{{ $country->country ?: 'Unknown' }}</span>
                                </div>
                                <span class="bg-gray-100 text-gray-700 py-1 px-2 rounded text-xs font-bold">
                                    {{ $country->total }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        @if($topCountries->isEmpty())
                        <tr><td class="p-4 text-center text-gray-400">Nu sunt date de localizare încă.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. BROWSERS & REFERRERS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <!-- Browsers Section -->
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-bold text-gray-700">Browsere</h3>
            </div>
            <div class="p-4 flex-1 border-b border-gray-100">
                <div class="relative h-32 w-full flex justify-center">
                    <canvas id="browserChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    @foreach($browsers as $browser)
                    <div class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                        <span class="text-gray-600 truncate">{{ $browser->browser }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Referrers Section (Mini List) -->
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-700">Top Surse</h3>
            </div>
            <div class="p-0">
                @foreach($trafficSources->take(4) as $source)
                <div class="px-4 py-2 border-b border-gray-50 flex justify-between items-center text-sm">
                    <span class="text-gray-600 truncate w-32">{{ $source->source }}</span>
                    <span class="font-bold text-gray-800">{{ $source->total }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- ========================= -->
    <!-- ROW 4: ULTIMII VIZITATORI (Live Feed) -->
    <!-- ========================= -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-10">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Jurnal Vizite Recente</h3>
            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded animate-pulse">● Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Vizitator</th>
                        <th class="px-6 py-3">Pagină</th>
                        <th class="px-6 py-3">Sursă</th>
                        <th class="px-6 py-3 text-right">Acțiune</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentVisits as $visit)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ $visit->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900">{{ $visit->ip }}</span>
                                <span class="text-xs text-gray-400">
                                    {{ $visit->city ?? '-' }}, {{ $visit->country ?? 'Unknown' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-50 text-blue-700 py-1 px-2 rounded text-xs font-medium truncate max-w-[150px] inline-block">
                                {{ $visit->url }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 truncate max-w-[150px]">
                            {{ $visit->referer ?? 'Direct' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="#" class="text-gray-400 hover:text-blue-600"><i class="fas fa-ellipsis-h"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    // Pregătire date pentru charts
    $dates = $dailyStats->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'));
    $visits = $dailyStats->pluck('visits');
    $unique = $dailyStats->pluck('unique_ips');

    $deviceLabels = $devices->pluck('device');
    $deviceData = $devices->pluck('total');

    $browserLabels = $browsers->pluck('browser');
    $browserData = $browsers->pluck('total');
@endphp

<script>
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. MAIN CHART (Line - Visits vs Unique)
    const ctxMain = document.getElementById('mainChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: @json($dates),
            datasets: [
                {
                    label: 'Vizite',
                    data: @json($visits),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'IP-uri Unice',
                    data: @json($unique),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. DEVICES CHART (Pie)
    new Chart(document.getElementById('deviceChart'), {
        type: 'pie',
        data: {
            labels: @json($deviceLabels),
            datasets: [{
                data: @json($deviceData),
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // 3. BROWSER CHART (Doughnut)
    new Chart(document.getElementById('browserChart'), {
        type: 'doughnut',
        data: {
            labels: @json($browserLabels),
            datasets: [{
                data: @json($browserData),
                backgroundColor: ['#3b82f6', '#0ea5e9', '#f43f5e', '#eab308', '#64748b'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection