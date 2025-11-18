@extends('layouts.app')

@section('title', 'Contul meu')

@section('content')

<div class="max-w-7xl mx-auto mt-10 mb-20">

    <h1 class="text-3xl font-bold mb-8 text-gray-900">
        Salut, {{ auth()->user()->name }}
    </h1>

    <!-- =========================== -->
    <!--        TAB NAVIGATION       -->
    <!-- =========================== -->
    <div class="border-b border-gray-300 mb-8">
        <ul class="flex gap-8 text-lg font-semibold text-gray-600">

            <li>
                <a href="?tab=anunturi"
                   class="pb-3 {{ request('tab') === 'anunturi' || !request('tab')
                       ? 'text-primary-end border-b-2 border-primary-end'
                       : 'hover:text-primary-end' }}">
                    Anunțurile mele
                </a>
            </li>

            <li>
                <a href="?tab=favorite"
                   class="pb-3 {{ request('tab') === 'favorite'
                       ? 'text-primary-end border-b-2 border-primary-end'
                       : 'hover:text-primary-end' }}">
                    Favoritele mele
                </a>
            </li>

            <li>
                <a href="?tab=profil"
                   class="pb-3 {{ request('tab') === 'profil'
                       ? 'text-primary-end border-b-2 border-primary-end'
                       : 'hover:text-primary-end' }}">
                    Profilul meu
                </a>
            </li>

        </ul>
    </div>

    <!-- =================================================================== -->
    <!--                 TAB 1 — ANUNȚURILE MELE                             -->
    <!-- =================================================================== -->

    @if(request('tab') === 'anunturi' || !request('tab'))

        @php
            $myServices = \App\Models\Service::where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->get();
        @endphp

        @if($myServices->isEmpty())
            <p class="text-gray-600">Nu ai publicat niciun anunț încă.</p>

            <a href="{{ route('services.create') }}"
               class="inline-block mt-4 px-5 py-3 bg-primary-end text-white rounded-lg shadow hover:bg-primary-start transition">
                + Publică un anunț
            </a>

        @else

        <div id="myServicesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($myServices as $service)

            <div class="bg-white rounded-xl shadow border border-gray-200 p-4 service-card"
                 id="service-{{ $service->id }}">

                <a href="{{ route('services.show', [$service->id, $service->slug]) }}">
                    <img src="{{ asset('storage/services/' . ($service->images[0] ?? 'no-image.jpg')) }}"
                         class="w-full h-40 object-cover rounded-lg mb-3">
                </a>

                <h3 class="font-bold text-lg truncate">{{ $service->title }}</h3>

                <p class="text-primary-end font-semibold text-sm mt-1">
                    @if($service->price_value)
                        {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                        @if($service->price_type == 'negotiable')
                            Negociabil
                        @endif
                    @else
                        Cere ofertă
                    @endif
                </p>

                <div class="text-xs text-gray-500 mt-1 flex justify-between">
                    <span>{{ $service->views }} vizualizări</span>
                    <span>{{ $service->created_at->format('d.m.Y') }}</span>
                </div>

                <div class="mt-4 flex gap-2">

                    <a href="{{ route('services.edit', $service->id) }}"
                       class="px-3 py-2 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                       Editare
                    </a>

                    <button 
                        type="button"
                        onclick="deleteService({{ $service->id }})"
                        class="px-3 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Șterge
                    </button>

                </div>

            </div>

            @endforeach

        </div>

        @endif

    @endif


    <!-- =================================================================== -->
    <!--                 TAB 2 — FAVORITELE MELE                              -->
    <!-- =================================================================== -->

    @if(request('tab') === 'favorite')

        @php
            $favorites = auth()->user()
                ->favorites()
                ->with('service')
                ->get()
                ->pluck('service')
                ->filter();
        @endphp

        @if($favorites->isEmpty())
            <p id="favoriteEmptyMsg" class="text-gray-600">Nu ai anunțuri favorite.</p>
        @else

        <p id="favoriteEmptyMsg" class="text-gray-600 hidden">Nu ai anunțuri favorite.</p>

        <div id="favoriteList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($favorites as $service)

            <div class="bg-white rounded-xl shadow border p-4 favorite-card" id="favorite-{{ $service->id }}">

                <a href="{{ route('services.show', [$service->id, $service->slug]) }}">
                    <img src="{{ asset('storage/services/' . ($service->images[0] ?? 'no-image.jpg')) }}"
                         class="w-full h-40 object-cover rounded-lg mb-3">
                </a>

                <h3 class="font-bold text-lg truncate">{{ $service->title }}</h3>

                <p class="text-primary-end font-semibold text-sm mt-1">
                    @if($service->price_value)
                        {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                    @else
                        Cere ofertă
                    @endif
                </p>

                <button 
                    onclick="toggleFavorite({{ $service->id }}, this)"
                    class="mt-3 w-full px-3 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600">
                    Scoate din favorite
                </button>

            </div>

            @endforeach

        </div>

        @endif

    @endif


   <!-- =================================================================== -->
<!--                 TAB 3 — PROFILUL MEU                                -->
<!-- =================================================================== -->

@if(request('tab') === 'profil')

<div class="bg-white border border-gray-200 shadow-md rounded-xl p-8 max-w-xl">

    <h2 class="text-xl font-bold text-gray-900 mb-6">
        Informațiile tale
    </h2>

    <label class="block mb-4">
        <span class="text-gray-700 font-semibold">Nume</span>
        <input id="editName" type="text" value="{{ auth()->user()->name }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>

    <label class="block mb-4">
        <span class="text-gray-700 font-semibold">Email</span>
        <input id="editEmail" type="email" value="{{ auth()->user()->email }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>

    <label class="block mb-6">
        <span class="text-gray-700 font-semibold">Schimbă parola</span>
        <input id="editPassword" type="password" placeholder="Lasă gol dacă nu vrei să o schimbi"
               class="mt-1 w-full border rounded-lg px-3 py-2">
    </label>

    <!-- BUTON IDENTIC CU cel din favorite -->
    <button onclick="updateProfile()"
        class="mt-6 w-full px-3 py-2 text-sm bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
        Salvează modificările
    </button>

</div>

@endif

</div>


<!-- ============================================================== -->
<!--           AJAX: REMOVE FAVORITE                                -->
<!-- ============================================================== -->
<script>
function toggleFavorite(serviceId, btn) {
    fetch("{{ route('favorite.toggle') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ service_id: serviceId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "removed") {

            let card = document.getElementById("favorite-" + serviceId);
            card.style.transition = "0.4s";
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";

            setTimeout(() => card.remove(), 400);

            if (document.querySelectorAll('.favorite-card').length === 1) {
                document.getElementById('favoriteEmptyMsg').classList.remove('hidden');
            }
        }
    });
}
</script>

