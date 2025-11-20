@extends('admin.layout')

@section('content')
<div class="max-w-7xl mx-auto py-10">

    <!-- TITLU -->
    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 mb-10">
        Panou Administrare
    </h1>

    <!-- ========================= -->
    <!--     ROW 1: KPI CARDS      -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">

        <!-- Utilizatori -->
        <div class="p-6 bg-white shadow-lg rounded-xl border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Utilizatori</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-1">{{ $userCount }}</h3>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Anunțuri -->
        <div class="p-6 bg-white shadow-lg rounded-xl border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Anunțuri</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-1">{{ $serviceCount }}</h3>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-bullhorn text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Vizite totale -->
        <div class="p-6 bg-white shadow-lg rounded-xl border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total vizite</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-1">{{ $totalVisits }}</h3>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Vizite azi -->
        <div class="p-6 bg-white shadow-lg rounded-xl border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Astăzi</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-1">{{ $todayVisits }}</h3>
                </div>
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-sun text-2xl"></i>
                </div>
            </div>
        </div>

    </div>


    <!-- ========================= -->
    <!--   ROW 2: PERIOD CARDS     -->
    <!-- ========================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

        <!-- Ieri -->
        <div class="p-5 bg-white shadow rounded-lg border border-gray-100">
            <p class="text-gray-500 text-sm">Ieri</p>
            <h3 class="text-3xl font-bold text-gray-700">{{ $yesterdayVisits }}</h3>
        </div>

        <!-- Ultimele 7 zile -->
        <div class="p-5 bg-white shadow rounded-lg border border-gray-100">
            <p class="text-gray-500 text-sm">Ultimele 7 zile</p>
            <h3 class="text-3xl font-bold text-gray-700">{{ $weekVisits }}</h3>
        </div>

        <!-- Ultimele 30 zile -->
        <div class="p-5 bg-white shadow rounded-lg border border-gray-100">
            <p class="text-gray-500 text-sm">Ultimele 30 zile</p>
            <h3 class="text-3xl font-bold text-gray-700">{{ $monthVisits }}</h3>
        </div>

    </div>


    <!-- ========================= -->
    <!-- ROW 3: Vizite pe ore (Line Chart) -->
    <!-- ========================= -->
    <div class="bg-white shadow-xl rounded-xl p-8 mb-10 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Vizite pe ore (astăzi)</h2>
        <canvas id="hourlyChart" height="140"></canvas>
    </div>

    <!-- ========================= -->
    <!-- ROW 4: Vizite pe 7 zile (Bar Chart) -->
    <!-- ========================= -->
    <div class="bg-white shadow-xl rounded-xl p-8 mb-10 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Vizite în ultimele 7 zile</h2>
        <canvas id="dailyChart" height="140"></canvas>
    </div>

    <!-- ========================= -->
    <!-- ROW 5: Traflic sources (Pie Chart) -->
    <!-- ========================= -->
    <div class="bg-white shadow-xl rounded-xl p-8 mb-20 border border-gray-100">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Sursa traficului</h2>
        <canvas id="trafficPie" height="140"></canvas>
    </div>

</div>

<!-- FONT AWESOME -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* ============================
   GRAFIC – Vizite pe ore
   ============================ */
new Chart(document.getElementById('hourlyChart'), {
    type: 'line',
    data: {
        labels: [@foreach($hourly as $h) "{{ $h->hour }}", @endforeach],
        datasets: [{
            label: 'Vizite',
            data: [@foreach($hourly as $h) {{ $h->total }}, @endforeach],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    }
});


/* ============================
   GRAFIC – Vizite pe zile
   ============================ */
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: [@foreach($daily as $d) "{{ \Carbon\Carbon::parse($d->day)->format('d M') }}", @endforeach],
        datasets: [{
            label: 'Vizite',
            data: [@foreach($daily as $d) {{ $d->total }}, @endforeach],
            backgroundColor: '#10b981'
        }]
    }
});


/* ============================
   GRAFIC – Sursa traficului
   ============================ */
new Chart(document.getElementById('trafficPie'), {
    type: 'pie',
    data: {
        labels: [@foreach($trafficSources as $s) "{{ $s->source }}", @endforeach],
        datasets: [{
            data: [@foreach($trafficSources as $s) {{ $s->total }}, @endforeach],
            backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#6366f1'
            ]
        }]
    }
});
</script>

@endsection
