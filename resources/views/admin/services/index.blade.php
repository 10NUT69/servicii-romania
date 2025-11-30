
@extends('admin.layout')

@section('content')
<div class="p-6">

    <h1 class="text-2xl font-bold mb-6">Lista Anunțurilor</h1>

    {{-- AFIȘARE MESAJE SESSIUNE --}}
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


    {{-- ######################################################## --}}
    {{-- ## 1. FORMULARUL PRINCIPAL (BULK) ## --}}
    {{-- Acest formular ÎNCADREAZĂ tabelul --}}
    {{-- ######################################################## --}}
    <form action="{{ route('admin.services.bulk') }}" method="POST" id="bulkForm">
        @csrf

        <div class="flex items-center gap-3 mb-4">
            <select name="action" class="border rounded p-2">
                <option value="">— Alege acțiunea —</option>
                <option value="activate">Activează</option>
                <option value="deactivate">Dezactivează</option>
                <option value="delete">Șterge</option>
            </select>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Aplică
            </button>
        </div>

        {{-- TABELUL SE AFLĂ ÎN INTERIORUL FORMULARULUI BULK --}}
        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b bg-gray-100">
                        <th class="p-3">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th class="p-3">#</th>
                        <th class="p-3">Titlu</th>
                        <th class="p-3">User</th>
                        <th class="p-3">Categorie</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Data</th>
                        <th class="p-3 text-center">Acțiuni</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($services as $service)
                        <tr class="border-b hover:bg-gray-50">

                            {{-- Checkbox-ul face parte din formularul #bulkForm --}}
                            <td class="p-3">
                                <input type="checkbox" name="ids[]" value="{{ $service->id }}" class="rowCheck">
                            </td>

                            <td class="p-3 font-semibold">
    <a href="{{ route('service.show', [
        'category' => $service->category->slug,
        'county'   => $service->county,
        'slug'     => $service->slug,
        'id'       => $service->id
    ]) }}"
       class="text-blue-600 hover:underline" target="_blank">
        {{ $service->title }}
    </a>
</td>


                            <td class="p-3">
                                {{ $service->user->name ?? 'User șters' }}
                            </td>

                            <td class="p-3">
                                {{ $service->category->name ?? '-' }}
                            </td>

                            <td class="p-3">
                                @if($service->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-sm">Activ</span>
                                @else
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm">Inactiv</span>
                                @endif
                            </td>

                            <td class="p-3">
                                {{ $service->created_at->format('d.m.Y') }}
                            </td>

                            {{-- ######################################################## --}}
                            {{-- ## 2. ACȚIUNI INDIVIDUALE (CORECTATE) ## --}}
                            {{-- Acestea NU mai sunt formulare, ci butoane simple --}}
                            {{-- ######################################################## --}}
                            <td class="p-3 text-center">

                                {{-- BUTON PENTRU ACTIVARE / DEZACTIVARE --}}
                                <button type="button"
                                        onclick="toggleService({{ $service->id }})"
                                        class="px-3 py-1 text-white rounded
                                                {{ $service->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
                                    {{ $service->is_active ? 'Dezactivează' : 'Activează' }}
                                </button>

                                {{-- BUTON PENTRU ȘTERGERE --}}
                                <button type="button"
                                        onclick="deleteService({{ $service->id }})"
                                        class="px-3 py-1 bg-red-700 text-white rounded hover:bg-red-800 ml-2">
                                    Șterge
                                </button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-500">
                                Nu există anunțuri.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </form> {{-- AICI SE ÎNCHIDE FORMULARUL #bulkForm --}}

    <div class="mt-6">
        {{ $services->links() }}
    </div>

</div> {{-- Sfârșitul <div class="p-6"> --}}


{{-- ######################################################## --}}
{{-- ## 3. FORMULARE ASCUNSE ȘI SCRIPTURI (PLASATE AFARĂ) ## --}}
{{-- Acestea sunt formularele pe care le va folosi JavaScript --}}
{{-- ######################################################## --}}

{{-- Formular ascuns pentru Toggle (Activare/Dezactivare) --}}
<form id="toggleForm" action="" method="POST" style="display: none;">
    @csrf
</form>

{{-- Formular ascuns pentru Delete (Ștergere) --}}
<form id="deleteForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


<script>
    // JS pentru "Select All" (codul tău original)
    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.rowCheck').forEach(cb => cb.checked = this.checked);
    });


    /**
     * Generează un URL "template" folosind ruta Blade și un placeholder.
     * Aceasta este o metodă sigură care va funcționa indiferent de cum
     * ai definit tu parametrul în fișierul de rute (ca {service} sau {id}).
     */
    const toggleUrlTemplate = '{{ route("admin.services.toggle", "ID_PLACEHOLDER") }}';
    const deleteUrlTemplate = '{{ route("admin.services.destroy", "ID_PLACEHOLDER") }}';


    // Funcție JS pentru Toggle
    function toggleService(id) {
        const form = document.getElementById('toggleForm');
        
        // Înlocuim placeholder-ul cu ID-ul real
        form.action = toggleUrlTemplate.replace('ID_PLACEHOLDER', id);
        
        form.submit();
    }

    // Funcție JS pentru Delete
    function deleteService(id) {
        // Păstrăm confirmarea
        if (confirm('Sigur ștergi acest anunț?')) {
            const form = document.getElementById('deleteForm');
            
            // Înlocuim placeholder-ul cu ID-ul real
            form.action = deleteUrlTemplate.replace('ID_PLACEHOLDER', id);

            form.submit();
        }
    }
</script>

@endsection
