@extends('layouts.dashboard')

@section('page-title', 'Formations PDF')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Équipements / Formations PDF'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <div class="flex flex-wrap items-end gap-3">
        <form method="POST" action="{{ route('formations.import-pdf') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Désignation</label>
                <input type="text" name="designation" required class="w-64 px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ex: Moniteur cardiaque">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                <select name="document_kind" class="px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                    <option value="technical_manual">Formation technique</option>
                    <option value="user_manual">Formation utilisateur</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input id="formation_pdf" type="file" name="formation_pdf" accept="application/pdf" class="hidden" required>
                <label for="formation_pdf" class="inline-flex items-center justify-center w-11 h-11 border border-red-200 text-red-600 rounded-lg bg-white hover:bg-red-50 cursor-pointer" title="Importer PDF">
                    <i class="fas fa-file-import"></i>
                </label>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-upload mr-2"></i>Importer PDF
                </button>
            </div>
        </form>

        <a href="{{ route('formations.export-pdf', ['q' => $search ?? '']) }}" class="inline-flex items-center px-4 py-2 border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50">
            <i class="fas fa-file-pdf mr-2"></i>Exporter PDF
        </a>
        <button type="button" onclick="downloadFormationsTemplate('technique')" class="inline-flex items-center px-4 py-2 border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-50">
            <i class="fas fa-file-download mr-2"></i>Template technique
        </button>
        <button type="button" onclick="downloadFormationsTemplate('utilisateur')" class="inline-flex items-center px-4 py-2 border border-amber-200 text-amber-700 rounded-lg hover:bg-amber-50">
            <i class="fas fa-file-download mr-2"></i>Template utilisateur
        </button>
    </div>
</div>

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('formations.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche par désignation</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Ex: Échographe, Moniteur..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('formations.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Reset</a>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-md border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800"><i class="fas fa-file-pdf text-indigo-600 mr-2"></i>Formation technique</h3>
            <span class="text-xs text-gray-500">{{ ($technicalDocuments ?? collect())->count() }} PDF</span>
        </div>

        @if (($technicalDocuments ?? collect())->isEmpty())
            <p class="px-4 py-5 text-sm text-gray-500">Aucun PDF technique importé.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="text-left px-3 py-2 border-b">Désignation</th>
                            <th class="text-left px-3 py-2 border-b">Fichier</th>
                            <th class="text-left px-3 py-2 border-b">Date</th>
                            <th class="text-left px-3 py-2 border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (($technicalDocuments ?? collect()) as $document)
                            <tr>
                                <td class="px-3 py-2 border-b">{{ $document['designation'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">{{ $document['file_name'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">{{ $document['updated_at'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">
                                    <a href="{{ $document['view_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                        <i class="fas fa-eye mr-2"></i>Voir PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-100">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800"><i class="fas fa-file-pdf text-blue-600 mr-2"></i>Formation utilisateur</h3>
            <span class="text-xs text-gray-500">{{ ($userDocuments ?? collect())->count() }} PDF</span>
        </div>

        @if (($userDocuments ?? collect())->isEmpty())
            <p class="px-4 py-5 text-sm text-gray-500">Aucun PDF utilisateur importé.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="text-left px-3 py-2 border-b">Désignation</th>
                            <th class="text-left px-3 py-2 border-b">Fichier</th>
                            <th class="text-left px-3 py-2 border-b">Date</th>
                            <th class="text-left px-3 py-2 border-b">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (($userDocuments ?? collect()) as $document)
                            <tr>
                                <td class="px-3 py-2 border-b">{{ $document['designation'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">{{ $document['file_name'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">{{ $document['updated_at'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-b">
                                    <a href="{{ $document['view_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                        <i class="fas fa-eye mr-2"></i>Voir PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    function downloadCsvFallback(fileName, rows) {
        const escapeCell = (value) => {
            const text = (value ?? '').toString();
            const escaped = text.replace(/"/g, '""');
            return `"${escaped}"`;
        };

        const csvLines = rows.map((row) => row.map(escapeCell).join(','));
        const blob = new Blob(["\uFEFF" + csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function downloadFormationsTemplate(kind) {
        const typeLabel = kind === 'technique' ? 'Formation technique' : 'Formation utilisateur';
        const rows = [
            ['Designation', 'Type document', 'Nom fichier PDF'],
            ['Moniteur cardiaque', typeLabel, 'formation-exemple.pdf'],
            ['', typeLabel, '']
        ];

        if (!window.XLSX) {
            const dateSuffix = new Date().toISOString().slice(0, 10);
            downloadCsvFallback(`template-formations-${kind}-${dateSuffix}.csv`, rows);
            return;
        }

        const worksheet = XLSX.utils.aoa_to_sheet(rows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Template');

        const dateSuffix = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(workbook, `template-formations-${kind}-${dateSuffix}.xlsx`);
    }
</script>
@endsection
