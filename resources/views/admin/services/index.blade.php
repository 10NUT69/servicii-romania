@extends('admin.layout')

@section('content')
<div class="max-w-[1600px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">

    <!-- HEADER: Titlu & Statistici Rapide -->
    <div class="flex flex-col md:flex-row justify-between items-end mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Administrare Anunțuri</h1>
            <p class="text-sm text-slate-500 mt-1">Gestionează toate serviciile publicate pe platformă.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-white border border-slate-200 px-3 py-1 rounded-md text-xs font-medium text-slate-500 shadow-sm">
                Total: <strong class="text-slate-800">{{ $services->total() }}</strong>
            </span>
        </div>
    </div>

    {{-- MESAJE FLASH --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center shadow-sm relative animate-fade-in-down">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <div>
                <span class="font-bold">Succes!</span> {{ session('success') }}
            </div>
            <button onclick="this.parentElement.remove()" class="absolute top-3 right-3 text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- CARD PRINCIPAL --}}
    <div class="bg-white rounded-xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">

        {{-- FORMULAR BULK (Include Tabelul) --}}
        <form action="{{ route('admin.services.bulk') }}" method="POST" id="bulkForm">
            @csrf

            {{-- TOOLBAR (Acțiuni în Masă & Filtre) --}}
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                
                {{-- Stânga: Acțiuni Bulk --}}
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fas fa-tasks text-xs"></i>
                        </div>
                        <select name="action" class="pl-9 pr-8 py-2 bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all cursor-pointer font-medium hover:border-blue-300 w-full sm:w-48 shadow-sm">
                            <option value="">Acțiuni în masă...</option>
                            <option value="activate">✅ Activează Selectate</option>
                            <option value="deactivate">⏸ Dezactivează Selectate</option>
                            <option value="delete">🗑️ Șterge Selectate</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all shadow-md active:transform active:scale-95">
                        Aplică
                    </button>
                </div>

                {{-- Dreapta: Search --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" placeholder="Caută anunț..." class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200 font-semibold">
                            <th class="p-4 w-10 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                            </th>
                            <th class="p-4">Anunț / Categorie</th>
                            <th class="p-4">Autor</th>
                            <th class="p-4">Preț</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Data</th>
                            <th class="p-4 text-right">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($services as $service)
                            <tr class="group hover:bg-slate-50/80 transition-colors duration-150">
                                
                                {{-- Checkbox --}}
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="ids[]" value="{{ $service->id }}" class="rowCheck rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                </td>

                                {{-- Titlu & Categorie --}}
                                <td class="p-4">
                                    <div class="flex items-start gap-3">
                                        {{-- Thumbnail Mic --}}
                                        <div class="w-10 h-10 rounded-lg bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                            @php
                                                // Folosim accesorul din Model care gestionează automat fallback-ul
                                                $imgSrc = $service->main_image_url;
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            {{-- 🔥 FIX AICI: Adăugați toți parametrii necesari rutei --}}
                                            <a href="{{ route('service.show', [
                                                    'category' => $service->category ? $service->category->slug : 'diverse',
                                                    'county'   => $service->county ? $service->county->slug : 'romania',
                                                    'slug'     => $service->slug,
                                                    'id'       => $service->id
                                                ]) }}" 
                                               target="_blank" 
                                               class="text-sm font-bold text-slate-800 hover:text-blue-600 truncate block transition-colors leading-tight mb-0.5">
                                                {{ $service->title }}
                                            </a>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                                {{ $service->category->name ?? 'Fără Categorie' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Autor --}}
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                            {{ strtoupper(substr($service->user->name ?? 'A', 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-700">{{ $service->user->name ?? 'User Șters' }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $service->county->name ?? 'România' }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Preț --}}
                                <td class="p-4">
                                    @if($service->price_value)
                                        <span class="text-sm font-bold text-slate-700">{{ number_format($service->price_value) }} {{ $service->currency }}</span>
                                    @else
                                        <span class="text-xs font-medium text-slate-400 italic">Negociabil</span>
                                    @endif
                                </td>

                                {{-- Status (Badge) --}}
                                <td class="p-4 text-center">
                                    @if($service->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Activ
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Inactiv
                                        </span>
                                    @endif
                                </td>

                                {{-- Data --}}
                                <td class="p-4">
                                    <span class="text-xs text-slate-500 font-mono">
                                        {{ $service->created_at->format('d M Y') }}
                                    </span>
                                </td>

                                {{-- Acțiuni --}}
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        
                                        {{-- Vizualizare (Folosim același link complex) --}}
                                        <a href="{{ route('service.show', [
                                                'category' => $service->category ? $service->category->slug : 'diverse',
                                                'county'   => $service->county ? $service->county->slug : 'romania',
                                                'slug'     => $service->slug,
                                                'id'       => $service->id
                                            ]) }}" 
                                           target="_blank"
                                           class="p-2 bg-white border border-slate-200 text-slate-500 rounded-lg hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition shadow-sm"
                                           title="Vezi pe site">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>

                                        {{-- Toggle Status --}}
                                        <button type="button" 
                                                onclick="toggleService({{ $service->id }})"
                                                class="p-2 bg-white border border-slate-200 rounded-lg transition shadow-sm
                                                {{ $service->is_active ? 'text-amber-500 hover:bg-amber-50 hover:border-amber-200' : 'text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200' }}"
                                                title="{{ $service->is_active ? 'Dezactivează' : 'Activează' }}">
                                            <i class="fas {{ $service->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>

                                        {{-- Ștergere --}}
                                        <button type="button" 
                                                onclick="deleteService({{ $service->id }})"
                                                class="p-2 bg-white border border-slate-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm"
                                                title="Șterge definitiv">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                            <i class="far fa-folder-open text-3xl"></i>
                                        </div>
                                        <p class="text-lg font-medium text-slate-600">Nu există anunțuri</p>
                                        <p class="text-sm">Încearcă să schimbi filtrele sau adaugă un anunț nou.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINARE --}}
            @if($services->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-center">
                {{ $services->links() }} 
            </div>
            @endif

        </form>
    </div>
</div>

{{-- FORMULARE ASCUNSE (PENTRU JS) --}}
<form id="toggleForm" action="" method="POST" style="display: none;">
    @csrf
</form>

<form id="deleteForm" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- SCRIPTS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script>
    // 1. Select All Checkboxes
    const selectAll = document.getElementById('selectAll');
    const rowChecks = document.querySelectorAll('.rowCheck');

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            rowChecks.forEach(cb => cb.checked = this.checked);
        });
    }

    // 2. Templates pentru URL-uri
    const toggleUrlTemplate = '{{ route("admin.services.toggle", "ID_PLACEHOLDER") }}';
    const deleteUrlTemplate = '{{ route("admin.services.destroy", "ID_PLACEHOLDER") }}';

    // 3. Funcție Toggle Status
    function toggleService(id) {
        const form = document.getElementById('toggleForm');
        form.action = toggleUrlTemplate.replace('ID_PLACEHOLDER', id);
        form.submit();
    }

    // 4. Funcție Ștergere (Cu confirmare modernă standard)
    function deleteService(id) {
        if (confirm('⚠️ Ești sigur că vrei să ștergi acest anunț?\n\nAceastă acțiune este ireversibilă și va șterge și imaginile asociate.')) {
            const form = document.getElementById('deleteForm');
            form.action = deleteUrlTemplate.replace('ID_PLACEHOLDER', id);
            form.submit();
        }
    }
</script>

@endsection