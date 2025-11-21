@extends('layouts.app')

@section('title', $service->title)

@section('content')
<div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-10 pt-6 pb-10">

    <div class="md:col-span-2">

        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 mb-5 px-4 py-2 rounded-lg 
                  font-semibold text-white 
                  bg-gradient-to-r from-[#CC2E2E] to-red-600
                  hover:opacity-90 transition shadow-md active:scale-95">

            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 19l-7-7 7-7" />
            </svg>
            Înapoi
        </a>

        <h1 class="text-3xl font-bold text-gray-900 dark:text-[#F2F2F2] mb-4 uppercase leading-tight">
            {{ $service->title }}
        </h1>

        <div class="flex flex-wrap gap-3 mb-6">
            <span class="px-3 py-1 text-sm rounded-lg font-semibold 
                         bg-blue-100 text-blue-700 
                         dark:bg-blue-900/30 dark:text-blue-300 border border-transparent dark:border-blue-800">
                {{ $service->category->name }}
            </span>
            
            <span class="px-3 py-1 text-sm rounded-lg font-semibold 
                         bg-purple-100 text-purple-700 
                         dark:bg-purple-900/30 dark:text-purple-300 border border-transparent dark:border-purple-800">
                {{ $service->county->name }}
            </span>

            @if($service->city)
            <span class="px-3 py-1 text-sm rounded-lg font-semibold 
                         bg-green-100 text-green-700 
                         dark:bg-green-900/30 dark:text-green-300 border border-transparent dark:border-green-800">
                {{ $service->city }}
            </span>
            @endif
        </div>

        @php
            $images = $service->images ?: [];
            if (is_string($images)) $images = json_decode($images, true);
            if (!is_array($images)) $images = [];
            $images = array_values(array_filter($images));
        @endphp

        @if(count($images) > 0)
            {{-- Aici am revenit la object-cover si h-96 cum era in original --}}
            <div class="mb-6 relative">
                <img 
                    id="mainImage"
                    src="{{ asset('storage/services/' . $images[0]) }}" 
                    class="w-full h-96 object-cover rounded-xl shadow-md border border-gray-200 dark:border-[#333333] transition duration-300"
                    alt="Imagine principală"
                >
            </div>

            <div class="grid grid-cols-4 md:grid-cols-6 gap-3 mb-10">
                @foreach($images as $key => $image)
                    <img 
                        src="{{ asset('storage/services/' . $image) }}" 
                        class="h-24 w-full object-cover rounded-lg shadow cursor-pointer hover:opacity-75 border border-gray-200 dark:border-[#333333] transition"
                        onclick="document.getElementById('mainImage').src=this.src"
                    >
                @endforeach
            </div>
        @else
            <div class="w-full h-64 bg-gray-100 dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] rounded-xl flex items-center justify-center mb-10">
                <span class="text-gray-500 dark:text-gray-400">Acest anunț nu are imagini</span>
            </div>
        @endif

        <div class="text-gray-700 dark:text-[#D4D4D4] leading-relaxed text-lg mb-6 whitespace-pre-line">
            {{ $service->description }}
        </div>

        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-[#A1A1AA] mt-4 pb-4 border-t border-gray-100 dark:border-[#333333] pt-4">

            <span class="flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                           4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                {{ $service->views ?? 0 }} vizualizări
            </span>

            <span class="text-gray-300 dark:text-gray-600">|</span>

            <span>
                Publicat la: {{ $service->created_at->format('d.m.Y') }}
            </span>

        </div>

    </div>

    <div class="mt-[6.5rem] md:mt-[5.5rem]">

        <div class="sticky top-24 space-y-6">

            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-100 dark:border-[#333333] overflow-hidden transition-colors duration-300">

                <div class="p-6 border-b border-gray-100 dark:border-[#2C2C2C] bg-gray-50/50 dark:bg-[#252525]">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1 font-medium">Preț solicitat</p>
                    <div class="flex items-baseline gap-2">
                        @if($service->price_value)
                            <span class="text-3xl font-extrabold text-gray-900 dark:text-[#F2F2F2]">
                                {{ number_format($service->price_value, 0, ',', '.') }}
                            </span>
                            <span class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $service->currency }}</span>
                            
                            @if($service->price_type === 'negotiable')
                                <span class="ml-2 px-2.5 py-1 text-xs font-bold text-green-700 bg-green-100 dark:bg-green-900/40 dark:text-green-400 rounded-full uppercase tracking-wide">
                                    Negociabil
                                </span>
                            @endif
                        @else
                            <span class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                Cere ofertă
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-center gap-4 mb-6">
                        
                        {{-- LOGICĂ AVATAR: Inițiala Numelui sau 'U' --}}
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-gray-700 to-gray-900 text-white flex items-center justify-center font-bold text-xl shadow-md border-2 border-white dark:border-[#333333]">
                            @if($service->user)
                                {{ strtoupper(substr($service->user->name, 0, 1)) }}
                            @else
                                U
                            @endif
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider mb-0.5">
                                Publicat de
                            </p>

                            <h4 class="font-bold text-gray-900 dark:text-white text-lg leading-none">
                                @if($service->user)
                                    {{ $service->user->name }}
                                @else
                                    Utilizator Neînregistrat
                                @endif
                            </h4>

                            <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                @if($service->user)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Membru din {{ $service->user->created_at->format('Y') }}
                                @else
                                    <span class="italic">Cont nevalidat</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        
                        @if($service->phone)
                            @php
                                // 1. Curățăm numărul de orice caracter care nu e cifră (pentru siguranță)
                                $rawPhone = preg_replace('/[^0-9]/', '', $service->phone);
                                
                                // 2. Îl formatăm: 07xx xxx xxx (dacă are 10 cifre, standard RO)
                                if(strlen($rawPhone) == 10) {
                                    $formattedPhone = substr($rawPhone, 0, 4) . ' ' . substr($rawPhone, 4, 3) . ' ' . substr($rawPhone, 7, 3);
                                } else {
                                    // Dacă e un număr atipic (fix scurt sau internațional), îl lăsăm cum e
                                    $formattedPhone = $service->phone;
                                }
                            @endphp

                            <a href="tel:{{ $rawPhone }}"
                               class="group relative flex items-center justify-center gap-3 w-full py-3.5 rounded-xl 
                                      bg-[#CC2E2E] text-white font-bold text-base shadow-lg shadow-red-500/20
                                      hover:bg-[#B72626] hover:shadow-red-500/40 hover:-translate-y-0.5 
                                      active:translate-y-0 transition-all duration-200 overflow-hidden">
                                
                                <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                
                                {{-- Aici afișăm variabila formatată --}}
                                <span>Sună: {{ $formattedPhone }}</span>
                            </a>
                        @else
                            {{-- Mesaj dacă nu există niciun contact --}}
                            <div class="text-center p-3 bg-gray-50 dark:bg-[#2C2C2C] rounded-xl text-gray-500 dark:text-gray-400 text-sm italic">
                                Nu există număr de telefon disponibil.
                            </div>
                        @endif

                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-[#252525] p-4 border-t border-gray-100 dark:border-[#2C2C2C] flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>Date de contact verificate</span>
                </div>

            </div>

            <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
                <h5 class="font-bold text-blue-800 dark:text-blue-300 text-sm mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Sfaturi de siguranță
                </h5>
                <ul class="text-xs text-blue-700 dark:text-blue-200 space-y-1.5 list-disc list-inside">
                    <li>Nu plătiți niciodată în avans.</li>
                    <li>Verificați serviciul înainte de plată.</li>
                    <li>Întâlniți-vă în locuri publice sigure.</li>
                </ul>
            </div>

        </div>

    </div>

</div>
@endsection