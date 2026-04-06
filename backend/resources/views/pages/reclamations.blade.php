@extends('layouts.dashboard')

@section('page-title', 'Historique des Réclamations')

@section('content')
@include('components.module-page-header', [
	'breadcrumb' => 'Assistance / Historique des réclamations'
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

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
	<form method="GET" action="{{ route('reclamations.index') }}" class="flex flex-col md:flex-row md:items-end gap-3">
		<div class="w-full md:w-80">
			<label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par service</label>
			<select name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
				<option value="">Tous les services</option>
				@foreach ($services as $service)
					<option value="{{ $service->id }}" {{ (int) $selectedServiceId === (int) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
				@endforeach
			</select>
		</div>

		<div class="w-full md:w-64">
			<label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
			<select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
				<option value="">Tous</option>
				<option value="open" {{ $selectedStatus === 'open' ? 'selected' : '' }}>Ouverte</option>
				<option value="in_progress" {{ $selectedStatus === 'in_progress' ? 'selected' : '' }}>En cours</option>
				<option value="resolved" {{ $selectedStatus === 'resolved' ? 'selected' : '' }}>Résolue</option>
			</select>
		</div>

		<div class="flex gap-2">
			<button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
			<a href="{{ route('reclamations.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
		</div>
	</form>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
	<div class="overflow-x-auto">
		<table class="w-full">
			<thead class="bg-gray-50 border-b border-gray-200">
				<tr>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Service</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Équipement</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Déclarant</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Salle</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Priorité</th>
					<th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
					<th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Pièces jointes</th>
					<th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Détail</th>
					<th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Statut</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($complaints as $complaint)
					@php
						$attachments = collect($complaint->attachment_path ?? [])
							->filter(fn ($path) => is_string($path) && trim($path) !== '')
							->values();
						$previewAttachments = $attachments->take(2);
						$remainingAttachments = max(0, $attachments->count() - $previewAttachments->count());
					@endphp
					<tr class="border-b border-gray-200 hover:bg-gray-50">
						<td class="px-4 py-3 text-sm text-gray-700">{{ optional($complaint->created_at)->format('d/m/Y H:i') }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ $complaint->service?->name ?: '-' }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ $complaint->equipment?->inventory_number_current }} - {{ $complaint->equipment?->designation }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ $complaint->reported_by_name }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ $complaint->room_number ?: '-' }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ $complaint->priority_label }}</td>
						<td class="px-4 py-3 text-sm text-gray-700">{{ \Illuminate\Support\Str::limit($complaint->description, 90) }}</td>
						<td class="px-4 py-3 text-center text-sm text-gray-700">
							@if ($attachments->isEmpty())
								<span class="text-gray-400">0</span>
							@else
								<div class="flex items-center justify-center gap-1.5">
									@foreach ($previewAttachments as $previewIndex => $path)
										@php
											$imageUrl = route('dashboard.notifications.complaints.attachment', ['complaint' => $complaint, 'index' => $previewIndex]);
										@endphp
										<a href="{{ $imageUrl }}" target="_blank" rel="noopener" class="block w-8 h-8 rounded-md overflow-hidden border border-gray-200 bg-gray-100">
											<img src="{{ $imageUrl }}" alt="Pièce jointe" class="w-full h-full object-cover" loading="lazy">
										</a>
									@endforeach
									@if ($remainingAttachments > 0)
										<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">+{{ $remainingAttachments }}</span>
									@endif
								</div>
							@endif
						</td>
						<td class="px-4 py-3 text-center">
							<a href="{{ route('dashboard.notifications.complaints.show', $complaint) }}" class="inline-flex items-center px-3 py-1 text-xs font-semibold border border-blue-200 text-blue-700 bg-blue-50 rounded hover:bg-blue-100">
								Ouvrir
							</a>
						</td>
						<td class="px-4 py-3 text-center">
							<form method="POST" action="{{ route('reclamations.status.update', $complaint) }}" class="inline-flex gap-2 items-center">
								@csrf
								@method('PATCH')
								<select name="status" class="px-2 py-1 border border-gray-300 rounded text-sm">
									<option value="open" {{ $complaint->status === 'open' ? 'selected' : '' }}>Ouverte</option>
									<option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>En cours</option>
									<option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Résolue</option>
								</select>
								<button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded">OK</button>
							</form>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="10" class="px-6 py-8 text-center text-gray-500">Aucune réclamation trouvée.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>

	<div class="px-6 py-4 border-t border-gray-200">
		{{ $complaints->links() }}
	</div>
</div>
@endsection
