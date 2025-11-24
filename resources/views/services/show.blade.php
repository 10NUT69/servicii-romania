@extends('layouts.app')

@php
    // Titlu scurt SEO
    $seoTitle = Str::limit($service->title, 40);

    // Locația (oraș dacă există, altfel județul)
    $seoLocation = $service->city ?: $service->county->name;

    // Meta description (modelul ales)
    $seoDescription = "Cauti {$service->category->name} in {$seoLocation}? ".
                      "Gaseste rapid meseriasul potrivit, disponibil cand ai nevoie. ".
                      "Verifica detalii si contacteaza direct pe MeseriasBun.ro.";

    // Poză principală
    $seoImage = isset($service->images[0]) 
                ? asset('storage/services/' . $service->images[0])
                : asset('images/default-service.jpg');
@endphp

@section('title', $seoTitle . ' | ' . $service->category->name . ' in ' . $seoLocation . ' | MeseriasBun.ro')

@section('meta_description', $seoDescription)
@section('meta_title', $seoTitle . ' | ' . $service->category->name . ' in ' . $seoLocation)
@section('meta_image', $seoImage)

@section('content')

{{-- CONTAINER PRINCIPAL --}}
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10 pt-6 pb-10">

    {{-- COL 1: CONȚINUT PRINCIPAL (Stânga) --}}
    <div class="lg:col-span-2">

        {{-- Buton Înapoi --}}
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-[#2C2C2C] hover:bg-gray-200 dark:hover:bg-[#333333] transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Înapoi la listă
        </a>

        {{-- Titlu --}}
        <h1 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-[#F2F2F2] mb-4 leading-tight">
            {{ $service->title }}
        </h1>

        {{-- Tag-uri (Categorie, Județ, Oraș) --}}
        <div class="flex flex-wrap gap-2 mb-8">
            <span class="px-3 py-1 text-xs md:text-sm rounded-full font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                {{ $service->category->name }}
            </span>
            
            <span class="px-3 py-1 text-xs md:text-sm rounded-full font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $service->county->name }}
            </span>

            @if($service->city)
            <span class="px-3 py-1 text-xs md:text-sm rounded-full font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                {{ $service->city }}
            </span>
            @endif
        </div>

        {{-- LOGICĂ IMAGINI --}}
        @php
            $images = $service->images ?: [];
            if (is_string($images)) $images = json_decode($images, true);
            if (!is_array($images)) $images = [];
            $images = array_values(array_filter($images));
        @endphp

        {{-- ZONA IMAGINI --}}
        @if(count($images) > 0)
            
            {{-- Imagine Principală Mare (Fără Click/Lightbox) --}}
            <div class="mb-4 relative overflow-hidden rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] aspect-[16/10]">
                <img id="mainImage"
                     src="{{ asset('storage/services/' . $images[0]) }}" 
                     class="w-full h-full object-cover transition duration-500"
                     alt="{{ $service->title }}">
            </div>

            {{-- Thumbnails --}}
            <div class="grid grid-cols-5 gap-2 md:gap-3 mb-10">
                @foreach($images as $key => $image)
                    <div class="aspect-square relative rounded-xl overflow-hidden cursor-pointer border-2 border-transparent hover:border-[#CC2E2E] transition-all"
                         onclick="document.getElementById('mainImage').src='{{ asset('storage/services/' . $image) }}'">
                        <img src="{{ asset('storage/services/' . $image) }}" 
                             class="w-full h-full object-cover hover:opacity-90 transition"
                             alt="{{ $service->title }} – fotografie {{ $key + 1 }}"
>
                    </div>
                @endforeach
            </div>

        @else
            {{-- Placeholder fără imagini --}}
            <div class="w-full h-64 bg-gray-50 dark:bg-[#1E1E1E] border-2 border-dashed border-gray-200 dark:border-[#333333] rounded-2xl flex flex-col items-center justify-center mb-10">
                <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="text-gray-400 font-medium">Fără imagini încărcate</span>
            </div>
        @endif

        {{-- DESCRIERE --}}
        <div class="prose dark:prose-invert max-w-none mb-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Descriere detaliată</h3>
            <div class="text-gray-700 dark:text-[#D4D4D4] leading-relaxed whitespace-pre-line text-base md:text-lg">
                {{ $service->description }}
            </div>
        </div>

        {{-- META FOOTER --}}
        <div class="flex items-center gap-6 text-sm text-gray-500 dark:text-[#A1A1AA] border-t border-gray-100 dark:border-[#333333] pt-6">
            <span class="flex items-center gap-2" title="Vizualizări">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="font-medium">{{ $service->views ?? 0 }}</span>
            </span>

            <span class="text-gray-300">|</span>

            <span>Publicat: {{ $service->created_at->diffForHumans() }}</span>
        </div>

    </div>


    {{-- COL 2: SIDEBAR (Dreapta) --}}
    <div class="lg:col-span-1">
        
        {{-- STICKY SIDEBAR --}}
        <div class="sticky top-24 space-y-6">

            {{-- CARD PREȚ & CONTACT --}}
            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-100 dark:border-[#333333] overflow-hidden">
                
                {{-- Preț --}}
                <div class="p-6 border-b border-gray-100 dark:border-[#333333] bg-gray-50/50 dark:bg-[#252525] text-center">
                    <p class="text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 font-bold mb-2">Preț Solicitat</p>
                    
                    @if($service->price_value)
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-4xl font-black text-gray-900 dark:text-white">
                                {{ number_format($service->price_value, 0, ',', '.') }}
                            </span>
                            <span class="text-xl font-bold text-gray-500 dark:text-gray-400 mt-2">{{ $service->currency }}</span>
                        </div>
                        @if($service->price_type === 'negotiable')
                            <span class="inline-block mt-2 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-full uppercase">
                                Negociabil
                            </span>
                        @endif
                    @else
                        <span class="text-2xl font-bold text-[#CC2E2E]">Cere ofertă</span>
                    @endif
                </div>

                {{-- Contact --}}
                <div class="p-6 space-y-6">
                    
                    {{-- User Info --}}
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-gray-800 to-black text-white flex items-center justify-center font-bold text-xl shadow-md border border-gray-200 dark:border-gray-700">
                            @if($service->user)
                                {{ strtoupper(substr($service->user->name, 0, 1)) }}
                            @else
                                U
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold">Publicat de</p>
                            <h4 class="font-bold text-gray-900 dark:text-white text-lg">
                                {{ $service->user ? $service->user->name : 'Vizitator' }}
                            </h4>
                        </div>
                    </div>

                    {{-- Buton Telefon --}}
                    @if($service->phone)
                        @php
                            $rawPhone = preg_replace('/[^0-9]/', '', $service->phone);
                            $viewPhone = $service->phone; 
                        @endphp

                        <a href="tel:{{ $rawPhone }}"
                           class="group relative flex items-center justify-center gap-3 w-full py-4 rounded-xl 
                                  bg-[#CC2E2E] text-white font-bold text-lg shadow-lg shadow-red-500/30
                                  hover:bg-[#B72626] hover:shadow-red-500/50 hover:-translate-y-0.5 
                                  active:translate-y-0 transition-all duration-200 overflow-hidden">
                            
                            {{-- Shimmer Effect --}}
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ $viewPhone }}</span>
                        </a>
                    @else
                        <div class="p-3 bg-gray-100 dark:bg-[#2C2C2C] text-gray-500 text-center rounded-lg text-sm italic">
                            Fără număr de telefon
                        </div>
                    @endif

                    {{-- Safety Badge --}}
                    <div class="flex items-center justify-center gap-2 text-xs text-green-600 dark:text-green-400 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Date contact verificate
                    </div>

                </div>
            </div>

            {{-- Safety Tips --}}
            <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-xl p-5">
                <h5 class="font-bold text-blue-800 dark:text-blue-300 text-sm mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Siguranță
                </h5>
                <ul class="text-sm text-blue-700 dark:text-blue-200 space-y-2 list-disc list-inside">
                    <li>Nu plătiți niciodată în avans.</li>
                    <li>Verificați lucrarea înainte de plată.</li>
                    <li>Întâlniți-vă în locuri sigure.</li>
                </ul>
            </div>

        </div>
    </div>

</div>

@endsection

@section('schema')
<script type="application/ld+json">
{!! json_encode([
    "@context" => "https://schema.org",
    "@type" => "Service",
    "name" => $service->title,
    "description" => $seoDescription,
    "image" => $seoImage,
    "areaServed" => $seoLocation,
    "provider" => [
        "@type" => "Person",
        "name" => $service->user->name ?? 'Meserias'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

