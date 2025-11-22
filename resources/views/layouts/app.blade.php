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

    {{-- HEADER FIX --}}
    {{-- z-[999] este secretul: obligă headerul să stea peste orice altceva (inclusiv search z-50) --}}
    <nav class="fixed top-0 left-0 w-full h-16 md:h-20 z-[999] bg-gradient-to-r from-[#CC2E2E] via-purple-600 to-blue-600 shadow-lg transition-all">
        
        <div class="max-w-7xl mx-auto px-4 h-full flex items-center justify-between">
            
            {{-- 1. LOGO --}}
            <a href="{{ route('services.index') }}" class="flex items-center gap-1 group text-white no-underline">
                <span class="text-2xl md:text-3xl font-black tracking-tighter group-hover:scale-105 transition-transform">WTF</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </a>

            {{-- 2. MENIU DREAPTA --}}
            <div class="flex items-center gap-2 md:gap-6">

                {{-- Favorite --}}
                <button onclick="goToFavorites()" class="p-2 text-white/90 hover:text-white hover:bg-white/10 rounded-full transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>

                @guest
                    {{-- MOBIL: Iconiță User (Fără text) --}}
                    <a href="{{ route('login') }}" class="md:hidden p-2 text-white/90 hover:text-white hover:bg-white/10 rounded-full transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>

                    {{-- DESKTOP: Text Complet --}}
                    <div class="hidden md:flex items-center gap-4 text-sm font-bold text-white">
                        <a href="{{ route('login') }}" class="hover:opacity-80 transition">Intră în cont</a>
                        <span class="opacity-50">|</span>
                        <a href="{{ route('register') }}" class="hover:opacity-80 transition">Creează cont</a>
                    </div>
                @endguest

                @auth
                    {{-- MOBIL: Avatar --}}
                    <div class="md:hidden">
                        <a href="{{ route('account.index') }}" class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-full text-white font-bold border border-white/30">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </a>
                    </div>

                    {{-- DESKTOP: Nume --}}
                    <div class="hidden md:flex items-center gap-4 text-sm font-bold text-white">
                        <a href="{{ route('account.index') }}" class="hover:opacity-90">{{ auth()->user()->name }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded-lg transition text-xs uppercase">Delogare</button>
                        </form>
                    </div>
                @endauth

                {{-- BUTON ADAUGĂ --}}
                <a href="{{ route('services.create') }}" class="ml-1 bg-white text-blue-600 hover:bg-gray-50 font-bold rounded-lg px-3 py-2 md:px-4 text-sm flex items-center gap-1 shadow-lg active:scale-95 transition-transform">
                    <span class="text-lg leading-none font-black">+</span>
                    <span class="hidden xs:inline">Adaugă</span> {{-- Text ascuns pe ecrane f. mici --}}
                </a>

            </div>
        </div>
    </nav>

    {{-- SPACER (OBLIGATORIU) --}}
    {{-- Împinge conținutul în jos ca să nu intre sub header --}}
    <div class="h-16 md:h-20 w-full"></div>


    {{-- MAIN CONTENT --}}
    <main class="flex-grow w-full">
        @yield('content')
    </main>


    {{-- FOOTER --}}
    <footer class="text-center text-gray-600 dark:text-[#A1A1AA] text-sm py-8 border-t border-gray-200 dark:border-gray-800 mt-auto">
        © {{ date('Y') }} Servicii România
    </footer>

    {{-- SCRIPTURI SIMPLE --}}
    <script>
    function goToFavorites() {
        @if(auth()->check())
            window.location.href = "{{ route('account.index') }}"; 
        @else
            alert("Trebuie să fii autentificat pentru a vedea favoritele.");
        @endif
    }
    </script>

    <style>
    /* Afișează textul 'Adaugă' doar dacă ecranul e mai lat de 360px */
    @media (min-width: 360px) {
        .xs\:inline { display: inline !important; }
    }
    </style>

</body>
</html>