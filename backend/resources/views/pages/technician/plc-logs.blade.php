@extends('layouts.dashboard')

@section('page-title', 'Technicien - Journaux PLC')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 md:p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Journaux PLC</h2>
    <p class="text-sm text-gray-600 mb-6">Technicien → accès aux journaux.</p>

    <div class="bg-gray-900 text-green-200 rounded-xl p-4 text-xs overflow-auto max-h-[70vh] leading-6 font-mono">
        @forelse($lines as $line)
            <div>{{ $line }}</div>
        @empty
            <div>Aucun journal disponible.</div>
        @endforelse
    </div>
</div>
@endsection
