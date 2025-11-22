<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Servicii România')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f6f7fb] dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5] font-inter antialiased min-h-screen flex flex-col">

    {{-- HEADER "SMART" --}}
    {{-- 
        transition-transform: Esențial pentru ca dispariția/apariția să fie fluidă.
        duration-300: Viteza animației (0.3 secunde).
    --}}
    <nav id="main-nav" class="fixed top-0 left-0 w-full z-[999] h-14 md:h-16 transition-transform duration-300 ease-in-out
                              emag-gradient text-white border-b border-transparent dark:border-gray-800 flex items-center shadow-md will-change-transform">
        
        <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 flex items-center justify-between">

            {{-- 1. LOGO --}}
            <a href="{{ route('services.index') }}" class="flex items-center shrink-0 gap-1 group decoration-0">
                <img src="/images/logo.png" alt="Logo"
                     class="max-h-7 md:max-h-9 w-auto object-contain select-none">
            </a>

            {{-- 2. MENIU DREAPTA --}}
            <div class="flex items-center gap-2 sm:gap-5">

                {{-- Favorite --}}
                <button onclick="goToFavorites()" 
                        class="transition-all duration-300 flex items-center justify-center h-9 w-9 rounded-full hover:bg-white/10 shrink-0 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

                @auth
                    {{-- LOGAT --}}
                    <a href="{{ route('account.index') }}" 
                       class="md:hidden flex items-center justify-center w-8 h-8 rounded-full bg-white/20 border border-white/30 text-sm font-bold text-white">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </a>

                    <div class="hidden md:flex items-center gap-4 text-white">
                        <a href="{{ route('account.index') }}" class="font-bold hover:opacity-80 transition text-sm md:text-base">
                            {{ auth()->user()->name }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="font-medium hover:underline transition text-xs md:text-sm bg-white/10 px-3 py-1.5 rounded">
                                DELOGARE
                            </button>
                        </form>
                    </div>

                @else
                    {{-- NE-LOGAT --}}
                    <a href="{{ route('login') }}" class="md:hidden p-2 hover:bg-white/10 rounded-full transition text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>

                    <div class="hidden md:flex items-center gap-4 font-bold text-white text-sm md:text-base">
                        <a href="{{ route('login') }}" class="hover:underline transition">Intră în cont</a>
                        <span class="opacity-50">|</span>
                        <a href="{{ route('register') }}" class="hover:underline transition">Creează cont</a>
                    </div>
                @endauth

                {{-- BUTON ADAUGĂ --}}
                <a href="{{ route('services.create') }}" 
                   class="ml-1 bg-white text-gray-800 font-bold rounded-lg shadow hover:bg-gray-100 transition-all duration-300 active:scale-95 flex items-center gap-1
                          px-3 py-2 text-sm md:text-base md:px-4 md:py-2">
                    <span class="text-lg md:text-xl leading-none font-black">+</span>
                    <span class="hidden xs:inline">Adaugă</span>
                </a>

            </div>
        </div>
    </nav>

    {{-- SPACER STATIC --}}
    {{-- Ocupă locul fizic al header-ului pentru ca restul paginii să nu intre sub el. --}}
    <div class="h-14 md:h-16 w-full shrink-0"></div>


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
    function goToFavorites() {
        @if(auth()->check())
            window.location.href = "{{ route('account.index') }}"; 
        @else
            alert("Trebuie să fii autentificat.");
        @endif
    }

    // === LOGICA "OLX STYLE" (Hide on Scroll Down / Show on Scroll Up) ===
    let lastScrollY = window.scrollY;
    const nav = document.getElementById('main-nav');

    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;

        // 1. Dacă suntem sus de tot (top < 10px), arătăm meniul oricum
        if (currentScrollY < 10) {
            nav.style.transform = 'translateY(0)';
            lastScrollY = currentScrollY;
            return;
        }

        // 2. Verificăm direcția
        if (currentScrollY > lastScrollY) {
            // SCROLL JOS -> ASCUNDE (-100% îl trage în sus, în afara ecranului)
            nav.style.transform = 'translateY(-100%)';
        } else {
            // SCROLL SUS -> ARATĂ (0 îl aduce înapoi)
            nav.style.transform = 'translateY(0)';
        }

        lastScrollY = currentScrollY;
    });
    </script>

    <style>
    @media (min-width: 360px) {
        .xs\:inline { display: inline !important; }
    }
    html, body {
        overscroll-behavior-y: none; 
    }
    </style>

</body>
</html>