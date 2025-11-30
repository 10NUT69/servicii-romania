@extends('layouts.app')

@php
    // =========================================================
    // 1. LOGICA: 3 CUVINTE REALE + CURĂȚARE
    // =========================================================
    $brand = 'MeseriasBun.ro';
    
    // Curățăm simbolurile (cratime, bare) ca să nu le numere drept cuvinte
    $cleanTitleString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $service->title);
    $cleanTitleString = trim(preg_replace('/\s+/', ' ', $cleanTitleString));
    
    $words = explode(' ', $cleanTitleString);
    
    // Luăm primele 3 cuvinte
    $shortUserTitle = implode(' ', array_slice($words, 0, 3));

    // =========================================================
    // 2. CONSTRUIRE TITLU & META
    // =========================================================
    $categoryName = $service->category->name;
    $seoLocation = $service->city ?: $service->county->name;
    
    // Structura: [3 Cuvinte] – [Categorie] în [Oraș]
    $fullSeoTitle = $shortUserTitle . ' – ' . $categoryName . ' în ' . $seoLocation;
    
    // 🔥 MODIFICARE AICI: Am relaxat limita la 70 ca să încapă Brandul
    if (mb_strlen($fullSeoTitle) + mb_strlen(" | " . $brand) <= 70) {
        $fullSeoTitle .= " | " . $brand;
    }

    // Descrierea
    $introPart = "Cauti {$categoryName} în {$seoLocation}? ";
    $userDescription = \Illuminate\Support\Str::limit(strip_tags($service->description), 120);
    $seoDescription = $introPart . $userDescription;

    // URL & Imagine
    $pageUrl = $service->public_url;
    $seoImage = $service->main_image_url;

    // Schema.org
    $schemaData = [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => $fullSeoTitle,
        "description" => $seoDescription,
        "image" => $seoImage,
        "url" => $pageUrl,
        "areaServed" => $seoLocation,
        "provider" => ["@type" => "Person", "name" => $service->user->name ?? 'Meseriaș'],
        "offers" => ["@type" => "Offer", "priceCurrency" => $service->currency ?? 'RON', "price" => $service->price_value ?? '0']
    ];
    
    $catUrl = route('category.location', ['category' => $service->category->slug, 'county' => $service->county->slug]);
    $breadcrumbSchema = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => [
            ["@type" => "ListItem", "position" => 1, "name" => "Acasa", "item" => route('services.index')],
            ["@type" => "ListItem", "position" => 2, "name" => "{$categoryName} {$seoLocation}", "item" => $catUrl],
            ["@type" => "ListItem", "position" => 3, "name" => \Illuminate\Support\Str::limit($service->title, 20)]
        ]
    ];
@endphp

{{-- SECȚIUNI META --}}
@section('title', $fullSeoTitle)
@section('meta_title', $fullSeoTitle)
@section('meta_description', $seoDescription)
@section('meta_image', $seoImage)

{{-- CANONICAL TAG (CRITIC PENTRU SEO) --}}
@section('canonical')
    <link rel="canonical" href="{{ $pageUrl }}" />
@endsection

@section('schema')
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')

