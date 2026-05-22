@extends('layouts.dashboard')

@section('title', 'KPI MTTR / MTBF')
@section('page-title', 'KPI MTTR / MTBF — Disponibilité des Équipements')

@section('content')
<div class="space-y-6">

    {{-- ====== TOP KPI CARDS ====== --}}
    <div id="kpi-cards" class="grid grid-cols-2 md:grid-cols-4 gap-4 hidden">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Disponibilité moy.</p>
                <p class="text-2xl font-bold text-gray-800" id="kpi-dispo">—</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center">
                <i class="fas fa-wrench text-amber-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">MTTR moyen</p>
                <p class="text-2xl font-bold text-gray-800" id="kpi-mttr">—</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center">
                <i class="fas fa-clock text-green-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">MTBF moyen</p>
                <p class="text-2xl font-bold text-gray-800" id="kpi-mtbf">—</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium">Total pannes</p>
                <p class="text-2xl font-bold text-gray-800" id="kpi-pannes">—</p>
            </div>
        </div>

    </div>

    {{-- ====== FILTERS ====== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-filter text-blue-400"></i>
            <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Filtres</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date début</label>
                <input type="date" id="filter-start" value="{{ $defaultStart }}"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date fin</label>
                <input type="date" id="filter-end" value="{{ $defaultEnd }}"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Unité de temps</label>
                <select id="filter-time-unit"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white">
                    <option value="hours">Heures</option>
                    <option value="days">Jours</option>
                    <option value="months">Mois</option>
                    <option value="years">Années</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Service</label>
                <select id="filter-service"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none bg-white">
                    <option value="">— Tous les services —</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Désignation</label>
                <input type="text" id="filter-designation" placeholder="Filtrer par désignation…"
                       class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:border-blue-400 outline-none">
            </div>

        </div>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button id="btn-apply"
                    onclick="loadData()"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-colors">
                <i class="fas fa-sync-alt" id="btn-apply-icon"></i>
                Actualiser
            </button>
            <button onclick="resetFilters()"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium px-5 py-2 rounded-xl transition-colors">
                <i class="fas fa-undo"></i>
                Réinitialiser
            </button>
            <span id="period-label" class="text-xs text-gray-400 italic"></span>
        </div>
    </div>

    {{-- ====== CHART ====== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Graphique MTTR / MTBF / Disponibilité</h2>
                <p class="text-xs text-gray-400 mt-0.5">Classé par disponibilité croissante (pires équipements en premier)</p>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-4 h-3 rounded" style="background:#f59e0b"></span> MTTR (<span class="unit-label">h</span>)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-4 h-3 rounded" style="background:#3b82f6"></span> MTBF (<span class="unit-label">h</span>)
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block w-4 h-3 rounded" style="background:#10b981; height:3px; border-radius:2px; margin-top:4px;"></span> Disponibilité %
                </span>
            </div>
        </div>

        <div id="chart-empty" class="hidden py-16 text-center text-gray-400">
            <i class="fas fa-chart-bar text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">Aucune donnée pour les critères sélectionnés.</p>
        </div>

        <div id="chart-loading" class="py-16 text-center text-gray-400 hidden">
            <i class="fas fa-spinner fa-spin text-4xl mb-3 text-blue-400"></i>
            <p class="text-sm">Chargement en cours…</p>
        </div>

        <div id="chart-wrapper" style="position:relative; height:380px;">
            <canvas id="kpi-chart"></canvas>
        </div>
    </div>

    {{-- ====== TABLE ====== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Tableau Récapitulatif</h2>
            <button onclick="exportCsv()"
                    class="inline-flex items-center gap-2 text-xs bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 px-3 py-1.5 rounded-xl transition-colors font-medium">
                <i class="fas fa-file-csv"></i> Exporter CSV
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                        <th class="px-4 py-3 text-left font-semibold">Désignation</th>
                        <th class="px-4 py-3 text-center font-semibold">Nb équips.</th>
                        <th class="px-4 py-3 text-center font-semibold">Nb pannes</th>
                        <th class="px-4 py-3 text-center font-semibold">MTTR (<span class="unit-label">h</span>)</th>
                        <th class="px-4 py-3 text-center font-semibold">MTBF (<span class="unit-label">h</span>)</th>
                        <th class="px-4 py-3 text-center font-semibold">Tps arrêt total (<span class="unit-label">h</span>)</th>
                        <th class="px-4 py-3 text-center font-semibold">Disponibilité %</th>
                        <th class="px-4 py-3 text-center font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody id="kpi-table-body" class="divide-y divide-gray-50">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-xs italic"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
// ── Chart instance
let kpiChart = null;
let cachedData = [];
let currentUnit = 'hours';

// ── Format units 
function getUnitSuffix(unit) {
    if (unit === 'days') return ' j';
    if (unit === 'months') return ' mois';
    if (unit === 'years') return ' ans';
    return ' h';
}

function fmtValue(v) {
    if (v === null || v === undefined || isNaN(v)) return 'N/D';
    if (currentUnit === 'hours') {
        const hrs = Math.floor(v);
        const mins = Math.round((v - hrs) * 60);
        if (hrs === 0) return mins + 'min';
        if (mins === 0) return hrs + 'h';
        return hrs + 'h ' + mins + 'min';
    }
    return v.toFixed(2) + getUnitSuffix(currentUnit);
}

function fmtDispo(v) {
    if (v === null || v === undefined || isNaN(v)) return 'N/D';
    return v.toFixed(1) + '%';
}

function dispoColor(v) {
    if (v === null || v === undefined || isNaN(v)) return { bg: '#f3f4f6', text: '#6b7280', label: 'N/D' };
    if (v >= 90) return { bg: '#dcfce7', text: '#16a34a', label: 'Excellent' };
    if (v >= 75) return { bg: '#fef9c3', text: '#ca8a04', label: 'Acceptable' };
    if (v >= 50) return { bg: '#ffedd5', text: '#ea580c', label: 'Faible' };
    return { bg: '#fee2e2', text: '#dc2626', label: 'Critique' };
}

// ── Reset filters to defaults
function resetFilters() {
    document.getElementById('filter-start').value = '{{ $defaultStart }}';
    document.getElementById('filter-end').value   = '{{ $defaultEnd }}';
    document.getElementById('filter-service').value = '';
    document.getElementById('filter-designation').value = '';
    loadData();
}

// ── Main data loader
function loadData() {
    const start       = document.getElementById('filter-start').value;
    const end         = document.getElementById('filter-end').value;
    const serviceId   = document.getElementById('filter-service').value;
    const designation = document.getElementById('filter-designation').value;
    const timeUnit    = document.getElementById('filter-time-unit').value;

    // UI state: loading
    const icon = document.getElementById('btn-apply-icon');
    icon.classList.add('fa-spin');
    document.getElementById('chart-loading').classList.remove('hidden');
    document.getElementById('chart-wrapper').style.opacity = '0.3';
    document.getElementById('chart-empty').classList.add('hidden');

    const params = new URLSearchParams({ start_date: start, end_date: end, time_unit: timeUnit });
    if (serviceId)   params.append('service_id', serviceId);
    if (designation) params.append('designation', designation);

    fetch('{{ route("mttr-mtbf.data") }}?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(json => {
        cachedData = json.data || [];
        currentUnit = json.time_unit || 'hours';
        
        // Update unit labels in UI
        const unitLabelShort = getUnitSuffix(currentUnit).trim();
        document.querySelectorAll('.unit-label').forEach(el => el.textContent = unitLabelShort);

        renderKpiCards(json.summary || {});
        renderChart(cachedData);
        renderTable(cachedData);
        document.getElementById('period-label').textContent =
            'Période : ' + (json.period?.start || '') + ' → ' + (json.period?.end || '') +
            '  (' + (json.observation_hours || 0).toLocaleString() + ' h)' +
            (json.source === 'bilan_corrective_fallback' ? '  • Source: bilan corrective (durées indisponibles)' : '');
    })
    .catch(err => {
        console.error("AJAX Error:", err);
        const wrapper = document.getElementById('chart-wrapper');
        wrapper.innerHTML = '<div class="text-red-500 font-bold p-10 bg-red-50 rounded-xl">Une erreur est survenue lors du chargement (vérifiez la console).<br>' + err.message + '</div>';
    })
    .finally(() => {
        icon.classList.remove('fa-spin');
        document.getElementById('chart-loading').classList.add('hidden');
        document.getElementById('chart-wrapper').style.opacity = '1';
    });
}

// ── KPI Cards
function renderKpiCards(summary) {
    const cards = document.getElementById('kpi-cards');
    if (!summary || Object.keys(summary).length === 0) {
        cards.classList.add('hidden');
        return;
    }
    cards.classList.remove('hidden');
    document.getElementById('kpi-dispo').textContent   = fmtDispo(summary.avg_disponibilite);
    document.getElementById('kpi-mttr').textContent    = fmtValue(summary.avg_mttr);
    document.getElementById('kpi-mtbf').textContent    = fmtValue(summary.avg_mtbf);
    document.getElementById('kpi-pannes').textContent  = summary.total_pannes ?? '0';
}

// ── Chart (Chart.js combo: grouped bars + line)
function renderChart(rows) {
    const wrapper = document.getElementById('chart-wrapper');
    const empty   = document.getElementById('chart-empty');

    if (!rows || rows.length === 0) {
        wrapper.style.display = 'none';
        empty.classList.remove('hidden');
        if (kpiChart) { kpiChart.destroy(); kpiChart = null; }
        return;
    }

    wrapper.style.display = 'block';
    empty.classList.add('hidden');

    try {
        const labels   = rows.map(r => {
            const des = r.designation || 'Inconnu';
            return des.length > 25 ? des.substring(0, 25) + '…' : des;
        });
        const mttrData = rows.map(r => (r.mttr_hours === null || r.mttr_hours === undefined) ? null : parseFloat(Number(r.mttr_hours).toFixed(2)));
        const mtbfData = rows.map(r => (r.mtbf_hours === null || r.mtbf_hours === undefined) ? null : parseFloat(Number(r.mtbf_hours).toFixed(2)));
        const dispoData = rows.map(r => (r.disponibilite === null || r.disponibilite === undefined) ? null : parseFloat(Number(r.disponibilite).toFixed(2)));

        const config = {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'MTTR (h)',
                    data: mttrData,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'MTBF (h)',
                    data: mtbfData,
                    backgroundColor: 'rgba(59, 130, 246, 0.65)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    label: 'Disponibilité %',
                    data: dispoData,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y1',
                    order: 1,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 11 },
                        padding: 16,
                        usePointStyle: true,
                    }
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const label = context.dataset.label || '';
                            const val   = context.parsed.y;
                            if (val === null || val === undefined || isNaN(val)) return ' ' + label + ' : N/D';
                            if (label.includes('Disponibilité')) return ' Disponibilité : ' + val.toFixed(1) + '%';
                            return ' ' + label + ' : ' + fmtValue(val);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        maxRotation: 40,
                        minRotation: 20,
                    }
                },
                y: {
                    type: 'linear',
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Durée (' + getUnitSuffix(currentUnit).trim() + ')',
                        font: { size: 11, weight: '500' },
                        color: '#6b7280'
                    },
                    ticks: {
                        font: { size: 10 },
                        callback: (v) => fmtValue(v)
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Disponibilité (%)',
                        font: { size: 11, weight: '500' },
                        color: '#10b981'
                    },
                    ticks: {
                        font: { size: 10 },
                        color: '#10b981',
                        callback: (v) => v + '%'
                    },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    };

        if (kpiChart) { kpiChart.destroy(); }
        const ctx = document.getElementById('kpi-chart');
        if (!ctx) throw new Error("Canvas kpi-chart not found");
        kpiChart = new Chart(ctx, config);
    } catch (err) {
        console.error("Erreur renderChart:", err);
        wrapper.innerHTML = '<div class="text-red-500 font-bold p-5">Erreur rendu graphique: ' + err.message + '</div>';
    }
}

// ── Table
function renderTable(rows) {
    const tbody = document.getElementById('kpi-table-body');

    if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-xs italic">
            Aucune donnée pour les critères sélectionnés.
        </td></tr>`;
        return;
    }

    tbody.innerHTML = rows.map((r, i) => {
        const dColor   = dispoColor(r.disponibilite);
        const dispoValue = (r.disponibilite === null || r.disponibilite === undefined || isNaN(r.disponibilite)) ? null : Number(r.disponibilite);
        const dispoWidth = dispoValue === null ? 0 : Math.min(Math.max(dispoValue, 0), 100);
        const dispoText = dispoValue === null ? 'N/D' : `${dispoValue.toFixed(1)}%`;
        const rowClass = i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50';
        return `
        <tr class="${rowClass} hover:bg-blue-50/40 transition-colors text-sm">
            <td class="px-4 py-3 font-medium text-gray-800">${escHtml(r.designation)}</td>
            <td class="px-4 py-3 text-center text-gray-600">${r.qty}</td>
            <td class="px-4 py-3 text-center text-gray-600">${r.nb_pannes}</td>
            <td class="px-4 py-3 text-center font-mono text-amber-700">${fmtValue(r.mttr_hours)}</td>
            <td class="px-4 py-3 text-center font-mono text-blue-700">${fmtValue(r.mtbf_hours)}</td>
            <td class="px-4 py-3 text-center font-mono text-gray-600">${fmtValue(r.downtime_hours)}</td>
            <td class="px-4 py-3 text-center">
                <div class="inline-flex items-center gap-1">
                    <div class="w-20 h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full" style="width:${dispoWidth}%; background:${dColor.text}"></div>
                    </div>
                    <span class="font-semibold text-sm" style="color:${dColor.text}">${dispoText}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" style="background:${dColor.bg}; color:${dColor.text}">${dColor.label}</span>
            </td>
        </tr>`;
    }).join('');
}

function escHtml(str) {
    return String(str).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── CSV export
function exportCsv() {
    if (!cachedData || cachedData.length === 0) return;
    const csvNumber = (value) => (value === null || value === undefined || isNaN(value)) ? '' : Number(value).toFixed(2);
    const unitLabel = getUnitSuffix(currentUnit).trim();
    const headers = ['Désignation','Nb équips.','Nb pannes',`MTTR (${unitLabel})`,`MTBF (${unitLabel})`,`Arrêt total (${unitLabel})`,'Disponibilité %'];
    const rows = cachedData.map(r => [
        '"' + r.designation.replace(/"/g,'""') + '"',
        r.qty,
        r.nb_pannes,
        csvNumber(r.mttr_hours),
        csvNumber(r.mtbf_hours),
        csvNumber(r.downtime_hours),
        csvNumber(r.disponibilite)
    ].join(','));
    const csv = [headers.join(','), ...rows].join('\n');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'kpi_mttr_mtbf_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
    URL.revokeObjectURL(url);
}

// ── Auto-load on page ready
document.addEventListener('DOMContentLoaded', () => loadData());
</script>
@endsection
