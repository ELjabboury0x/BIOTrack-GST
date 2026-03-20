@extends('layouts.dashboard')

@section('page-title', 'Marchés & Équipements')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Marchés & Équipements',
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
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

@if (session('import_result'))
    @php $ir = session('import_result'); @endphp
    @if ($ir['type'] === 'success')
        <div class="mb-5 rounded-xl border-2 border-green-300 bg-gradient-to-r from-green-50 to-emerald-50 shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 bg-green-100 border-b border-green-200">
                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center shadow">
                    <i class="{{ $ir['icon'] }} text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-green-800">{{ $ir['title'] }}</h3>
                    <p class="text-sm text-green-700">{{ $ir['message'] }}</p>
                </div>
            </div>
            @if (!empty($ir['details']))
                <div class="px-5 py-3">
                    <ul class="space-y-1">
                        @foreach ($ir['details'] as $detail)
                            <li class="flex items-center gap-2 text-sm text-green-700">
                                <i class="fas fa-check text-green-500"></i> {{ $detail }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @elseif ($ir['type'] === 'warning')
        <div class="mb-5 rounded-xl border-2 border-yellow-300 bg-gradient-to-r from-yellow-50 to-amber-50 shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 bg-yellow-100 border-b border-yellow-200">
                <div class="w-10 h-10 rounded-full bg-yellow-500 flex items-center justify-center shadow">
                    <i class="{{ $ir['icon'] }} text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-yellow-800">{{ $ir['title'] }}</h3>
                    <p class="text-sm text-yellow-700">{{ $ir['message'] }}</p>
                </div>
            </div>
            @if (!empty($ir['details']) || !empty($ir['tips']))
                <div class="px-5 py-3 space-y-3">
                    @foreach ($ir['details'] ?? [] as $detail)
                        <p class="text-sm text-yellow-700"><i class="fas fa-info-circle mr-1"></i> {{ $detail }}</p>
                    @endforeach
                    @if (!empty($ir['tips']))
                        <div class="bg-yellow-100 rounded-lg p-3">
                            <p class="text-xs font-bold text-yellow-800 mb-1"><i class="fas fa-lightbulb mr-1"></i> Conseils :</p>
                            <ul class="space-y-1">
                                @foreach ($ir['tips'] as $tip)
                                    <li class="text-xs text-yellow-700">• {{ $tip }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div class="mb-5 rounded-xl border-2 border-red-300 bg-gradient-to-r from-red-50 to-rose-50 shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 bg-red-100 border-b border-red-200">
                <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center shadow">
                    <i class="{{ $ir['icon'] }} text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-red-800">{{ $ir['title'] }}</h3>
                    <p class="text-sm text-red-700">{{ $ir['message'] }}</p>
                </div>
            </div>
            @if (!empty($ir['details']) || !empty($ir['tips']))
                <div class="px-5 py-3 space-y-3">
                    @foreach ($ir['details'] ?? [] as $detail)
                        <p class="text-sm text-red-700"><i class="fas fa-times-circle mr-1"></i> {{ $detail }}</p>
                    @endforeach
                    @if (!empty($ir['tips']))
                        <div class="bg-red-100 rounded-lg p-3">
                            <p class="text-xs font-bold text-red-800 mb-2"><i class="fas fa-lightbulb mr-1"></i> Pourquoi ce problème ? Essayez ceci :</p>
                            <ul class="space-y-1">
                                @foreach ($ir['tips'] as $tip)
                                    <li class="text-xs text-red-700 flex items-start gap-1">
                                        <i class="fas fa-arrow-right mt-0.5 text-red-400"></i>
                                        <span>{{ $tip }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endif

@if(auth()->user()?->role !== 'major')
<div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <form method="POST" action="{{ route('markets.equipments.import-excel') }}" enctype="multipart/form-data" class="flex flex-col md:flex-row md:items-end gap-3">
        @csrf
        <div class="w-full md:w-[28rem]">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Importer un ou plusieurs fichiers Excel marché</label>
            <input type="file" name="excel_files[]" accept=".xlsx,.xls" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white" required>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2 bg-green-600 text-white rounded-lg">Importer Excel</button>
        </div>
    </form>
</div>
@endif

@php
    $linesTotal = (int) ($marketSummary['lines_total'] ?? 0);
    $deliveryFilled = (int) ($marketSummary['delivery_filled'] ?? 0);
    $complaintFilled = (int) ($marketSummary['complaint_filled'] ?? 0);
    $deliveryPct = $linesTotal > 0 ? (int) round(($deliveryFilled / $linesTotal) * 100) : 0;
    $complaintPct = $linesTotal > 0 ? (int) round(($complaintFilled / $linesTotal) * 100) : 0;
@endphp

<section class="mb-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3">
    <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Lignes importées</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($linesTotal, 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                <i class="fas fa-table"></i>
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">Total affiché avec filtres actifs</p>
    </div>

    <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">Marchés</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format((int) ($marketSummary['markets_total'] ?? 0), 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                <i class="fas fa-file-signature"></i>
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">Nombre de marchés importés</p>
    </div>

    <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-violet-700 uppercase tracking-wider">Sociétés</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format((int) ($marketSummary['companies_total'] ?? 0), 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center">
                <i class="fas fa-building"></i>
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">Sociétés distinctes</p>
    </div>

    <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Statut livraison</p>
                <p class="mt-2 text-3xl font-bold text-emerald-800">{{ number_format($deliveryFilled, 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                <i class="fas fa-truck"></i>
            </span>
        </div>
        <div class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                <span>Couverture</span>
                <span class="font-semibold text-emerald-700">{{ $deliveryPct }}%</span>
            </div>
            <div class="w-full bg-emerald-100 rounded-full h-2">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $deliveryPct }}%"></div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Statut réclamation</p>
                <p class="mt-2 text-3xl font-bold text-amber-800">{{ number_format($complaintFilled, 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                <i class="fas fa-exclamation-circle"></i>
            </span>
        </div>
        <div class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                <span>Couverture</span>
                <span class="font-semibold text-amber-700">{{ $complaintPct }}%</span>
            </div>
            <div class="w-full bg-amber-100 rounded-full h-2">
                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $complaintPct }}%"></div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5 grid grid-cols-1 lg:grid-cols-3 gap-3">
    <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Lignes sans statuts</p>
                <p class="mt-2 text-3xl font-bold text-rose-800">{{ number_format((int) ($marketSummary['no_status_lines'] ?? 0), 0, ',', ' ') }}</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center">
                <i class="fas fa-ban"></i>
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500">Livraison et réclamation vides sur la même ligne</p>
    </div>

    <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Répartition statuts livraison</p>
            <i class="fas fa-chart-bar text-emerald-500 text-sm"></i>
        </div>
        <div class="space-y-2">
            @forelse(array_slice($marketSummary['delivery_distribution'] ?? [], 0, 4) as $item)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-700 font-medium">{{ $item['label'] }}</span>
                        <span class="text-gray-500">{{ number_format((int) $item['count'], 0, ',', ' ') }} ({{ (int) $item['pct'] }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ (int) $item['pct'] }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-500">Aucune donnée</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Répartition statuts réclamation</p>
            <i class="fas fa-chart-pie text-amber-500 text-sm"></i>
        </div>
        <div class="space-y-2">
            @forelse(array_slice($marketSummary['complaint_distribution'] ?? [], 0, 4) as $item)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-700 font-medium">{{ $item['label'] }}</span>
                        <span class="text-gray-500">{{ number_format((int) $item['count'], 0, ',', ' ') }} ({{ (int) $item['pct'] }}%)</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ (int) $item['pct'] }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-500">Aucune donnée</p>
            @endforelse
        </div>
    </div>
</section>

<div class="mb-4 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-3">
        <h4 class="text-sm font-semibold text-gray-800">Recherche & navigation du tableau</h4>
        @if (!empty($marketsPagination) && $marketsPagination->total() > 0)
            <p class="text-xs text-gray-500">
                Affichage {{ $marketsPagination->firstItem() ?? 0 }}-{{ $marketsPagination->lastItem() ?? 0 }} sur {{ $marketsPagination->total() }} lignes
            </p>
        @endif
    </div>

    <form method="GET" action="{{ route('markets.equipments') }}" class="flex flex-col xl:flex-row xl:items-end gap-3">
        <div class="w-full xl:w-72">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de marché</label>
            <input type="text"
                   name="market_number"
                   value="{{ $marketNumberFilter ?? '' }}"
                   placeholder="Ex: 32/2020"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div class="w-full xl:w-72">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <input type="text"
                   name="company"
                   value="{{ $companyFilter ?? '' }}"
                   placeholder="Ex: MEDICAR"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
        </div>
        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('markets.equipments') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>

    @if (!empty($marketsPagination) && $marketsPagination->hasPages())
        <div class="mt-3 pt-3 border-t border-gray-100">
            {{ $marketsPagination->links() }}
        </div>
    @endif
</div>

<section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-5 py-4 bg-gray-50 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800">Liste marchés & lignes importées</h3>
        <p class="text-sm text-gray-600">Une table unique avec pagination.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° DU MARCHÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OBJETS DU MARCHÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SOCIÉTÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">LOT N°</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ART N°</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DÉSIGNATION</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">QUANTITÉ</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">STATUS DE LIVRAISON</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DATE DE LIVRAISON</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">STATUS DE RÉCLAMATION</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DATE DE RÉCLAMATION</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">OBSERVATIONS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">RECOMMANDATIONS</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse(($marketsData ?? []) as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['market_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['market_object'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['company'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['lot_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['article'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['designation'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['quantity'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['delivery_status'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['delivery_date'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['complaint_status'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['complaint_date'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['observations'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $row['recommendations'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex flex-wrap gap-2 min-w-[240px]">
                                @if(!empty($row['market_id']))
                                    <a href="{{ route('markets.show', $row['market_id']) }}" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100">
                                        <i class="fas fa-eye mr-1"></i> Voir
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('markets.equipments.line.quick-action', $row['line_id']) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="mark_delivered">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-emerald-200 text-emerald-700 bg-emerald-50 hover:bg-emerald-100">
                                        <i class="fas fa-truck mr-1"></i> Livré
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('markets.equipments.line.quick-action', $row['line_id']) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="mark_complaint">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Réclamation
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('markets.equipments.line.quick-action', $row['line_id']) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="clear_statuses">
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 bg-gray-50 hover:bg-gray-100">
                                        <i class="fas fa-eraser mr-1"></i> Reset
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('markets.equipments.line.destroy', $row['line_id']) }}" class="inline" onsubmit="return confirm('Supprimer cette ligne importée ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100">
                                        <i class="fas fa-trash mr-1"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-4 py-4 text-sm text-gray-500 text-center">Aucune donnée de marché importée disponible.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (!empty($marketsPagination) && $marketsPagination->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 bg-white">
            {{ $marketsPagination->links() }}
        </div>
    @endif
</section>
@endsection
