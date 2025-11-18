@extends('layouts.app')

@section('title', 'Adaugă anunț')

@section('content')

<div class="max-w-4xl mx-auto mt-4 mb-10">

    <h1 class="text-3xl font-bold mb-8 text-gray-900 text-center">
        Adaugă un anunț nou
    </h1>

    <form action="{{ route('services.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-8 md:p-10 rounded-2xl shadow-lg border border-gray-100 space-y-7">

        @csrf

        <!-- TITLU -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Titlul anunțului</label>
            <input type="text" name="title"
                   placeholder="Ex: Zugrav profesionist, reparații apartamente"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none"
                   required>
        </div>

        <!-- DESCRIERE -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Descriere</label>
            <textarea name="description" rows="6"
                      placeholder="Scrie detalii despre experiență, servicii oferite, garanție..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                             focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none"
                      required></textarea>
        </div>

        <!-- CATEGORIE + JUDEȚ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block mb-1 font-semibold text-gray-800">Categoria</label>
                <select name="category_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end focus:bg-white outline-none transition"
                        required>
                    <option value="">Alege categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-800">Județ</label>
                <select name="county_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end focus:bg-white outline-none transition"
                        required>
                    <option value="">Alege județul</option>
                    @foreach ($counties as $county)
                        <option value="{{ $county->id }}">{{ $county->name }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- PREȚ -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Preț</label>

            <div class="flex flex-col md:flex-row items-center gap-4">

                <input type="number" name="price_value" step="0.01"
                       placeholder="Ex: 200"
                       class="w-full md:w-44 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                              focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none no-spinner">

                <select name="currency"
                        class="px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none">
                    <option value="RON">RON</option>
                    <option value="EUR">EUR</option>
                </select>

                <div class="flex gap-5 text-gray-700 font-medium select-none">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="price_type" value="fixed" checked>
                        <span>Preț fix</span>
                    </label>

                <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="price_type" value="negotiable">
                        <span>Negociabil</span>
                    </label>
                </div>

            </div>
        </div>

        <!-- IMAGINI -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Imagini (max 10)</label>

            <input type="file" id="imageInput"
                   name="images[]" multiple accept="image/*"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white
                          focus:ring-2 focus:ring-primary-end outline-none cursor-pointer">

            <!-- PREVIEW -->
            <div id="previewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>

            <p class="text-sm text-gray-500 mt-1">
                Poți încărca până la <strong>10 imagini</strong>. Vor fi redimensionate automat.
            </p>
        </div>

        <!-- TELEFON -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Telefon</label>
            <input type="text" name="phone"
                   placeholder="Ex: 0740 123 456"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none">
        </div>

        <!-- EMAIL -->
        <div>
            <label class="block mb-1 font-semibold text-gray-800">Email</label>
            <input type="email" name="email"
                   placeholder="Ex: nume@mail.com"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none">
        </div>
		<div>
    <label class="block mb-1 font-semibold text-gray-800">Parolă (opțional)</label>
    <input type="password" name="password"
           placeholder="Setează o parolă pentru a crea un cont"
           class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                  focus:ring-2 focus:ring-primary-end focus:bg-white transition outline-none">
    <p class="text-sm text-gray-500 mt-1">
        Dacă introduci email + parolă → se creează automat cont.
    </p>
</div>

        <!-- BUTON (roșu, ca restul site-ului) -->
        <button
            class="w-full py-4 rounded-xl text-white text-lg font-semibold
                   bg-[#e52620] hover:bg-[#d51d68] active:scale-95 shadow-md transition">
            Publică anunțul
        </button>

    </form>
</div>

<!-- CSS no-spinner -->
<style>
.no-spinner::-webkit-inner-spin-button,
.no-spinner::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.no-spinner { -moz-appearance: textfield; }
</style>

<!-- LIVE PREVIEW JS – ACELAȘI CA LA TINE, NEMEXICAT -->
<script>
document.getElementById('imageInput').addEventListener('change', function (e) {
    const container = document.getElementById('previewContainer');
    container.innerHTML = "";
    [...e.target.files].forEach((file) => {
        const reader = new FileReader();
        reader.onload = function (event) {
            container.innerHTML += `
                <div class="relative group">
                    <img src="${event.target.result}" 
                         class="w-full h-32 object-cover rounded-xl shadow">

                    <button type="button"
                        onclick="this.parentElement.remove()"
                        class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded 
                               opacity-90 group-hover:opacity-100 transition">
                        X
                    </button>
                </div>`;
        };
        reader.readAsDataURL(file);
    });
});
</script>

@endsection
