@extends('layouts.dashboard')

@section('page-title', 'Tableau de Bord')

@section('content')
@php
    $isMajor = auth()->user()?->role === 'major';
    $criticalAlerts = $criticalAlerts ?? [];
    $problematicServices = $problematicServices ?? ['all' => [], 'top5' => [], 'max' => 0];
    $criticalAlertStrip = collect($criticalAlerts)
        ->filter(function ($alert) {
            $title = strtolower((string) ($alert['title'] ?? ''));

            return str_contains($title, 'mttr')
                || str_contains($title, 'preventives en retard')
                || str_contains($title, 'critiques en panne');
        })
        ->values()
        ->all();
@endphp

@if(!empty($criticalAlertStrip))
<div id="critical-alerts-section" class="mb-5 rounded-2xl border border-slate-200 bg-white px-3 py-3 shadow-sm">
    <div class="flex items-center gap-2 mb-2 px-1">
        <span class="inline-flex items-center rounded-full bg-red-50 text-red-700 border border-red-200 text-[11px] font-semibold px-2.5 py-1">Alertes critiques</span>
        <span class="text-xs text-slate-500">Suivi rapide</span>
    </div>

    <div class="flex flex-wrap items-stretch gap-2">
    @foreach($criticalAlertStrip as $index => $alert)
        @php
            $isRed = ($alert['tone'] ?? '') === 'red';
            $cardClasses = $isRed
                ? 'border-red-200 bg-red-50/70 hover:bg-red-50'
                : 'border-orange-200 bg-orange-50/70 hover:bg-orange-50';
            $iconClasses = $isRed
                ? 'text-red-600 bg-red-100'
                : 'text-orange-600 bg-orange-100';
            $countClasses = $isRed ? 'text-red-700' : 'text-orange-700';
        @endphp

        <a href="{{ $alert['url'] }}"
           class="group flex-1 min-w-[220px] rounded-xl border px-3 py-2.5 transition-all duration-200 hover:-translate-y-0.5 {{ $cardClasses }} animate-fade-in"
           style="animation-delay: {{ 0.08 + ($index * 0.08) }}s">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $alert['title'] }}</h3>
                    <p class="text-xs text-slate-500">Occurrences</p>
                </div>
                <div class="shrink-0 w-9 h-9 rounded-lg flex items-center justify-center {{ $iconClasses }}">
                    <i class="{{ $alert['icon'] }} text-base"></i>
                </div>
            </div>

            <div class="mt-1.5 flex items-end justify-between">
                <span class="text-2xl leading-none font-black tabular-nums {{ $countClasses }}">{{ (int) ($alert['count'] ?? 0) }}</span>
                <span class="text-xs font-semibold text-slate-600 group-hover:translate-x-0.5 transition-transform duration-200">
                    Voir <i class="fas fa-arrow-right ml-0.5"></i>
                </span>
            </div>

            @if(!empty($alert['description']))
                <div class="mt-1.5 text-[11px] text-slate-500 truncate">
                    {{ $alert['description'] }}
                </div>
            @endif
        </a>
    @endforeach
    </div>
</div>
@endif

<!-- KPI Cards -->
<div class="gst-stagger">
    @include('components.kpi-cards', ['kpi' => $kpi ?? []])
</div>

<div class="mb-8">
    <a href="{{ route('maintenance-preventive') }}"
       class="gst-hover-lift group block bg-white rounded-2xl shadow-md p-6 border-l-4 border-emerald-500 animate-fade-in"
       style="animation-delay: 0.55s">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-600 flex items-center justify-center group-hover:from-emerald-200 group-hover:to-emerald-300 transition-all duration-300 shadow-sm">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Maintenance Préventive</h3>
                    <p class="text-sm text-gray-600">Ajouter ou modifier les interventions préventives</p>
                </div>
            </div>
            <div class="text-emerald-600 group-hover:translate-x-2 transition-transform duration-300">
                <i class="fas fa-arrow-right text-xl"></i>
            </div>
        </div>
    </a>
</div>

<!-- Charts -->
@include('components.charts', [
    'charts' => $charts ?? [],
    'downtimeFilterDays' => $downtimeFilterDays ?? 30,
])

