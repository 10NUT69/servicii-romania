@extends('layouts.app')

@section('title', 'Editează anunț')

@section('content')

<div class="max-w-4xl mx-auto mt-16 mb-20">

    <h1 class="text-3xl font-bold mb-6 text-gray-900 text-center">
        Editează anunțul
    </h1>

    <form action="{{ route('services.update', $service->id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200 space-y-6">

        @csrf
        @method('PUT')   <!--  🔥 OBLIGATORIU PENTRU UPDATE -->

        <!-- TITLU -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Titlul anunțului</label>
            <input type="text" name="title"
                   value="{{ old('title', $service->title) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end outline-none"
                   required>
        </div>

        <!-- DESCRIERE -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Descriere</label>
            <textarea name="description" rows="6"
                      class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                             focus:ring-2 focus:ring-primary-end outline-none"
                      required>{{ old('description', $service->description) }}</textarea>
        </div>

        <!-- CATEGORIE + JUDEȚ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block mb-1 font-semibold text-gray-700">Categoria</label>
                <select name="category_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end outline-none"
                        required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ $category->id == $service->category_id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-semibold text-gray-700">Județ</label>
                <select name="county_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end outline-none"
                        required>
                    @foreach ($counties as $county)
                        <option value="{{ $county->id }}"
                            {{ $county->id == $service->county_id ? 'selected' : '' }}>
                            {{ $county->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- PREȚ -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Preț</label>

            <div class="flex flex-col md:flex-row items-center gap-4">

                <input type="number" name="price_value" step="0.01"
                       value="{{ old('price_value', $service->price_value) }}"
                       class="w-full md:w-40 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                              focus:ring-2 focus:ring-primary-end outline-none no-spinner"
                       placeholder="Ex: 200">

                <select name="currency"
                        class="px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                               focus:ring-2 focus:ring-primary-end outline-none">
                    <option value="RON" {{ $service->currency == 'RON' ? 'selected' : '' }}>RON</option>
                    <option value="EUR" {{ $service->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                </select>

                <div class="flex gap-4 text-gray-700 font-medium">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="price_type" value="fixed"
                               {{ $service->price_type == 'fixed' ? 'checked' : '' }}>
                        <span>Preț fix</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="price_type" value="negotiable"
                               {{ $service->price_type == 'negotiable' ? 'checked' : '' }}>
                        <span>Negociabil</span>
                    </label>
                </div>

            </div>
        </div>

        <!-- IMAGINI EXISTENTE -->
        @if($service->images && count($service->images))
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Imagini existente</label>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($service->images as $img)
                    <div class="relative">
                        <img src="{{ asset('storage/services/' . $img) }}"
                             class="w-full h-32 object-cover rounded-xl shadow">
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ADAUGĂ NOI IMAGINI -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Adaugă noi imagini (opțional)</label>

            <input type="file" name="images[]" multiple accept="image/*"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-white
                          focus:ring-2 focus:ring-primary-end outline-none">

            <p class="text-sm text-gray-500 mt-1">
                Poți încărca până la <strong>10 imagini</strong>. Dacă nu alegi nimic, cele vechi rămân.
            </p>
        </div>

        <!-- TELEFON -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Telefon</label>
            <input type="text" name="phone"
                   value="{{ old('phone', $service->phone) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end outline-none">
        </div>

        <!-- EMAIL -->
        <div>
            <label class="block mb-1 font-semibold text-gray-700">Email</label>
            <input type="email" name="email"
                   value="{{ old('email', $service->email) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50
                          focus:ring-2 focus:ring-primary-end outline-none">
        </div>

        <!-- BUTON UPDATE -->
        <button
            class="w-full py-4 rounded-xl text-white text-lg font-semibold
                   bg-[#e52620] hover:bg-[#d51d68] active:scale-95 shadow-lg transition">
            Salvează modificările
        </button>

    </form>
</div>

@endsection
