@extends('layouts.app')

@section('title', 'Editează anunț')

@section('content')

<div class="max-w-3xl mx-auto mt-8 mb-16">

    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight transition-colors">
            Editează anunțul
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg transition-colors">
            Actualizează detaliile pentru a menține anunțul relevant.
        </p>
    </div>

    <form action="{{ route('services.update', $service->id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="space-y-8">

        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 p-1.5 rounded-lg">📝</span>
                Detalii de bază
            </h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Titlul anunțului</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <input type="text" name="title" 
                            value="{{ old('title', $service->title) }}"
                            class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('title') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525]
                                   outline-none transition placeholder-gray-400"
                            required>
                    </div>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Categoria</label>
                        <div class="relative">
                            <select name="category_id"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border @error('category_id') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white appearance-none
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select"
                                    required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }} 
                                        class="dark:bg-[#1E1E1E]">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Județ</label>
                        <div class="relative">
                            <select name="county_id"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border @error('county_id') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white appearance-none
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select"
                                    required>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" 
                                        {{ old('county_id', $service->county_id) == $county->id ? 'selected' : '' }} 
                                        class="dark:bg-[#1E1E1E]">
                                        {{ $county->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Descriere</label>
                    <textarea name="description" rows="5"
                              class="w-full px-4 py-3 rounded-xl border @error('description') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                     bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                     focus:ring-2 focus:ring-primary-end outline-none transition resize-y placeholder-gray-400"
                              required>{{ old('description', $service->description) }}</textarea>
                     @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-green-100 dark:bg-green-900/30 text-green-600 p-1.5 rounded-lg">💰</span>
                Preț și Imagini
            </h2>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Prețul estimativ</label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <input type="number" name="price_value" step="0.01" 
                               value="{{ old('price_value', $service->price_value) }}"
                               placeholder="0.00"
                               class="w-full pl-4 pr-20 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040] 
                                      bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white font-bold text-lg
                                      focus:ring-2 focus:ring-primary-end outline-none transition no-spinner placeholder-gray-400">
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            <select name="currency" class="h-full py-0 pl-2 pr-7 bg-transparent text-gray-500 dark:text-gray-300 font-semibold border-none focus:ring-0 cursor-pointer">
                                <option value="RON" {{ old('currency', $service->currency) == 'RON' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">RON</option>
                                <option value="EUR" {{ old('currency', $service->currency) == 'EUR' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">EUR</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex bg-gray-100 dark:bg-[#2C2C2C] p-1 rounded-xl w-full sm:w-auto shrink-0">
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="fixed" 
                                   {{ old('price_type', $service->price_type) == 'fixed' ? 'checked' : '' }} 
                                   class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 transition-all peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm">
                                Preț Fix
                            </span>
                        </label>
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="negotiable" 
                                   {{ old('price_type', $service->price_type) == 'negotiable' ? 'checked' : '' }} 
                                   class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 transition-all peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm">
                                Negociabil
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- SECȚIUNEA BLINDATĂ PENTRU IMAGINI --}}
            @php
                $gallery = $service->images;
                if (is_null($gallery)) $gallery = [];
                if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
                // Ne asigurăm că e array și eliminăm elementele invalide
                if (is_array($gallery)) {
                    $gallery = array_filter($gallery, function($item) { return is_string($item) && !empty($item); });
                } else {
                    $gallery = [];
                }
            @endphp

            @if(count($gallery) > 0)
            <div class="mb-8">
                <label class="block mb-3 font-semibold text-gray-700 dark:text-gray-300">
                    Imagini actuale ({{ count($gallery) }})
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($gallery as $img)
                        <div class="relative group aspect-square" id="image-container-{{ $loop->index }}">
                            <img src="{{ asset('storage/services/' . $img) }}" 
                                 class="w-full h-full object-cover rounded-xl shadow border border-gray-200 dark:border-[#404040]">
                            
                            <button type="button" 
                                    onclick="deleteServerImage('{{ $img }}', {{ $service->id }}, 'image-container-{{ $loop->index }}')"
                                    class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white p-1.5 rounded-full 
                                           shadow-md opacity-90 group-hover:opacity-100 transition transform active:scale-90"
                                    title="Șterge imaginea definitiv">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Adaugă imagini noi</label>
                <div class="relative w-full group">
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-[#404040] rounded-2xl bg-gray-50 dark:bg-[#2C2C2C] group-hover:bg-gray-100 dark:group-hover:bg-[#333333] transition-colors">
                        <div class="p-3 bg-white dark:bg-[#404040] rounded-full shadow-sm mb-3">
                            <svg class="h-8 w-8 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-300 font-medium"><span class="text-[#CC2E2E]">Încarcă imagini</span> sau trage-le aici</p>
                        <p class="text-xs text-gray-400 mt-1">Noile imagini se adaugă la cele existente (max 10 total).</p>
                    </div>
                </div>
                <div id="previewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 p-1.5 rounded-lg">📞</span>
                Date de contact
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Telefon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        </div>
                        <input type="text" name="phone" 
                            value="{{ old('phone', $service->phone) }}"
                            class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('phone') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400"
                            required>
                    </div>
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </div>
                        <input type="email" name="email" 
                            value="{{ old('email', $service->email) }}"
                            class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('email') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400">
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 pb-12">
            <button type="submit"
                class="w-full py-4 rounded-xl text-white text-lg font-bold
                       bg-gradient-to-r from-[#CC2E2E] to-red-600 
                       hover:from-[#B72626] hover:to-red-700 
                       active:scale-[0.99] shadow-xl shadow-red-500/20 
                       transition-all duration-200 flex items-center justify-center gap-2">
                <span>Salvează Modificările</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </button>
            
            <div class="text-center mt-6">
                 <a href="{{ route('services.show', [$service->id, $service->slug]) }}" class="text-gray-500 dark:text-gray-400 hover:underline text-sm">
                    Renunță și întoarce-te la anunț
                </a>
            </div>
        </div>
    </form>
</div>

<style>
.no-spinner::-webkit-inner-spin-button,
.no-spinner::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.no-spinner { -moz-appearance: textfield; }

select.form-select, select {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: none !important;
    background-color: transparent;
}
</style>

<script>
document.getElementById('imageInput').addEventListener('change', function (e) {
    const container = document.getElementById('previewContainer');
    container.innerHTML = "";
    [...e.target.files].forEach((file) => {
        const reader = new FileReader();
        reader.onload = function (event) {
            container.innerHTML += `
                <div class="relative group animate-fade-in aspect-square">
                    <img src="${event.target.result}" class="w-full h-full object-cover rounded-xl shadow border border-gray-200 dark:border-[#404040]">
                    <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 bg-black/60 hover:bg-red-600 text-white text-xs p-1.5 rounded-full transition backdrop-blur-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>`;
        };
        reader.readAsDataURL(file);
    });
});

function deleteServerImage(imageName, serviceId, containerId) {
    if (!confirm('Sigur vrei să ștergi această imagine?')) return;

    fetch(`/services/${serviceId}/image`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ image: imageName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(containerId);
            el.style.transition = "all 0.3s ease";
            el.style.opacity = "0";
            el.style.transform = "scale(0.9)";
            setTimeout(() => el.remove(), 300);
        } else {
            alert('Eroare la ștergere: ' + (data.message || 'Necunoscută'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('A apărut o eroare de conexiune.');
    });
}
</script>

@endsection