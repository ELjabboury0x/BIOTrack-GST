@php
    $hasChildren = !empty($node['children']);
    $type = $node['type'] ?? 'structure';

    $iconByType = [
        'branche' => 'fa-diagram-project',
        'direction' => 'fa-building',
        'hopital' => 'fa-hospital',
        'batiment' => 'fa-building-columns',
        'etage' => 'fa-layer-group',
        'service' => 'fa-stethoscope',
        'unite' => 'fa-bed-pulse',
        'secteur' => 'fa-vector-square',
        'local' => 'fa-door-open',
        'equipement' => 'fa-microchip',
    ];

    $icon = $iconByType[$type] ?? 'fa-circle-nodes';
    $pannesCount = (int) ($node['interventions_count'] ?? $node['active_breakdowns_count'] ?? 0);
    $showPannesBadge = (bool) ($node['in_sanitary_branch'] ?? false);
@endphp

<li class="tree-item" data-level="{{ $level }}" data-type="{{ $type }}">
    <div class="tree-node tree-type-{{ $type }}">
        <div class="tree-node-main">
            @if ($hasChildren)
                <button type="button" class="tree-toggle" aria-expanded="{{ $level === 1 ? 'true' : 'false' }}" title="Déplier/Replier">
                    <i class="fas fa-chevron-right"></i>
                </button>
            @else
                <span class="tree-toggle tree-toggle-placeholder"></span>
            @endif

            <span class="tree-icon-wrap">
                <i class="fas {{ $icon }} tree-icon"></i>
            </span>

            <div class="tree-text-wrap">
                <h3 class="tree-label">{{ $node['name'] }}</h3>

                @if ($type === 'service')
                    <p class="tree-meta">
                        <span><strong>Code service:</strong> {{ $node['service_code'] ?: ($node['code'] ?: '-') }}</span>
                        <span><strong>Responsable:</strong> {{ $node['responsable'] ?: '-' }}</span>
                    </p>
                    <div class="tree-badges">
                        <span class="tree-badge tree-badge-blue">
                            <i class="fas fa-microchip"></i>
                            {{ (int) ($node['equipment_count'] ?? 0) }} équipements
                        </span>
                        @if ($showPannesBadge)
                            <span class="tree-badge {{ $pannesCount > 0 ? 'tree-badge-red' : 'tree-badge-neutral' }}">
                                <i class="fas fa-triangle-exclamation"></i>
                                {{ $pannesCount }} pannes
                            </span>
                        @endif
                    </div>
                @else
                    @if(!empty($node['responsable']))
                        <p class="tree-meta"><strong>Responsable:</strong> {{ $node['responsable'] }}</p>
                    @endif
                    @if ($showPannesBadge)
                        <div class="tree-badges">
                            <span class="tree-badge {{ $pannesCount > 0 ? 'tree-badge-red' : 'tree-badge-neutral' }}">
                                <i class="fas fa-triangle-exclamation"></i>
                                {{ $pannesCount }} pannes
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if ($type === 'service' && !empty($node['service_id']))
            <a href="{{ route('equipements', ['structure_id' => $node['id']]) }}" class="tree-action-btn">
                <i class="fas fa-microscope"></i>
                <span>Voir équipements</span>
            </a>
        @endif
    </div>

    @if ($hasChildren)
        <ul class="tree-children {{ $level === 1 ? 'is-open' : '' }}" @if ($level !== 1) style="max-height:0;" @endif>
            @foreach ($node['children'] as $child)
                @include('pages.hierarchie.partials.node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>
