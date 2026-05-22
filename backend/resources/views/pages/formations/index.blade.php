@extends('layouts.dashboard')

@section('page-title', 'Scans Formations')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Équipements / Bibliothèque scans PDF'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

<div class="mb-4 flex flex-wrap items-center justify-end gap-3">
    <form id="scan_import_form" method="POST" action="{{ route('formations.import-pdf') }}" enctype="multipart/form-data" class="inline-flex flex-wrap items-center gap-3">
        @csrf
        <div>
            <label for="scan_title" class="sr-only">Désignation (optionnel)</label>
            <input id="scan_title" type="text" name="scan_title" class="h-10 w-72 px-4 border border-gray-300 rounded-lg" placeholder="Désignation (optionnel)">
        </div>
        <div>
            <input id="scanned_pdf" type="file" name="scanned_pdf" accept="application/pdf" class="hidden" required>
            <button type="button" id="import_scan_pdf_btn" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <i class="fas fa-file-pdf"></i>
                <span>Importer</span>
            </button>
        </div>
    </form>

    <a href="{{ route('formations.export-pdf', ['q' => $search ?? '']) }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
        <i class="fas fa-file-export"></i>
        <span>Exporter</span>
    </a>
</div>

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('formations.index') }}" class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="w-full md:flex-1">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Ex: scanner, maintenance, irm..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('formations.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-md border border-gray-100">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-800"><i class="fas fa-folder-open text-indigo-600 mr-2"></i>Bibliothèque scans PDF</h3>
        <span class="text-xs text-gray-500">{{ ($documents ?? collect())->count() }} PDF</span>
    </div>

    @if (($documents ?? collect())->isEmpty())
        <p class="px-4 py-5 text-sm text-gray-500">Aucun scan PDF importé.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="text-left px-3 py-2 border-b">Désignation</th>
                        <th class="text-left px-3 py-2 border-b">Voir PDF scanné</th>
                        <th class="text-left px-3 py-2 border-b">Fichier</th>
                        <th class="text-left px-3 py-2 border-b">Date import</th>
                        <th class="text-left px-3 py-2 border-b">Suppression</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($documents ?? collect()) as $document)
                        <tr>
                            <td class="px-3 py-2 border-b">{{ $document['title'] ?? '-' }}</td>
                            <td class="px-3 py-2 border-b">
                                <a href="{{ $document['view_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                    <i class="fas fa-eye mr-2"></i>Voir le scan
                                </a>
                            </td>
                            <td class="px-3 py-2 border-b">{{ $document['file_name'] ?? '-' }}</td>
                            <td class="px-3 py-2 border-b">{{ $document['uploaded_at'] ?? '-' }}</td>
                            <td class="px-3 py-2 border-b">
                                <form method="POST" action="{{ $document['delete_url'] ?? '#' }}" class="scan-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 text-red-700 hover:bg-red-50 scan-delete-btn">
                                        <i class="fas fa-trash-alt mr-2"></i>Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div id="scan_delete_modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40" data-modal-close="1"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-xl bg-white shadow-2xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Confirmer la suppression</h3>
                <p class="mt-1 text-sm text-slate-600">Voulez-vous vraiment supprimer ce scan PDF ?</p>
            </div>
            <div class="px-5 py-4 flex items-center justify-end gap-2">
                <button type="button" id="scan_delete_cancel" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50">Annuler</button>
                <button type="button" id="scan_delete_confirm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Supprimer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const importButton = document.getElementById('import_scan_pdf_btn');
    const fileInput = document.getElementById('scanned_pdf');
    const form = document.getElementById('scan_import_form');

    if (!importButton || !fileInput || !form) {
        return;
    }

    importButton.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            form.submit();
        }
    });

    const deleteModal = document.getElementById('scan_delete_modal');
    const deleteCancel = document.getElementById('scan_delete_cancel');
    const deleteConfirm = document.getElementById('scan_delete_confirm');
    const deleteButtons = document.querySelectorAll('.scan-delete-btn');
    let pendingDeleteForm = null;

    function closeDeleteModal() {
        deleteModal?.classList.add('hidden');
        pendingDeleteForm = null;
    }

    function openDeleteModal(targetForm) {
        pendingDeleteForm = targetForm;
        deleteModal?.classList.remove('hidden');
    }

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const targetForm = button.closest('form.scan-delete-form');
            if (!targetForm) {
                return;
            }

            openDeleteModal(targetForm);
        });
    });

    deleteModal?.addEventListener('click', function (event) {
        const closeTarget = event.target.closest('[data-modal-close="1"]');
        if (closeTarget) {
            closeDeleteModal();
        }
    });

    deleteCancel?.addEventListener('click', function () {
        closeDeleteModal();
    });

    deleteConfirm?.addEventListener('click', function () {
        if (pendingDeleteForm) {
            pendingDeleteForm.submit();
        }
        closeDeleteModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });
});
</script>
@endsection
