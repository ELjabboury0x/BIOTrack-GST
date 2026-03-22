<!-- KPI Cards Section -->
@php
    $kpiValues = array_merge([
        'total_equipements' => 0,
        'interventions_en_cours' => 0,
        'interventions_en_retard' => 0,
        'equipements_panne_total' => 0,
        'equipements_panne_critique' => 0,
        'disponibilite' => 0,
        'temps_arret_moyen_heures' => 0,
        'planning_societes_a_venir' => 0,
        'planning_prochaine_societe' => 'Aucune société planifiée',
        'planning_prochaine_date_label' => '—',
        'reclamations_ouvertes' => 0,
        'maintenances_preventives_a_venir' => 0,
        'pieces_stock_faible' => 0,
        'mttr_heures' => 0,
        'mtbf_heures' => 0,
        'mtbf_preventif_heures' => 0,
        'mtbf_curatif_heures' => 0,
    ], $kpi ?? []);

    $upcomingCompaniesCount = (int) ($kpiValues['planning_societes_a_venir'] ?? 0);
    $nextCompanyName = (string) ($kpiValues['planning_prochaine_societe'] ?? 'Aucune société planifiée');
    $nextCompanyDateLabel = (string) ($kpiValues['planning_prochaine_date_label'] ?? '—');
    $nextCompanyInfo = trim($nextCompanyDateLabel . ' • ' . $nextCompanyName);
    $planningQuickFrom = now()->format('Y-m-d');
    $planningQuickTo = now()->addDays(14)->format('Y-m-d');
    $planningQuickUrl = route('planning.index', [
        'date_from' => $planningQuickFrom,
        'date_to' => $planningQuickTo,
    ]);
    $equipementsUrl = route('equipements');
    $interventionsOpenUrl = route('interventions', ['status' => 'en_cours']);
    $interventionsLateUrl = route('interventions', ['status' => 'en_attente', 'retard' => 1]);
    $equipementsPanneUrl = route('equipements', ['status' => 'panne']);
    $availabilityUrl = route('equipements', ['status' => 'fonctionnel']);
    $reclamationsUrl = route('reclamations.index');
    $preventivesUrl = route('maintenance-preventive');
    $stockUrl = route('pieces');
@endphp

<style>
/* ── KPI Card Design System ── */
.kpi-card-v2 {
    position: relative;
    background: white;
    border-radius: 1.25rem;
    padding: 1.5rem;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    transition: all 0.4s cubic-bezier(.4,0,.2,1);
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.04);
}
.kpi-card-v2::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 1.25rem 1.25rem 0 0;
    transition: height 0.3s;
}
.kpi-card-v2:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.kpi-card-v2:hover::before { height: 6px; }

