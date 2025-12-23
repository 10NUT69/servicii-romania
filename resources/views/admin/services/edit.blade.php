@extends('admin.layout')

@section('content')
<div class="max-w-[900px] mx-auto">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Editează anunț</h1>
            <p class="text-sm text-slate-500 mt-1">
                ID: #{{ $service->id }}
                @if($service->trashed())
                    <span class="ml-2 inline-flex px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-bold">
                        ȘTERS
                    </span>
                @endif
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ $service->public_url }}" target="_blank"
               class="px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
                Vezi public
            </a>

            <a href="{{ route('admin.services.index', request()->only(['search','status','page'])) }}"
               class="px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
                Înapoi la listă
            </a>
        </div>
    </div>

    {{-- MESAJE --}}
    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            <b>{{ session('success') }}</b>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <b>Erori:</b>
            <ul class="list-disc ml-5 mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('admin.services.update', $service->id) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- TITLU --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700">Titlu</label>
                <input type="text"
                       name="title"
                       value="{{ old('title', $service->title) }}"
                       class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"
                       required>
            </div>

            {{-- CATEGORIE --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700">Categorie</label>
                <select name="category_id"
                        class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"
                        required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ (int)old('category_id', $service->category_id) === (int)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DESCRIERE --}}
            <div>
                <label class="block mb-2 text-sm font-semibold text-slate-700">Descriere</label>
                <textarea name="description"
                          rows="10"
                          class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none resize-y"
                          required>{{ old('description', $service->description) }}</textarea>
            </div>

            {{-- SUBMIT --}}
            <div class="pt-2">
                <button type="submit"
                        class="px-5 py-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-semibold transition">
                    Salvează modificările
                </button>
            </div>
        </form>
		<hr class="my-6">

<h2 class="text-lg font-bold text-slate-800 mb-3">Imagini</h2>

@php
    $gallery = $service->images;
    if (is_null($gallery)) $gallery = [];
    if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
    if (!is_array($gallery)) $gallery = [];
    $gallery = array_values(array_filter($gallery));
@endphp

@if(count($gallery) === 0)
    <div class="text-sm text-slate-500">
        Nu există imagini încărcate. (Se va afișa automat poza default din categorie.)
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($gallery as $img)
            <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white">
                <img src="{{ asset('storage/services/' . $img) }}" class="w-full h-full object-cover">

                <form method="POST"
                      action="{{ route('admin.services.deleteImage', $service->id) }}"
                      onsubmit="return confirm('Ștergi această imagine?')"
                      class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="image" value="{{ $img }}">
                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs font-bold">
                        Șterge
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endif

    </div>

</div>
@endsection
