@extends('layouts.dashboard')

@section('page-title', 'Gestion des Services')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Structure Hôpital / Services',
    'addRoute' => 'services.create',
    'addLabel' => 'Ajouter un service',
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

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('services.index') }}" class="flex flex-col md:flex-row md:items-end gap-3">
        <div class="w-full md:w-96">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par service</label>
            <input type="text" name="service" value="{{ $serviceFilter ?? '' }}" placeholder="Code ou nom du service" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('services.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nom Service</th>
                    @if(auth()->user()?->role !== 'major')
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $service->code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $service->name }}</td>
                        @if(auth()->user()?->role !== 'major')
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('services.edit', $service) }}" class="px-3 py-1 text-sm text-blue-600 border border-blue-200 rounded hover:bg-blue-50">Modifier</a>
                                <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?')">
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
                        <td colspan="{{ auth()->user()?->role === 'major' ? 2 : 3 }}" class="px-6 py-8 text-center text-gray-500">Aucun service trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        {{ $services->links() }}
    </div>
</div>
@endsection
