@extends('layouts.dashboard')

@section('page-title', 'Marchés et contrats de maintenance')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Marchés et contrats de maintenance',
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        {{ session('error') }}
    </div>
@endif

@if (session('import_result'))
    @php $ir = session('import_result'); @endphp
    @php
        $type = (string) ($ir['type'] ?? 'warning');
        $alertClass = $type === 'success'
            ? 'border-green-200 bg-green-50 text-green-700'
            : ($type === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-yellow-200 bg-yellow-50 text-yellow-700');
    @endphp
    <div class="mb-4 rounded-lg border px-4 py-3 {{ $alertClass }}">
        <p class="font-semibold">{{ $ir['title'] ?? 'Résultat import' }}</p>
        <p class="text-sm">{{ $ir['message'] ?? '' }}</p>
    </div>
@endif

@if(auth()->user()?->role !== 'major')
<div class="mb-4 flex flex-wrap justify-end gap-3">
    <form id="market_import_form" method="POST" action="{{ route('markets.equipments.import-excel') }}" enctype="multipart/form-data" class="inline-flex">
        @csrf
        <input id="market_excel_files" type="file" name="excel_files[]" accept=".xlsx,.xls" multiple class="hidden" required>
        <button type="button" id="market_import_btn" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
            <i class="fas fa-file-excel"></i>
            <span>Importer</span>
        </button>
    </form>

    <button type="button" id="market_export_btn" class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
        <i class="fas fa-file-export"></i>
        <span>Exporter Excel</span>
    </button>
</div>
@endif

<div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
        <h4 class="text-sm font-semibold text-gray-800">Recherche & navigation du tableau</h4>
        @if (!empty($marketsPagination) && $marketsPagination->total() > 0)
            <p class="text-xs text-gray-500">
                Affichage {{ $marketsPagination->firstItem() ?? 0 }}-{{ $marketsPagination->lastItem() ?? 0 }} sur {{ $marketsPagination->total() }} lignes
            </p>
        @endif
    </div>

    <form method="GET" action="{{ route('markets.equipments') }}" class="flex flex-col xl:flex-row xl:items-end gap-3">
        <div class="w-full xl:w-72">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de marché</label>
            <input type="text"
                   name="market_number"
                   value="{{ $marketNumberFilter ?? '' }}"
                   placeholder="Ex: 32/2020"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div class="w-full xl:w-72">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <input type="text"
                   name="company"
                   value="{{ $companyFilter ?? '' }}"
                   placeholder="Ex: MEDICAR"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('markets.equipments') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>

    @if (!empty($marketsPagination) && $marketsPagination->hasPages())
        <div class="mt-3 pt-3 border-t border-gray-100">
            {{ $marketsPagination->links() }}
        </div>
    @endif
</div>

<section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800">Liste des marchés importés</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° DU MARCHÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">RÉFÉRENCE</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SOCIÉTÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DATE MARCHÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">LIGNES IMPORTÉES</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse(($marketsData ?? []) as $row)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('markets.show', $row['market_id']) }}'">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['market_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['reference'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['company'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['market_date'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['lines_count'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700" onclick="event.stopPropagation()">
                            <a href="{{ route('markets.show', $row['market_id']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                <i class="fas fa-eye mr-1"></i> Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-sm text-gray-500 text-center">Aucun marché importé disponible.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (!empty($marketsPagination) && $marketsPagination->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-white">
            {{ $marketsPagination->links() }}
        </div>
    @endif
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const importBtn = document.getElementById('market_import_btn');
    const exportBtn = document.getElementById('market_export_btn');
    const fileInput = document.getElementById('market_excel_files');
    const form = document.getElementById('market_import_form');

    if (!importBtn || !fileInput || !form) {
        return;
    }

    importBtn.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            form.submit();
        }
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', exportMarketTableToExcel);
    }
});

function exportMarketTableToExcel() {
    const table = document.querySelector('section table');
    if (!table) {
        window.alert('Tableau introuvable.');
        return;
    }

    const headerCells = Array.from(table.querySelectorAll('thead th'));
    const includedIndexes = [];
    const headers = [];

    headerCells.forEach(function (cell, index) {
        const text = (cell.textContent || '').trim();
        if (text.toUpperCase() === 'ACTIONS') {
            return;
        }

        includedIndexes.push(index);
        headers.push(text);
    });

    const bodyRows = Array.from(table.querySelectorAll('tbody tr'));
    const rows = [];

    bodyRows.forEach(function (row) {
        const cells = Array.from(row.querySelectorAll('td'));
        if (!cells.length) {
            return;
        }

        const firstCellText = (cells[0].textContent || '').trim();
        if (cells.length === 1 && firstCellText.toLowerCase().includes('aucune donnée')) {
            return;
        }

        const rowValues = includedIndexes.map(function (idx) {
            return (cells[idx]?.textContent || '').replace(/\s+/g, ' ').trim();
        });

        rows.push(rowValues);
    });

    if (!rows.length) {
        window.alert('Aucune donnée à exporter.');
        return;
    }

    const dateSuffix = new Date().toISOString().slice(0, 10);

    if (window.XLSX) {
        const worksheet = XLSX.utils.aoa_to_sheet([headers, ...rows]);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Marches');
        XLSX.writeFile(workbook, `marches-contrats-${dateSuffix}.xlsx`);
        return;
    }

    const toCsvCell = function (value) {
        const text = String(value ?? '');
        return '"' + text.replace(/"/g, '""') + '"';
    };

    const csvLines = [headers, ...rows].map(function (line) {
        return line.map(toCsvCell).join(',');
    });

    const blob = new Blob(["\uFEFF" + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `marches-contrats-${dateSuffix}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
@endsection