.kpi-card-v2.kpi-blue::before    { background: linear-gradient(90deg, #3b82f6, #6366f1); }
.kpi-card-v2.kpi-cyan::before    { background: linear-gradient(90deg, #06b6d4, #3b82f6); }
.kpi-card-v2.kpi-red::before     { background: linear-gradient(90deg, #ef4444, #f97316); }
.kpi-card-v2.kpi-green::before   { background: linear-gradient(90deg, #10b981, #34d399); }
.kpi-card-v2.kpi-amber::before   { background: linear-gradient(90deg, #f59e0b, #ef4444); }
.kpi-card-v2.kpi-violet::before  { background: linear-gradient(90deg, #8b5cf6, #ec4899); }

/* Icon containers */
.kpi-icon-box {
    width: 56px; height: 56px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    transition: all 0.4s;
}
.kpi-card-v2:hover .kpi-icon-box { transform: scale(1.1) rotate(-3deg); }

/* SVG icon animations */
.kpi-icon-box svg { width: 28px; height: 28px; }

/* Pulse ring on icon */
.kpi-icon-pulse {
    position: absolute; inset: -4px;
    border-radius: 20px;
    border: 2px solid currentColor;
    opacity: 0;
    animation: kpiPulseRing 2.5s ease-out infinite;
}
@keyframes kpiPulseRing {
    0%   { transform: scale(0.9); opacity: 0.6; }
    100% { transform: scale(1.3); opacity: 0; }
}

/* Real-time flash */
@keyframes kpiFlash {
    0%   { box-shadow: 0 0 0 0 rgba(59,130,246,0.5); }
    50%  { box-shadow: 0 0 20px 8px rgba(59,130,246,0.25); }
    100% { box-shadow: 0 0 0 0 rgba(59,130,246,0); }
}
.kpi-flash-blue   { animation: kpiFlash 0.8s ease-out; --flash: #3b82f6; }
.kpi-flash-cyan   { animation: kpiFlash 0.8s ease-out; --flash: #06b6d4; }
.kpi-flash-red    { animation: kpiFlash 0.8s ease-out; --flash: #ef4444; }
.kpi-flash-green  { animation: kpiFlash 0.8s ease-out; --flash: #10b981; }
.kpi-flash-amber  { animation: kpiFlash 0.8s ease-out; --flash: #f59e0b; }
.kpi-flash-violet { animation: kpiFlash 0.8s ease-out; --flash: #8b5cf6; }
[class*="kpi-flash-"] {
    animation: kpiFlashGeneric 0.8s ease-out;
}
@keyframes kpiFlashGeneric {
    0%   { box-shadow: 0 0 0 0 rgba(var(--flash-rgb, 59,130,246), 0.5); }
    50%  { box-shadow: 0 0 24px 10px rgba(var(--flash-rgb, 59,130,246), 0.2); }
    100% { box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
}

/* Value change bump */
@keyframes kpiValueBump {
    0%   { transform: scale(1); }
    30%  { transform: scale(1.15); }
    60%  { transform: scale(0.95); }
    100% { transform: scale(1); }
}
.kpi-value-bump { animation: kpiValueBump 0.5s ease-out; }

/* Live indicator */
.kpi-live-dot {
    width: 8px; height: 8px;
    background: #10b981;
    border-radius: 50%;
    display: inline-block;
    position: relative;
    margin-left: 6px;
    vertical-align: middle;
}
.kpi-live-dot::after {
    content: '';
    position: absolute; inset: -3px;
    border-radius: 50%;
    border: 2px solid #10b981;
    animation: kpiPulseRing 2s ease-out infinite;
}

/* Spinning gear for OT/DM en cours */
@keyframes kpiGearSpin {
    from { transform: rotate(0); }
    to   { transform: rotate(360deg); }
}
.kpi-gear-spin { animation: kpiGearSpin 8s linear infinite; }

/* Hourglass flip */
@keyframes kpiHourglassFlip {
    0%, 100% { transform: rotate(0); }
    50%      { transform: rotate(180deg); }
}
.kpi-hourglass-flip { animation: kpiHourglassFlip 3s ease-in-out infinite; }

/* ECG heartbeat line */
@keyframes kpiEcgDash {
    to { stroke-dashoffset: -200; }
}
.kpi-ecg-line {
    stroke-dasharray: 40 160;
    stroke-dashoffset: 0;
    animation: kpiEcgDash 2s linear infinite;
}

/* Stopwatch tick */
@keyframes kpiStopwatchTick {
    from { transform: rotate(0); }
    to   { transform: rotate(360deg); }
}
.kpi-stopwatch-hand {
    transform-origin: center;
    animation: kpiStopwatchTick 4s linear infinite;
}

/* Megaphone shake */
@keyframes kpiMegaphoneShake {
    0%, 100% { transform: rotate(0); }
    10%  { transform: rotate(-8deg); }
    20%  { transform: rotate(8deg); }
    30%  { transform: rotate(-5deg); }
    40%  { transform: rotate(5deg); }
    50%  { transform: rotate(0); }
}
.kpi-megaphone-shake { animation: kpiMegaphoneShake 3s ease-in-out infinite; }

/* Dark mode overrides */
.dark .kpi-card-v2 {
    background: rgba(30,41,59,0.8);
    border-color: rgba(255,255,255,0.06);
    box-shadow: 0 4px 24px rgba(0,0,0,0.3);
}
.dark .kpi-card-v2:hover {
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
}
.dark .kpi-card-v2 .kpi-title { color: #cbd5e1; }
.dark .kpi-card-v2 .kpi-value { color: #f1f5f9; }
.dark .kpi-card-v2 .kpi-sub   { color: #94a3b8; }

/* Availability bar */
.kpi-availability-track {
    height: 8px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.75rem;
}
.dark .kpi-availability-track { background: rgba(255,255,255,0.1); }
.kpi-availability-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #10b981, #34d399);
    transition: width 1s cubic-bezier(.4,0,.2,1);
    position: relative;
}
.kpi-availability-fill::after {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0;
    width: 30px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4));
    border-radius: 999px;
    animation: kpiBarShine 2s ease-in-out infinite;
}
@keyframes kpiBarShine {
    0%, 100% { opacity: 0; }
    50%      { opacity: 1; }
}
</style>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-3 gap-5 mb-8">

    {{-- Card 1: Total Équipements — Microscope/Circuit icon --}}
    <a href="{{ $equipementsUrl }}" class="kpi-card-v2 kpi-blue animate-fade-in block" style="animation-delay: 0s" data-kpi-card="equipements" title="Ouvrir Équipements">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Total Équipements</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-total-equipements" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ $kpiValues['total_equipements'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Équipements importés</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #dbeafe, #c7d2fe); color: #4f46e5;">
                <div class="kpi-icon-pulse" style="color: #4f46e5;"></div>
                {{-- Custom SVG: Circuit board / medical equipment --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="4" width="16" height="16" rx="3"/>
                    <circle cx="9" cy="9" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="9" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="9" cy="15" r="1.5" fill="currentColor" stroke="none"/>
                    <circle cx="15" cy="15" r="1.5" fill="currentColor" stroke="none"/>
                    <line x1="9" y1="10.5" x2="9" y2="13.5"/>
                    <line x1="15" y1="10.5" x2="15" y2="13.5"/>
                    <line x1="10.5" y1="9" x2="13.5" y2="9"/>
                    <line x1="10.5" y1="15" x2="13.5" y2="15"/>
                    <line x1="12" y1="1" x2="12" y2="4"/>
                    <line x1="12" y1="20" x2="12" y2="23"/>
                    <line x1="1" y1="12" x2="4" y2="12"/>
                    <line x1="20" y1="12" x2="23" y2="12"/>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 2: OT/DM en Cours — Animated gear --}}
    <a href="{{ $interventionsOpenUrl }}" class="kpi-card-v2 kpi-cyan animate-fade-in block" style="animation-delay: 0.08s" data-kpi-card="interventions-cours" title="Ouvrir OT/DM en cours">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">OT/DM en Cours</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-interventions-cours" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ $kpiValues['interventions_en_cours'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Interventions ouvertes</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #cffafe, #a5f3fc); color: #0891b2;">
                <div class="kpi-icon-pulse" style="color: #0891b2;"></div>
                {{-- Custom SVG: Spinning gear with wrench cutout --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <g class="kpi-gear-spin" style="transform-origin: 12px 12px;">
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        <circle cx="12" cy="12" r="5"/>
                    </g>
                    <circle cx="12" cy="12" r="2" fill="currentColor" stroke="none"/>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 3: OT/DM en Retard — Hourglass with alert --}}
    <a href="{{ $interventionsLateUrl }}" class="kpi-card-v2 kpi-red animate-fade-in block" style="animation-delay: 0.16s" data-kpi-card="interventions-retard" title="Ouvrir OT/DM en retard">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">OT/DM en Retard</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-interventions-retard" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ $kpiValues['interventions_en_retard'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Retard &gt; 2 jours</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;">
                <div class="kpi-icon-pulse" style="color: #dc2626;"></div>
                {{-- Custom SVG: Hourglass + alert --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <g class="kpi-hourglass-flip" style="transform-origin: 12px 12px;">
                        <path d="M6.5 2h11M6.5 22h11"/>
                        <path d="M7 2a5 5 0 0 0 5 6 5 5 0 0 0 5-6"/>
                        <path d="M7 22a5 5 0 0 1 5-6 5 5 0 0 1 5 6"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                    </g>
                    {{-- Alert dot --}}
                    <circle cx="20" cy="4" r="3" fill="#ef4444" stroke="white" stroke-width="1.5"/>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 3 bis: Equipements en panne (critique + total) --}}
    <a href="{{ $equipementsPanneUrl }}" class="kpi-card-v2 kpi-red animate-fade-in block" style="animation-delay: 0.2s" data-kpi-card="equipements-panne" title="Ouvrir équipements en panne">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Équipements en panne</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 class="kpi-value text-3xl font-extrabold text-gray-900 tabular-nums">
                    <span id="kpi-equipements-panne-total" class="kpi-counter" data-target="{{ (int) $kpiValues['equipements_panne_total'] }}">0</span>
                </h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">
                    Critiques: <span id="kpi-equipements-panne-critique" class="font-bold text-red-600 tabular-nums">{{ (int) $kpiValues['equipements_panne_critique'] }}</span>
                    · Total: <span class="font-bold text-gray-700" id="kpi-equipements-panne-total-inline">{{ (int) $kpiValues['equipements_panne_total'] }}</span>
                </p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;">
                <div class="kpi-icon-pulse" style="color: #dc2626;"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 4: Disponibilité — ECG heartbeat --}}
    <a href="{{ $availabilityUrl }}" class="kpi-card-v2 kpi-green animate-fade-in block" style="animation-delay: 0.24s" data-kpi-card="disponibilite" title="Ouvrir disponibilité équipements">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Disponibilité</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="kpi-value text-3xl font-extrabold text-gray-900">
                    <span id="kpi-disponibilite" class="kpi-counter tabular-nums" data-target="{{ $kpiValues['disponibilite'] }}">0</span><span class="text-xl">%</span>
                </h3>
                <div class="kpi-availability-track">
                    <div id="kpi-disponibilite-bar" class="kpi-availability-fill" style="width: {{ max(0, min(100, (int) $kpiValues['disponibilite'])) }}%"></div>
                </div>
            </div>
            <div class="kpi-icon-box ml-3" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #059669;">
                <div class="kpi-icon-pulse" style="color: #059669;"></div>
                {{-- Custom SVG: ECG Heartbeat monitor --}}
                <svg viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="24" height="20" rx="4" stroke-width="1.5"/>
                    <polyline class="kpi-ecg-line" points="4,16 8,16 10,10 12,20 14,8 16,18 18,14 20,16 24,16" stroke-width="2" fill="none"/>
                    <circle cx="22" cy="7" r="2" fill="#10b981" stroke="none" opacity="0.8">
                        <animate attributeName="opacity" values="0.8;0.2;0.8" dur="1.5s" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 5: Planning Sociétés — Calendar --}}
    <a href="{{ $planningQuickUrl }}" class="kpi-card-v2 kpi-amber animate-fade-in block" style="animation-delay: 0.32s" data-kpi-card="planning-societes" title="Ouvrir Planning Sociétés (2 semaines)">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
                <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Planning societes</p>
                <div class="relative group">
                    <span class="text-gray-300 hover:text-gray-500 transition-colors" aria-hidden="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                    </span>
                    <div class="pointer-events-none absolute left-1/2 -translate-x-1/2 mt-2 w-64 rounded-xl bg-gray-900 text-white text-xs p-3 opacity-0 group-hover:opacity-100 transition-opacity z-20 shadow-xl">
                        Nombre de sociétés externes planifiées dans les 2 prochaines semaines. Cliquez pour ouvrir le planning filtré.
                    </div>
                </div>
            </div>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0 pr-2">
                <h3 id="kpi-planning-societes-count" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ $upcomingCompaniesCount }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Sociétés (2 semaines)</p>
                <p id="kpi-planning-societes-next" class="text-xs text-gray-500 mt-1 font-medium truncate" title="{{ $nextCompanyInfo }}">Prochaine: {{ $nextCompanyInfo }}</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706;">
                <div class="kpi-icon-pulse" style="color: #d97706;"></div>
                {{-- Custom SVG: Calendar + check --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2" ry="2"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="M9 15l2 2 4-4"/>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 6: Réclamations ouvertes — Megaphone/Alert --}}
    <a href="{{ $reclamationsUrl }}" class="kpi-card-v2 kpi-violet animate-fade-in block" style="animation-delay: 0.4s" data-kpi-card="reclamations" title="Ouvrir Réclamations">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Réclamations</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-reclamations-ouvertes" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ $kpiValues['reclamations_ouvertes'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Ouvertes + En cours</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed;">
                <div class="kpi-icon-pulse" style="color: #7c3aed;"></div>
                {{-- Custom SVG: Megaphone with notification waves --}}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="kpi-megaphone-shake" style="transform-origin: 12px 12px;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    {{-- Sound waves --}}
                    <path d="M21 5c1 1.5 1 3.5 0 5" opacity="0.5">
                        <animate attributeName="opacity" values="0.5;1;0.5" dur="1.5s" repeatCount="indefinite"/>
                    </path>
                    <path d="M23.5 3c2 3 2 6.5 0 9.5" opacity="0.3">
                        <animate attributeName="opacity" values="0.3;0.7;0.3" dur="1.5s" repeatCount="indefinite" begin="0.3s"/>
                    </path>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 7: Préventives à venir --}}
    <a href="{{ $preventivesUrl }}" class="kpi-card-v2 kpi-green animate-fade-in block" style="animation-delay: 0.48s" data-kpi-card="preventives-avenir" title="Ouvrir Maintenance préventive">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Préventives à venir</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-preventives-avenir" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ (int) $kpiValues['maintenances_preventives_a_venir'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Maintenances actives planifiées</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;">
                <div class="kpi-icon-pulse" style="color: #16a34a;"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <path d="M9 15l2 2 4-4"></path>
                </svg>
            </div>
        </div>
    </a>

    {{-- Card 8: Pièces en stock faible --}}
    <a href="{{ $stockUrl }}" class="kpi-card-v2 kpi-red animate-fade-in block" style="animation-delay: 0.56s" data-kpi-card="stock-faible" title="Ouvrir Pièces de rechange">
        <div class="flex items-center justify-between mb-3">
            <p class="kpi-title text-gray-500 text-xs font-bold uppercase tracking-wider">Stock faible</p>
            <span class="kpi-live-dot" title="Temps réel"></span>
        </div>
        <div class="flex items-start justify-between">
            <div>
                <h3 id="kpi-stock-faible" class="kpi-value text-3xl font-extrabold text-gray-900 kpi-counter tabular-nums" data-target="{{ (int) $kpiValues['pieces_stock_faible'] }}">0</h3>
                <p class="kpi-sub text-xs text-gray-400 mt-1 font-medium">Pièces ≤ 5 unités</p>
            </div>
            <div class="kpi-icon-box" style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;">
                <div class="kpi-icon-pulse" style="color: #dc2626;"></div>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <path d="M3.3 7l8.7 5 8.7-5"></path>
                    <line x1="12" y1="22" x2="12" y2="12"></line>
                </svg>
            </div>
        </div>
    </a>

</div>

<script>
(function() {
    'use strict';

    // Store previous values for change detection
    const _prevKpi = {
        total_equipements: {{ (int) $kpiValues['total_equipements'] }},
        interventions_en_cours: {{ (int) $kpiValues['interventions_en_cours'] }},
        interventions_en_retard: {{ (int) $kpiValues['interventions_en_retard'] }},
        equipements_panne_total: {{ (int) $kpiValues['equipements_panne_total'] }},
        equipements_panne_critique: {{ (int) $kpiValues['equipements_panne_critique'] }},
        disponibilite: {{ (int) $kpiValues['disponibilite'] }},
        planning_societes_a_venir: {{ (int) $kpiValues['planning_societes_a_venir'] }},
        planning_prochaine_info: @json($nextCompanyInfo),
        reclamations_ouvertes: {{ (int) $kpiValues['reclamations_ouvertes'] }},
        maintenances_preventives_a_venir: {{ (int) $kpiValues['maintenances_preventives_a_venir'] }},
        pieces_stock_faible: {{ (int) $kpiValues['pieces_stock_faible'] }},
        mtbf_preventif_heures: {{ (float) $kpiValues['mtbf_preventif_heures'] }},
        mtbf_curatif_heures: {{ (float) $kpiValues['mtbf_curatif_heures'] }},
    };

    // Flash animation when a card value changes
    function flashCard(cardSelector, colorClass) {
        const card = document.querySelector('[data-kpi-card="' + cardSelector + '"]');
        if (!card) return;
        card.classList.remove('kpi-flash-' + colorClass);
        void card.offsetWidth;  // reflow
        card.classList.add('kpi-flash-' + colorClass);
        card.addEventListener('animationend', function handler() {
            card.classList.remove('kpi-flash-' + colorClass);
            card.removeEventListener('animationend', handler);
        });
    }

    // Bump animation on the value element
    function bumpValue(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;
        el.classList.remove('kpi-value-bump');
        void el.offsetWidth;
        el.classList.add('kpi-value-bump');
        el.addEventListener('animationend', function handler() {
            el.classList.remove('kpi-value-bump');
            el.removeEventListener('animationend', handler);
        });
    }

    // Smooth animated counter from old to new value
    function animateValue(elementId, from, to, duration, decimals) {
        const el = document.getElementById(elementId);
        if (!el) return;
        if (from === to) return;

        const precision = Number.isFinite(decimals) ? decimals : 0;
        const factor = Math.pow(10, precision);
        const format = function(value) {
            return Number(value).toLocaleString('fr-FR', {
                minimumFractionDigits: precision,
                maximumFractionDigits: precision,
            });
        };

        const startTime = performance.now();
        const diff = to - from;

        function step(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round((from + diff * eased) * factor) / factor;
            el.textContent = format(current);
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = format(to);
            }
        }
        requestAnimationFrame(step);
    }

    window.updateDashboardKpi = function (kpi) {
        const values = {
            total_equipements: parseInt(kpi.total_equipements ?? 0, 10),
            interventions_en_cours: parseInt(kpi.interventions_en_cours ?? 0, 10),
            interventions_en_retard: parseInt(kpi.interventions_en_retard ?? 0, 10),
            equipements_panne_total: parseInt(kpi.equipements_panne_total ?? 0, 10),
            equipements_panne_critique: parseInt(kpi.equipements_panne_critique ?? 0, 10),
            disponibilite: parseInt(kpi.disponibilite ?? 0, 10),
            planning_societes_a_venir: parseInt(kpi.planning_societes_a_venir ?? 0, 10),
            planning_prochaine_societe: String(kpi.planning_prochaine_societe ?? 'Aucune société planifiée'),
            planning_prochaine_date_label: String(kpi.planning_prochaine_date_label ?? '—'),
            reclamations_ouvertes: parseInt(kpi.reclamations_ouvertes ?? 0, 10),
            maintenances_preventives_a_venir: parseInt(kpi.maintenances_preventives_a_venir ?? 0, 10),
            pieces_stock_faible: parseInt(kpi.pieces_stock_faible ?? 0, 10),
            mtbf_preventif_heures: parseFloat(kpi.mtbf_preventif_heures ?? kpi.mtbf_heures ?? 0),
            mtbf_curatif_heures: parseFloat(kpi.mtbf_curatif_heures ?? 0),
        };

        // Detect changes & flash + animate
        const checks = [
            { key: 'total_equipements',      elId: 'kpi-total-equipements',      card: 'equipements',         color: 'blue' },
            { key: 'interventions_en_cours',  elId: 'kpi-interventions-cours',    card: 'interventions-cours', color: 'cyan' },
            { key: 'interventions_en_retard', elId: 'kpi-interventions-retard',   card: 'interventions-retard',color: 'red' },
            { key: 'equipements_panne_total', elId: 'kpi-equipements-panne-total', card: 'equipements-panne', color: 'red' },
            { key: 'disponibilite',           elId: 'kpi-disponibilite',          card: 'disponibilite',       color: 'green' },
            { key: 'planning_societes_a_venir', elId: 'kpi-planning-societes-count', card: 'planning-societes', color: 'amber' },
            { key: 'reclamations_ouvertes',   elId: 'kpi-reclamations-ouvertes',  card: 'reclamations',        color: 'violet' },
            { key: 'maintenances_preventives_a_venir', elId: 'kpi-preventives-avenir', card: 'preventives-avenir', color: 'green' },
            { key: 'pieces_stock_faible',     elId: 'kpi-stock-faible',          card: 'stock-faible',        color: 'red' },
        ];

        checks.forEach(function(c) {
            const oldVal = _prevKpi[c.key];
            const newVal = values[c.key];
            if (oldVal !== newVal) {
                flashCard(c.card, c.color);
                bumpValue(c.elId);
                animateValue(c.elId, oldVal, newVal, 800, c.decimals ?? 0);
                const el = document.getElementById(c.elId);
                if (el) el.setAttribute('data-target', newVal.toString());
            } else {
                // No change — just set text
                const el = document.getElementById(c.elId);
                if (el) {
                    const decimals = c.decimals ?? 0;
                    el.textContent = Number(newVal).toLocaleString('fr-FR', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals,
                    });
                }
            }
        });

        // Availability bar
        const availabilityBar = document.getElementById('kpi-disponibilite-bar');
        if (availabilityBar) {
            availabilityBar.style.width = Math.max(0, Math.min(100, values.disponibilite)) + '%';
        }

        const planningInfoNode = document.getElementById('kpi-planning-societes-next');
        const planningNextInfo = (values.planning_prochaine_date_label + ' • ' + values.planning_prochaine_societe).trim();
        if (planningInfoNode) {
            planningInfoNode.textContent = 'Prochaine: ' + planningNextInfo;
            planningInfoNode.setAttribute('title', planningNextInfo);
        }

        if (_prevKpi.planning_prochaine_info !== planningNextInfo) {
            flashCard('planning-societes', 'amber');
        }

        const criticalNode = document.getElementById('kpi-equipements-panne-critique');
        if (criticalNode) {
            criticalNode.textContent = Number(values.equipements_panne_critique).toLocaleString('fr-FR');
        }

        const totalInlineNode = document.getElementById('kpi-equipements-panne-total-inline');
        if (totalInlineNode) {
            totalInlineNode.textContent = Number(values.equipements_panne_total).toLocaleString('fr-FR');
        }

        if (_prevKpi.equipements_panne_critique !== values.equipements_panne_critique) {
            flashCard('equipements-panne', 'red');
            bumpValue('kpi-equipements-panne-critique');
        }

        // Update previous values
        Object.keys(values).forEach(function(k) { _prevKpi[k] = values[k]; });
        _prevKpi.planning_prochaine_info = planningNextInfo;
    };

    // Initial counter animation on page load
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.kpi-counter');
        counters.forEach(function(counter) {
            const decimals = parseInt(counter.getAttribute('data-decimals') ?? '0', 10);
            const target = parseFloat(counter.getAttribute('data-target') ?? '0');
            if (target === 0) {
                counter.textContent = Number(0).toLocaleString('fr-FR', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });
                return;
            }
            animateValue(counter.id, 0, target, 1800, decimals);
        });
    });
})();
</script>
