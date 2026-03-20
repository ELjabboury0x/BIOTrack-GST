@extends('layouts.dashboard')

@section('page-title', 'Organisation GST')

@section('content')
    @include('components.module-page-header', [
        'breadcrumb' => 'Organisation / GST',
    ])

    @php
        $gstName = (string) ($tree['gst_name'] ?? 'GST Tanger–Tétouan–Al Hoceima');
        $hme = $tree['hospitals']['hme'] ?? [];
        $hsp = $tree['hospitals']['hsp'] ?? [];
        $floors = $hme['floors'] ?? collect();
    @endphp

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">{{ $gstName }}</h1>
        <p class="mt-1 text-sm text-slate-500">Structure institutionnelle officielle du GST</p>

        <div class="mt-5 space-y-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-landmark text-slate-600"></i>
                    <h2 class="font-semibold text-slate-800">Direction Générale</h2>
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-hospital-user text-slate-600"></i>
                    <h2 class="font-semibold text-slate-800">Branche Sanitaire</h2>
                </div>
            </div>

            <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-hospital text-blue-700"></i>
                    <h2 class="font-semibold text-slate-900">{{ $hme['name'] ?? 'Hôpital Mère-Enfants' }}</h2>
                </div>

                <div class="mt-4 ml-4 space-y-3 border-l border-blue-200 pl-4">
                    @foreach($floors as $floor)
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-layer-group text-slate-500"></i>
                                    <h3 class="font-semibold text-slate-800">{{ $floor['name'] }}</h3>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-blue-700">{{ (int) ($floor['totals']['equipments_total'] ?? 0) }} équipements</span>
                                    <span class="rounded-full {{ (int) ($floor['totals']['breakdowns_total'] ?? 0) > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }} px-2 py-1">
                                        {{ (int) ($floor['totals']['breakdowns_total'] ?? 0) }} pannes
                                    </span>
                                </div>
                            </div>

                            <ul class="mt-3 ml-3 space-y-2 border-l border-slate-200 pl-3">
                                @foreach(($floor['services'] ?? []) as $service)
                                    @php
                                        $kpi = $service['kpi'] ?? [];
                                        $severity = $kpi['severity'] ?? 'green';
                                        $severityClass = $severity === 'red'
                                            ? 'bg-red-100 text-red-700'
                                            : ($severity === 'orange' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700');
                                    @endphp
                                    <li class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <i class="fas fa-stethoscope text-slate-500"></i>
                                                    <h4 class="font-medium text-slate-900 truncate">{{ $service['name'] }}</h4>
                                                </div>
                                                <p class="mt-1 text-xs text-slate-500">{{ $service['code'] ?: 'Code non défini' }}</p>
                                            </div>
                                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $severityClass }}">
                                                {{ (int) ($kpi['breakdowns_total'] ?? 0) }} pannes
                                            </span>
                                        </div>

                                        <div class="mt-2 grid grid-cols-2 md:grid-cols-5 gap-2 text-xs">
                                            <div class="rounded bg-white px-2 py-1"><span class="text-slate-500">Équipements:</span> <strong>{{ (int) ($kpi['equipments_total'] ?? 0) }}</strong></div>
                                            <div class="rounded bg-white px-2 py-1"><span class="text-slate-500">Pannes:</span> <strong>{{ (int) ($kpi['breakdowns_total'] ?? 0) }}</strong></div>
                                            <div class="rounded bg-white px-2 py-1"><span class="text-slate-500">MTTR:</span> <strong>{{ $kpi['mttr_hours'] ?? '-' }}</strong></div>
                                            <div class="rounded bg-white px-2 py-1"><span class="text-slate-500">MTBF:</span> <strong>{{ $kpi['mtbf_hours'] ?? '-' }}</strong></div>
                                            <div class="rounded bg-white px-2 py-1"><span class="text-slate-500">Disponibilité:</span> <strong>{{ isset($kpi['availability']) ? $kpi['availability'].'%' : '-' }}</strong></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-hospital-alt text-slate-600"></i>
                    <h2 class="font-semibold text-slate-800">{{ $hsp['name'] ?? 'Hôpital des Spécialités' }}</h2>
                </div>
            </div>
        </div>
    </section>
@endsection
