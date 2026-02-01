@extends('layouts.app')

@php
    // --- 1. PRELUARE DATE (SECURIZATĂ) ---
    // Păstrăm numele variabilelor exact cum le cere formularul tău de mai jos
    $selectedCategoryId = request('category') ?? optional($currentCategory ?? null)->id ?? null;
    $selectedCountyId   = request('county') ?? optional($currentCounty ?? null)->id ?? null;

    // Identificăm obiectele (Categorie / Județ)
    $categoryObj = $selectedCategoryId ? $categories->firstWhere('id', $selectedCategoryId) : null;
    $countyObj   = $selectedCountyId ? $counties->firstWhere('id', $selectedCountyId) : null;

    $catName    = optional($categoryObj)->name;
    $countyName = optional($countyObj)->name;
    $year       = date('Y');

    // --- 2. LOGICA SEO "SMART" (Optimizată pentru Conversie și Încredere) ---
    
    // SCENARIUL DEFAULT (Homepage)
    // Titlu: Autoritate generală. Acoperă cele mai căutate 3 meserii.
    $seoTitle = "Găsește Meseriași Verificați - Instalatori, Electricieni, Constructori";
    $seoDesc  = "Platforma de servicii locale ➤ Profesioniști verificați în toată țara ➤ Publică anunț gratuit sau contactează direct meseriașul din zona ta ➤ Vezi recenzii și tarife.";

    // SCENARIUL 1: Categorie + Județ (Ex: "Instalator București") -> "MONEY PAGE"
    // Aceasta este pagina care îți aduce traficul cel mai valoros.
    if ($catName && $countyName) {
        // Titlu: [Meserie] [Oraș] - [Beneficiu 1], [Beneficiu 2] și [Încredere]
        $seoTitle = "{$catName} {$countyName} - Prețuri, Recenzii și Meseriași Verificați";
        
        // Descriere: Întrebare (relevanță) + Săgeți (atenție) + CTA (acțiune)
        $seoDesc  = "Cauți {$catName} în {$countyName}? ➤ Vezi lista cu profesioniști disponibili acum ➤ Compară tarife, citește recenzii reale și cere o ofertă gratuit.";
    } 
    
    // SCENARIUL 2: Doar Categorie (Ex: "Acoperișuri" sau "Electrician")
    elseif ($catName) {
        // Titlu: Autoritate pe nișă + Anul curent (arată că ești activ)
        $seoTitle = "{$catName} - Oferte și Firme Autorizate ({$year})";
        
        // Descriere: Focus pe portofoliu și siguranță
        $seoDesc  = "Ai nevoie de {$catName}? ➤ Găsește rapid meseriași și firme autorizate în zona ta ➤ Vezi portofolii ➤ Cere oferte gratuite și alege informat.";
    } 
    
    // SCENARIUL 3: Doar Județ (Ex: "Cluj")
    elseif ($countyName) {
        // Titlu: Diversitate locală
        $seoTitle = "Meseriași și Constructori în {$countyName} - Disponibili Acum";
        
        // Descriere: Rezolvarea problemelor diverse
        $seoDesc  = "Renovezi sau ai o urgență în {$countyName}? ➤ Găsește instalatori, zugravi, electricieni și alți profesioniști verificați ➤ Contactează-i direct.";
    }

    $canonicalUrl = url()->current(); 
@endphp

