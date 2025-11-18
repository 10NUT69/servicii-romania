@extends('admin.layout')

@section('content')
<div class="max-w-6xl mx-auto py-10">

    <h1 class="text-3xl font-bold mb-8">Panou Administrare</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Utilizatori -->
        <div class="p-6 bg-white shadow rounded">
            <h2 class="text-lg font-semibold">Utilizatori</h2>
            <p class="text-3xl font-bold mt-2">{{ $userCount }}</p>
        </div>

        <!-- Anunțuri -->
        <div class="p-6 bg-white shadow rounded">
            <h2 class="text-lg font-semibold">Anunțuri</h2>
            <p class="text-3xl font-bold mt-2">{{ $serviceCount }}</p>
        </div>

        <!-- Vizite (placeholder) -->
        <div class="p-6 bg-white shadow rounded">
            <h2 class="text-lg font-semibold">Vizite</h2>
            <p class="text-3xl font-bold mt-2">0</p>
        </div>

    </div>

</div>
@endsection
