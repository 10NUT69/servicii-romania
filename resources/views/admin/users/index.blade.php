@extends('admin.layout')

@section('content')
<div class="p-6">

    <h1 class="text-2xl font-bold mb-6">Lista Utilizatorilor</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow rounded overflow-x-auto">

        <table class="w-full text-left">
            <thead>
                <tr class="border-b bg-gray-100">
                    <th class="p-3">#</th>
                    <th class="p-3">Nume</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Anunțuri</th>
                    <th class="p-3">Rol</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Creat la</th>
                    <th class="p-3 text-center">Acțiuni</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $user->id }}</td>

                        <td class="p-3 font-semibold">
                            {{ $user->name }}
                        </td>

                        <td class="p-3">
                            {{ $user->email }}
                        </td>

                        <td class="p-3">
                            {{ $user->services_count }}
                        </td>

                        <td class="p-3">
                            @if($user->is_admin)
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-sm">Admin</span>
                            @else
                                <span class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-sm">User</span>
                            @endif
                        </td>

                        <td class="p-3">
                            @if($user->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">Activ</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">Blocat</span>
                            @endif
                        </td>

                        <td class="p-3">
                            {{ $user->created_at->format('d.m.Y') }}
                        </td>

                        <td class="p-3 text-center">

                            {{-- ACTIVARE / DEZACTIVARE --}}
                            <form action="{{ route('admin.users.toggle', $user->id) }}"
                                  method="POST"
                                  class="inline-block">
                                @csrf
                                <button class="px-3 py-1 text-white rounded
                                    {{ $user->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
                                    {{ $user->is_active ? 'Blochează' : 'Activează' }}
                                </button>
                            </form>

                            {{-- ȘTERGERE --}}
                            <form action="{{ route('admin.users.destroy', $user->id) }}"
                                  method="POST"
                                  class="inline-block ml-2"
                                  onsubmit="return confirm('Sigur ștergi acest utilizator?');">
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
                        <td colspan="8" class="p-6 text-center text-gray-500">
                            Nu există utilizatori.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

</div>
@endsection
