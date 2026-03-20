@extends('layouts.dashboard')

@section('page-title', 'Tableau de Bord')

@section('content')
@php
    $isMajor = auth()->user()?->role === 'major';
@endphp

<!-- KPI Cards -->
<div class="gst-stagger">
    @include('components.kpi-cards', ['kpi' => $kpi ?? []])
</div>

@if($isMajor)
<div class="mb-4">
    <a href="{{ route('operator.defects.create') }}"
    class="gst-hover-lift group block bg-white rounded-2xl shadow-md p-6 border-l-4 border-blue-500 animate-fade-in major-complaint-cta"
       style="animation-delay: 0.5s">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-200 text-blue-600 flex items-center justify-center group-hover:from-blue-200 group-hover:to-indigo-300 transition-all duration-300 shadow-sm">
                    <i class="fas fa-triangle-exclamation text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Nouvelle Réclamation</h3>
                    <p class="text-sm text-gray-600">Déclarer une panne pour vos services assignés</p>
                </div>
            </div>
            <div class="text-blue-600 group-hover:translate-x-2 transition-transform duration-300">
                <i class="fas fa-arrow-right text-xl"></i>
            </div>
        </div>
    </a>
</div>
@endif

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
