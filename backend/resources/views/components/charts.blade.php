<!-- Charts Section -->
@php
    $chartValues = array_merge([
        'interventions' => ['labels' => [], 'preventive' => [], 'curative' => []],
        'maintenance_types' => ['labels' => [], 'data' => []],
        'equipments_added' => ['labels' => [], 'data' => []],
        'downtime' => ['labels' => [], 'avg_hours' => []],
        'reliability' => ['labels' => [], 'mttr' => [], 'mtbf' => [], 'mtbf_preventif' => [], 'mtbf_curatif' => []],
        'reliability_by_designation' => [
            'labels' => [],
            'mttr' => [],
            'mtbf' => [],
            'disponibilite' => [],
            'designations' => [],
            'selected_designation' => '',
        ],
        'external_companies' => [
            'labels' => [],
            'score' => [],
            'respect_planning' => [],
            'avg_delay_days' => [],
            'mttr' => [],
            'reintervention_rate' => [],
            'availability' => [],
            'interventions_total' => [],
            'top5' => [],
            'top_fastest' => [],
            'top_failures' => [],
            'filters' => [
                'months' => [],
                'years' => [],
                'services' => [],
                'selected_month' => null,
                'selected_year' => null,
                'selected_service_id' => null,
            ],
        ],
    ], $charts ?? []);
@endphp

<style>
.dashboard-chart-card .chart-canvas-wrapper {
    height: 20rem;
}

.dashboard-chart-card .chart-header-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.dashboard-chart-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.6);
    z-index: 45;
}

.dashboard-chart-backdrop.hidden {
    display: none;
}

.dashboard-chart-card.chart-card-expanded {
    grid-column: 1 / -1;
}

.dashboard-chart-card.chart-card-expanded .chart-canvas-wrapper {
    height: 30rem;
}

.dashboard-chart-card.chart-card-fullscreen {
    position: fixed;
    inset: 0.75rem;
    z-index: 50;
    overflow: auto;
}

.dashboard-chart-card.chart-card-fullscreen .chart-canvas-wrapper {
    height: min(78vh, 42rem);
}

@media (max-width: 768px) {
    .dashboard-chart-card {
        padding: 1rem;
    }

    .dashboard-chart-card .chart-canvas-wrapper {
        height: 14rem;
    }

    .dashboard-chart-card.chart-card-expanded .chart-canvas-wrapper {
        height: 22rem;
    }

    .dashboard-chart-card .chart-size-toggle {
        width: 100%;
        justify-content: center;
    }

    .dashboard-chart-card .chart-fullscreen-toggle {
        width: 100%;
        justify-content: center;
    }

    .dashboard-chart-card.chart-card-fullscreen {
        inset: 0.25rem;
    }

    .dashboard-chart-card.chart-card-fullscreen .chart-canvas-wrapper {
        height: 70vh;
    }

    .dashboard-chart-card .chart-header-controls,
    .dashboard-chart-card .chart-header-controls select,
    .dashboard-chart-card .chart-header-controls label {
        width: 100%;
    }

    .dashboard-chart-card .chart-header-controls label {
        margin-bottom: -0.25rem;
    }
}

