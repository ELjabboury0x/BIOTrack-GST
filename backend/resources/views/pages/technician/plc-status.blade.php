@extends('layouts.dashboard')

@section('page-title', 'Technicien - État PLC')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 md:p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">État PLC</h2>
    <p class="text-sm text-gray-600 mb-6">Technicien → accès à l’état PLC.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($plcStatus as $plc)
            <div class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-gray-800">{{ $plc['name'] }}</p>
                    <span class="px-2 py-1 rounded text-xs {{ $plc['status'] === 'online' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ strtoupper($plc['status']) }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Dernière activité : {{ $plc['last_seen']->diffForHumans() }}</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
