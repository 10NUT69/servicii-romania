<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Servicii România')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- 
    MODIFICĂRI DARK MODE:
    1. dark:bg-[#121212] -> Setează fundalul "aproape negru" standard.
    2. dark:text-[#E5E5E5] -> Setează textul default la un alb-gri odihnitor.
    3. min-h-screen flex flex-col -> Asigură că footer-ul e mereu jos și fundalul acoperă tot ecranul.
--}}
<body class="bg-[#f6f7fb] dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5] font-inter antialiased min-h-screen flex flex-col">

    <nav id="mainHeader" 
         class="emag-gradient text-white shadow-md fixed top-0 left-0 right-0 z-50 
                transition-all duration-300 ease-in-out h-16 flex items-center border-b border-transparent dark:border-gray-800">

        <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 flex items-center justify-between">

            <a href="{{ route('services.index') }}" class="flex items-center">
                <img src="/images/logo.png" alt="Logo"
                     id="logoImage"
                     class="max-h-10 sm:max-h-11 w-auto transition-all duration-300 object-contain select-none">
            </a>

            <div id="headerMenu" class="flex items-center gap-2 sm:gap-5 transition-all duration-300">

                <button onclick="goToFavorites()" 
                    class="hover:opacity-80 transition flex items-center justify-center h-8 w-8 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 
                                 0-3.597 1.126-4.312 2.733C11.285 
                                 4.876 9.623 3.75 7.688 3.75 
                                 5.099 3.75 3 5.765 3 
                                 8.25c0 7.22 9 12 9 
                                 12s9-4.78 9-12z" />
                    </svg>
                </button>

                @auth
                    <a href="{{ route('account.index') }}"
                       class="font-medium hover:opacity-80 text-sm sm:text-base transition-all duration-300 header-text">
                        {{ auth()->user()->name }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button class="ml-2 font-medium hover:underline text-sm sm:text-base flex items-center 
                                       transition-all duration-300 header-text">
                            Delogare
                        </button>
                    </form>

                @else
                    <a href="{{ route('login') }}"
                       class="font-medium hover:underline text-sm sm:text-base transition-all duration-300 header-text">
                        Intră în cont
                    </a>

                    <a href="{{ route('register') }}"
                       class="font-medium hover:underline text-sm sm:text-base transition-all duration-300 header-text">
                        Creează cont
                    </a>
                @endauth

                {{-- Butonul rămâne alb pe header-ul colorat, arată bine și în dark mode --}}
                <a href="{{ route('services.create') }}"
                   class="px-3 py-1.5 bg-white text-gray-800 font-semibold rounded-md shadow 
                          hover:bg-gray-100 transition active:scale-95 text-sm flex items-center gap-1 header-button">

                    <span class="text-lg font-bold">+</span>
                    <span>Adaugă</span>
                </a>

            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    {{-- flex-grow asigură că acest div ocupă tot spațiul disponibil --}}
    <main class="max-w-7xl mx-auto px-4 py-10 w-full flex-grow">
        @yield('content')
    </main>

    {{-- mt-auto împinge footer-ul jos. Culoarea textului adaptată pentru dark mode --}}
    <footer class="text-center text-gray-600 dark:text-[#A1A1AA] text-sm py-10 mt-auto transition-colors">
        © {{ date('Y') }} Servicii România
    </footer>



    <script>
    function goToFavorites() {
        @if(auth()->check())
            window.location.href = "/contul-meu?tab=favorite";
        @else
            showFavoriteWarning();
        @endif
    }

    function showFavoriteWarning() {
        let box = document.createElement('div');
        // Am adăugat dark:bg-[#2C2C2C] și dark:text-white pentru pop-up, deși fiind roșu e ok și așa.
        box.className = `
            fixed top-6 right-6
            bg-red-600 text-white px-4 py-3 rounded-xl shadow-lg
            animate-fade-in z-50 font-medium
        `;
        box.innerText = "Trebuie să fii autentificat pentru a vedea favoritele.";

        document.body.appendChild(box);

        setTimeout(() => {
            box.style.opacity = "0";
            box.style.transition = "0.5s";
            setTimeout(() => box.remove(), 500);
        }, 2000);
    }
    </script>

    <script>
    const header = document.getElementById("mainHeader");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 30) {
            header.classList.add("header-small");
        } else {
            header.classList.remove("header-small");
        }
    });
    </script>

    <style>
    .header-small {
        height: 54px !important;
    }

    .header-small #logoImage {
        max-height: 30px !important;
    }

    .header-small .header-text {
        font-size: 0.78rem !important;
    }

    .header-small .header-button {
        padding: 0.25rem 0.65rem !important;
        font-size: 0.74rem !important;
    }

    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
    </style>

</body>
</html>