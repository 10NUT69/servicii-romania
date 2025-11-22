@extends('layouts.app')

@section('title', 'Servicii România - Caută meseriași')

@section('content')

{{-- 
    MODIFICARE FIXĂ: 
    Am schimbat z-50 în z-30 aici. 
    Acum Search-ul stă PESTE carduri (z-0), dar SUB Header (care are z-999).
--}}
<div class="max-w-7xl mx-auto mt-8 mb-12 px-4 md:px-0 relative z-30">

    <form method="GET" action="{{ route('services.index') }}"
        class="relative w-full transition-all duration-300
               bg-white dark:bg-[#1E1E1E] 
               rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-none 
               border border-gray-100 dark:border-[#333333]
               p-2 md:p-3 
               grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3">

        {{-- 1. SEARCH INPUT (4 Coloane) --}}
        <div class="col-span-1 md:col-span-4 relative group">
            <div class="flex items-center h-[3.25rem] w-full rounded-xl px-4
                        bg-gray-50 dark:bg-[#2C2C2C] 
                        border border-transparent 
                        hover:bg-gray-100 dark:hover:bg-[#333333]
                        focus-within:bg-white dark:focus-within:bg-[#252525] 
                        focus-within:ring-2 focus-within:ring-[#CC2E2E]/20 focus-within:border-[#CC2E2E] 
                        transition-all duration-200 cursor-text"
                 onclick="this.querySelector('input').focus()">
                
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 text-[#CC2E2E] opacity-80 group-focus-within:opacity-100 transition-opacity mr-3"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <div class="flex-1 min-w-0">
                    <input type="text"
                        name="search"
                        placeholder="Ce cauți? (ex: Instalator, Zugrav ...)"
                        value="{{ request('search') }}"
                        class="w-full bg-transparent border-none p-0 
                               text-gray-900 dark:text-[#F2F2F2] font-medium 
                               placeholder-gray-400 dark:placeholder-gray-500
                               focus:ring-0 text-base truncate leading-normal">
                </div>
            </div>
        </div>

        {{-- 2. CATEGORY DROPDOWN (3 Coloane) --}}
        <div class="col-span-1 md:col-span-3 relative group" id="category-wrapper">
            <input type="hidden" name="category" id="category-input" value="{{ request('category') }}">
            
            <button type="button" onclick="toggleCategoryDropdown()" 
                class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-4
                       bg-gray-50 dark:bg-[#2C2C2C] 
                       border border-transparent 
                       hover:bg-gray-100 dark:hover:bg-[#333333]
                       focus:bg-white dark:focus:bg-[#252525]
                       focus:ring-2 focus:ring-[#CC2E2E]/20 focus:border-[#CC2E2E]
                       transition-all duration-200 outline-none text-left">
                
                <div class="flex items-center gap-3 overflow-hidden flex-1">
                    <svg class="h-5 w-5 text-[#CC2E2E] opacity-80 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    
                    <span id="category-display" class="truncate font-medium text-gray-700 dark:text-[#F2F2F2] text-base block">
                        @if(request('category') && $selectedCat = $categories->firstWhere('id', request('category')))
                            {{ $selectedCat->name }}
                        @else
                            Toate categoriile
                        @endif
                    </span>
                </div>
                
                <svg id="category-arrow" class="w-4 h-4 text-gray-400 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="category-list" 
                class="hidden absolute top-full left-0 right-0 mt-2 
                       bg-white dark:bg-[#252525] 
                       border border-gray-100 dark:border-[#404040]
                       rounded-xl shadow-xl shadow-gray-400/20 dark:shadow-black/50 
                       z-[100] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100">
                <div class="max-h-80 overflow-y-auto custom-scrollbar p-1.5">
                    
                    <div onclick="selectCategory('', 'Toate categoriile')"
                        class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none mb-1
                        {{ !request('category') 
                            ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' 
                            : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                        Toate categoriile
                    </div>

                    @foreach($categories as $category)
                    <div onclick="selectCategory('{{ $category->id }}', '{{ $category->name }}')"
                        class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none
                        {{ request('category') == $category->id 
                            ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' 
                            : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                        {{ $category->name }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. COUNTY DROPDOWN (3 Coloane) --}}
        <div class="col-span-1 md:col-span-3 relative group" id="county-wrapper">
            <input type="hidden" name="county" id="county-input" value="{{ request('county') }}">

            <button type="button" onclick="toggleCountyDropdown()"
                class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-4
                       bg-gray-50 dark:bg-[#2C2C2C] 
                       border border-transparent 
                       hover:bg-gray-100 dark:hover:bg-[#333333]
                       focus:bg-white dark:focus:bg-[#252525]
                       focus:ring-2 focus:ring-[#CC2E2E]/20 focus:border-[#CC2E2E]
                       transition-all duration-200 outline-none text-left">
                
                <div class="flex items-center gap-3 overflow-hidden flex-1">
                    <svg class="h-5 w-5 text-[#CC2E2E] opacity-80 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    
                    <span id="county-display" class="truncate font-medium text-gray-700 dark:text-[#F2F2F2] text-base block">
                        @if(request('county') && $selectedCounty = $counties->firstWhere('id', request('county')))
                            {{ $selectedCounty->name }}
                        @else
                            Toate județele
                        @endif
                    </span>
                </div>
                <svg id="county-arrow" class="w-4 h-4 text-gray-400 ml-2 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div id="county-list" 
                 class="hidden absolute top-full left-0 right-0 mt-2 
                        bg-white dark:bg-[#252525] 
                        border border-gray-100 dark:border-[#404040] 
                        rounded-xl shadow-xl shadow-gray-400/20 dark:shadow-black/50 
                        z-[100] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100">
                <div class="max-h-80 overflow-y-auto custom-scrollbar p-1.5">
                    
                    <div onclick="selectCounty('', 'Toate județele')"
                         class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none mb-1
                                {{ !request('county') 
                                    ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' 
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                        Toate județele
                    </div>

                    @foreach($counties as $county)
                        <div onclick="selectCounty('{{ $county->id }}', '{{ $county->name }}')"
                             class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none
                                    {{ request('county') == $county->id 
                                        ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' 
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">
                            {{ $county->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 4. ACTIONS (2 Coloane) --}}
        <div class="col-span-1 md:col-span-2 flex items-center gap-2 h-[3.25rem]">
            
            {{-- RESET BUTTON --}}
            @if(request('search') || request('category') || request('county'))
                <a href="{{ route('services.index') }}"
                   class="h-full aspect-square flex items-center justify-center rounded-xl 
                          text-gray-400 hover:text-[#CC2E2E] hover:bg-red-50 dark:hover:bg-red-900/20 border border-transparent hover:border-red-100 dark:hover:border-red-900/30
                          transition-all duration-200 group bg-transparent"
                   title="Resetează filtrele">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif

            {{-- SEARCH BUTTON --}}
            <button type="submit"
                class="h-full flex-1 bg-[#CC2E2E] text-white font-bold rounded-xl shadow-lg shadow-red-600/20
                       hover:bg-[#B72626] hover:shadow-red-600/40 hover:-translate-y-0.5 active:translate-y-0 active:scale-95 
                       transition-all duration-200 flex items-center gap-2 justify-center text-lg tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" 
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="hidden lg:inline">Caută</span>
                <span class="lg:hidden">Caută</span>
            </button>
        </div>

    </form>
</div>

{{-- TITLE --}}
<h2 class="text-2xl md:text-3xl font-bold mb-8 text-gray-900 dark:text-[#F2F2F2] max-w-7xl mx-auto px-4 md:px-0 flex items-center gap-3">
    <span class="w-1.5 h-8 bg-[#CC2E2E] rounded-full"></span>    
    Anunțuri recente
</h2>


{{-- CARDS GRID --}}
<div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 pb-10 px-4 md:px-0 relative z-0">

@forelse($services as $service)

    @php
        // LOGICĂ IMAGINI
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) $images = [];
        $images = array_values(array_filter($images)); 
        $cover = count($images) > 0 ? $images[0] : 'no-image.jpg';
        
        // Logică Favorite
        $isFav = auth()->check() && $service->favorites()->where('user_id', auth()->id())->exists();
    @endphp

    {{-- CARD INDIVIDUAL --}}
    <div class="card-animate relative bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-200 dark:border-[#333333] shadow-sm 
                hover:shadow-xl dark:hover:shadow-none dark:hover:border-[#555555] 
                transition-all duration-300 overflow-hidden group flex flex-col h-full">

        {{-- Favorite Button --}}
        <button type="button"
                onclick="toggleHeart(this, {{ $service->id }})"
                @if(!auth()->check()) onclick="window.location.href='{{ route('login') }}'" @endif
                class="absolute top-3 right-3 z-30 p-2 rounded-full backdrop-blur-md shadow-sm transition-all duration-200
                       bg-white/80 dark:bg-black/50 hover:bg-white dark:hover:bg-[#2C2C2C] group/heart border border-white/20"
                title="Adaugă la favorite">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="heart-icon h-5 w-5 transition-transform duration-300 ease-in-out group-active/heart:scale-75
                       {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E] scale-110' : 'text-gray-600 dark:text-gray-300 fill-none group-hover/heart:text-[#CC2E2E]' }}"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 
                    0-3.597 1.126-4.312 2.733C11.285 4.876 
                    9.623 3.75 7.688 3.75 5.099 3.75 
                    3 5.765 3 8.25c0 7.22 9 12 
                    9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <a href="{{ route('services.show', ['id' => $service->id, 'slug' => $service->slug]) }}" class="block flex-grow flex flex-col">

            {{-- Image Area --}}
            <div class="relative w-full aspect-[4/3] bg-gray-100 dark:bg-[#121212] overflow-hidden">
                <img src="{{ asset('storage/services/' . $cover) }}"
                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                     {{-- SEO: Text alternativ complet pentru Google --}}
                     alt="{{ $service->title }} - {{ $service->county->name }} - {{ $service->category->name }}"
                     {{-- Performanță: Nu încarcă poza până nu ajungi cu scroll la ea --}}
                     loading="lazy">

                {{-- Badge Categorie --}}
                <span class="absolute bottom-3 left-3 bg-black/70 text-white text-xs px-2.5 py-1 rounded-md font-bold uppercase backdrop-blur-md border border-white/10 shadow-lg">
                    {{ $service->category->name }}
                </span>
            </div>

            {{-- Card Content --}}
            <div class="p-4 flex flex-col flex-grow">

                {{-- TITLU --}}
                <h3 class="text-base md:text-lg font-bold text-gray-900 dark:text-[#F2F2F2] mb-2 line-clamp-2 leading-tight min-h-[3.5rem] group-hover:text-[#CC2E2E] transition-colors" 
                    title="{{ $service->title }}">
                    {{ $service->title }}
                </h3>

                {{-- PREȚ --}}
                <div class="mb-3">
                    @if(!empty($service->price_value))
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                            </span>
                            @if($service->price_type === 'negotiable')
                                <span class="text-gray-500 dark:text-gray-400 text-xs font-normal">Neg.</span>
                            @endif
                        </div>
                    @else
                        <span class="text-lg font-bold text-[#CC2E2E]">Cere ofertă</span>
                    @endif
                </div>

                {{-- META INFO --}}
                <div class="mt-auto pt-3 flex items-center justify-between text-xs md:text-sm text-gray-500 dark:text-[#A1A1AA] border-t border-gray-100 dark:border-[#333333]">
                    
                    {{-- Stânga: Locație --}}
                    <div class="flex items-center gap-1.5 truncate max-w-[50%]" title="{{ $service->city ?? $service->county->name }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#CC2E2E] opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate font-medium">{{ $service->city ?? $service->county->name }}</span>
                    </div>

                    {{-- Dreapta: Vizualizări & Dată --}}
                    <div class="flex items-center gap-3">
                        
                        {{-- Vizualizări --}}
                        <div class="flex items-center gap-1 opacity-80" title="Vizualizări">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="font-medium">{{ $service->views ?? 0 }}</span>
                        </div>

                        {{-- Dată --}}
                        <span class="opacity-60 text-gray-300 dark:text-gray-600">|</span>
                        <span class="opacity-80 whitespace-nowrap">{{ $service->created_at->diffForHumans(null, true, true) }}</span>
                    </div>

                </div>

            </div>
        </a>
    </div>

@empty
    {{-- EMPTY STATE --}}
    <div class="col-span-full text-center py-20 bg-white dark:bg-[#1E1E1E] rounded-2xl border border-dashed border-gray-300 dark:border-[#333333]">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#2C2C2C] mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nu am găsit rezultate</h3>
        <p class="text-gray-500 dark:text-[#A1A1AA] mb-6">Încearcă să cauți altceva sau să elimini filtrele.</p>
        <a href="{{ route('services.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#CC2E2E] text-white font-bold rounded-xl hover:bg-[#B72626] transition-colors">
            Resetează tot
        </a>
    </div>
@endforelse

</div>

{{-- Pagination --}}
@if($services->hasPages())
    <div class="mt-4 mb-16 max-w-7xl mx-auto px-4 md:px-0">
        {{ $services->links() }}
    </div>
@endif


{{-- STILURI CUSTOM + ANIMAȚII --}}
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #d1d5db; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-animate {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }
    /* Stagger delays for first 12 items */
    .card-animate:nth-child(1) { animation-delay: 0.05s; }
    .card-animate:nth-child(2) { animation-delay: 0.1s; }
    .card-animate:nth-child(3) { animation-delay: 0.15s; }
    .card-animate:nth-child(4) { animation-delay: 0.2s; }
    .card-animate:nth-child(5) { animation-delay: 0.25s; }
    .card-animate:nth-child(6) { animation-delay: 0.3s; }
    .card-animate:nth-child(7) { animation-delay: 0.35s; }
    .card-animate:nth-child(8) { animation-delay: 0.4s; }
    .card-animate:nth-child(9) { animation-delay: 0.45s; }
    .card-animate:nth-child(10) { animation-delay: 0.5s; }
    .card-animate:nth-child(11) { animation-delay: 0.55s; }
    .card-animate:nth-child(12) { animation-delay: 0.6s; }
</style>


{{-- SCRIPTS --}}
<script>
function toggleCountyDropdown() {
    const list = document.getElementById('county-list');
    const arrow = document.getElementById('county-arrow');
    document.getElementById('category-list').classList.add('hidden');
    document.getElementById('category-arrow').style.transform = 'rotate(0deg)';
    
    if (list.classList.contains('hidden')) {
        list.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        list.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function selectCounty(id, name) {
    document.getElementById('county-input').value = id;
    document.getElementById('county-display').innerText = name;
    toggleCountyDropdown();
}

function toggleCategoryDropdown() {
    const list = document.getElementById('category-list');
    const arrow = document.getElementById('category-arrow');
    document.getElementById('county-list').classList.add('hidden');
    document.getElementById('county-arrow').style.transform = 'rotate(0deg)';

    if (list.classList.contains('hidden')) {
        list.classList.remove('hidden');
        arrow.style.transform = 'rotate(180deg)';
    } else {
        list.classList.add('hidden');
        arrow.style.transform = 'rotate(0deg)';
    }
}

function selectCategory(id, name) {
    document.getElementById('category-input').value = id;
    document.getElementById('category-display').innerText = name;
    toggleCategoryDropdown();
}

document.addEventListener('click', function(event) {
    const countyWrap = document.getElementById('county-wrapper');
    const categoryWrap = document.getElementById('category-wrapper');
    
    if (countyWrap && !countyWrap.contains(event.target)) {
        document.getElementById('county-list').classList.add('hidden');
        document.getElementById('county-arrow').style.transform = 'rotate(0deg)';
    }
    
    if (categoryWrap && !categoryWrap.contains(event.target)) {
        document.getElementById('category-list').classList.add('hidden');
        document.getElementById('category-arrow').style.transform = 'rotate(0deg)';
    }
});

function toggleHeart(btn, serviceId) {
    const icon = btn.querySelector('svg');
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
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ service_id: serviceId })
    })
    .then(res => res.json())
    .catch(err => console.error(err));
}
</script>

@endsection