<!-- ============================================================== -->
<!--                 AJAX: DELETE SERVICE                             -->
<!-- ============================================================== -->
<script>
function deleteService(id) {
    if (!confirm("Sigur vrei să ștergi acest anunț?")) return;

    fetch("/anunt/" + id, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "deleted") {

            let card = document.getElementById("service-" + id);
            card.style.transition = "0.4s";
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";

            setTimeout(() => card.remove(), 400);
        }
    })
    .catch(err => console.error(err));
}
</script>

<!-- ============================================================== -->
<!--           AJAX: UPDATE PROFILE                                  -->
<!-- ============================================================== -->
<script>
function updateProfile() {

    let name     = document.getElementById("editName").value;
    let email    = document.getElementById("editEmail").value;
    let password = document.getElementById("editPassword").value;

    fetch("{{ route('profile.ajaxUpdate') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ name, email, password })
    })
    .then(res => res.json())
    .then(data => {

        // arată mesajul
        let msg = document.getElementById("profileSavedMsg");
        msg.classList.remove("hidden");
        msg.style.opacity = 1;

        // ascunde după 2 secunde
        setTimeout(() => {
            msg.style.transition = "0.4s";
            msg.style.opacity = 0;
        }, 2000);

        // reset password field
        document.getElementById("editPassword").value = "";
    })
    .catch(err => console.error(err));
}
</script>

@endsection
