<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Servicii România')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f6f7fb] dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5] font-inter antialiased min-h-screen flex flex-col">

    {{-- HEADER COMPACT & FIX --}}
    {{-- Am pus înapoi clasa 'emag-gradient' în loc de culorile hardcodate --}}
    <nav id="main-nav" class="fixed top-0 left-0 w-full z-[999] h-14 md:h-16 transition-all duration-300 ease-in-out
                              emag-gradient text-white shadow-lg border-b border-transparent dark:border-gray-800 flex items-center">
        
        <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 flex items-center justify-between">

            {{-- 1. LOGO --}}
            <a href="{{ route('services.index') }}" class="flex items-center shrink-0 gap-1 group decoration-0">
                <img src="/images/logo.png" alt="Logo"
                     id="logo-img"
                     class="max-h-8 md:max-h-9 w-auto object-contain select-none transition-all duration-300">
            </a>

            {{-- 2. MENIU DREAPTA --}}
            <div class="flex items-center gap-2 sm:gap-4 transition-all duration-300">

                {{-- Buton Favorite --}}
                <button onclick="goToFavorites()" 
                        class="hover:opacity-80 transition flex items-center justify-center h-8 w-8 rounded-full hover:bg-white/10 shrink-0 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

                @auth
                    {{-- LOGAT --}}
                    
                    {{-- Mobil: Avatar Mic --}}
                    <a href="{{ route('account.index') }}" 
                       class="md:hidden flex items-center justify-center w-7 h-7 rounded-full bg-white/20 border border-white/30 text-xs font-bold text-white">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </a>

                    {{-- Desktop: Text --}}
                    <div class="hidden md:flex items-center gap-3 text-white">
                        <a href="{{ route('account.index') }}" class="font-medium hover:opacity-80 transition text-sm">
                            {{ auth()->user()->name }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="font-medium hover:underline transition text-xs bg-white/10 px-2 py-1 rounded">
                                DELOGARE
                            </button>
                        </form>
                    </div>

                @else
                    {{-- NE-LOGAT --}}

                    {{-- Mobil: Iconiță User --}}
                    <a href="{{ route('login') }}" class="md:hidden p-1.5 hover:bg-white/10 rounded-full transition text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>

                    {{-- Desktop: Text --}}
                    <div class="hidden md:flex items-center gap-3 text-sm font-bold text-white">
                        <a href="{{ route('login') }}" class="hover:underline transition">Intră în cont</a>
                        <span class="opacity-50">|</span>
                        <a href="{{ route('register') }}" class="hover:underline transition">Creează cont</a>
                    </div>
                @endauth

                {{-- BUTON ADAUGĂ (Compact) --}}
                <a href="{{ route('services.create') }}" 
                   class="ml-1 px-3 py-1.5 bg-white text-gray-800 font-semibold rounded-md shadow hover:bg-gray-100 transition active:scale-95 text-xs sm:text-sm flex items-center gap-1"
                   id="add-btn">
                    <span class="text-lg font-bold leading-none">+</span>
                    <span class="hidden xs:inline">Adaugă</span>
                </a>

            </div>
        </div>
    </nav>

    {{-- SPACER FIX --}}
    {{-- Trebuie să fie egal cu înălțimea INIȚIALĂ a header-ului (h-14 mobil / h-16 desktop) --}}
    <div class="h-14 md:h-16 w-full transition-all duration-300" id="nav-spacer"></div>


    {{-- MAIN CONTENT --}}
    <main class="max-w-7xl mx-auto px-4 py-6 w-full flex-grow relative z-0">
        @yield('content')
    </main>


    {{-- FOOTER --}}
    <footer class="text-center text-gray-600 dark:text-[#A1A1AA] text-sm py-10 mt-auto transition-colors border-t border-gray-200 dark:border-gray-800">
        © {{ date('Y') }} Servicii România
    </footer>


    {{-- SCRIPTURI --}}
    <script>
    // 1. FAVORITE
    function goToFavorites() {
        @if(auth()->check())
            window.location.href = "{{ route('account.index') }}"; 
        @else
            alert("Trebuie să fii autentificat.");
        @endif
    }

    // 2. HEADER SHRINK (Logică nouă: Start Mic -> Scroll Foarte Mic)
    window.addEventListener('scroll', function() {
        const nav = document.getElementById('main-nav');
        const spacer = document.getElementById('nav-spacer');
        const logo = document.getElementById('logo-img');
        
        if (window.scrollY > 20) {
            // -- SCROLL JOS (Super Compact) --
            // Desktop: h-16 -> h-14
            // Mobil: h-14 -> h-12 (48px)
            nav.classList.remove('h-14', 'md:h-16');
            nav.classList.add('h-12', 'md:h-14', 'shadow-xl');
            
            // Spacer-ul se micșorează și el ca să nu rămână gaură albă sus
            spacer.classList.remove('h-14', 'md:h-16');
            spacer.classList.add('h-12', 'md:h-14');

            // Micșorăm și logo-ul puțin
            if(logo) {
                logo.classList.remove('max-h-8', 'md:max-h-9');
                logo.classList.add('max-h-6', 'md:max-h-7');
            }

        } else {
            // -- SUS (Compact Standard) --
            nav.classList.remove('h-12', 'md:h-14', 'shadow-xl');
            nav.classList.add('h-14', 'md:h-16');
            
            spacer.classList.remove('h-12', 'md:h-14');
            spacer.classList.add('h-14', 'md:h-16');

            if(logo) {
                logo.classList.remove('max-h-6', 'md:max-h-7');
                logo.classList.add('max-h-8', 'md:max-h-9');
            }
        }
    });
    </script>

    <style>
    @media (min-width: 360px) {
        .xs\:inline { display: inline !important; }
    }
    </style>

</body>
</html>