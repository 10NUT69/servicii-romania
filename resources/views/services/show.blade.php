@extends('layouts.app')

@php
    // =========================================================
    // 1. TITLU SEO (Max ~60 caractere)
    // =========================================================
    $words = preg_split('/\s+/', trim($service->title));
    $shortUserTitle = implode(' ', array_slice($words, 0, 3)); 
    
    $seoLocation = $service->city ?: $service->county->name;

    // Construim titlul brut
    $rawTitle = $shortUserTitle . ' | ' . $service->category->name . ' în ' . $seoLocation . ' | MeseriasBun.ro';
    
    // Tăiem la 60 de caractere
    $fullSeoTitle = \Illuminate\Support\Str::limit($rawTitle, 60);


    // =========================================================
    // 2. DESCRIERE SEO (Max ~160 caractere & Hibridă)
    // =========================================================
    $introPart = "Cauti {$service->category->name} în {$seoLocation}? Găsește rapid meseriașul potrivit. ";
    
    $availableSpace = 155 - strlen($introPart);
    if ($availableSpace < 20) $availableSpace = 20;

    $userDescription = \Illuminate\Support\Str::limit(strip_tags($service->description), $availableSpace);
    $seoDescription = $introPart . $userDescription;


    // =========================================================
    // 3. IMAGINE SEO (Safe Link Absolute)
    // =========================================================
    $rawImage = $service->main_image_url;

    if (empty($rawImage)) {
        $seoImage = asset('images/logo.webp');
    } elseif (str_starts_with($rawImage, 'http')) {
        $seoImage = $rawImage;
    } else {
        $seoImage = asset($rawImage);
    }


    // =========================================================
    // 4. SCHEMA.ORG (JSON-LD)
    // =========================================================
    $schemaData = [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => $service->title,
        "description" => $seoDescription,
        "image" => $seoImage,
        "areaServed" => $seoLocation,
        "provider" => [
            "@type" => "Person",
            "name" => $service->user->name ?? 'Meseriaș'
        ]
    ];
@endphp

{{-- SECȚIUNI OUTPUT LAYOUT --}}
@section('title', $fullSeoTitle)
@section('meta_title', $fullSeoTitle)
@section('meta_description', $seoDescription)
@section('meta_image', $seoImage)

@section('schema')
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')

{{-- CONTAINER PRINCIPAL --}}
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10 pt-6 pb-10">

    {{-- ================================================= --}}
    {{-- COL 1: CONȚINUT PRINCIPAL (Stânga) --}}
    {{-- ================================================= --}}
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

        {{-- Tag-uri --}}
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
            $userImages = $service->images;
            if (is_string($userImages)) $userImages = json_decode($userImages, true);
            if (!is_array($userImages)) $userImages = [];
            $userImages = array_values(array_filter($userImages));
        @endphp

        {{-- ZONA IMAGINI PRINCIPALĂ --}}
        <div class="mb-4 relative overflow-hidden rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] aspect-[16/10]">
            <img id="mainImage"
                 src="{{ $service->main_image_url }}" 
                 class="w-full h-full object-cover transition duration-500"
                 alt="{{ $service->title }}"
                 fetchpriority="high"
                 loading="eager">
        </div>

        {{-- ZONA THUMBNAILS --}}
        @if(count($userImages) > 0)
            <div class="grid grid-cols-5 gap-2 md:gap-3 mb-10">
                @foreach($userImages as $key => $image)
                    <div class="aspect-square relative rounded-xl overflow-hidden cursor-pointer border-2 border-transparent hover:border-[#CC2E2E] transition-all"
                         onclick="document.getElementById('mainImage').src='{{ asset('storage/services/' . $image) }}'">
                        <img src="{{ asset('storage/services/' . $image) }}" 
                             class="w-full h-full object-cover hover:opacity-90 transition"
                             alt="{{ $service->title }}" >
                    </div>
                @endforeach
            </div>
        @else
            <div class="mb-8"></div>
        @endif

        {{-- DESCRIERE --}}
        <div class="prose dark:prose-invert max-w-none mb-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Descriere detaliată</h3>
            <div class="text-gray-700 dark:text-[#D4D4D4] leading-relaxed whitespace-pre-line text-base md:text-lg">
                {{ $service->description }}
            </div>
        </div>

        {{-- META FOOTER (Views/Date) --}}
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


    {{-- ================================================= --}}
    {{-- COL 2: SIDEBAR (Dreapta) --}}
    {{-- ================================================= --}}
    <div class="lg:col-span-1">
        
        {{-- Wrapper Sticky --}}
        <div class="sticky top-24 space-y-6">

            {{-- 1. CARD COMPLET: PREȚ + CONTACT + SHARE --}}
            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-100 dark:border-[#333333] overflow-hidden">
                
                {{-- A. ZONA PREȚ --}}
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

                {{-- B. ZONA CONTACT --}}
                <div class="p-6 pb-2 space-y-6">
                    
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

                    {{-- Buton Telefon (HERO) --}}
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
                    <div class="flex items-center justify-center gap-2 text-xs text-green-600 dark:text-green-400 font-medium pb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Date contact verificate
                    </div>
                </div>

                {{-- C. ZONA SHARE (FOOTER INTEGRAT) --}}
                <div class="bg-gray-50 dark:bg-[#252525] border-t border-gray-100 dark:border-[#333333] p-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase text-center mb-3 tracking-wider">Distribuie anunțul</p>
                    
                    <div class="grid grid-cols-3 gap-2">
                        {{-- FACEBOOK --}}
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                           target="_blank"
                           class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] hover:border-blue-500 hover:text-blue-600 text-gray-600 dark:text-gray-300 transition-all group">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </a>

                        {{-- WHATSAPP --}}
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($service->title . ' - ' . url()->current()) }}" 
                           target="_blank"
                           class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] hover:border-green-500 hover:text-green-600 text-gray-600 dark:text-gray-300 transition-all group">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.8.48 3.5 1.33 5L2.6 21.6a.5.5 0 00.64.64l4.6-1.33C9.5 21.52 10.75 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm.16 16.92c-1.57 0-3.09-.43-4.42-1.22l-.32-.19-2.92.85.85-2.92-.19-.32a8.53 8.53 0 01-1.22-4.42c0-4.72 3.84-8.56 8.56-8.56 4.72 0 8.56 3.84 8.56 8.56 0 4.72-3.84 8.56-8.56 8.56z"/></svg>
                        </a>

                        {{-- COPY --}}
                        <button onclick="copyToClipboard()" id="copyBtn"
                                class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] hover:border-gray-500 hover:text-gray-800 dark:hover:text-white text-gray-600 dark:text-gray-300 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </button>
                    </div>
                </div>

            </div>

            {{-- 2. CARD SIGURANȚĂ (PĂSTRAT MAI JOS) --}}
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

            {{-- Script Copiere Link --}}
            <script>
            function copyToClipboard() {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    const btn = document.getElementById('copyBtn');
                    // Efect vizual simplu
                    btn.classList.add('border-green-500', 'text-green-600');
                    setTimeout(() => {
                        btn.classList.remove('border-green-500', 'text-green-600');
                    }, 1000);
                });
            }
            </script>

        </div>
    </div>

</div>

@endsection