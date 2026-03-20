@extends('layouts.dashboard')

@section('page-title', 'Tickets SAV sociétés externes')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / SAV externe / Tickets',
])

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('external-interventions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Toutes</option>
                @foreach(($companies ?? collect()) as $company)
                    <option value="{{ $company->id }}" {{ (int) ($filters['company_id'] ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tous</option>
                @foreach(($statuses ?? []) as $status)
                    <option value="{{ $status }}" {{ (string) ($filters['status'] ?? '') === (string) $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ticket, équipement, technicien...">
        </div>

        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('external-interventions.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Reset</a>
        </div>
    </form>
</div>

<div class="mb-6 bg-white rounded-xl shadow-md p-4">
    <h3 class="text-sm font-semibold text-gray-800 mb-3">Nouveau ticket SAV</h3>
    <form method="POST" action="{{ route('external-interventions.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Équipement</label>
            <select name="equipment_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="">Sélectionner</option>
                @foreach(($equipments ?? collect()) as $equipment)
                    <option value="{{ $equipment->id }}">{{ ($equipment->inventory_number_current ?: '-') . ' - ' . ($equipment->designation ?: '-') }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Société</label>
            <select name="company_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="">Sélectionner</option>
                @foreach(($companies ?? collect()) as $company)
                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Service</label>
            <input type="text" name="service_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ex: Réanimation">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Date panne</label>
            <input type="datetime-local" name="failure_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Premier appel</label>
            <input type="datetime-local" name="first_call_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Arrivée technicien</label>
            <input type="datetime-local" name="arrival_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Technicien société</label>
            <input type="text" name="technician_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Statut</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                <option value="ouvert">Ouvert</option>
                <option value="en_cours">En cours</option>
                <option value="resolu">Résolu</option>
                <option value="ferme">Fermé</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Résolution</label>
            <input type="datetime-local" name="resolution_datetime" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-700 mb-1">Description intervention</label>
            <textarea name="intervention_description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
        </div>

        <div class="md:col-span-3">
            <label class="block text-xs font-semibold text-gray-700 mb-1">Pièces remplacées</label>
            <textarea name="replaced_parts" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
        </div>

        <div class="md:col-span-3">
            <button class="px-5 py-2 bg-emerald-600 text-white rounded-lg">Créer ticket SAV</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-md p-4">
    <h3 class="text-sm font-semibold text-gray-800 mb-3">Suivi des tickets SAV</h3>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border border-gray-200">
                <tr>
                    <th class="px-3 py-2 text-left">Ticket</th>
                    <th class="px-3 py-2 text-left">Équipement</th>
                    <th class="px-3 py-2 text-left">Service</th>
                    <th class="px-3 py-2 text-left">Société</th>
                    <th class="px-3 py-2 text-left">Panne</th>
                    <th class="px-3 py-2 text-left">1er appel</th>
                    <th class="px-3 py-2 text-left">Résolution</th>
                    <th class="px-3 py-2 text-left">Durée (h)</th>
                    <th class="px-3 py-2 text-left">Statut</th>
                    <th class="px-3 py-2 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($rows ?? []) as $row)
                    <tr class="border-b border-gray-200 align-top">
                        <td class="px-3 py-2">{{ $row->ticket_number ?: ('SAV-' . $row->id) }}</td>
                        <td class="px-3 py-2">{{ $row->equipment?->designation ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row->service_name ?: ($row->equipment?->service?->name ?: '-') }}</td>
                        <td class="px-3 py-2">{{ $row->company?->name ?: '-' }}</td>
                        <td class="px-3 py-2">{{ optional($row->failure_datetime)->format('Y-m-d H:i') ?: '-' }}</td>
                        <td class="px-3 py-2">{{ optional($row->first_call_datetime)->format('Y-m-d H:i') ?: '-' }}</td>
                        <td class="px-3 py-2">{{ optional($row->resolution_datetime)->format('Y-m-d H:i') ?: '-' }}</td>
                        <td class="px-3 py-2">{{ $row->intervention_duration_hours !== null ? number_format((float) $row->intervention_duration_hours, 2) : '-' }}</td>
                        <td class="px-3 py-2">{{ $row->status ?: $row->intervention_status ?: '-' }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('external-interventions.update', $row) }}" class="space-y-2 min-w-[260px]">
                                @csrf
                                @method('PUT')
                                <select name="status" class="w-full px-2 py-1 border border-gray-300 rounded">
                                    @foreach(($statuses ?? []) as $status)
                                        <option value="{{ $status }}" {{ (string) ($row->status ?: $row->intervention_status) === (string) $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                    @endforeach
                                </select>
                                <input type="datetime-local" name="first_call_datetime" value="{{ optional($row->first_call_datetime)->format('Y-m-d\\TH:i') }}" class="w-full px-2 py-1 border border-gray-300 rounded">
                                <input type="datetime-local" name="arrival_datetime" value="{{ optional($row->arrival_datetime ?: $row->technician_arrival_datetime)->format('Y-m-d\\TH:i') }}" class="w-full px-2 py-1 border border-gray-300 rounded">
                                <input type="datetime-local" name="resolution_datetime" value="{{ optional($row->resolution_datetime)->format('Y-m-d\\TH:i') }}" class="w-full px-2 py-1 border border-gray-300 rounded">
                                <input type="text" name="technician_name" value="{{ $row->technician_name }}" placeholder="Technicien" class="w-full px-2 py-1 border border-gray-300 rounded">
                                <input type="text" name="resolution_status" value="{{ $row->resolution_status }}" placeholder="Statut résolution" class="w-full px-2 py-1 border border-gray-300 rounded">
                                <button class="w-full px-3 py-1.5 bg-blue-600 text-white rounded">Mettre à jour</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-3 py-4 text-center text-gray-500">Aucun ticket SAV.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
</div>
@endsection
