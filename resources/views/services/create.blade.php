@extends('layouts.app')

@section('title', 'Adaugă anunț gratuit - MeseriasBun.ro')
@section('meta_description', 'Publică rapid anunțul tău. Formular simplu pentru meseriași și prestatori de servicii.')

@section('content')

<div class="max-w-3xl mx-auto mt-8 mb-16 px-4 md:px-0">

    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight transition-colors">
            Hai să publicăm anunțul tău
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg transition-colors">
            Completează detaliile pentru a găsi clienți rapid.
        </p>
    </div>

    <form action="{{ route('services.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="space-y-8"
          id="serviceForm">

        @csrf

        {{-- SECȚIUNEA 1: DETALII DE BAZĂ --}}
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
                        <input type="text" name="title" value="{{ old('title') }}"
                            placeholder="Ex: Zugrav profesionist, execut lucrări rapide"
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
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer appearance-none form-select"
                                    required>
                                <option value="" class="dark:bg-[#1E1E1E]">Alege categoria</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">
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
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer appearance-none form-select"
                                    required>
                                <option value="" class="dark:bg-[#1E1E1E]">Alege județul</option>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" {{ old('county_id') == $county->id ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">
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
                              placeholder="Descrie serviciile tale..."
                              class="w-full px-4 py-3 rounded-xl border @error('description') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                     bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                     focus:ring-2 focus:ring-primary-end outline-none transition resize-y placeholder-gray-400"
                              required>{{ old('description') }}</textarea>
                     @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- SECȚIUNEA 2: PREȚ ȘI IMAGINI --}}
        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-green-100 dark:bg-green-900/30 text-green-600 p-1.5 rounded-lg">💰</span>
                Preț și Imagini
            </h2>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Prețul estimativ (Daca nu completezi prețul, afisăm "Cere Ofertă")</label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <input type="number" name="price_value" step="0.01" placeholder="0.00" value="{{ old('price_value') }}"
                               class="w-full pl-4 pr-20 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040] 
                                      bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white font-bold text-lg
                                      focus:ring-2 focus:ring-primary-end outline-none transition no-spinner placeholder-gray-400">
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            <select name="currency" class="h-full py-0 pl-2 pr-7 bg-transparent text-gray-500 dark:text-gray-300 font-semibold border-none focus:ring-0 cursor-pointer">
                                <option value="RON" {{ old('currency') == 'RON' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">RON</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">EUR</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex bg-gray-100 dark:bg-[#2C2C2C] p-1 rounded-xl w-full sm:w-auto shrink-0">
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="fixed" {{ old('price_type', 'fixed') == 'fixed' ? 'checked' : '' }} class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 transition-all peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm">
                                Preț Fix
                            </span>
                        </label>
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="negotiable" {{ old('price_type') == 'negotiable' ? 'checked' : '' }} class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 transition-all peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm">
                                Negociabil
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300 flex justify-between items-center">
                    <span>Galerie Foto <span class="text-gray-400 font-normal ml-1">(Opțional)</span></span>
                    <span class="text-xs font-normal text-gray-400">Poți publica și fără poze (O sa afisam noi o poza generica pt tine)</span>
                </label>
                
                <div class="relative w-full group">
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-[#404040] rounded-2xl bg-gray-50 dark:bg-[#2C2C2C] group-hover:bg-gray-100 dark:group-hover:bg-[#333333] transition-colors">
                        <div class="p-3 bg-white dark:bg-[#404040] rounded-full shadow-sm mb-3">
                            <svg class="h-8 w-8 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-300 font-medium"><span class="text-[#CC2E2E]">Încarcă imagini</span> sau trage-le aici</p>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG (max 10 imagini, max 15MB)</p>
                    </div>
                </div>

                <div id="previewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                </div>
            </div>
        </div>

        {{-- SECȚIUNEA 3: DATE CONTACT --}}
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
                        <input type="text" name="phone" value="{{ old('phone') }}"
                            placeholder="07xx xxx xxx"
                            class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('phone') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400"
                            required>
                    </div>
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                @auth
                    <div class="flex items-center text-green-600 dark:text-green-400 text-sm font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Ești autentificat ca {{ auth()->user()->name }}
                    </div>
                @endauth
            </div>

            @guest
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-[#2C2C2C]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">
                            Nume <span class="text-gray-400 font-normal text-sm">(Opțional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="Numele tău complet"
                                class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">
                            Email <span class="text-gray-400 font-normal text-sm">(Opțional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="Adaugă email dacă vrei cont"
                                class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('email') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                   focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400">
                        </div>
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">
                        Parolă <span class="text-gray-400 font-normal text-sm">(Opțional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <input type="password" name="password"
                            placeholder="Creează o parolă (minim 6 caractere)"
                            class="w-full pl-10 pr-4 py-3.5 rounded-xl border @error('password') border-red-500 @else border-gray-300 dark:border-[#404040] @enderror
                                bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                focus:ring-2 focus:ring-primary-end focus:bg-white dark:focus:bg-[#252525] transition outline-none placeholder-gray-400">
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4 flex items-start gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
                    <svg class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Dacă lași Email și Parola goale, anunțul se publică <strong>anonim</strong>. Dacă le completezi, îți creăm <strong>automat un cont</strong>.
                    </p>
                </div>
            </div>
            @endguest
        </div>

        <div class="pt-4 pb-12">
            <button type="submit" 
                class="w-full py-4 rounded-xl text-white text-lg font-bold
                       bg-gradient-to-r from-[#CC2E2E] to-red-600 
                       hover:from-[#B72626] hover:to-red-700 
                       active:scale-[0.99] shadow-xl shadow-red-500/20 
                       transition-all duration-200 flex items-center justify-center gap-2">
                <span>Publică Anunțul</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
        </div>
    </form>
</div>

<style>
/* Stiluri importante pentru inputuri */
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
// Array global pentru stocarea fișierelor
let uploadedFiles = [];
const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');
const LIMITA_MB = 15;

imageInput.addEventListener('change', function (e) {
    const newFiles = Array.from(e.target.files);
    let hasError = false;

    // 1. Validare
    newFiles.forEach(file => {
        if (file.size > LIMITA_MB * 1024 * 1024) {
            alert(`STOP: Imaginea "${file.name}" este prea mare!\nLimita acceptată este de ${LIMITA_MB} MB.`);
            hasError = true;
        }
    });

    if (hasError) {
        updateInputFiles(); // Resetează input-ul la starea validă anterioară
        return;
    }

    // 2. Adăugăm fișierele noi la cele vechi
    uploadedFiles = uploadedFiles.concat(newFiles);

    // Limităm la 10 fișiere
    if (uploadedFiles.length > 10) {
        alert("Poți încărca maxim 10 imagini.");
        uploadedFiles = uploadedFiles.slice(0, 10);
    }

    // 3. Regenerăm UI-ul și input-ul
    renderPreviews();
    updateInputFiles();
});

function renderPreviews() {
    previewContainer.innerHTML = "";

    uploadedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const isMain = index === 0; // Prima imagine este principală
            
            const div = document.createElement('div');
            // Stiluri dinamice: bordură verde pentru Main
            div.className = `relative group animate-fade-in aspect-square rounded-xl overflow-hidden shadow-sm border-2 ${isMain ? 'border-green-500 ring-2 ring-green-500/30' : 'border-gray-200 dark:border-[#404040]'}`;
            
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover bg-gray-100">
                
                ${isMain ? `
                    <div class="absolute top-0 left-0 w-full bg-green-500 text-white text-[10px] font-bold py-1 text-center shadow-lg z-10">
                        PRINCIPALĂ
                    </div>
                ` : ''}

                <div class="absolute bottom-1 left-1 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded backdrop-blur-sm font-mono z-10">
                    ${(file.size / 1024 / 1024).toFixed(2)} MB
                </div>

                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-4">
                    
                    ${!isMain ? `
                    <button type="button" onclick="setMainPhoto(${index})" 
                            class="px-3 py-1.5 bg-white text-gray-900 text-xs font-bold rounded-lg hover:bg-green-50 hover:text-green-600 transition shadow-lg w-full">
                        Setează Principală
                    </button>
                    ` : ''}

                    <button type="button" onclick="removePhoto(${index})" 
                            class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition shadow-lg w-full flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Șterge
                    </button>
                </div>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Mută imaginea de la index pe poziția 0
window.setMainPhoto = function(index) {
    if (index === 0) return;
    const fileToMove = uploadedFiles.splice(index, 1)[0];
    uploadedFiles.unshift(fileToMove);
    renderPreviews();
    updateInputFiles();
}

// Șterge imaginea
window.removePhoto = function(index) {
    uploadedFiles.splice(index, 1);
    renderPreviews();
    updateInputFiles();
}

// Sincronizează array-ul JS cu input-ul HTML care pleacă spre server
function updateInputFiles() {
    const dataTransfer = new DataTransfer();
    uploadedFiles.forEach(file => {
        dataTransfer.items.add(file);
    });
    imageInput.files = dataTransfer.files;
}
</script>

@endsection