@media (max-width: 420px) {
    .dashboard-chart-card {
        padding: 0.75rem;
        border-radius: 0.75rem;
    }

    .dashboard-chart-card .chart-canvas-wrapper {
        height: 12.25rem;
    }

    .dashboard-chart-card.chart-card-expanded .chart-canvas-wrapper {
        height: 16rem;
    }

    .dashboard-chart-card.chart-card-fullscreen .chart-canvas-wrapper {
        height: 66vh;
    }

    .dashboard-chart-card h3 {
        font-size: 0.95rem;
        line-height: 1.3;
    }

    .dashboard-chart-card p,
    .dashboard-chart-card label,
    .dashboard-chart-card select,
    .dashboard-chart-card button {
        font-size: 0.75rem;
    }

    .dashboard-chart-card #externalCompanyTopFastest,
    .dashboard-chart-card #externalCompanyTopFailures {
        max-height: 12rem;
        overflow-y: auto;
        padding-right: 0.25rem;
    }
}
</style>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Reliability Charts (Top, right below KPI cards) -->
    <div class="lg:col-span-3 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="dashboard-chart-card bg-gradient-to-br from-white to-amber-50/60 rounded-2xl shadow-md border border-amber-100 p-6 animate-fade-in" data-chart-card data-chart-key="mttrChart" style="animation-delay: 0.5s">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">MTTR (heures) par désignation</h3>
                    <p class="text-xs text-amber-700 font-medium">Mean Time To Repair</p>
                </div>
                <div class="chart-header-controls">
                    <label for="designationFilter" class="text-sm font-semibold text-gray-700">Désignation</label>
                    <select id="designationFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm max-w-[240px]">
                        <option value="">Toutes les désignations</option>
                        @foreach(($chartValues['reliability_by_designation']['designations'] ?? []) as $designation)
                            <option value="{{ $designation }}" {{ (string) ($chartValues['reliability_by_designation']['selected_designation'] ?? '') === (string) $designation ? 'selected' : '' }}>{{ $designation }}</option>
                        @endforeach
                    </select>
                    <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-amber-200 rounded-lg text-sm font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                        <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                        <span>Agrandir</span>
                    </button>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">Fiabilité</span>
                </div>
            </div>
            <div class="relative chart-canvas-wrapper">
                <canvas id="mttrChart"></canvas>
            </div>
        </div>

        <div class="dashboard-chart-card bg-gradient-to-br from-white to-blue-50/60 rounded-2xl shadow-md border border-blue-100 p-6 animate-fade-in" data-chart-card data-chart-key="mtbfChart" style="animation-delay: 0.56s">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">MTBF & Disponibilité par désignation</h3>
                    <p class="text-xs text-blue-700 font-medium">Comparatif MTBF (h) et disponibilité (%)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-blue-200 rounded-lg text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                        <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                        <span>Agrandir</span>
                    </button>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700 border border-blue-200">Fiabilité</span>
                </div>
            </div>
            <div class="relative chart-canvas-wrapper">
                <canvas id="mtbfChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bar Chart: Interventions par Mois -->
    <div class="dashboard-chart-card lg:col-span-2 bg-white rounded-xl shadow-md p-6 animate-fade-in" data-chart-card data-chart-key="interventionsChart" style="animation-delay: 0.62s">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800">Interventions par Mois</h3>
            <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                <span>Agrandir</span>
            </button>
        </div>
        <div class="relative chart-canvas-wrapper">
            <canvas id="interventionsChart"></canvas>
        </div>
    </div>

    <!-- Pie Chart: Répartition des Types -->
    <div class="dashboard-chart-card bg-white rounded-xl shadow-md p-6 animate-fade-in" data-chart-card data-chart-key="maintenanceTypesChart" style="animation-delay: 0.68s">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800">Types de Maintenance</h3>
            <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                <span>Agrandir</span>
            </button>
        </div>
        <div class="relative chart-canvas-wrapper">
            <canvas id="maintenanceTypesChart"></canvas>
        </div>
    </div>

    <!-- Line Chart: Équipements ajoutés -->
    <div class="dashboard-chart-card lg:col-span-3 bg-white rounded-xl shadow-md p-6 animate-fade-in" data-chart-card data-chart-key="costChart" style="animation-delay: 0.74s">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800">Équipements ajoutés par mois</h3>
            <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                <span>Agrandir</span>
            </button>
        </div>
        <div class="relative chart-canvas-wrapper">
            <canvas id="costChart"></canvas>
        </div>
    </div>

    <div class="dashboard-chart-card lg:col-span-3 bg-white rounded-xl shadow-md p-6 animate-fade-in" data-chart-card data-chart-key="downtimeChart" style="animation-delay: 0.8s">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h3 class="text-lg font-bold text-gray-800">Temps d'arrêt moyen (Réclamation → Clôture)</h3>
            <div class="chart-header-controls">
                <label for="downtimeDaysFilter" class="text-sm font-semibold text-gray-700">Période</label>
                <select id="downtimeDaysFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="7" {{ (int) ($downtimeFilterDays ?? 30) === 7 ? 'selected' : '' }}>7 jours</option>
                    <option value="30" {{ (int) ($downtimeFilterDays ?? 30) === 30 ? 'selected' : '' }}>30 jours</option>
                    <option value="90" {{ (int) ($downtimeFilterDays ?? 30) === 90 ? 'selected' : '' }}>90 jours</option>
                    <option value="180" {{ (int) ($downtimeFilterDays ?? 30) === 180 ? 'selected' : '' }}>180 jours</option>
                    <option value="365" {{ (int) ($downtimeFilterDays ?? 30) === 365 ? 'selected' : '' }}>365 jours</option>
                </select>
                <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                    <span>Agrandir</span>
                </button>
            </div>
        </div>
        <div class="relative chart-canvas-wrapper">
            <canvas id="downtimeChart"></canvas>
        </div>
    </div>

    <div class="dashboard-chart-card lg:col-span-3 bg-white rounded-xl shadow-md p-6 animate-fade-in" data-chart-card data-chart-key="externalCompaniesChart" style="animation-delay: 0.85s">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Performance des sociétés externes</h3>
                <p class="text-xs text-gray-600">Interventions par société, MTTR, top rapides et sociétés avec le plus de pannes</p>
            </div>
            <div class="flex flex-col md:flex-row gap-2 w-full lg:w-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 w-full lg:w-auto">
                    <select id="companyPeriodMonthFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Tous les mois</option>
                        @foreach(($chartValues['external_companies']['filters']['months'] ?? []) as $month)
                            <option value="{{ $month['value'] }}" {{ (int) ($chartValues['external_companies']['filters']['selected_month'] ?? 0) === (int) $month['value'] ? 'selected' : '' }}>{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                    <select id="companyPeriodYearFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach(($chartValues['external_companies']['filters']['years'] ?? []) as $year)
                            <option value="{{ $year }}" {{ (int) ($chartValues['external_companies']['filters']['selected_year'] ?? 0) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <select id="companyServiceFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Tous les services</option>
                        @foreach(($chartValues['external_companies']['filters']['services'] ?? []) as $service)
                            <option value="{{ $service['id'] }}" {{ (int) ($chartValues['external_companies']['filters']['selected_service_id'] ?? 0) === (int) $service['id'] ? 'selected' : '' }}>{{ $service['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" data-chart-toggle class="chart-size-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-expand-arrows-alt text-[11px]"></i>
                    <span>Agrandir</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2">
                <div class="relative chart-canvas-wrapper">
                    <canvas id="externalCompaniesChart"></canvas>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Top sociétés les plus rapides (MTTR)</h4>
                <div id="externalCompanyTopFastest" class="space-y-2 mb-4">
                    @foreach(($chartValues['external_companies']['top_fastest'] ?? []) as $row)
                        @php
                            $badgeClass = ($row['badge'] ?? '') === 'success'
                                ? 'bg-green-100 text-green-700 border-green-200'
                                : (($row['badge'] ?? '') === 'warning'
                                    ? 'bg-amber-100 text-amber-700 border-amber-200'
                                    : 'bg-red-100 text-red-700 border-red-200');
                        @endphp
                        <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800">{{ $row['company'] ?? '-' }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">{{ number_format((float) ($row['mttr'] ?? 0), 1) }}h</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Interventions: {{ (int) ($row['interventions_total'] ?? 0) }} • Disponibilité: {{ number_format((float) ($row['availability'] ?? 0), 1) }}%</p>
                        </div>
                    @endforeach
                </div>

                <h4 class="text-sm font-semibold text-gray-700 mb-3">Sociétés avec le plus de pannes</h4>
                <div id="externalCompanyTopFailures" class="space-y-2">
                    @foreach(($chartValues['external_companies']['top_failures'] ?? []) as $row)
                        @php
                            $badgeClass = ($row['badge'] ?? '') === 'success'
                                ? 'bg-green-100 text-green-700 border-green-200'
                                : (($row['badge'] ?? '') === 'warning'
                                    ? 'bg-amber-100 text-amber-700 border-amber-200'
                                    : 'bg-red-100 text-red-700 border-red-200');
                        @endphp
                        <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800">{{ $row['company'] ?? '-' }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">{{ (int) ($row['interventions_total'] ?? 0) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">MTTR: {{ number_format((float) ($row['mttr'] ?? 0), 1) }} h • Disponibilité: {{ number_format((float) ($row['availability'] ?? 0), 1) }}%</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dashboardChartBackdrop" class="dashboard-chart-backdrop hidden" aria-hidden="true"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Color Palette (Light Blue Theme)
    const colors = {
        primary: '#3b82f6',
        secondary: '#60a5fa',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444'
    };

    window.dashboardChartInstances = window.dashboardChartInstances || {};
    const chartCards = Array.from(document.querySelectorAll('[data-chart-card]'));
    const chartBackdrop = document.getElementById('dashboardChartBackdrop');
    let fullscreenCard = null;

    const isMobileViewport = () => window.matchMedia('(max-width: 768px)').matches;
    const isUltraMobileViewport = () => window.matchMedia('(max-width: 420px)').matches;

    const resizeLinkedChart = (chartKey) => {
        if (!chartKey) {
            return;
        }

        const chart = window.dashboardChartInstances?.[chartKey];
        if (chart && typeof chart.resize === 'function') {
            chart.resize();
            chart.update('none');
        }
    };

    const applyChartCardState = (card, expanded) => {
        const toggleButton = card.querySelector('[data-chart-toggle]');
        const key = card.getAttribute('data-chart-key') || '';

        card.classList.toggle('chart-card-expanded', expanded);

        if (toggleButton) {
            const label = toggleButton.querySelector('span');
            toggleButton.setAttribute('aria-pressed', expanded ? 'true' : 'false');
            if (label) {
                label.textContent = expanded ? 'Réduire' : 'Agrandir';
            }
        }

        if (key) {
            localStorage.setItem(`dashboard.chart.size.${key}`, expanded ? 'expanded' : 'default');
            setTimeout(() => resizeLinkedChart(key), 120);
        }
    };

    const applyResponsiveChartOptions = (chart) => {
        if (!chart || !chart.options) {
            return;
        }

        const isMobile = isMobileViewport();
        const isUltraMobile = isUltraMobileViewport();
        const options = chart.options;

        chart.$responsiveDefaults = chart.$responsiveDefaults || {
            legendPosition: options.plugins?.legend?.position || 'top',
        };

        if (options.plugins?.legend) {
            options.plugins.legend.position = isMobile ? 'bottom' : chart.$responsiveDefaults.legendPosition;
            options.plugins.legend.display = isUltraMobile ? !['interventionsChart', 'externalCompaniesChart'].includes(chart.canvas?.id || '') : true;
            options.plugins.legend.labels = {
                ...(options.plugins.legend.labels || {}),
                boxWidth: isUltraMobile ? 8 : (isMobile ? 10 : 12),
                padding: isUltraMobile ? 6 : (isMobile ? 8 : 15),
                font: {
                    size: isUltraMobile ? 9 : (isMobile ? 10 : 12),
                },
            };
        }

        if (options.scales?.x) {
            options.scales.x.ticks = {
                ...(options.scales.x.ticks || {}),
                autoSkip: true,
                maxTicksLimit: isUltraMobile ? 4 : (isMobile ? 6 : 12),
                minRotation: 0,
                maxRotation: isMobile ? 0 : 35,
                font: {
                    size: isUltraMobile ? 9 : (isMobile ? 10 : 12),
                },
            };

            if (isMobile) {
                options.scales.x.ticks.callback = function (value) {
                    const label = this.getLabelForValue ? this.getLabelForValue(value) : value;
                    const text = String(label || '');
                    const maxLength = isUltraMobile ? 8 : 12;
                    return text.length > maxLength ? `${text.slice(0, maxLength)}…` : text;
                };
            } else if (options.scales.x.ticks.callback) {
                delete options.scales.x.ticks.callback;
            }
        }

        if (options.scales?.y) {
            options.scales.y.ticks = {
                ...(options.scales.y.ticks || {}),
                font: {
                    size: isUltraMobile ? 9 : (isMobile ? 10 : 12),
                },
                maxTicksLimit: isUltraMobile ? 5 : undefined,
            };
        }

        if (options.scales?.y1) {
            options.scales.y1.ticks = {
                ...(options.scales.y1.ticks || {}),
                font: {
                    size: isUltraMobile ? 9 : (isMobile ? 10 : 12),
                },
                maxTicksLimit: isUltraMobile ? 5 : undefined,
            };
        }

        chart.update('none');
    };

    const applyResponsiveOptionsToAllCharts = () => {
        Object.values(window.dashboardChartInstances || {}).forEach((chart) => {
            applyResponsiveChartOptions(chart);
        });
    };

    const setFullscreenButtonLabel = (button, isFullscreen) => {
        if (!button) {
            return;
        }

        const label = button.querySelector('span');
        button.setAttribute('aria-pressed', isFullscreen ? 'true' : 'false');
        if (label) {
            label.textContent = isFullscreen ? 'Fermer plein écran' : 'Plein écran';
        }
    };

    const closeFullscreenCard = () => {
        if (!fullscreenCard) {
            return;
        }

        const key = fullscreenCard.getAttribute('data-chart-key') || '';
        const button = fullscreenCard.querySelector('[data-chart-fullscreen]');
        fullscreenCard.classList.remove('chart-card-fullscreen');
        document.body.classList.remove('overflow-hidden');
        if (chartBackdrop) {
            chartBackdrop.classList.add('hidden');
        }
        setFullscreenButtonLabel(button, false);
        fullscreenCard = null;
        setTimeout(() => resizeLinkedChart(key), 120);
    };

    const openFullscreenCard = (card) => {
        if (!card) {
            return;
        }

        if (fullscreenCard && fullscreenCard !== card) {
            closeFullscreenCard();
        }

        const key = card.getAttribute('data-chart-key') || '';
        const button = card.querySelector('[data-chart-fullscreen]');

        fullscreenCard = card;
        fullscreenCard.classList.add('chart-card-fullscreen');
        document.body.classList.add('overflow-hidden');
        if (chartBackdrop) {
            chartBackdrop.classList.remove('hidden');
        }
        setFullscreenButtonLabel(button, true);
        setTimeout(() => resizeLinkedChart(key), 120);
    };

    // 1. Interventions par Mois (Bar Chart)
    const interventionsCtx = document.getElementById('interventionsChart');
    if (interventionsCtx) {
        window.dashboardChartInstances.interventionsChart = new Chart(interventionsCtx, {
            type: 'bar',
            data: {
                labels: @json($chartValues['interventions']['labels'] ?? []),
                datasets: [{
                    label: 'Interventions préventives',
                    data: @json($chartValues['interventions']['preventive'] ?? []),
                    backgroundColor: colors.primary,
                    borderColor: colors.primary,
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Interventions correctives',
                    data: @json($chartValues['interventions']['curative'] ?? []),
                    backgroundColor: colors.secondary,
                    borderColor: colors.secondary,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const downtimeCtx = document.getElementById('downtimeChart');
    if (downtimeCtx) {
        window.dashboardChartInstances.downtimeChart = new Chart(downtimeCtx, {
            type: 'bar',
            data: {
                labels: @json($chartValues['downtime']['labels'] ?? []),
                datasets: [{
                    label: 'Temps d\'arrêt moyen (heures)',
                    data: @json($chartValues['downtime']['avg_hours'] ?? []),
                    backgroundColor: colors.warning,
                    borderColor: colors.warning,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const mttrCtx = document.getElementById('mttrChart');
    if (mttrCtx) {
        window.dashboardChartInstances.mttrChart = new Chart(mttrCtx, {
            type: 'line',
            data: {
                labels: @json($chartValues['reliability_by_designation']['labels'] ?? []),
                datasets: [{
                    label: 'MTTR (h)',
                    data: @json($chartValues['reliability_by_designation']['mttr'] ?? []),
                    borderColor: colors.warning,
                    backgroundColor: 'rgba(245, 158, 11, 0.18)',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: colors.warning,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(245, 158, 11, 0.15)'
                        },
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    const mtbfCtx = document.getElementById('mtbfChart');
    if (mtbfCtx) {
        window.dashboardChartInstances.mtbfChart = new Chart(mtbfCtx, {
            type: 'line',
            data: {
                labels: @json($chartValues['reliability_by_designation']['labels'] ?? []),
                datasets: [{
                    label: 'MTBF (h)',
                    data: @json($chartValues['reliability_by_designation']['mtbf'] ?? []),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(59, 130, 246, 0.18)',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                }, {
                    label: 'Disponibilité (%)',
                    data: @json($chartValues['reliability_by_designation']['disponibilite'] ?? []),
                    borderColor: colors.success,
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    tension: 0.35,
                    fill: false,
                    pointRadius: 3,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 5,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(59, 130, 246, 0.12)'
                        },
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        max: 100,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Disponibilité (%)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function toPercentSeries(values) {
        return (values || []).map((value) => {
            const parsed = Number(value);
            if (!Number.isFinite(parsed)) return 0;
            return Math.max(0, Math.min(100, parsed));
        });
    }

    function buildExternalCompaniesPayload(source) {
        const labels = source?.labels || [];
        const score = toPercentSeries(source?.score || []);
        const respectPlanning = toPercentSeries(source?.respect_planning || []);
        const availability = toPercentSeries(source?.availability || []);

        const maxSeriesValue = Math.max(
            0,
            ...score,
            ...respectPlanning,
            ...availability
        );

        const suggestedMax = maxSeriesValue <= 70 ? 75 : 100;
        const hasKpiLines = [...respectPlanning, ...availability].some((value) => value > 0);

        return {
            labels,
            score,
            respectPlanning,
            availability,
            hasKpiLines,
            suggestedMax,
        };
    }

    function applyExternalCompaniesChartData(chart, source) {
        const payload = buildExternalCompaniesPayload(source || {});

        chart.data.labels = payload.labels;
        chart.data.datasets[0].data = payload.score;
        chart.data.datasets[1].data = payload.respectPlanning;
        chart.data.datasets[2].data = payload.availability;
        chart.data.datasets[1].hidden = !payload.hasKpiLines;
        chart.data.datasets[2].hidden = !payload.hasKpiLines;
        chart.options.scales.y.max = payload.suggestedMax;
        chart.$hasKpiLines = payload.hasKpiLines;
    }

    const externalCompaniesCtx = document.getElementById('externalCompaniesChart');
    if (externalCompaniesCtx) {
        const initialExternalPayload = buildExternalCompaniesPayload(@json($chartValues['external_companies'] ?? []));

        window.dashboardChartInstances.externalCompaniesChart = new Chart(externalCompaniesCtx, {
            type: 'bar',
            data: {
                labels: initialExternalPayload.labels,
                datasets: [{
                    label: 'Score global',
                    data: initialExternalPayload.score,
                    backgroundColor: (context) => {
                        const chart = context.chart;
                        const { ctx, chartArea } = chart;
                        if (!chartArea) return 'rgba(37, 99, 235, 0.75)';

                        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.95)');
                        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.35)');
                        return gradient;
                    },
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 10,
                    borderSkipped: false,
                    barPercentage: 0.72,
                    categoryPercentage: 0.72,
                    yAxisID: 'y',
                }, {
                    label: 'Respect planning (%)',
                    data: initialExternalPayload.respectPlanning,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.18)',
                    borderWidth: 2.5,
                    type: 'line',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    yAxisID: 'y',
                }, {
                    label: 'Disponibilité (%)',
                    data: initialExternalPayload.availability,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.14)',
                    borderWidth: 2.5,
                    borderDash: [5, 4],
                    type: 'line',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    yAxisID: 'y',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                animation: {
                    duration: 700,
                    easing: 'easeOutQuart',
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            font: {
                                size: 11,
                                weight: '600',
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const label = context.dataset.label || 'Valeur';
                                const value = Number(context.parsed.y || 0).toFixed(1);
                                return `${label}: ${value}%`;
                            },
                        },
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: initialExternalPayload.suggestedMax,
                        ticks: {
                            callback: (value) => `${value}%`,
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.22)',
                            borderDash: [4, 3],
                        },
                        title: {
                            display: true,
                            text: 'Performance (%)'
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            maxRotation: 25,
                            minRotation: 0,
                        },
                    }
                }
            },
            plugins: [{
                id: 'externalCompaniesNoKpiOverlay',
                afterDraw(chart) {
                    if (chart.$hasKpiLines) return;

                    const { ctx, chartArea } = chart;
                    if (!chartArea) return;

                    ctx.save();
                    ctx.fillStyle = 'rgba(71, 85, 105, 0.75)';
                    ctx.font = '600 12px Segoe UI';
                    ctx.textAlign = 'center';
                    ctx.fillText(
                        'Indicateurs planning/disponibilité indisponibles pour ce filtre',
                        chartArea.left + (chartArea.right - chartArea.left) / 2,
                        chartArea.top + 20
                    );
                    ctx.restore();
                },
            }],
        });

        applyExternalCompaniesChartData(window.dashboardChartInstances.externalCompaniesChart, @json($chartValues['external_companies'] ?? []));
    }

    // 2. Répartition des Types (Pie Chart)
    const maintenanceTypesCtx = document.getElementById('maintenanceTypesChart');
    if (maintenanceTypesCtx) {
        window.dashboardChartInstances.maintenanceTypesChart = new Chart(maintenanceTypesCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartValues['maintenance_types']['labels'] ?? []),
                datasets: [{
                    data: @json($chartValues['maintenance_types']['data'] ?? []),
                    backgroundColor: [
                        colors.primary,
                        colors.secondary,
                        colors.warning
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }

    // 3. Coût Mensuel (Line Chart)
    const costCtx = document.getElementById('costChart');
    if (costCtx) {
        window.dashboardChartInstances.costChart = new Chart(costCtx, {
            type: 'line',
            data: {
                labels: @json($chartValues['equipments_added']['labels'] ?? []),
                datasets: [{
                    label: 'Équipements ajoutés',
                    data: @json($chartValues['equipments_added']['data'] ?? []),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    chartCards.forEach((card) => {
        const key = card.getAttribute('data-chart-key') || '';
        const toggleButton = card.querySelector('[data-chart-toggle]');
        if (!toggleButton) {
            return;
        }

        const fullscreenButton = document.createElement('button');
        fullscreenButton.type = 'button';
        fullscreenButton.setAttribute('data-chart-fullscreen', 'true');
        fullscreenButton.className = 'chart-fullscreen-toggle inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors';
        fullscreenButton.innerHTML = '<i class="fas fa-expand text-[11px]"></i><span>Plein écran</span>';
        toggleButton.insertAdjacentElement('afterend', fullscreenButton);

        const savedState = key ? localStorage.getItem(`dashboard.chart.size.${key}`) : null;
        if (savedState === 'expanded') {
            applyChartCardState(card, true);
        } else {
            applyChartCardState(card, false);
        }

        toggleButton.addEventListener('click', function () {
            const expanded = card.classList.contains('chart-card-expanded');
            applyChartCardState(card, !expanded);
        });

        fullscreenButton.addEventListener('click', function () {
            if (card.classList.contains('chart-card-fullscreen')) {
                closeFullscreenCard();
                return;
            }

            openFullscreenCard(card);
        });
    });

    applyResponsiveOptionsToAllCharts();

    if (chartBackdrop) {
        chartBackdrop.addEventListener('click', function () {
            closeFullscreenCard();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeFullscreenCard();
        }
    });

    window.addEventListener('resize', function () {
        chartCards.forEach((card) => {
            const key = card.getAttribute('data-chart-key') || '';
            resizeLinkedChart(key);
        });
        applyResponsiveOptionsToAllCharts();
    });

    window.updateDashboardCharts = function (charts) {
        const interventionsChart = window.dashboardChartInstances?.interventionsChart;
        if (interventionsChart && charts?.interventions) {
            interventionsChart.data.labels = charts.interventions.labels || [];
            interventionsChart.data.datasets[0].data = charts.interventions.preventive || [];
            interventionsChart.data.datasets[1].data = charts.interventions.curative || [];
            interventionsChart.update();
        }

        const maintenanceChart = window.dashboardChartInstances?.maintenanceTypesChart;
        if (maintenanceChart && charts?.maintenance_types) {
            maintenanceChart.data.labels = charts.maintenance_types.labels || [];
            maintenanceChart.data.datasets[0].data = charts.maintenance_types.data || [];
            maintenanceChart.update();
        }

        const equipmentChart = window.dashboardChartInstances?.costChart;
        if (equipmentChart && charts?.equipments_added) {
            equipmentChart.data.labels = charts.equipments_added.labels || [];
            equipmentChart.data.datasets[0].data = charts.equipments_added.data || [];
            equipmentChart.update();
        }

        const downtimeChart = window.dashboardChartInstances?.downtimeChart;
        if (downtimeChart && charts?.downtime) {
            downtimeChart.data.labels = charts.downtime.labels || [];
            downtimeChart.data.datasets[0].data = charts.downtime.avg_hours || [];
            downtimeChart.update();
        }

        const mttrChart = window.dashboardChartInstances?.mttrChart;
        if (mttrChart && charts?.reliability_by_designation) {
            mttrChart.data.labels = charts.reliability_by_designation.labels || [];
            mttrChart.data.datasets[0].data = charts.reliability_by_designation.mttr || [];
            mttrChart.update();
        }

        const mtbfChart = window.dashboardChartInstances?.mtbfChart;
        if (mtbfChart && charts?.reliability_by_designation) {
            mtbfChart.data.labels = charts.reliability_by_designation.labels || [];
            mtbfChart.data.datasets[0].data = charts.reliability_by_designation.mtbf || [];
            if (mtbfChart.data.datasets[1]) {
                mtbfChart.data.datasets[1].data = charts.reliability_by_designation.disponibilite || [];
            }
            mtbfChart.update();
        }

        const designationSelect = document.getElementById('designationFilter');
        if (designationSelect && charts?.reliability_by_designation) {
            const currentValue = charts.reliability_by_designation.selected_designation || '';
            const designations = charts.reliability_by_designation.designations || [];

            while (designationSelect.options.length > 1) {
                designationSelect.remove(1);
            }

            designations.forEach((designation) => {
                const option = document.createElement('option');
                option.value = designation;
                option.textContent = designation;
                designationSelect.appendChild(option);
            });

            designationSelect.value = currentValue;
        }

        const externalCompaniesChart = window.dashboardChartInstances?.externalCompaniesChart;
        if (externalCompaniesChart && charts?.external_companies) {
            applyExternalCompaniesChartData(externalCompaniesChart, charts.external_companies);
            externalCompaniesChart.update();
        }

        if (charts?.external_companies?.filters) {
            const monthSelect = document.getElementById('companyPeriodMonthFilter');
            const yearSelect = document.getElementById('companyPeriodYearFilter');
            const serviceSelect = document.getElementById('companyServiceFilter');
            const filters = charts.external_companies.filters;

            if (monthSelect) {
                monthSelect.value = filters.selected_month || '';
            }
            if (yearSelect) {
                yearSelect.value = filters.selected_year || '';
            }
            if (serviceSelect) {
                const selectedServiceId = filters.selected_service_id || '';
                while (serviceSelect.options.length > 1) {
                    serviceSelect.remove(1);
                }
                (filters.services || []).forEach((service) => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = service.name;
                    serviceSelect.appendChild(option);
                });
                serviceSelect.value = selectedServiceId;
            }
        }

        const topFastestContainer = document.getElementById('externalCompanyTopFastest');
        if (topFastestContainer && charts?.external_companies?.top_fastest) {
            const rows = charts.external_companies.top_fastest || [];
            topFastestContainer.innerHTML = '';

            rows.forEach((row) => {
                const badgeClass = row.badge === 'success'
                    ? 'bg-green-100 text-green-700 border-green-200'
                    : (row.badge === 'warning' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-red-100 text-red-700 border-red-200');

                const wrapper = document.createElement('div');
                wrapper.className = 'p-3 rounded-lg border border-gray-200 bg-gray-50';
                wrapper.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-800">${row.company || '-'}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${badgeClass}">${Number(row.mttr || 0).toFixed(1)}h</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Interventions: ${parseInt(row.interventions_total || 0, 10)} • Disponibilité: ${Number(row.availability || 0).toFixed(1)}%</p>
                `;
                topFastestContainer.appendChild(wrapper);
            });
        }

        const topFailuresContainer = document.getElementById('externalCompanyTopFailures');
        if (topFailuresContainer && charts?.external_companies?.top_failures) {
            const rows = charts.external_companies.top_failures || [];
            topFailuresContainer.innerHTML = '';

            rows.forEach((row) => {
                const badgeClass = row.badge === 'success'
                    ? 'bg-green-100 text-green-700 border-green-200'
                    : (row.badge === 'warning' ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-red-100 text-red-700 border-red-200');

                const wrapper = document.createElement('div');
                wrapper.className = 'p-3 rounded-lg border border-gray-200 bg-gray-50';
                wrapper.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-800">${row.company || '-'}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border ${badgeClass}">${parseInt(row.interventions_total || 0, 10)}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">MTTR: ${Number(row.mttr || 0).toFixed(1)} h • Disponibilité: ${Number(row.availability || 0).toFixed(1)}%</p>
                `;
                topFailuresContainer.appendChild(wrapper);
            });
        }

        applyResponsiveOptionsToAllCharts();
    };
});
</script>
