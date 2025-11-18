@extends('layouts.app')

@section('title', $service->title)

@section('content')
<div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 pt-6 pb-10">

    <!-- ============================= -->
    <!--       C O L O A N A   STÂNGA -->
    <!-- ============================= -->
    <div class="md:col-span-2">

        <!-- TITLU -->
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4 uppercase">
            {{ $service->title }}
        </h1>

        <!-- BADGES -->
        <div class="flex gap-3 mb-6">
            <span class="px-3 py-1 text-sm rounded-lg bg-blue-100 text-blue-700 font-semibold">
                {{ $service->category->name }}
            </span>
            <span class="px-3 py-1 text-sm rounded-lg bg-purple-100 text-purple-700 font-semibold">
                {{ $service->county->name }}
            </span>

            @if($service->city)
            <span class="px-3 py-1 text-sm rounded-lg bg-green-100 text-green-700 font-semibold">
                {{ $service->city }}
            </span>
            @endif
        </div>

        <!-- ============================= -->
        <!--         POZA PRINCIPALĂ       -->
        <!-- ============================= -->
        @php
            $images = $service->images ?: [];
        @endphp

        @if(count($images) > 0)
            <div class="mb-6">
                <img 
                    id="mainImage"
                    src="{{ asset('storage/services/' . $images[0]) }}" 
                    class="w-full h-96 object-cover rounded-xl shadow-md transition duration-300"
                    alt="Imagine principală"
                >
            </div>

            <!-- MINIATURI -->
            <div class="grid grid-cols-4 md:grid-cols-6 gap-3 mb-10">
                @foreach($images as $key => $image)
                    <img 
                        src="{{ asset('storage/services/' . $image) }}" 
                        class="h-24 w-full object-cover rounded-lg shadow cursor-pointer hover:opacity-75 transition"
                        onclick="document.getElementById('mainImage').src=this.src"
                    >
                @endforeach
            </div>
        @else
            <!-- FALLBACK FĂRĂ IMAGINI -->
            <div class="w-full h-64 bg-gray-100 border rounded-xl flex items-center justify-center mb-10">
                <span class="text-gray-500">Acest anunț nu are imagini</span>
            </div>
        @endif



        <!-- ============================= -->
        <!--        DESCRIERE ANUNȚ        -->
        <!-- ============================= -->
        <div class="text-gray-700 dark:text-gray-300 leading-relaxed text-lg mb-6 whitespace-pre-line">
            {{ $service->description }}
        </div>


        <!-- ============================= -->
        <!--     VIZUALIZĂRI + DATA        -->
        <!-- ============================= -->
        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mt-4">

            <!-- Vizualizări -->
            <span class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                           4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                {{ $service->views ?? 0 }} vizualizări
            </span>

            <!-- Data publicării -->
            <span>
                Publicat la: {{ $service->created_at->format('d.m.Y') }}
            </span>

        </div>

    </div>



    <!-- ============================= -->
<!--       C O L O A N A   DREAPTA -->
<!-- ============================= -->
<div class="mt-[6.5rem]"> <!-- 🔥 acest mt mută cardul doar la pozitia initială -->

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 
                p-6 h-fit sticky top-16">  <!-- 🔥 sticky la inaltimea headerului -->

        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
            Contact
        </h3>

        <div class="space-y-4 text-gray-800 dark:text-gray-300">

            @if($service->phone)
            <div class="flex items-center gap-3">
                <span class="text-primary text-lg">📱</span>
                <a href="tel:{{ $service->phone }}" class="font-semibold hover:text-primary">
                    {{ $service->phone }}
                </a>
            </div>
            @endif

            @if($service->email)
            <div class="flex items-center gap-3">
                <span class="text-primary text-lg">📧</span>
                <a href="mailto:{{ $service->email }}" class="hover:text-primary">
                    {{ $service->email }}
                </a>
            </div>
            @endif

            <!-- PREȚ -->
            @if($service->price_value)
            <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-800 dark:text-gray-200 font-semibold">
                {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                @if($service->price_type === 'negotiable')
                    <span class="text-orange-600 dark:text-orange-300">Negociabil</span>
                @endif
            </div>
            @else
            <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-800 dark:text-gray-200 font-semibold">
                Cere ofertă
            </div>
            @endif

            <!-- BUTON SUNĂ -->
            @if($service->phone)
            <a href="tel:{{ $service->phone }}"
                class="block w-full text-center mt-5 py-3 rounded-lg text-white font-semibold 
                       bg-gradient-to-r from-red-500 to-orange-500 hover:opacity-90 transition">
                Sună acum
            </a>
            @endif

        </div>

    </div>
</div>


</div>
@endsection