{{-- CONTAINER PRINCIPAL --}}
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-10 pt-6 pb-10 px-4 md:px-0">

    {{-- ================================================= --}}
    {{-- COL 1: CONȚINUT PRINCIPAL (Stânga) --}}
    {{-- ================================================= --}}
    <div class="lg:col-span-2">

        {{-- 🔥 BREADCRUMBS VIZUAL (NAVIGARE) --}}
        <nav class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-6 flex-wrap gap-2">
            <a href="{{ route('services.index') }}" class="hover:text-[#CC2E2E] transition">Acasă</a>
            <span class="text-gray-300">/</span>
            
            {{-- Link către Categorie + Județ (Ex: Zugravi Braila) --}}
            <a href="{{ route('category.location', ['category' => $service->category->slug, 'county' => $service->county->slug]) }}" 
               class="hover:text-[#CC2E2E] transition font-medium">
                {{ $service->category->name }} {{ $service->county->name }}
            </a>
            
            <span class="text-gray-300">/</span>
            <span class="text-gray-800 dark:text-gray-200 truncate max-w-[150px] md:max-w-xs" title="{{ $service->title }}">
                Anunț #{{ $service->id }}
            </span>
        </nav>

        {{-- Titlu --}}
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-[#F2F2F2] mb-4 leading-tight">
            {{ $service->title }}
        </h1>

        {{-- TAG-URI CLICKABILE (LINK BUILDING INTERN) --}}
        <div class="flex flex-wrap gap-2 mb-8">
            {{-- Tag Categorie --}}
            <a href="{{ route('category.location', ['category' => $service->category->slug, 'county' => 'romania']) }}" 
               class="px-3 py-1 text-xs md:text-sm rounded-full font-bold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-100 dark:border-blue-800 hover:bg-blue-100 transition">
                {{ $service->category->name }}
            </a>
            
            {{-- Tag Județ --}}
            <a href="{{ route('category.location', ['category' => $service->category->slug, 'county' => $service->county->slug]) }}" 
               class="px-3 py-1 text-xs md:text-sm rounded-full font-bold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-100 dark:border-purple-800 hover:bg-purple-100 transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $service->county->name }}
            </a>

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
        <div class="mb-4 relative overflow-hidden rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] aspect-[16/10] bg-gray-100 dark:bg-[#121212]">
            <img id="mainImage"
                 src="{{ $service->main_image_url }}" 
                 class="w-full h-full object-cover transition duration-500"
                 alt="{{ $service->title }}"
                 fetchpriority="high">
        </div>

        {{-- ZONA THUMBNAILS --}}
        @if(count($userImages) > 0)
            <div class="grid grid-cols-5 gap-2 md:gap-3 mb-10">
                @foreach($userImages as $key => $image)
                    <div class="aspect-square relative rounded-xl overflow-hidden cursor-pointer border-2 border-transparent hover:border-[#CC2E2E] transition-all"
                         onclick="document.getElementById('mainImage').src='{{ asset('storage/services/' . $image) }}'">
                        <img src="{{ asset('storage/services/' . $image) }}" 
                             class="w-full h-full object-cover hover:opacity-90 transition"
                             alt="{{ $service->title }} - poza {{ $key + 1 }}" >
                    </div>
                @endforeach
            </div>
        @else
            <div class="mb-8"></div>
        @endif

        {{-- DESCRIERE --}}
        <div class="prose dark:prose-invert max-w-none mb-8">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-[#333333] pb-2">
                Descriere detaliată
            </h3>
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
                <span class="font-medium">{{ $service->views ?? 0 }} vizualizări</span>
            </span>
            
            <span class="text-gray-300">|</span>
            
            <span>
                ID Anunț: <strong>{{ $service->id }}</strong>
            </span>

            <span class="text-gray-300">|</span>

            <span>
                Publicat: 
                @if($service->created_at->isToday())
                    <span class="text-green-600 dark:text-green-400 font-bold">Azi</span>
                @elseif($service->created_at->isYesterday())
                    <span>Ieri</span>
                @else
                    {{ $service->created_at->format('d.m.Y') }}
                @endif
            </span>
        </div>

    </div>


    {{-- ================================================= --}}
    {{-- COL 2: SIDEBAR (Dreapta) --}}
    {{-- ================================================= --}}
    <div class="lg:col-span-1">
        
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

                {{-- C. ZONA SHARE (FIXAT CU LINKUL NOU) --}}
                <div class="bg-gray-50 dark:bg-[#252525] border-t border-gray-100 dark:border-[#333333] p-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase text-center mb-3 tracking-wider">Distribuie anunțul</p>
                    
                    <div class="grid grid-cols-3 gap-2">
                        
                        {{-- FACEBOOK --}}
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($pageUrl) }}" 
                           target="_blank"
                           class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-[#1877F2]/10 hover:bg-[#1877F2]/20 text-[#1877F2] dark:bg-[#1877F2]/20 dark:hover:bg-[#1877F2]/30 transition-all group">
                            <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                            <span class="text-[10px] font-bold">Facebook</span>
                        </a>

                        {{-- WHATSAPP --}}
                        <a href="https://api.whatsapp.com/send?text={{ urlencode($service->title . ' - ' . $pageUrl) }}" 
                           target="_blank"
                           class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-green-100 hover:bg-green-200 text-green-600 dark:bg-green-900/30 dark:hover:bg-green-900/50 dark:text-green-400 transition-all group">
                            <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 1.8.48 3.5 1.33 5L2.6 21.6a.5.5 0 00.64.64l4.6-1.33C9.5 21.52 10.75 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm.16 16.92c-1.57 0-3.09-.43-4.42-1.22l-.32-.19-2.92.85.85-2.92-.19-.32a8.53 8.53 0 01-1.22-4.42c0-4.72 3.84-8.56 8.56-8.56 4.72 0 8.56 3.84 8.56 8.56 0 4.72-3.84 8.56-8.56 8.56z"/></svg>
                            <span class="text-[10px] font-bold">WhatsApp</span>
                        </a>

                        {{-- COPY --}}
                        <button onclick="copyToClipboard()" id="copyBtn"
                                class="flex flex-col items-center justify-center gap-1 p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-all group">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            <span class="text-[10px] font-bold" id="copyText">Copiază</span>
                        </button>
                    </div>
                </div>

            </div>

            {{-- 2. CARD SIGURANȚĂ --}}
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
                // Folosim link-ul oficial (canonical) dacă e disponibil, altfel URL-ul curent
                const urlToCopy = "{{ $pageUrl }}";
                
                navigator.clipboard.writeText(urlToCopy).then(() => {
                    const btn = document.getElementById('copyBtn');
                    const text = document.getElementById('copyText');
                    
                    // Efect vizual
                    btn.classList.remove('bg-gray-100', 'text-gray-600', 'dark:bg-gray-800', 'dark:text-gray-300');
                    btn.classList.add('bg-green-100', 'text-green-600', 'dark:bg-green-900', 'dark:text-green-400');
                    text.innerText = 'Copiat!';
                    
                    setTimeout(() => {
                        btn.classList.remove('bg-green-100', 'text-green-600', 'dark:bg-green-900', 'dark:text-green-400');
                        btn.classList.add('bg-gray-100', 'text-gray-600', 'dark:bg-gray-800', 'dark:text-gray-300');
                        text.innerText = 'Copiază';
                    }, 2000);
                });
            }
            </script>

        </div>
    </div>

</div>

@endsection