@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <!-- HEADER & DATA -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Statistici în timp real și performanța platformei.</p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 text-blue-700">
                <i class="fas fa-calendar-alt mr-2"></i> {{ now()->format('d M Y') }}
            </span>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 1: KPI CARDS COMPACTE -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        
        <!-- Card 1: Vizite Astăzi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Vizite Astăzi</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($todayVisits) }}</p>
                <p class="text-xs text-green-600 mt-1 font-medium">
                    <i class="fas fa-history"></i> Ieri: {{ number_format($yesterdayVisits) }}
                </p>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                <i class="fas fa-chart-bar text-xl"></i>
            </div>
        </div>

        <!-- Card 2: Total Utilizatori -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Utilizatori</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($userCount) }}</p>
                <p class="text-xs text-gray-400 mt-1">Înregistrați</p>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>

        <!-- Card 3: Servicii/Anunțuri -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Anunțuri</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($serviceCount) }}</p>
                <p class="text-xs text-gray-400 mt-1">Active pe site</p>
            </div>
            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                <i class="fas fa-bullhorn text-xl"></i>
            </div>
        </div>

        <!-- Card 4: Total Vizite All Time -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Trafic</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalVisits) }}</p>
                <p class="text-xs text-gray-400 mt-1">Vizualizări pagini</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg text-gray-600">
                <i class="fas fa-globe text-xl"></i>
            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 2: GRAFIC PRINCIPAL + SURSE -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Left: Evoluție 7 Zile (Ocupă 2 coloane) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Evoluție trafic (7 zile)</h3>
                <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded">Ultima săptămână</span>
            </div>
            <!-- Container cu înălțime fixă pentru a preveni mărirea exagerată -->
            <div class="relative h-72 w-full">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Right: Surse Trafic (Ocupă 1 coloană) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Surse Trafic</h3>
            
            <!-- Doughnut Chart mai mic -->
            <div class="relative h-48 w-full mb-4 flex justify-center">
                <canvas id="trafficPie"></canvas>
            </div>

            <!-- Legendă Custom sub grafic (arată mai bine decât tooltip-urile) -->
            <div class="space-y-3 mt-4 overflow-y-auto max-h-40 pr-2 custom-scrollbar">
                @foreach($trafficSources as $index => $source)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-2" style="background-color: {{ ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'][$index % 5] }}"></span>
                        <span class="text-gray-600">{{ $source->source }}</span>
                    </div>
                    <span class="font-semibold text-gray-800">{{ $source->total }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ========================= -->
    <!-- ROW 3: ORE DE VÂRF + ALTE METRICI -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Ore de vârf -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ore de vârf (Astăzi)</h3>
            <div class="relative h-60 w-full">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Un tabel sumar rapid (Placeholder pentru date viitoare sau top pagini) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-center items-center text-center">
            <div class="p-4 bg-blue-50 rounded-full text-blue-500 mb-3">
                <i class="fas fa-map-marked-alt text-3xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800">Geolocație Activă</h3>
            <p class="text-gray-500 text-sm mt-2 max-w-xs">
                Datele despre țară și oraș sunt procesate automat în fundal la fiecare oră pentru a nu afecta viteza site-ului.
            </p>
            <div class="mt-6 grid grid-cols-2 gap-4 w-full text-sm">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <span class="block text-gray-400 text-xs uppercase">Săptămâna asta</span>
                    <span class="block text-xl font-bold text-gray-800">{{ $weekVisits }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <span class="block text-gray-400 text-xs uppercase">Luna asta</span>
                    <span class="block text-xl font-bold text-gray-800">{{ $monthVisits }}</span>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- 
    ================================================================
    FIX EROARE PARSE:
    Pregătim datele PHP în afara directivei @json pentru a evita
    erorile de sintaxă Blade cu funcțiile săgeată.
    ================================================================
--}}
@php
    // 1. Date Zilnice
    $labelsDaily = $daily->map(fn($d) => \Carbon\Carbon::parse($d->day)->format('d M'));
    $dataDaily   = $daily->pluck('total');

    // 2. Date Surse Trafic
    $labelsTraffic = $trafficSources->pluck('source');
    $dataTraffic   = $trafficSources->pluck('total');

    // 3. Date Orare
    $labelsHourly = $hourly->map(fn($h) => $h->hour . ':00');
    $dataHourly   = $hourly->pluck('total');
@endphp

<script>
    // Setări globale pentru fonturi Chart.js ca să arate mai clean
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';

    // Preluăm datele procesate anterior în PHP
    const dailyLabels = @json($labelsDaily);
    const dailyData   = @json($dataDaily);

    const trafficLabels = @json($labelsTraffic);
    const trafficData   = @json($dataTraffic);

    const hourlyLabels = @json($labelsHourly);
    const hourlyData   = @json($dataHourly);


    /* ============================
       1. GRAFIC ZILE (Line Chart Smooth)
       ============================ */
    const ctxDaily = document.getElementById('dailyChart').getContext('2d');
    
    // Gradient pentru fundalul graficului
    let gradientDaily = ctxDaily.createLinearGradient(0, 0, 0, 400);
    gradientDaily.addColorStop(0, 'rgba(59, 130, 246, 0.2)'); // Albastru transparent sus
    gradientDaily.addColorStop(1, 'rgba(59, 130, 246, 0)');   // Transparent jos

    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Vizite',
                data: dailyData,
                borderColor: '#3b82f6', // Tailwind Blue-500
                backgroundColor: gradientDaily,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#f1f5f9' },
                    ticks: { padding: 10 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    /* ============================
       2. SURSE TRAFIC (Doughnut Chart)
       ============================ */
    new Chart(document.getElementById('trafficPie'), {
        type: 'doughnut',
        data: {
            labels: trafficLabels,
            datasets: [{
                data: trafficData,
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    /* ============================
       3. ORE DE VÂRF (Bar Chart)
       ============================ */
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hourlyLabels,
            datasets: [{
                label: 'Vizite',
                data: hourlyData,
                backgroundColor: '#cbd5e1',
                hoverBackgroundColor: '#3b82f6',
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    display: false,
                    beginAtZero: true
                },
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 8, autoSkip: true }
                }
            }
        }
    });
</script>

<style>
    /* Scrollbar finuț pentru lista de surse */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
</style>

@endsection