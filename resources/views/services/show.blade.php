@extends('layouts.app')

@php
    $brand = 'MeseriasBun.ro';
    $isDeleted = $service->trashed(); 

    // --- SEO & DATA PREP ---
    $cleanTitleString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $service->title);
    $cleanTitleString = trim(preg_replace('/\s+/', ' ', $cleanTitleString));
    $words = explode(' ', $cleanTitleString);
    $shortUserTitle = implode(' ', array_slice($words, 0, 3));
    
    $categoryName = $service->category->name;
    $seoLocation = $service->city ?: $service->county->name;
    
    $prefix = $isDeleted ? 'INDISPONIBIL - ' : '';
    $fullSeoTitle = $prefix . $shortUserTitle . ' – ' . $categoryName . ' în ' . $seoLocation;
    
    if (mb_strlen($fullSeoTitle) + mb_strlen(" | " . $brand) <= 70) {
        $fullSeoTitle .= " | " . $brand;
    }

    $introPart = "Cauti {$categoryName} în {$seoLocation}? ";
    $userDescription = \Illuminate\Support\Str::limit(strip_tags($service->description), 120);
    $seoDescription = $isDeleted ? 'Acest anunț nu mai este valabil. ' : ($introPart . $userDescription);
    
    $pageUrl = $service->public_url;
    $seoImage = $service->main_image_url; 

    // --- USER INFO ---
    $displayName = $service->author_name;       
    $displayInitial = $service->author_initial;

    // --- LOGICĂ TELEFON ---
    $hasPhone = !empty($service->phone); 
    $rawPhone = '';
    $formattedPhone = '';
    
    if ($hasPhone) {
        $rawPhone = preg_replace('/[^0-9]/', '', $service->phone);
        if (strlen($rawPhone) === 10) {
            $formattedPhone = preg_replace('/^(\d{4})(\d{3})(\d{3})$/', '$1 $2 $3', $rawPhone);
        } else {
            $formattedPhone = $service->phone;
        }
    }

    // --- IMAGINI PENTRU GALERIE ---
    $userImages = is_string($service->images) ? json_decode($service->images, true) : ($service->images ?? []);
    $userImages = array_filter((array)$userImages);
    
    $galleryUrls = [];
    $galleryUrls[] = $service->main_image_url;
    foreach($userImages as $img) {
        $galleryUrls[] = asset('storage/services/' . $img);
    }

    // --- SCHEMA ---
    $schemaData = [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => $fullSeoTitle,
        "description" => $seoDescription,
        "image" => $seoImage,
        "url" => $pageUrl,
        "areaServed" => $seoLocation,
        "provider" => ["@type" => "Person", "name" => $displayName],
        "offers" => $isDeleted ? null : ["@type" => "Offer", "priceCurrency" => $service->currency ?? 'RON', "price" => $service->price_value ?? '0']
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

    // --- DATA ---
    $createdAt = $service->created_at;
    if ($createdAt->isToday()) {
        $postedAtLabel = 'astăzi';
    } elseif ($createdAt->isYesterday()) {
        $postedAtLabel = 'ieri';
    } else {
        $postedAtLabel = $createdAt->format('d.m.Y');
    }
@endphp

@section('title', $fullSeoTitle)
@section('meta_title', $fullSeoTitle)
@section('meta_description', $seoDescription)
@section('meta_image', $seoImage)

@section('canonical')
    <link rel="canonical" href="{{ $pageUrl }}" />
@endsection

@section('schema')
<script type="application/ld+json">
{!! json_encode(array_filter($schemaData), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')

{{-- MODAL LIGHTBOX (FULLSCREEN) --}}
<div id="lightboxModal" class="hidden fixed inset-0 z-[100] bg-black/95 backdrop-blur-md touch-none flex items-center justify-center transition-opacity duration-300 opacity-0">
    
    {{-- 🔥 MODIFICAT: Buton Close (X) Mult mai vizibil --}}
    <button onclick="closeLightbox()" 
            class="absolute top-24 right-5 z-[120] p-2 bg-white/20 hover:bg-white/40 text-white rounded-full backdrop-blur-md border border-white/10 shadow-lg transition-all active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Imagine Full --}}
    {{-- 🔥 MODIFICAT: Am adăugat 'w-full h-full' și am schimbat max-w/max-h la 95vw/vh --}}
    {{-- Asta forțează imaginea să se extindă pe tot ecranul, chiar dacă rezoluția ei e mică --}}
    <img id="lightboxImage" 
         src="" 
         class="w-full h-full max-w-[95vw] max-h-[95vh] object-contain transition-transform duration-300 drop-shadow-2xl" 
         alt="{{ $service->title }}">
		 {{-- Săgeți Lightbox --}}
    @if(count($galleryUrls) > 1)
        <button onclick="prevImage(event)" class="absolute left-3 md:left-8 top-1/2 -translate-y-1/2 z-[110] p-3 text-white bg-black/40 hover:bg-black/70 rounded-full backdrop-blur-sm border border-white/10 transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button onclick="nextImage(event)" class="absolute right-3 md:right-8 top-1/2 -translate-y-1/2 z-[110] p-3 text-white bg-black/40 hover:bg-black/70 rounded-full backdrop-blur-sm border border-white/10 transition-all active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    @endif

    {{-- Counter Lightbox --}}
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 text-white/90 text-sm font-bold bg-black/60 border border-white/10 px-4 py-1.5 rounded-full backdrop-blur-sm">
        <span id="lightboxCounter">1</span> / {{ count($galleryUrls) }}
    </div>
</div>
{{-- END LIGHTBOX --}}


{{-- MOBILE BOTTOM NAV --}}
<div class="fixed bottom-0 left-0 right-0 z-50 lg:hidden safe-area-bottom
            bg-white/95 dark:bg-[#121212]/95 backdrop-blur-md
            border-t border-gray-100 dark:border-[#333333] 
            shadow-[0_-4px_20px_rgba(0,0,0,0.1)] px-4 py-3">

    <div class="flex items-center justify-between gap-4">
        
        <div class="flex flex-col justify-center min-w-0 flex-1 {{ $isDeleted ? 'opacity-50 grayscale' : '' }}">
            @if(!$isDeleted)
            <div class="flex items-center gap-1.5 mb-1">
                <div class="w-4 h-4 rounded-full bg-gray-100 dark:bg-[#333333] flex items-center justify-center border border-gray-200 dark:border-[#444] shrink-0">
                    <span class="text-[9px] font-bold text-gray-600 dark:text-gray-300">
                        {{ $displayInitial }}
                    </span>
                </div>
                <span class="text-[11px] font-medium text-gray-500 dark:text-gray-300 truncate max-w-[120px]">
                    {{ $displayName }}
                </span>
            </div>
            @endif

            @if($service->price_value)
                <div class="flex items-baseline gap-1">
                    <span class="text-xl font-black text-gray-900 dark:text-white leading-none tracking-tight">
                        {{ number_format($service->price_value, 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase transform translate-y-[-1px]">{{ $service->currency }}</span>
                </div>
            @else
                <span class="text-base font-bold text-[#CC2E2E] leading-tight">Cere ofertă</span>
            @endif
        </div>

        <div class="shrink-0 w-[55%]">
            @if($isDeleted)
                <button disabled class="w-full h-12 flex items-center justify-center gap-2 bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold rounded-xl border border-gray-300 dark:border-gray-700 cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <span>Indisponibil</span>
                </button>
            @elseif($hasPhone)
                <div id="phone-wrapper-mobile">
                    <button onclick="revealPhone('mobile', '{{ $rawPhone }}', '{{ $formattedPhone }}')"
                       class="group relative w-full h-12 flex items-center justify-center gap-2 
                              bg-[#CC2E2E] active:bg-[#B72626] text-white rounded-xl 
                              shadow-lg shadow-red-600/20 active:shadow-none active:scale-[0.98] 
                              transition-all duration-200 overflow-hidden cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] animate-[shimmer_2s_infinite]"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="font-bold text-base tracking-wide">Arată telefon</span>
                    </button>
                </div>
            @else
                <button disabled class="w-full h-12 bg-gray-100 dark:bg-[#252525] text-gray-400 dark:text-gray-500 font-bold rounded-xl text-sm border border-gray-200 dark:border-[#333]">
                    Fără telefon
                </button>
            @endif
        </div>

    </div>
</div>

{{-- DESKTOP CONTAINER --}}
<div class="max-w-7xl mx-auto pt-4 pb-24 lg:pb-12 px-4 md:px-0">

    @if($isDeleted)
        <div class="mb-8 p-3 bg-red-50 border border-red-100 text-red-600 rounded-lg shadow-sm flex items-center justify-center gap-2 text-sm font-semibold">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Acest anunț a fost dezactivat și nu mai este disponibil.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 {{ $isDeleted ? 'opacity-50 grayscale' : '' }}">

        {{-- COL 1 --}}
        <div class="lg:col-span-8 xl:col-span-9">

            <nav class="hidden md:flex items-center text-sm text-gray-500 dark:text-gray-400 mb-6 gap-2">
                <a href="{{ route('services.index') }}" class="hover:text-[#CC2E2E] transition">Acasă</a>
                <span class="text-gray-300">/</span>
                <a href="{{ route('category.location', ['category' => $service->category->slug, 'county' => $service->county->slug]) }}" class="hover:text-[#CC2E2E] transition font-medium">
                    {{ $service->category->name }} {{ $service->county->name }}
                </a>
                <span class="text-gray-300">/</span>
                <span class="text-gray-400 truncate max-w-[200px]">{{ $service->title }}</span>
            </nav>

            <div class="mb-6">
                <div class="flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('category.index', ['category' => $service->category->slug]) }}" 
                       class="px-2.5 py-0.5 text-xs font-bold rounded text-blue-700 bg-blue-50 dark:text-blue-200 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 uppercase tracking-wide hover:bg-blue-100 transition">
                        {{ $service->category->name }}
                    </a>

                    <a href="{{ route('category.location', ['category' => $service->category->slug, 'county' => $service->county->slug]) }}" 
                       class="px-2.5 py-0.5 text-xs font-bold rounded text-purple-700 bg-purple-50 dark:text-purple-200 dark:bg-purple-900/30 border border-purple-100 dark:border-purple-800 flex items-center gap-1 uppercase tracking-wide hover:bg-purple-100 transition">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ $service->county->name }}
                    </a>

                    @if($isDeleted)
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded text-gray-600 bg-gray-200 border border-gray-300 uppercase tracking-wide">
                            INDISPONIBIL
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-[#F2F2F2] leading-tight mb-2">
                    {{ $service->title }}
                </h1>
                
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    Postat {{ $postedAtLabel }}
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    ID: {{ $service->id }}
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    Vizualizări: {{ $service->views ?? 0 }}
                </p>

                @if(!$isDeleted)
                    <div class="lg:hidden flex items-center justify-between mt-3 mb-4">
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Distribuie anunțul
                        </span>
                        <div class="flex items-center gap-3">
                            <button onclick="shareFacebook()" class="text-gray-400 hover:text-blue-600 transition p-1.5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg>
                            </button>
                            <button onclick="shareWhatsapp()" class="text-gray-400 hover:text-green-500 transition p-1.5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></button>
                            <button disabled class="text-gray-200 cursor-not-allowed p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z" /></svg></button>
                        @else
                            <button onclick="shareFacebook()" class="text-gray-400 hover:text-blue-600 transition p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg></button>
                            <button onclick="shareWhatsapp()" class="text-gray-400 hover:text-green-500 transition p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></button>
                            <button onclick="copyLink()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z" /></svg></button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- GALERIE FOTO --}}
            <div class="space-y-4 mb-10">
                {{-- CLICK PE DIV DESCHIDE LIGHTBOX --}}
                <div id="inlineGalleryContainer" 
                     class="relative w-full aspect-video md:aspect-[16/10] bg-gray-100 dark:bg-[#121212] rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-[#333333] group select-none cursor-pointer"
                     onclick="openLightbox(currentIndex)">
                    
                    {{-- BLUR BACKDROP --}}
                    <img id="mainImageBlur" 
                         src="{{ $service->main_image_url }}" 
                         class="absolute inset-0 w-full h-full object-cover blur-xl opacity-50 scale-110 dark:opacity-30 transition-all duration-500"
                         alt="{{ $service->title }}"> 
                    
                    {{-- MAIN IMAGE --}}
                    <img id="mainImage" 
                         src="{{ $service->main_image_url }}" 
                         class="relative w-full h-full object-contain z-10 transition-transform duration-300" 
                         alt="{{ $service->title }}">
                         
                    {{-- ICONIȚĂ ZOOM (INDICĂ POSIBILITATEA DE MĂRIRE) --}}
                    <div class="absolute top-2 right-2 z-20 bg-black/50 p-1.5 rounded-lg text-white opacity-0 group-hover:opacity-100 transition-opacity">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                         </svg>
                    </div>

                    {{-- BUTOANE NAVIGARE (SĂGEȚI) --}}
                    @if(count($galleryUrls) > 1)
                        <button onclick="prevImage(event)" class="absolute left-2 top-1/2 -translate-y-1/2 z-20 p-2 md:p-3 rounded-full bg-white/60 dark:bg-black/40 hover:bg-white dark:hover:bg-black/70 text-gray-800 dark:text-white transition shadow-lg backdrop-blur-sm flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <button onclick="nextImage(event)" class="absolute right-2 top-1/2 -translate-y-1/2 z-20 p-2 md:p-3 rounded-full bg-white/60 dark:bg-black/40 hover:bg-white dark:hover:bg-black/70 text-gray-800 dark:text-white transition shadow-lg backdrop-blur-sm flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        {{-- INDICATOR MOBIL (BULINE) --}}
                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex gap-1.5 md:hidden pointer-events-none">
                            @foreach($galleryUrls as $index => $img)
                                <div class="w-1.5 h-1.5 rounded-full transition-colors {{ $index === 0 ? 'bg-white' : 'bg-white/40' }}" id="dot-{{$index}}"></div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- THUMBNAILS SCROLL --}}
                @if(!$isDeleted && count($galleryUrls) > 1)
                    <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar snap-x">
                        @foreach($galleryUrls as $index => $img)
                            <div class="snap-start shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden border-2 cursor-pointer transition-all thumbnail-item {{ $index === 0 ? 'border-[#CC2E2E]' : 'border-transparent hover:border-gray-300' }}"
                                 id="thumb-{{ $index }}"
                                 onclick="goToImage({{ $index }})">
                                <img src="{{ $img }}" 
                                     class="w-full h-full object-cover"
                                     alt="Galerie foto {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="prose prose-lg dark:prose-invert max-w-none mb-10 text-gray-700 dark:text-gray-300">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-[#333333]">
                    <svg class="w-5 h-5 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                    Descriere detaliată
                </h3>
                <div class="whitespace-pre-line leading-relaxed text-base md:text-lg">
                    {{ $service->description }}
                </div>
            </div>

            <div class="lg:hidden bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-xl p-4 mb-6">
                <h4 class="text-xs uppercase font-bold text-blue-800 dark:text-blue-200 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Siguranță
                </h4>
                <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1 ml-1 leading-tight">
                    <li>• Nu plătiți niciodată în avans.</li>
                    <li>• Verificați lucrarea la final.</li>
                </ul>
            </div>

        </div>

        {{-- COL 2: SIDEBAR --}}
        <div class="hidden lg:block lg:col-span-4 xl:col-span-3">
            <div class="sticky top-24 space-y-5">

                <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] p-5">
                    
                    {{-- Pret --}}
                    <div class="mb-4 text-center">
                        <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1">Preț Solicitat</p>
                        @if($service->price_value)
                            <div class="flex items-center justify-center gap-1">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($service->price_value, 0, ',', '.') }}</span>
                                <span class="text-base font-bold text-gray-500">{{ $service->currency }}</span>
                            </div>
                            @if($service->price_type === 'negotiable')
                                <span class="inline-block bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-[4px] text-[10px] font-bold uppercase mt-1">Negociabil</span>
                            @endif
                        @else
                            <span class="text-xl font-bold text-[#CC2E2E]">Cere ofertă</span>
                        @endif
                    </div>

                    <div class="h-px bg-gray-100 dark:bg-[#333333] w-full mb-4"></div>

                    @if(!$isDeleted)
                    <div class="flex items-center gap-3 mb-5">
                        <div class="h-10 w-10 shrink-0 rounded-full bg-gray-100 dark:bg-[#333333] flex items-center justify-center text-gray-700 dark:text-white font-bold text-base border border-gray-200 dark:border-[#444]">
                            {{ $displayInitial }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[10px] uppercase text-gray-400 font-bold leading-none mb-0.5">Publicat de</p>
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate leading-tight">{{ $displayName }}</h4>
                        </div>
                    </div>
                    @endif

                    @if($isDeleted)
                        <div class="w-full mb-3">
                            <button disabled class="w-full flex items-center justify-center gap-2 bg-gray-200 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold py-3 rounded-xl cursor-not-allowed border border-gray-300 dark:border-gray-700">
                                <span class="text-base">Telefon Indisponibil</span>
                            </button>
                        </div>
                    @elseif($hasPhone)
                        <div id="phone-wrapper-desktop" class="w-full mb-3">
                            <button onclick="revealPhone('desktop', '{{ $rawPhone }}', '{{ $formattedPhone }}')"
                                    class="w-full flex items-center justify-center gap-2 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold py-3 rounded-xl shadow-lg shadow-red-600/20 transition-all hover:-translate-y-0.5 active:translate-y-0 group">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="text-base">Arată telefon</span>
                            </button>
                        </div>
                    @else
                        <div class="w-full bg-gray-100 dark:bg-[#2C2C2C] py-3 rounded-xl text-center text-gray-500 font-medium mb-3 text-sm">Fără telefon</div>
                    @endif
                    
                    @if(!$isDeleted)
                    <div class="text-center text-[10px] text-green-600 dark:text-green-400 font-bold flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Contact verificat
                    </div>
                    @endif

                    <div class="flex justify-center gap-3 mt-4 pt-3 border-t border-gray-100 dark:border-[#333333]">
                        @if($isDeleted)
                            <button disabled class="text-gray-200 cursor-not-allowed p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg></button>
                            <button disabled class="text-gray-200 cursor-not-allowed p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></button>
                            <button disabled class="text-gray-200 cursor-not-allowed p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z" /></svg></button>
                        @else
                            <button onclick="shareFacebook()" class="text-gray-400 hover:text-blue-600 transition p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg></button>
                            <button onclick="shareWhatsapp()" class="text-gray-400 hover:text-green-500 transition p-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></button>
                            <button onclick="copyLink()" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition p-1"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 012-2v-8a2 2 0 01-2-2h-8a2 2 0 01-2 2v8a2 2 0 012 2z" /></svg></button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const galleryImages = {!! json_encode($galleryUrls) !!};
    let currentIndex = 0;
    let isLightboxOpen = false;

    // --- MAIN LOGIC ---
    function updateMainImage() {
        const src = galleryImages[currentIndex];
        
        // 1. Update Inline Gallery
        const mainImg = document.getElementById('mainImage');
        const blurImg = document.getElementById('mainImageBlur');
        
        mainImg.style.opacity = '0.7';
        setTimeout(() => {
            mainImg.src = src;
            blurImg.src = src;
            mainImg.style.opacity = '1';
        }, 150);

        // Update Thumbnails Border
        document.querySelectorAll('.thumbnail-item').forEach((el, idx) => {
            if(idx === currentIndex) {
                el.classList.add('border-[#CC2E2E]');
                el.classList.remove('border-transparent');
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            } else {
                el.classList.remove('border-[#CC2E2E]');
                el.classList.add('border-transparent');
            }
        });

        // Update Dots (Mobile)
        galleryImages.forEach((_, idx) => {
            const dot = document.getElementById(`dot-${idx}`);
            if(dot) {
                if(idx === currentIndex) {
                    dot.classList.remove('bg-white/40');
                    dot.classList.add('bg-white');
                } else {
                    dot.classList.add('bg-white/40');
                    dot.classList.remove('bg-white');
                }
            }
        });

        // 2. Update Lightbox (if open)
        if(isLightboxOpen) {
            const lbImg = document.getElementById('lightboxImage');
            const lbCounter = document.getElementById('lightboxCounter');
            
            // Fade effect inside lightbox
            lbImg.style.opacity = '0.5';
            setTimeout(() => {
                lbImg.src = src;
                lbImg.style.opacity = '1';
            }, 150);
            
            if(lbCounter) lbCounter.innerText = currentIndex + 1;
        }
    }

    function changeImage(src) {
        const idx = galleryImages.indexOf(src);
        if (idx !== -1) {
            currentIndex = idx;
            updateMainImage();
        }
    }

    function goToImage(index) {
        currentIndex = index;
        updateMainImage();
    }

    function nextImage(e) {
        if(e) e.stopPropagation();
        currentIndex++;
        if (currentIndex >= galleryImages.length) {
            currentIndex = 0; 
        }
        updateMainImage();
    }

    function prevImage(e) {
        if(e) e.stopPropagation();
        currentIndex--;
        if (currentIndex < 0) {
            currentIndex = galleryImages.length - 1; 
        }
        updateMainImage();
    }

    // --- LIGHTBOX FUNCTIONS ---
    function openLightbox(index) {
        if (galleryImages.length === 0) return;

        currentIndex = index;
        isLightboxOpen = true;
        
        const modal = document.getElementById('lightboxModal');
        const lbImg = document.getElementById('lightboxImage');
        const lbCounter = document.getElementById('lightboxCounter');

        lbImg.src = galleryImages[currentIndex];
        if(lbCounter) lbCounter.innerText = currentIndex + 1;

        // Show Modal
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
        }, 10);
        
        // Disable scroll body
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        isLightboxOpen = false;
        const modal = document.getElementById('lightboxModal');
        
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300); // Wait for transition

        // Enable scroll body
        document.body.style.overflow = '';
    }

    // Închide la Escape Key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape" && isLightboxOpen) {
            closeLightbox();
        }
        // Săgeți tastatură
        if (isLightboxOpen) {
            if (event.key === "ArrowRight") nextImage(null);
            if (event.key === "ArrowLeft") prevImage(null);
        }
    });

    // --- SWIPE LOGIC (Unified for both Inline and Lightbox) ---
    function setupSwipe(elementId) {
        const el = document.getElementById(elementId);
        if(!el) return;

        let touchStartX = 0;
        let touchEndX = 0;

        el.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, {passive: true});

        el.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe(touchStartX, touchEndX);
        }, {passive: true});
    }

    function handleSwipe(start, end) {
        const threshold = 50; 
        if (end < start - threshold) {
            nextImage(null);
        }
        if (end > start + threshold) {
            prevImage(null);
        }
    }

    // Init Swipe Listeners
    setupSwipe('inlineGalleryContainer'); // Pt galeria din pagină
    setupSwipe('lightboxModal');          // Pt modalul full screen

    // --- PHONE REVEAL & SHARE ---
    function revealPhone(type, rawPhone, formattedPhone) {
        const wrapperId = type === 'desktop' ? 'phone-wrapper-desktop' : 'phone-wrapper-mobile';
        const wrapper = document.getElementById(wrapperId);
        
        if (!wrapper) return;

        const newHtml = `
            <a href="tel:${rawPhone}" class="w-full flex items-center justify-center gap-2 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold ${type === 'desktop' ? 'py-3' : 'h-12'} rounded-xl shadow-lg shadow-red-600/20 transition-all hover:-translate-y-0.5 active:translate-y-0 group animate-in fade-in zoom-in-95 duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ${type === 'desktop' ? 'group-hover:rotate-12 transition-transform' : 'fill-current'}" ${type === 'mobile' ? 'viewBox="0 0 24 24"' : 'fill="none" viewBox="0 0 24 24" stroke="currentColor"'}>
                    ${type === 'desktop' 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />'
                        : '<path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 1.23 0 2.44.2 3.57.57.35.13.75.05 1.02-.24l2.2-2.2z"/>'
                    }
                </svg>
                <span class="text-base tracking-wide">${formattedPhone}</span>
            </a>
        `;

        wrapper.innerHTML = newHtml;
    }

    function shareFacebook() {
        window.open('https://www.facebook.com/sharer/sharer.php?u={{ urlencode($pageUrl) }}', '_blank');
    }
    
    function shareWhatsapp() {
        window.open('https://api.whatsapp.com/send?text={{ urlencode($service->title . ' - ' . $pageUrl) }}', '_blank');
    }
    
    function copyLink() {
        navigator.clipboard.writeText("{{ $pageUrl }}").then(() => {
            alert('Link copiat!');
        });
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
</style>

@endsection