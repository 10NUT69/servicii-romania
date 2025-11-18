@extends('layouts.app')

@section('title', 'Servicii România')

@section('content')

<!-- 🔍 BARA DE CĂUTARE -->
<div class="max-w-7xl mx-auto mt-8 mb-12 relative z-20">

    <form method="GET"
        class="bg-white rounded-2xl shadow-xl border border-gray-200
               p-4 md:p-5 flex flex-col md:flex-row items-center gap-4">

        <!-- INPUT SEARCH -->
        <div class="flex items-center w-full bg-gray-50 rounded-xl border border-gray-300 px-4">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 text-gray-400"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 21l-4.35-4.35m1.1-5.4a6.5 6.5 0 11-13 0 
                      6.5 6.5 0 0113 0z" />
            </svg>

            <input type="text"
                name="search"
                placeholder="Ex: electrician, zugrav, instalator"
                value="{{ request('search') }}"
                class="flex-1 bg-transparent border-none focus:ring-0 
                       py-3 px-3 text-gray-700 text-base">
        </div>

        <!-- SELECT JUDEȚ -->
        <select name="county"
            class="px-4 py-3 rounded-xl border border-gray-300 bg-gray-50
                   focus:ring-2 focus:ring-primary-end outline-none text-gray-700 w-full md:w-56">
            <option value="">Alege județul</option>
            @foreach($counties as $county)
                <option value="{{ $county->id }}" {{ request('county') == $county->id ? 'selected' : '' }}>
                    {{ $county->name }}
                </option>
            @endforeach
        </select>

        <!-- BUTON CAUTĂ -->
        <button
            class="px-8 py-3 bg-[#e52620] text-white font-semibold rounded-xl shadow-md
                   hover:bg-[#d51d68] hover:shadow-lg active:scale-95 transition-all duration-200
                   w-full md:w-auto flex items-center gap-2 justify-center">

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" 
                    d="M21 21l-4.35-4.35m1.1-5.4a6.5 6.5 0 11-13 0 
                       6.5 6.5 0 0113 0z" />
            </svg>

            Caută
        </button>

    </form>
</div>


<!-- 🟩 TITLU SECȚIUNE -->
<h2 class="text-2xl font-bold mb-6 text-gray-900 max-w-7xl mx-auto">
    Anunțuri recente
</h2>


<!-- 🟦 GRID CARDURI -->
<div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

@forelse($services as $service)

    @php
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        if (!is_array($images)) $images = [];
        $images = array_values(array_filter($images));
    @endphp

    <div class="relative bg-white rounded-2xl border border-gray-200 shadow-sm 
                hover:shadow-xl transition overflow-hidden group">

        <!-- FAVORITE BUTTON -->
        <button type="button"
                class="absolute top-2 right-2 z-30 bg-white/90 backdrop-blur-sm rounded-full p-1.5 shadow favorite-btn"
                data-id="{{ $service->id }}">

            @php
                $isFav = auth()->check() && $service->favorites()
                    ->where('user_id', auth()->id())
                    ->exists();
            @endphp

            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6 transition {{ $isFav ? 'text-red-500' : 'text-gray-400' }}"
                fill="{{ $isFav ? 'currentColor' : 'none' }}"
                viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 
                    0-3.597 1.126-4.312 2.733C11.285 4.876 
                    9.623 3.75 7.688 3.75 5.099 3.75 
                    3 5.765 3 8.25c0 7.22 9 12 
                    9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <!-- CARD LINK -->
        <a href="{{ route('services.show', ['id' => $service->id, 'slug' => $service->slug]) }}" class="block">

            <!-- IMAGINE -->
            <div class="relative w-full aspect-[4/3] bg-gray-100 overflow-hidden">
                @if(!empty($images))
                    <img src="{{ asset('storage/services/' . $images[0]) }}"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                @else
                    <img src="{{ asset('images/no-image.jpg') }}"
                         class="w-full h-full object-cover opacity-60">
                @endif

                <!-- BADGE CATEGORIE -->
                <span class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-1 rounded-md font-semibold uppercase">
                    {{ $service->category->name }}
                </span>
            </div>

            <!-- TEXT -->
            <div class="p-4">

                <!-- TITLU -->
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 uppercase 
           truncate whitespace-nowrap overflow-hidden" 
     title="{{ $service->title }}">
    {{ $service->title }}
</h3>


         <div class="text-base font-bold mb-1">

    @if(!empty($service->price_value) && $service->price_type === 'negotiable')
        <span class="text-green-600 dark:text-green-400">
            {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
        </span>
        <span class="text-gray-600 dark:text-gray-300">Negociabil</span>

    @elseif(!empty($service->price_value) && $service->price_type === 'fixed')
        <span class="text-green-600 dark:text-green-400">
            {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
        </span>

    @else
        <span class="text-orange-600 dark:text-orange-400">
            Cere ofertă
        </span>
    @endif

</div>



                <!-- INFO ROW -->
                <div class="flex items-center gap-2 text-[11px] text-gray-500">

                    <!-- LOCATIE -->
                    <span class="flex items-center gap-1 truncate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[11px] w-[11px]" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21c4.5-6 7.5-10.5 7.5-13.5A7.5 7.5 0 104.5 7.5C4.5 10.5 7.5 15 12 21z" />
                        </svg>
                        {{ $service->city ?? $service->county->name }}
                    </span>

                    <span class="text-gray-300">·</span>

                    <!-- VIZUALIZARI -->
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[12px] w-[12px]" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                 d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                                    4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        {{ $service->views ?? 0 }}
                    </span>

                    <span class="text-gray-300">·</span>

                    <!-- DATA -->
                    <span>
                        {{ $service->created_at->format('d.m.Y') }}
                    </span>
                </div>

            </div>
        </a>
    </div>

@empty

    <p class="text-gray-500 max-w-7xl mx-auto">Momentan nu există anunțuri disponibile.</p>

@endforelse

</div>


<!-- PAGINAȚIE -->
<div class="mt-10 max-w-7xl mx-auto">
    {{ $services->links() }}
</div>

@endsection
	