<section class="mt-8 bg-white rounded-2xl shadow-md p-6 border border-slate-100 animate-fade-in" style="animation-delay: 0.75s">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-5">
        <div>
            <h3 class="text-xl font-extrabold text-slate-900">Services hospitaliers les plus problématiques</h3>
            <p class="text-sm text-slate-500">Nombre de pannes par service et top 5 les plus impactés</p>
        </div>
        <span class="inline-flex items-center rounded-full bg-red-50 text-red-700 border border-red-200 text-xs font-semibold px-3 py-1 w-fit">
            Données en temps réel
        </span>
    </div>

    @if(empty($problematicServices['all']))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Aucune panne active détectée par service.
        </div>
    @else
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-4">
                <h4 class="text-sm font-bold uppercase tracking-wide text-red-700 mb-3">Top 5 services les plus impactés</h4>
                <div class="space-y-2">
                    @foreach($problematicServices['top5'] as $i => $row)
                        <div class="flex items-center justify-between rounded-lg bg-white border border-red-100 px-3 py-2">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-7 h-7 rounded-full bg-red-100 text-red-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                <span class="text-sm font-semibold text-slate-800 truncate">{{ $row['name'] }}</span>
                            </div>
                            <span class="text-sm font-extrabold text-red-700 tabular-nums">{{ (int) $row['breakdowns_count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-orange-100 bg-gradient-to-br from-orange-50 to-white p-4">
                <h4 class="text-sm font-bold uppercase tracking-wide text-orange-700 mb-3">Pannes par service</h4>
                <div class="space-y-3 max-h-80 overflow-auto pr-1">
                    @foreach($problematicServices['all'] as $row)
                        @php
                            $max = max(1, (int) ($problematicServices['max'] ?? 1));
                            $width = min(100, (int) round(((int) $row['breakdowns_count'] / $max) * 100));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-700 truncate pr-2">{{ $row['name'] }}</span>
                                <span class="font-bold text-orange-700 tabular-nums">{{ (int) $row['breakdowns_count'] }}</span>
                            </div>
                            <div class="h-2 rounded-full bg-orange-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-orange-400 to-red-500 transition-all duration-700" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</section>

@if (!($hasData ?? false))
<div class="mt-8 bg-white rounded-xl shadow-md p-8 text-center text-gray-500 animate-fade-in" style="animation-delay: 0.9s">
    <i class="fas fa-database text-3xl mb-3"></i>
    <p>Aucune donnée réelle disponible pour le tableau de bord.</p>
    <p class="text-sm mt-1">Importez vos équipements et opérations pour alimenter les KPI et activités.</p>
</div>
@endif

<div id="dashboard-live-config"
    data-live-url="{{ route('dashboard.live-metrics') }}"
    data-downtime-days="{{ (int) ($downtimeFilterDays ?? 30) }}"
    data-selected-designation="{{ (string) ($selectedDesignation ?? '') }}"
    data-ws-url="{{ (request()->isSecure() ? 'wss' : 'ws') . '://' . request()->getHost() . ':' . (int) env('REALTIME_PORT', 6001) . '/ws' }}"></div>

@if(!empty($criticalAlerts))
<a href="#critical-alerts-section"
   class="critical-alert-fab"
   title="Aller aux alertes critiques"
   aria-label="Aller aux alertes critiques">
    <i class="fas fa-stopwatch"></i>
</a>
@endif

@endsection

@section('styles')
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes ctaBlink {
    0%, 100% {
        box-shadow: 0 10px 24px -16px rgba(37, 99, 235, 0.45);
        border-left-color: #3b82f6;
    }
    50% {
        box-shadow: 0 16px 30px -14px rgba(37, 99, 235, 0.75);
        border-left-color: #1d4ed8;
    }
}

.animate-fade-in {
    animation: fadeIn 0.6s ease-out forwards;
}

.kpi-card {
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.major-complaint-cta {
    animation: fadeIn 0.6s ease-out forwards, ctaBlink 1.35s ease-in-out infinite;
}

.critical-alert-fab {
    position: fixed;
    right: 1.25rem;
    bottom: 1.25rem;
    width: 3.4rem;
    height: 3.4rem;
    border-radius: 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f9e5cd, #f3dbbf);
    color: #ea580c;
    box-shadow: 0 10px 30px -10px rgba(234, 88, 12, 0.55);
    border: 1px solid rgba(251, 146, 60, 0.45);
    z-index: 60;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.critical-alert-fab i {
    font-size: 1.45rem;
}

.critical-alert-fab:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 16px 36px -12px rgba(234, 88, 12, 0.65);
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = document.getElementById('dashboard-live-config');
    if (!config) {
        return;
    }

    const endpoint = config.getAttribute('data-live-url');
    const wsUrl = config.getAttribute('data-ws-url');
    const downtimeFilterSelect = document.getElementById('downtimeDaysFilter');
    const designationFilterSelect = document.getElementById('designationFilter');
    const companyMonthFilterSelect = document.getElementById('companyPeriodMonthFilter');
    const companyYearFilterSelect = document.getElementById('companyPeriodYearFilter');
    const companyServiceFilterSelect = document.getElementById('companyServiceFilter');
    let selectedDesignation = (config.getAttribute('data-selected-designation') || '').trim();
    let periodMonth = parseInt(new URL(window.location.href).searchParams.get('period_month') || '0', 10) || 0;
    let periodYear = parseInt(new URL(window.location.href).searchParams.get('period_year') || '0', 10) || 0;
    let serviceId = parseInt(new URL(window.location.href).searchParams.get('service_id') || '0', 10) || 0;
    let downtimeDays = parseInt(config.getAttribute('data-downtime-days') || '30', 10);
    if (![7, 30, 90, 180, 365].includes(downtimeDays)) {
        downtimeDays = 30;
    }
    if (!endpoint) {
        return;
    }

    let loading = false;
    let socket = null;
    let socketConnected = false;
    let reconnectDelay = 3000;

    const refreshLiveMetrics = async () => {
        if (loading) {
            return;
        }

        loading = true;

        try {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('downtime_days', String(downtimeDays));
            if (selectedDesignation) {
                url.searchParams.set('designation', selectedDesignation);
            }
            if (periodMonth > 0) {
                url.searchParams.set('period_month', String(periodMonth));
            }
            if (periodYear > 0) {
                url.searchParams.set('period_year', String(periodYear));
            }
            if (serviceId > 0) {
                url.searchParams.set('service_id', String(serviceId));
            }

            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            if (typeof window.updateDashboardKpi === 'function') {
                window.updateDashboardKpi(payload.kpi || {});
            }

            if (typeof window.updateDashboardCharts === 'function') {
                window.updateDashboardCharts(payload.charts || {});
            }
        } catch (error) {
            console.error('Dashboard live metrics error:', error);
        } finally {
            loading = false;
        }
    };

    const startWebSocket = () => {
        if (!wsUrl) {
            return;
        }

        try {
            socket = new WebSocket(wsUrl);

            socket.onopen = function () {
                socketConnected = true;
            };

            socket.onmessage = function (event) {
                try {
                    const message = JSON.parse(event.data || '{}');
                    if (message.channel !== 'dashboard.metrics') {
                        return;
                    }

                    refreshLiveMetrics();
                } catch (error) {
                    console.error('Realtime message error:', error);
                }
            };

            socket.onclose = function () {
                socketConnected = false;
                setTimeout(startWebSocket, reconnectDelay);
            };

            socket.onerror = function () {
                socketConnected = false;
            };
        } catch (error) {
            socketConnected = false;
        }
    };

    startWebSocket();
    refreshLiveMetrics();

    if (downtimeFilterSelect) {
        downtimeFilterSelect.value = String(downtimeDays);
        downtimeFilterSelect.addEventListener('change', function () {
            const nextValue = parseInt(this.value || '30', 10);
            downtimeDays = [7, 30, 90, 180, 365].includes(nextValue) ? nextValue : 30;

            const pageUrl = new URL(window.location.href);
            pageUrl.searchParams.set('downtime_days', String(downtimeDays));
            if (selectedDesignation) {
                pageUrl.searchParams.set('designation', selectedDesignation);
            } else {
                pageUrl.searchParams.delete('designation');
            }
            if (periodMonth > 0) {
                pageUrl.searchParams.set('period_month', String(periodMonth));
            }
            if (periodYear > 0) {
                pageUrl.searchParams.set('period_year', String(periodYear));
            }
            if (serviceId > 0) {
                pageUrl.searchParams.set('service_id', String(serviceId));
            }
            window.history.replaceState({}, '', pageUrl.toString());

            refreshLiveMetrics();
        });
    }

    if (designationFilterSelect) {
        designationFilterSelect.value = selectedDesignation;
        designationFilterSelect.addEventListener('change', function () {
            selectedDesignation = (this.value || '').trim();

            const pageUrl = new URL(window.location.href);
            pageUrl.searchParams.set('downtime_days', String(downtimeDays));
            if (selectedDesignation) {
                pageUrl.searchParams.set('designation', selectedDesignation);
            } else {
                pageUrl.searchParams.delete('designation');
            }
            if (periodMonth > 0) {
                pageUrl.searchParams.set('period_month', String(periodMonth));
            }
            if (periodYear > 0) {
                pageUrl.searchParams.set('period_year', String(periodYear));
            }
            if (serviceId > 0) {
                pageUrl.searchParams.set('service_id', String(serviceId));
            }
            window.history.replaceState({}, '', pageUrl.toString());

            refreshLiveMetrics();
        });
    }

    if (companyMonthFilterSelect) {
        if (periodMonth > 0) {
            companyMonthFilterSelect.value = String(periodMonth);
        }
        companyMonthFilterSelect.addEventListener('change', function () {
            const next = parseInt(this.value || '0', 10);
            periodMonth = next > 0 ? next : 0;
            refreshLiveMetrics();
        });
    }

    if (companyYearFilterSelect) {
        if (periodYear > 0) {
            companyYearFilterSelect.value = String(periodYear);
        }
        companyYearFilterSelect.addEventListener('change', function () {
            const next = parseInt(this.value || '0', 10);
            periodYear = next > 0 ? next : 0;
            refreshLiveMetrics();
        });
    }

    if (companyServiceFilterSelect) {
        if (serviceId > 0) {
            companyServiceFilterSelect.value = String(serviceId);
        }
        companyServiceFilterSelect.addEventListener('change', function () {
            const next = parseInt(this.value || '0', 10);
            serviceId = next > 0 ? next : 0;
            refreshLiveMetrics();
        });
    }

    setInterval(function () {
        if (!socketConnected) {
            refreshLiveMetrics();
        }
    }, 5000);
});
</script>
@endsection
