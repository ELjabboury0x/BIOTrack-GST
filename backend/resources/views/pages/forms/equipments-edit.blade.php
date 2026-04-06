@extends('layouts.dashboard')

@section('page-title', 'Modifier un équipement')

@section('content')
@php
    $unitNameValue = old('unit_name', $equipment->unit_name ?: $equipment->service_name);
    $sectorDescriptionValue = old('sector_description', $equipment->sector_description ?: $equipment->exact_location);
    $marketLabelValue = old('market_label', $equipment->market_label ?: ($equipment->market?->market_number ?: $equipment->market?->reference));
@endphp
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-4 sm:p-6 md:p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">Modification Équipement</h2>
        <a href="{{ route('equipements') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Retour</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('equipements.update', $equipment->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro inventaire (unique) <span class="text-red-500">*</span></label>
            <input name="inventory_number_current" value="{{ old('inventory_number_current', $equipment->inventory_number_current) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de série</label>
            <input name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Désignation <span class="text-red-500">*</span></label>
            <input name="designation" value="{{ old('designation', $equipment->designation) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Marque</label>
            <input name="brand_name" value="{{ old('brand_name', $equipment->brand_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Modèle</label>
            <input name="model_name" value="{{ old('model_name', $equipment->model_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Unité</label>
            <input name="unit_name" value="{{ $unitNameValue }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Secteur</label>
            <input name="sector_name" value="{{ old('sector_name', $equipment->sector_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description secteur</label>
            <input name="sector_description" value="{{ $sectorDescriptionValue }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Marché</label>
            <input name="market_label" value="{{ $marketLabelValue }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Lot</label>
            <input name="lot_number" value="{{ old('lot_number', $equipment->lot_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Article</label>
            <input name="article" value="{{ old('article', $equipment->article) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date de réception provisoire</label>
            <input type="date" name="date_reception_provisoire" value="{{ old('date_reception_provisoire', optional($equipment->date_reception_provisoire)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de garantie</label>
            <input name="duree_garantie" value="{{ old('duree_garantie', $equipment->duree_garantie) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date de réception définitive</label>
            <input type="date" name="date_reception_definitive" value="{{ old('date_reception_definitive', optional($equipment->date_reception_definitive)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm text-blue-800 font-semibold mb-3">Fichiers partagés par désignation</p>
            <p class="text-xs text-blue-700 mb-4">Les fichiers uploadés ici s'appliquent à tous les équipements avec la même désignation.</p>

            @if (!empty($designationAssetImageUrl) || !empty($designationUserManualUrl) || !empty($designationTechnicalManualUrl))
                <div class="mb-4 rounded-lg border border-blue-200 bg-white p-3">
                    <p class="text-xs font-semibold text-gray-700 mb-2">Fichiers actuels pour cette désignation</p>

                    @if (!empty($designationAssetImageUrl))
                        <div class="mb-3">
                            <img src="{{ $designationAssetImageUrl }}" alt="Image actuelle" class="max-h-56 rounded-lg border border-gray-200">
                            @if (!empty($designationAssetImageDeleteUrl))
                                <button type="button" data-delete-url="{{ $designationAssetImageDeleteUrl }}" data-delete-label="l'image" class="mt-2 inline-flex px-3 py-2 bg-red-50 border border-red-300 rounded-lg text-sm text-red-700 hover:bg-red-100 designation-file-delete-btn">
                                    Supprimer l'image
                                </button>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @if (!empty($designationUserManualUrl))
                            <a href="{{ $designationUserManualUrl }}" target="_blank" rel="noopener" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-200">
                                Manuel d'utilisation actuel
                            </a>
                            @if (!empty($designationUserManualDeleteUrl))
                                <button type="button" data-delete-url="{{ $designationUserManualDeleteUrl }}" data-delete-label="le manuel d'utilisation" class="px-3 py-2 bg-red-50 border border-red-300 rounded-lg text-sm text-red-700 hover:bg-red-100 designation-file-delete-btn">
                                    Supprimer manuel d'utilisation
                                </button>
                            @endif
                        @endif

                        @if (!empty($designationTechnicalManualUrl))
                            <a href="{{ $designationTechnicalManualUrl }}" target="_blank" rel="noopener" class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-200">
                                Manuel technique actuel
                            </a>
                            @if (!empty($designationTechnicalManualDeleteUrl))
                                <button type="button" data-delete-url="{{ $designationTechnicalManualDeleteUrl }}" data-delete-label="le manuel technique" class="px-3 py-2 bg-red-50 border border-red-300 rounded-lg text-sm text-red-700 hover:bg-red-100 designation-file-delete-btn">
                                    Supprimer manuel technique
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Image équipement</label>
                    <input type="file" name="designation_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Manuel d'utilisation</label>
                    <input type="file" name="user_manual_file" accept=".pdf" class="w-full text-sm text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Manuel technique</label>
                    <input type="file" name="technical_manual_file" accept=".pdf" class="w-full text-sm text-gray-700">
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex flex-col sm:flex-row justify-end gap-3">
            <a href="{{ route('equipements') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 text-center">Annuler</a>
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Mettre à jour</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.designation-file-delete-btn');
    if (!buttons.length) {
        return;
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', async function () {
            const deleteUrl = button.getAttribute('data-delete-url') || '';
            const label = button.getAttribute('data-delete-label') || 'ce fichier';

            if (!deleteUrl) {
                return;
            }

            const confirmed = window.confirm(`Supprimer ${label} pour toute cette désignation ?`);
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || payload.ok !== true) {
                    throw new Error(payload.message || 'Suppression impossible.');
                }

                window.location.reload();
            } catch (error) {
                window.alert(error?.message || 'Erreur lors de la suppression du fichier.');
            }
        });
    });
});
</script>
@endsection