{{-- --- 3. META TAGS --- --}}
@section('meta_title', $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_image', asset('images/social-share.webp'))

@section('canonical')
    <link rel="canonical" href="{{ $canonicalUrl }}">
@endsection

{{-- Aici începe secțiunea HERO --}}
@section('hero')
<div class="relative w-full bg-gray-900 group">
    
    {{-- A. IMAGINE + OVERLAY (Aceasta rămâne neschimbată) --}}
    <div class="absolute inset-0 h-[280px] md:h-[350px] w-full overflow-hidden z-0">
        <img src="{{ asset('images/hero-desktop.webp') }}" alt="Meserias Bun Fundal" 
             class="hidden md:block w-full h-full object-cover object-center transform transition duration-1000 group-hover:scale-105">
        <img src="{{ asset('images/hero-mobile.webp') }}" alt="Meserias Bun Fundal" 
             class="block md:hidden w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-black/50"></div>
    </div>

    {{-- B. CONȚINUT TEXT --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 h-auto md:h-[350px] flex flex-col justify-start md:justify-center items-start pt-14 pb-4 md:pt-10 md:pb-8
">        
        <h1 class="text-white font-extrabold tracking-tight drop-shadow-xl text-left mb-2 md:mb-4 max-w-2xl animate-in slide-in-from-left-4 duration-700">
            <span class="block text-2xl md:text-3xl lg:text-4xl mb-1 md:mb-2">GĂSEȘTI MEȘTERI</span>
            <span class="block text-2xl md:text-3xl lg:text-4xl text-white font-black">
                VERIFICAȚI, RAPID!
            </span>
        </h1>

        <p class="text-gray-200 text-sm md:text-lg max-w-xl font-medium mb-4 md:mb-8 leading-relaxed shadow-black drop-shadow-md hidden md:block">
            Găsești profesioniști pentru proiectul tău, oriunde în România.
        </p>
    </div>

    {{-- C. BARA DE CĂUTARE NOUĂ (Compactă, Aliniată Stânga) --}}
    <div class="relative z-30 max-w-7xl mx-auto px-4 mt-0 md:-mt-24 pb-2 md:pb-4">
        
        <form id="search-form" onsubmit="event.preventDefault(); loadServices(1);"
    class="relative w-full transition-all duration-300 ease-out grid grid-cols-2 md:grid-cols-12 gap-2 md:gap-4 bg-transparent p-0">

    {{-- 1. CATEGORY DROPDOWN --}}
    {{-- Pe mobil: col-span-1 (50%), Pe desktop: md:col-span-3 (mai scurt, 25%) --}}
    <div id="cat-col" class="col-span-1 md:col-span-3 relative group">
        <input type="hidden" name="category" id="category-input" value="{{ $selectedCategoryId }}">
        <button type="button" onclick="toggleCategoryDropdown()" 
            class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-3 md:px-4 transition-all duration-200 outline-none text-left whitespace-nowrap
                   bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333333] shadow-lg shadow-gray-200/20
                   hover:bg-gray-50 dark:hover:bg-[#252525]
                   group-focus:ring-2 group-focus:ring-[#CC2E2E]/20">
            
            <span id="category-display" class="font-medium text-gray-700 dark:text-gray-200 truncate mr-1 md:mr-2 text-sm md:text-base">
    {{ $selectedCategoryName ?? 'Ce cauți? (ex: Instalator...)' }}
</span>
            
            <svg id="category-arrow" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Dropdown list --}}
        <div id="category-list" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#252525] rounded-xl shadow-2xl z-[999] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100 border border-gray-100 dark:border-[#404040] max-h-80 overflow-y-auto custom-scrollbar min-w-[200px] md:min-w-0">
             {{-- (Notă: am pus min-w-[200px] ca pe mobil lista să fie puțin mai lată decât inputul dacă e nevoie) --}}
            <div class="p-1.5">
                <div onclick="selectCategory('', 'Ce cauți?')" class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none mb-1 text-gray-500 italic hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]">Toate categoriile</div>
                @foreach($categories as $category)
                    <div onclick="selectCategory('{{ $category->id }}', '{{ $category->name }}')"
                         class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none
                         {{ (string)$selectedCategoryId === (string)$category->id ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                        {{ $category->name }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 2. COUNTY DROPDOWN --}}
    {{-- Pe mobil: col-span-1 (50%), Pe desktop: md:col-span-3 (mai scurt, 25%) --}}
    <div id="county-col" class="col-span-1 md:col-span-3 relative group">
        <input type="hidden" name="county" id="county-input" value="{{ $selectedCountyId }}">
        <button type="button" onclick="toggleCountyDropdown()" 
            class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-3 md:px-4 transition-all duration-200 outline-none text-left whitespace-nowrap
                   bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333333] shadow-lg shadow-gray-200/20
                   hover:bg-gray-50 dark:hover:bg-[#252525]">
            <span id="county-display" class="font-medium text-gray-700 dark:text-gray-200 truncate mr-1 md:mr-2 text-sm md:text-base">
    {{ $selectedCountyName ?? 'Toată Țara' }}
</span>
            <svg id="county-arrow" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div id="county-list" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#252525] rounded-xl shadow-2xl z-[999] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100 border border-gray-100 dark:border-[#404040] max-h-80 overflow-y-auto custom-scrollbar min-w-[200px] md:min-w-0 text-left">
             {{-- (Notă: added text-left to force alignment inside list) --}}
            <div class="p-1.5">
                <div onclick="selectCounty('', 'Județ')" class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none mb-1 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]">Toată țara</div>
                @foreach($counties as $county)
                    <div onclick="selectCounty('{{ $county->id }}', '{{ $county->name }}')"
                         class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none
                         {{ (string)$selectedCountyId === (string)$county->id ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                        {{ $county->name }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. ACTIONS --}}
    {{-- Pe mobil: col-span-2 (Full width pe rând nou), Pe desktop: md:col-span-2 (Compact) --}}
    <div id="actions-col" class="col-span-2 md:col-span-2 flex gap-2 h-[3.25rem] overflow-visible mt-1 md:mt-0">
        <button type="button" id="reset-btn" onclick="resetFilters()"
           class="hidden flex-1 h-full items-center justify-center gap-2 rounded-xl transition-all duration-300 group animate-in slide-in-from-right-4 fade-in whitespace-nowrap
                  bg-white text-[#CC2E2E] border border-red-100 shadow-lg shadow-red-100/50 hover:bg-red-50 font-bold text-sm
                  dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30 dark:hover:bg-red-900/40 dark:shadow-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:rotate-90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span class="md:hidden lg:inline">Șterge</span>
        </button>

        <button type="submit" class="flex-1 h-full bg-[#CC2E2E] text-white font-bold rounded-xl shadow-lg shadow-red-600/30 hover:bg-[#B72626] transition-all flex items-center gap-2 justify-center text-lg tracking-wide hover:-translate-y-0.5 active:translate-y-0 min-w-[100px]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Caută</span>
        </button>
    </div>

</form>
    </div>
</div>
@endsection

@section('content')
{{-- === 1. BANNER MESERIAȘI (CLEAN - TEXT NOU) === --}}
<div class="relative z-10 max-w-7xl mx-auto px-4 mt-1 md:mt-6 mb-2">
    {{-- Container Alb Complet, Rotunjit, cu Umbră fină --}}
    <div class="bg-white border border-gray-100 rounded-xl shadow-lg shadow-gray-200/40 p-3 md:py-4 md:px-6 flex flex-col md:flex-row items-center justify-between gap-4 md:gap-8 transition-transform hover:-translate-y-0.5 duration-300 group">
        
        {{-- Partea Stângă: Text --}}
        <div class="flex flex-row items-center gap-4 text-center md:text-left">
            {{-- Iconiță Meseriaș --}}
            <div class="hidden md:flex h-10 w-10 bg-red-50 rounded-full items-center justify-center text-[#CC2E2E] group-hover:scale-110 transition-transform flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>

            <div class="flex flex-col md:block">
                {{-- TITLU: Gri închis (soft) combinat cu Roșu --}}
                <span class="text-gray-700 font-bold text-lg md:text-xl tracking-tight leading-tight">
                    Oferi Servicii sau Ești <span class="text-[#CC2E2E] font-extrabold">Meseriaș?</span>
                </span>
                
                {{-- SUBTITLU: Gri mediu, ușor de citit --}}
                <span class="text-gray-500 text-sm md:text-base ml-0 md:ml-1 font-medium block md:inline mt-1 md:mt-0">
                    Publică anunțuri <span class="text-[#CC2E2E] font-bold">Gratuit & Nelimitat</span> și clienții te vor contacta.
                </span>
            </div>
        </div>

        {{-- Partea Dreaptă: Buton --}}
        <div class="w-full md:w-auto flex-shrink-0">
            <a href="/adauga-anunt" 
               class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#CC2E2E] hover:bg-[#b72626] text-white font-bold text-sm rounded-lg shadow-md hover:shadow-lg transition-all">
                <span>Adaugă Anunț</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>

    </div>
</div>
{{-- TITLU LISTĂ --}}
<div class="mt-5 md:mt-12 mb-8 flex items-center gap-3 max-w-7xl mx-auto px-4">
    <span class="w-1.5 h-8 bg-[#CC2E2E] rounded-full shadow-sm"></span>      
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-[#F2F2F2]">
        Anunțuri recente
    </h2>
</div>

{{-- GRID ANUNȚURI --}}
<div id="services-container" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 pb-10 relative z-0 max-w-7xl mx-auto px-4">
    @include('services.partials.service_cards', ['services' => $services])
</div>

{{-- LOADING --}}
<div id="loading-indicator" class="text-center py-8 {{ $services->isEmpty() || !$hasMore ? 'hidden' : '' }}">
    <svg class="animate-spin h-8 w-8 text-[#CC2E2E] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3.003 7.91l2.997-2.619z"></path>
    </svg>
    <p class="text-sm text-gray-500 mt-2">Se încarcă...</p>
</div>

<div id="load-more-trigger"
     data-next-page="2"
     data-has-more="{{ $hasMore ? 'true' : 'false' }}"
     style="height: 1px;"></div>

<script>
    // ========= Hărți ID → slug pentru URL SEO =========
    const baseUrl = "{{ url('/') }}";
    const categoriesSlugMap = @json($categories->mapWithKeys(fn($c) => [$c->id => $c->slug]));
    const countiesSlugMap   = @json($counties->mapWithKeys(fn($c) => [$c->id => $c->slug]));

    let isLoading   = false;
    let currentPage = 2; // Pagina următoare (pentru load initial PHP e pagina 1)
    let hasMore     = document.getElementById('load-more-trigger').dataset.hasMore === 'true';
    let debounceTimer;

    // Observer setup
    const observer = new IntersectionObserver((entries) => {
        // Verificăm dacă elementul e vizibil, nu încărcăm deja ceva și mai avem pagini
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            loadServices(currentPage);
        }
    }, { rootMargin: '0px 0px 400px 0px' }); // Declanseaza cu 400px inainte de final

    document.addEventListener('DOMContentLoaded', () => {
        checkResetVisibility();
        const trigger = document.getElementById('load-more-trigger');
        if(trigger) observer.observe(trigger);
        updateUrl(false);
    });

    // ... [Funcțiile buildSeoUrlFromFilters, updateUrl, checkResetVisibility, resetFilters rămân neschimbate] ...
    
    // Copiază funcțiile tale existente: buildSeoUrlFromFilters, updateUrl, checkResetVisibility AICI
    // Pentru claritate, rescriu doar loadServices si resetFilters care sunt importante

    function buildSeoUrlFromFilters(categoryId, countyId) {
        categoryId = categoryId || null;
        countyId   = countyId || null;
        if (!categoryId) return baseUrl;
        const catSlug = categoriesSlugMap[categoryId];
        if (!catSlug) return baseUrl;
        if (!countyId) return `${baseUrl}/${catSlug}`;
        const countySlug = countiesSlugMap[countyId];
        if (!countySlug) return `${baseUrl}/${catSlug}`;
        return `${baseUrl}/${catSlug}/${countySlug}`;
    }

    function updateUrl(push = false) {
        const categoryId = document.getElementById('category-input').value || null;
        const countyId   = document.getElementById('county-input').value || null;
        // Nu mai există 'search-input'
        
        const seoUrl = buildSeoUrlFromFilters(categoryId, countyId);
        // Nu mai avem parametri de query string (?search=...)
        
        const state  = { categoryId, countyId };
        if (push) window.history.pushState(state, '', seoUrl);
        else window.history.replaceState(state, '', seoUrl);
    }

    // --- 2. CHECK RESET VISIBILITY (Simplificat) ---
    function checkResetVisibility() {
        const c = document.getElementById('category-input').value;
        const j = document.getElementById('county-input').value;
        const btn = document.getElementById('reset-btn');
        // Nu mai avem nevoie să redimensionăm coloanele, lăsăm layout-ul fix
        // pentru că alinierea e la stânga și nu deranjează.
        
        if (c || j) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }

    // --- 3. RESET FILTERS ---
    function resetFilters() {
        // Reset inputs
        document.getElementById('category-input').value = '';
        document.getElementById('county-input').value = '';
        
        // Reset display text
        document.getElementById('category-display').innerText = 'Ce cauți? (ex: Instalator...)'; // Textul default
        document.getElementById('county-display').innerText   = 'Toată Țara';
        
        // Hide lists
        document.getElementById('category-list').classList.add('hidden');
        document.getElementById('county-list').classList.add('hidden');

        checkResetVisibility();
        updateUrl(true);
        loadServices(1);
    }

    // --- 4. LOAD SERVICES (Fără parametrul search) ---
    function loadServices(page) {
        const isNewFilter = page === 1;
        
        if (isLoading) return;
        if (!hasMore && !isNewFilter) return;

        if (isNewFilter) {
            hasMore = true; 
            document.getElementById('services-container').style.opacity = '0.5'; 
            document.getElementById('load-more-trigger').dataset.hasMore = 'true';
            checkResetVisibility();
        } else {
            document.getElementById('loading-indicator').classList.remove('hidden');
        }

        isLoading = true;

        const params = new URLSearchParams({
            // search: document.getElementById('search-input').value, // Am scos linia asta
            category: document.getElementById('category-input').value,
            county:     document.getElementById('county-input').value,
            page: page,
            ajax: 1
        });

        fetch(`{{ route('services.index') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            // ... (Restul funcției rămâne identic cu ce aveai înainte)
            // Doar copiază corpul .then() din codul anterior, e perfect valabil.
            const container = document.getElementById('services-container');
            if (isNewFilter) {
                container.innerHTML = data.html;
                container.style.opacity = '1';
                currentPage = 2; 
                if (data.loadedCount === 0) {
                     // Aici asigura-te ca HTML-ul de eroare nu cere sa scrii altceva, 
                     // ci doar sa resetezi filtrele.
                     container.innerHTML = `<div class="col-span-full... (HTML-ul tau de empty state)...</div>`;
                }
            } else {
                container.insertAdjacentHTML('beforeend', data.html);
                currentPage++; 
            }
            hasMore = data.hasMore;
            document.getElementById('load-more-trigger').dataset.hasMore = hasMore;
            
            if (hasMore) {
                const trigger = document.getElementById('load-more-trigger');
                observer.unobserve(trigger);
                observer.observe(trigger);
                setTimeout(() => {
                   if (trigger.getBoundingClientRect().top < window.innerHeight) {
                       loadServices(currentPage);
                   }
                }, 200);
            }
        })
        .finally(() => {
            isLoading = false;
            document.getElementById('loading-indicator').classList.add('hidden');
        });
    }
   
    // ========= SELECTARE COUNTY / CATEGORY (Functions) =========
    function selectCounty(id, name) {
        document.getElementById('county-input').value   = id;
        document.getElementById('county-display').innerText = name;
        toggleCountyDropdown(); 
        checkResetVisibility();
        updateUrl(true);   
        loadServices(1);
    }

    function selectCategory(id, name) {
        document.getElementById('category-input').value   = id;
        document.getElementById('category-display').innerText = name;
        toggleCategoryDropdown(); 
        checkResetVisibility();
        updateUrl(true);   
        loadServices(1);
    }
    
    function toggleCountyDropdown() { 
        const list  = document.getElementById('county-list');
        const arrow = document.getElementById('county-arrow');
        document.getElementById('category-list').classList.add('hidden');
        list.classList.toggle('hidden');
        arrow.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }
    
    function toggleCategoryDropdown() { 
        const list  = document.getElementById('category-list');
        const arrow = document.getElementById('category-arrow');
        document.getElementById('county-list').classList.add('hidden');
        list.classList.toggle('hidden');
        arrow.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    document.addEventListener('click', function(event) {
        const catGroup    = document.getElementById('cat-col');
        const countyGroup = document.getElementById('county-col');
        
        if (catGroup && !catGroup.contains(event.target)) {
            document.getElementById('category-list').classList.add('hidden');
            const arrow = document.getElementById('category-arrow');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
        if (countyGroup && !countyGroup.contains(event.target)) {
            document.getElementById('county-list').classList.add('hidden');
            const arrow = document.getElementById('county-arrow');
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });

    // ========= FAVORITE =========
    function toggleHeart(btn, serviceId) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}"; 
            return;
        @endif

        const icon    = btn.querySelector('svg');
        const isLiked = icon.classList.contains('text-[#CC2E2E]');
        if (isLiked) {
            icon.classList.remove('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-110');
            icon.classList.add('text-gray-600', 'dark:text-gray-300', 'fill-none');
        } else {
            icon.classList.remove('text-gray-600', 'dark:text-gray-300', 'fill-none');
            icon.classList.add('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-125');
            setTimeout(() => {
                icon.classList.remove('scale-125');
                icon.classList.add('scale-110');
            }, 200);
        }

        fetch("{{ route('favorite.toggle') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ service_id: serviceId })
        }).catch(err => console.error(err));
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }
</style>
@endsection

