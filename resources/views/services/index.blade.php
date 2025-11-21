@extends('layouts.app')

@section('title', 'Servicii România')

@section('content')

{{-- 
    NOTA: Asigura-te ca in layouts/app.blade.php, tag-ul <body> are:
    <body class="bg-gray-50 dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5]"> 
--}}

{{-- 🔥 FIX: Am schimbat z-20 în z-50 aici. Asta ridică toată bara de căutare peste carduri --}}
<div class="max-w-7xl mx-auto mt-8 mb-12 relative z-50">

    {{-- SEARCH CONTAINER --}}
    <form method="GET" action="{{ route('services.index') }}"
        class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-200 dark:border-[#333333]
               p-4 md:p-5 flex flex-col md:flex-row items-center gap-4 transition-colors duration-300">

        {{-- 1. SEARCH BAR INPUT --}}
        <div class="flex items-center w-full bg-gray-50 dark:bg-[#2C2C2C] rounded-xl border border-gray-300 dark:border-[#404040] px-4 transition-colors focus-within:border-[#CC2E2E] focus-within:ring-1 focus-within:ring-[#CC2E2E]">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-gray-400 dark:text-gray-400"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            <input type="text"
                name="search"
                placeholder="Ex: electrician, zugrav, instalator"
                value="{{ request('search') }}"
                class="flex-1 bg-transparent border-none focus:ring-0 
                       py-3 px-3 text-gray-700 dark:text-[#F2F2F2] dark:placeholder-gray-500 text-base placeholder-gray-400">
        </div>

        {{-- 2. CUSTOM COUNTY DROPDOWN (Modernizat & Înalt) --}}
        <div class="relative w-full md:w-72 group" id="county-wrapper">
            
            {{-- Input Ascuns care trimite datele real --}}
            <input type="hidden" name="county" id="county-input" value="{{ request('county') }}">

            {{-- Butonul Trigger --}}
            <button type="button" 
                    onclick="toggleCountyDropdown()"
                    class="w-full flex items-center justify-between pl-4 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040] 
                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-700 dark:text-[#F2F2F2] 
                           focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent outline-none transition cursor-pointer text-left h-full">
                
                <div class="flex items-center gap-2 overflow-hidden">
                    <svg class="h-5 w-5 text-gray-400 flex-shrink-0 group-hover:text-[#CC2E2E] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    
                    <span id="county-display" class="truncate font-medium">
                        @if(request('county') && $selectedCounty = $counties->firstWhere('id', request('county')))
                            {{ $selectedCounty->name }}
                        @else
                            Toate județele
                        @endif
                    </span>
                </div>

                <svg id="county-arrow" class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            {{-- Lista Dropdown (Z-Index Mare & Înălțime Dublă) --}}
            <div id="county-list" 
                 class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#252525] border border-gray-200 dark:border-[#404040] 
                        rounded-xl shadow-2xl z-[100] overflow-hidden origin-top transition-all duration-200">
                
                {{-- max-h-96 = 384px (destul de înalt) --}}
                <div class="max-h-96 overflow-y-auto custom-scrollbar p-1">
                    
                    {{-- Opțiunea "Toate județele" --}}
                    <div onclick="selectCounty('', 'Toate județele')"
                         class="px-4 py-2.5 rounded-lg cursor-pointer transition-colors text-sm font-medium select-none
                                {{ !request('county') ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#333333]' }}">
                        Toate județele
                    </div>

                    {{-- Iterare Județe --}}
                    @foreach($counties as $county)
                        <div onclick="selectCounty('{{ $county->id }}', '{{ $county->name }}')"
                             class="px-4 py-2.5 rounded-lg cursor-pointer transition-colors text-sm font-medium select-none
                                    {{ request('county') == $county->id ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#333333]' }}">
                            {{ $county->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. SEARCH BUTTON --}}
        <button
            class="px-8 py-3 bg-[#CC2E2E] text-white font-bold rounded-xl shadow-lg shadow-red-500/30
                   hover:bg-[#B72626] hover:shadow-red-500/50 hover:-translate-y-0.5 active:translate-y-0 active:scale-95 
                   transition-all duration-200 w-full md:w-auto flex items-center gap-2 justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Caută
        </button>

    </form>
</div>

{{-- Title --}}
<h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-[#F2F2F2] max-w-7xl mx-auto transition-colors pl-2 md:pl-0 flex items-center gap-2">
    <span class="w-1 h-6 bg-[#CC2E2E] rounded-full"></span>    
    Anunțuri recente
</h2>


{{-- Cards Grid --}}
{{-- z-0 aici pentru a ne asigura că sunt sub meniul de sus --}}
<div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 pb-10 px-2 md:px-0 relative z-0">

@forelse($services as $service)

    @php
        // LOGICĂ IMAGINI (BLINDATĂ)
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) $images = [];
        $images = array_values(array_filter($images)); // Elimină null/empty
        $cover = count($images) > 0 ? $images[0] : 'no-image.jpg';
        
        // Logică Favorite
        $isFav = auth()->check() && $service->favorites()->where('user_id', auth()->id())->exists();
    @endphp

    {{-- One Card --}}
    <div class="relative bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-200 dark:border-[#333333] shadow-sm 
                hover:shadow-xl dark:hover:shadow-none dark:hover:border-[#555555] 
                transition-all duration-300 overflow-hidden group h-full flex flex-col">

        {{-- Favorite Button (CU EFECT POP & Z-INDEX MIC) --}}
        {{-- z-30 este suficient să fie peste imagine, dar sub dropdown (care e in z-50 parent) --}}
        <button type="button"
                onclick="toggleHeart(this, {{ $service->id }})"
                @if(!auth()->check()) onclick="window.location.href='{{ route('login') }}'" @endif
                class="absolute top-2 right-2 z-30 p-2 rounded-full backdrop-blur-md shadow-sm transition-all duration-200
                       bg-white/80 dark:bg-black/50 hover:bg-white dark:hover:bg-[#2C2C2C] group/heart"
                title="Adaugă la favorite">

            <svg xmlns="http://www.w3.org/2000/svg"
                class="heart-icon h-5 w-5 transition-transform duration-300 ease-in-out group-active/heart:scale-75
                       {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E] scale-110' : 'text-gray-500 dark:text-gray-300 fill-none group-hover/heart:text-[#CC2E2E]' }}"
                viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 
                    0-3.597 1.126-4.312 2.733C11.285 4.876 
                    9.623 3.75 7.688 3.75 5.099 3.75 
                    3 5.765 3 8.25c0 7.22 9 12 
                    9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <a href="{{ route('services.show', ['id' => $service->id, 'slug' => $service->slug]) }}" class="block flex-grow">

            {{-- Image --}}
            <div class="relative w-full aspect-[4/3] bg-gray-100 dark:bg-[#121212] overflow-hidden">
                <img src="{{ asset('storage/services/' . $cover) }}"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">

                <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded-md font-bold uppercase backdrop-blur-sm border border-white/10">
                    {{ $service->category->name }}
                </span>
            </div>

            {{-- Card Content --}}
            <div class="p-4 flex flex-col h-full">

                <h3 class="text-sm font-bold text-gray-900 dark:text-[#F2F2F2] mb-1 uppercase 
                           truncate transition-colors group-hover:text-[#CC2E2E]" 
                    title="{{ $service->title }}">
                    {{ $service->title }}
                </h3>

                {{-- Price --}}
                <div class="text-base font-bold mb-1">
                    @if(!empty($service->price_value))
                        <span class="text-gray-900 dark:text-white">
                            {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                        </span>
                        @if($service->price_type === 'negotiable')
                            <span class="text-gray-500 dark:text-gray-400 text-xs font-normal ml-1">Neg.</span>
                        @endif
                    @else
                        <span class="text-[#CC2E2E]">Cere ofertă</span>
                    @endif
                </div>

                {{-- Meta Info --}}
                <div class="mt-auto pt-3 flex items-center gap-2 text-[11px] text-gray-500 dark:text-[#A1A1AA] border-t border-gray-100 dark:border-[#333333]">

                    <span class="flex items-center gap-1 truncate max-w-[50%]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $service->city ?? $service->county->name }}
                    </span>

                    <span class="text-gray-300 dark:text-[#404040]">|</span>

                    <span class="flex items-center gap-1">
                        {{-- Vizualizări --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $service->views ?? 0 }}
                    </span>

                    <span class="ml-auto text-[10px] opacity-70">{{ $service->created_at->format('d.m') }}</span>
                </div>

            </div>
        </a>
    </div>

@empty
    <div class="col-span-full text-center py-16">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-[#2C2C2C] mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <p class="text-gray-500 dark:text-[#A1A1AA] text-lg">Nu am găsit anunțuri conform criteriilor.</p>
        <a href="{{ route('services.index') }}" class="text-[#CC2E2E] font-bold hover:underline mt-2 inline-block">Resetează filtrele</a>
    </div>
@endforelse

</div>

{{-- Pagination --}}
<div class="mt-4 mb-16 max-w-7xl mx-auto px-4 md:px-0">
    {{ $services->links() }}
</div>


{{-- STILURI CUSTOM --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #4b5563;
    }
    /* Pentru animația Pop */
    .ease-spring {
        transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
</style>


{{-- SCRIPTS --}}
<script>
// 1. DROPDOWN LOGIC
function toggleCountyDropdown() {
    const list = document.getElementById('county-list');
    const arrow = document.getElementById('county-arrow');
    
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

document.addEventListener('click', function(event) {
    const wrapper = document.getElementById('county-wrapper');
    const list = document.getElementById('county-list');
    const arrow = document.getElementById('county-arrow');
    
    if (!wrapper.contains(event.target)) {
        if (!list.classList.contains('hidden')) {
            list.classList.add('hidden');
            arrow.style.transform = 'rotate(0deg)';
        }
    }
});

// 2. HEART POP LOGIC (AJAX)
function toggleHeart(btn, serviceId) {
    // Redirecționare guest e făcută în HTML (onclick)
    
    const icon = btn.querySelector('svg');
    const isLiked = icon.classList.contains('text-[#CC2E2E]');

    // Efect vizual instant (Optimistic)
    if (isLiked) {
        icon.classList.remove('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-110');
        icon.classList.add('text-gray-500', 'dark:text-gray-300', 'fill-none');
    } else {
        icon.classList.remove('text-gray-500', 'dark:text-gray-300', 'fill-none');
        icon.classList.add('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-125'); // Pop effect
        
        setTimeout(() => {
            icon.classList.remove('scale-125');
            icon.classList.add('scale-110');
        }, 200);
    }

    // Request AJAX
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