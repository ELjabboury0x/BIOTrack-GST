@extends('layouts.dashboard')

@section('page-title', 'Gestion des Zones')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Structure Hôpital / Zones',
    'addRoute' => 'zones.create',
    'addLabel' => 'Ajouter une zone',
    'addIcon' => 'fa-plus'
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

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nb services</th>
                    @if(auth()->user()?->role !== 'major')
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($zones as $zone)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-800 font-semibold">{{ $zone->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $zone->description ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $zone->services_count }}</td>
                        @if(auth()->user()?->role !== 'major')
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('zones.edit', $zone) }}" class="px-3 py-1 text-sm text-blue-600 border border-blue-200 rounded hover:bg-blue-50">Modifier</a>
                                <form method="POST" action="{{ route('zones.destroy', $zone) }}" onsubmit="return confirm('Supprimer cette zone ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 text-sm text-red-600 border border-red-200 rounded hover:bg-red-50">Supprimer</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()?->role === 'major' ? 3 : 4 }}" class="px-6 py-8 text-center text-gray-500">Aucune zone trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $zones->links() }}
    </div>
</div>
@endsection
