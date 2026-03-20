@extends('layouts.dashboard')

@section('page-title', 'Notification Réclamation')

@section('content')
@php
    $attachments = collect($complaint->attachment_path ?? [])
        ->filter(fn ($path) => is_string($path) && trim($path) !== '')
        ->values();
    $canCreateExternalTicket = (int) ($complaint->equipment?->company_id ?? 0) > 0;
@endphp
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Notification SAP-PM</h2>
            <p class="text-sm text-gray-500 mt-1">Détail et clôture de la réclamation</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700">
            {{ session('warning') }}
        </div>
    @endif

        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Service</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $complaint->service?->code }} - {{ $complaint->service?->name }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Équipement</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $complaint->equipment?->inventory_number_current }} - {{ $complaint->equipment?->designation }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Déclarant</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $complaint->reported_by_name ?: '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Salle</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $complaint->room_number ?: '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Priorité</p>
            <p class="font-semibold text-gray-800 mt-1 uppercase">{{ $complaint->priority }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Statut</p>
            <p class="font-semibold text-gray-800 mt-1 uppercase">{{ $complaint->status }}</p>
        </div>
    </div>

    <div class="mt-6 p-4 rounded-lg bg-gray-50 border border-gray-200">
        <p class="text-gray-500 text-sm">Description</p>
        <p class="text-gray-800 mt-2 whitespace-pre-line">{{ $complaint->description }}</p>
    </div>

    <div class="mt-6 p-4 rounded-lg bg-gray-50 border border-gray-200">
        <p class="text-gray-500 text-sm">Pièces jointes</p>

        @if ($attachments->isEmpty())
            <p class="text-gray-700 mt-2">Aucune pièce jointe.</p>
        @else
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach ($attachments as $index => $attachment)
                    @php
                        $url = route('dashboard.notifications.complaints.attachment', ['complaint' => $complaint, 'index' => $index]);
                    @endphp
                    <a href="{{ $url }}" target="_blank" rel="noopener" class="group inline-block rounded-lg border border-gray-200 bg-white overflow-hidden hover:shadow-md transition-shadow">
                        <img src="{{ $url }}" alt="Pièce jointe réclamation" class="block w-auto h-auto max-w-full sm:max-w-[32rem] max-h-[28rem] object-contain bg-gray-100" loading="lazy">
                        <div class="px-3 py-2 text-xs text-blue-600 font-semibold group-hover:underline">Ouvrir l'image</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8 flex items-center gap-3">
        @if ($complaint->status !== 'resolved')
            @if(auth()->user()?->role !== 'major')
            <form method="POST" action="{{ route('dashboard.notifications.complaints.close', $complaint) }}">
                @csrf
                @method('PATCH')
                <div class="mb-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-blue-900 {{ $canCreateExternalTicket ? '' : 'opacity-70 cursor-not-allowed' }}">
                        <input type="checkbox" name="external_call_made" value="1" {{ old('external_call_made') ? 'checked' : '' }} {{ $canCreateExternalTicket ? '' : 'disabled' }} class="rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                        Appel effectué à la société externe (créer automatiquement un ticket SAV)
                    </label>
                    @if ($canCreateExternalTicket)
                        <p class="mt-1 text-xs text-blue-700">Laisser décoché pour traitement interne normal.</p>
                    @else
                        <p class="mt-1 text-xs text-amber-700">Cette option est indisponible: l'équipement n'est lié à aucune société externe.</p>
                    @endif
                </div>
                <button type="submit" class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                    Clôturer la notification et créer OT/DM
                </button>
            </form>
            @else
            <span class="inline-flex items-center px-3 py-2 rounded-lg bg-yellow-100 text-yellow-700 font-semibold text-sm">
                Vous avez un accès en consultation uniquement.
            </span>
            @endif
        @else
            <span class="inline-flex items-center px-3 py-2 rounded-lg bg-green-100 text-green-700 font-semibold text-sm">
                Cette notification est déjà clôturée.
            </span>
        @endif
    </div>
</div>
@endsection
