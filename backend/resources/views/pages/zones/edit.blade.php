@extends('layouts.dashboard')

@section('page-title', 'Modifier Zone')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Structure Hôpital / Zones / Modification'
])

<div class="bg-white rounded-xl shadow-md p-6 max-w-3xl">
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('zones.update', $zone) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la zone</label>
            <input type="text" name="name" value="{{ old('name', $zone->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('description', $zone->description) }}</textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Mettre à jour</button>
            <a href="{{ route('zones.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
        </div>
    </form>
</div>
@endsection
