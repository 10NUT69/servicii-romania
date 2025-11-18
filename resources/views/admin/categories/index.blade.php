@extends('admin.layout')

@section('content')
<div class="p-6">

    <h1 class="text-2xl font-bold mb-6">Lista Categoriilor</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <div></div>

        <a href="{{ route('admin.categories.create') }}"
           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            + Adaugă categorie
        </a>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">

        <table class="w-full text-left">
            <thead>
                <tr class="border-b bg-gray-100">
                    <th class="p-3">#</th>
                    <th class="p-3">Nume</th>
                    <th class="p-3">Slug</th>
                    <th class="p-3">Ordine</th>
                    <th class="p-3">Anunțuri</th>
                    <th class="p-3 text-center">Acțiuni</th>
                </tr>
            </thead>

            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3">{{ $category->id }}</td>

                        <td class="p-3 font-semibold">{{ $category->name }}</td>

                        <td class="p-3">{{ $category->slug }}</td>

                        <td class="p-3">{{ $category->sort_order }}</td>

                        <td class="p-3">{{ $category->services_count }}</td>

                        <td class="p-3 text-center">

                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                               class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                Editează
                            </a>

                            <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                  method="POST"
                                  class="inline-block ml-2"
                                  onsubmit="return confirm('Sigur ștergi această categorie?');">
                                @csrf
                                @method('DELETE')

                                <button class="px-3 py-1 bg-red-700 text-white rounded hover:bg-red-800">
                                    Șterge
                                </button>
                            </form>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">
                            Nu există categorii.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $categories->links() }}
    </div>

</div>
@endsection
