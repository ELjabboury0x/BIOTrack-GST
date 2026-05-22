@extends('layouts.dashboard')

@section('page-title', 'Rapport d\'intervention interne')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Rapports / Intervention interne',
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ $report->exists ? route('maintenance-reports.update', $report) : route('maintenance-reports.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">
    @csrf
    @if($report->exists)
        @method('PUT')
    @endif

    @php
        $statusLabel = match ((string) $report->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumis',
            'validated' => 'Validé',
            'closed' => 'Clôturé',
            default => (string) ($report->status ?: '-'),
        };
    @endphp

    <div class="bg-white rounded-xl shadow-md p-6 grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">N° rapport</label>
            <input type="text" value="{{ $report->report_number ?? 'Auto à la création' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Statut du rapport</label>
            <input type="text" value="{{ $statusLabel }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Type d'intervention</label>
            <select name="intervention_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                <option value="preventive" {{ old('intervention_type', $report->intervention_type) === 'preventive' ? 'selected' : '' }}>Préventive</option>
                <option value="curative" {{ old('intervention_type', $report->intervention_type) === 'curative' ? 'selected' : '' }}>Corrective</option>
                <option value="diagnostic" {{ old('intervention_type', $report->intervention_type) === 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nature d'intervention</label>
            <select name="intervention_scope" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                <option value="interne" {{ old('intervention_scope', $report->intervention_scope ?? 'interne') === 'interne' ? 'selected' : '' }}>Interne</option>
                <option value="externe" {{ old('intervention_scope', $report->intervention_scope ?? 'interne') === 'externe' ? 'selected' : '' }}>Externe</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date d'intervention</label>
            <input type="date" name="intervention_date" value="{{ old('intervention_date', optional($report->intervention_date)->toDateString()) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">1. Informations générales</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Hôpital</label>
                <input type="text" name="hospital_name" value="{{ old('hospital_name', $report->hospital_name ?: 'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
                <select name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">Sélectionner...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ (int) old('service_id', $report->service_id) === (int) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Code unité/secteur/local</label>
                <input type="text" name="unit_code" value="{{ old('unit_code', $report->unit_code) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">2. Équipement biomédical</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Équipement</label>
                <select name="equipment_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">Sélectionner...</option>
                    @foreach($equipments as $equipment)
                        <option value="{{ $equipment->id }}" {{ (int) old('equipment_id', $report->equipment_id) === (int) $equipment->id ? 'selected' : '' }}>
                            {{ $equipment->inventory_number_current }} - {{ $equipment->designation }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Désignation</label>
                <input type="text" name="equipment_designation" value="{{ old('equipment_designation', $report->equipment_designation) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">N° série</label>
                <input type="text" name="equipment_serial_number" value="{{ old('equipment_serial_number', $report->equipment_serial_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">N° inventaire</label>
                <input type="text" name="equipment_inventory_number" value="{{ old('equipment_inventory_number', $report->equipment_inventory_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fournisseur</label>
                <input type="text" name="supplier_name" value="{{ old('supplier_name', $report->supplier_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Marque</label>
                <input type="text" name="brand_name" value="{{ old('brand_name', $report->brand_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Modèle</label>
                <input type="text" name="model_name" value="{{ old('model_name', $report->model_name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">3. Intervention</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Début</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at', optional($report->started_at)->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fin</label>
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at', optional($report->ended_at)->format('Y-m-d\\TH:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Durée (auto)</label>
                <input type="text" value="{{ $report->duration_minutes ? $report->duration_minutes . ' min' : '-' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" readonly>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description du problème</label>
            <textarea name="problem_description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('problem_description', $report->problem_description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Opérations effectuées</label>
            <textarea name="operations_performed" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('operations_performed', $report->operations_performed) }}</textarea>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">4. Signatures & pièces jointes</h3>
        @php
            $intervenants = collect($technicians ?? [])->filter(function ($user) {
                return in_array((string) ($user->role ?? ''), ['technicien', 'technician', 'ingenieur'], true);
            });

            $majorValidators = collect($engineers ?? [])->filter(function ($user) {
                return (string) ($user->role ?? '') === 'major';
            });
        @endphp
        @php
            $techSigUrl = $report->exists && $report->technician_signature_path
                ? url('/dashboard/rapports/interventions-internes/' . $report->id . '/signature/technician')
                : null;
            $engSigUrl = $report->exists && $report->engineer_signature_path
                ? url('/dashboard/rapports/interventions-internes/' . $report->id . '/signature/engineer')
                : null;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Intervenant</label>
                <select name="user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">Sélectionner...</option>
                    @foreach($intervenants as $technician)
                        <option value="{{ $technician->id }}" {{ (int) old('user_id', $report->user_id) === (int) $technician->id ? 'selected' : '' }}>
                            {{ $technician->name ?: $technician->login }} ({{ $technician->role }})
                        </option>
                    @endforeach
                </select>
                <input id="technician-signature-input" type="file" name="technician_signature" accept="image/*" class="mt-2 block w-full text-sm">
                <img
                    id="technician-signature-preview"
                    src="{{ $techSigUrl ?? '' }}"
                    class="mt-2 h-16 border rounded {{ $techSigUrl ? '' : 'hidden' }}"
                    alt=""
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Major de Service</label>
                <select name="engineer_user_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Sélectionner...</option>
                    @foreach($majorValidators as $engineer)
                        <option value="{{ $engineer->id }}" {{ (int) old('engineer_user_id', $report->engineer_user_id) === (int) $engineer->id ? 'selected' : '' }}>
                            {{ $engineer->name ?: $engineer->login }} ({{ $engineer->role }})
                        </option>
                    @endforeach
                </select>
                <input id="engineer-signature-input" type="file" name="engineer_signature" accept="image/*" class="mt-2 block w-full text-sm">
                <img
                    id="engineer-signature-preview"
                    src="{{ $engSigUrl ?? '' }}"
                    class="mt-2 h-16 border rounded {{ $engSigUrl ? '' : 'hidden' }}"
                    alt=""
                >
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Photos de l'intervention</label>
            <input type="file" name="photos[]" accept="image/*" multiple class="block w-full text-sm">
            @if(is_array($report->photo_paths) && count($report->photo_paths) > 0)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($report->photo_paths as $photo)
                        <img src="{{ asset('storage/' . $photo) }}" class="h-20 border rounded" alt="photo intervention">
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">5. Historique des rapports</h3>

        @if(($reportHistory ?? collect())->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border border-gray-200">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">N° Rapport</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Date</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Type</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Équipement</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Technicien</th>
                            <th class="px-3 py-2 text-left font-semibold text-gray-700">Statut</th>
                            <th class="px-3 py-2 text-right font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportHistory as $historyRow)
                            <tr class="border-b border-gray-200">
                                <td class="px-3 py-2 text-gray-800">{{ $historyRow['numero'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $historyRow['date'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $historyRow['type'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $historyRow['equipement'] }}</td>
                                <td class="px-3 py-2 text-gray-700">{{ $historyRow['technicien'] }}</td>
                                <td class="px-3 py-2 text-gray-700">
                                    @php
                                        $status = (string) ($historyRow['statut'] ?? '');
                                        $statusLabel = match ($status) {
                                            'draft' => 'Brouillon',
                                            'submitted' => 'Soumis',
                                            'validated' => 'Validé',
                                            'closed' => 'Clôturé',
                                            default => $status,
                                        };
                                        $statusClass = match ($status) {
                                            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'submitted' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                            'validated' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'closed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ $historyRow['edit_url'] }}" class="inline-flex items-center px-3 py-1.5 border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50">
                                        Ouvrir
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                Aucun historique disponible pour ce contexte (équipement/service).
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        @if($report->exists)
            <a href="{{ route('maintenance-reports.pdf', $report) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700">Exporter PDF</a>
        @endif
        <a href="{{ route('maintenance-reports.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700">Retour</a>
    </div>
</form>

@if($report->exists)
    <div class="mt-4 flex flex-wrap gap-3">
        @if($report->status !== 'closed')
            <form method="POST" action="{{ route('maintenance-reports.validate', $report) }}">
                @csrf
                @method('PATCH')
                <button class="inline-flex items-center gap-2 px-6 py-2 bg-amber-600 text-white rounded-lg">
                    <i class="fas fa-check"></i>
                    <span>Valider</span>
                </button>
            </form>

            <form method="POST" action="{{ route('maintenance-reports.close', $report) }}">
                @csrf
                @method('PATCH')
                <button class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 text-white rounded-lg">
                    <i class="fas fa-lock"></i>
                    <span>Clôturer</span>
                </button>
            </form>
        @endif
    </div>
@endif
@endsection

@section('scripts')
<script>
    function setupSignaturePreview(inputId, previewId) {
        var input = document.getElementById(inputId);
        var preview = document.getElementById(previewId);

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) {
                preview.src = '';
                preview.classList.add('hidden');
                return;
            }

            var objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.classList.remove('hidden');
            preview.onerror = function () {
                preview.src = '';
                preview.classList.add('hidden');
            };
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupSignaturePreview('technician-signature-input', 'technician-signature-preview');
        setupSignaturePreview('engineer-signature-input', 'engineer-signature-preview');
    });
</script>
@endsection
