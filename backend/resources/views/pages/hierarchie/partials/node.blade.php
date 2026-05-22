@php
    $type = (string) ($node['type'] ?? 'service');
    $hasChildren = !empty($node['children']);
    $isFloor = $type === 'etage';
    $isService = $type === 'service';
    $serviceCode = trim((string) ($node['service_code'] ?? $node['code'] ?? ''));
    $equipmentPreview = is_array($node['equipment_preview'] ?? null) ? $node['equipment_preview'] : [];
    $equipmentsUrl = (string) ($node['equipments_url'] ?? route('equipements'));
@endphp

<li
    class="hierarchy-node"
    @if ($isFloor)
        data-floor-level="{{ (int) ($node['code'] ?? 0) }}"
    @endif
    x-data="hierarchyTreeNode(@js($node), {{ $level }})"
>
    @if ($isFloor)
        <button type="button" class="hier-floor-header" @click="toggle()">
            <span class="hier-floor-arrow" :class="{ 'is-open': isExpanded() }">
                <i class="fas fa-chevron-right"></i>
            </span>

            <div class="hier-floor-main">
                <p class="hier-floor-title">{{ $node['name'] }}</p>
                <p class="hier-floor-subtitle">{{ count($node['children'] ?? []) }} service(s)</p>
            </div>

            <div class="hier-floor-metrics">
                <span class="hier-floor-chip">
                    <strong>{{ (int) ($node['equipment_count'] ?? 0) }}</strong>
                    <small>Équipements</small>
                </span>
                <span class="hier-floor-chip">
                    <strong>{{ (int) ($node['affected_equipment_count'] ?? 0) }}</strong>
                    <small>Affectés</small>
                </span>
                <span class="hier-floor-chip">
                    <strong>{{ (int) ($node['breakdown_count'] ?? 0) }}</strong>
                    <small>Pannes</small>
                </span>
                <span class="hier-floor-chip">
                    <strong>{{ (int) ($node['open_ticket_count'] ?? 0) }}</strong>
                    <small>Tickets</small>
                </span>
            </div>
        </button>
    @elseif ($isService)
        <article class="hier-service-row">
            <div class="hier-service-main">
                <div class="hier-service-top">
                    <div class="hier-service-heading">
                        <span class="hier-service-icon" aria-hidden="true">
                            <i class="fas fa-hospital"></i>
                        </span>
                        <h3 class="hier-service-title">{{ $node['name'] }}</h3>
                    </div>

                    <button
                        type="button"
                        class="hier-service-edit-btn"
                        data-edit-structure-id="{{ (int) ($node['structure_id'] ?? $node['id'] ?? 0) }}"
                        data-edit-service-id="{{ (int) ($node['service_id'] ?? 0) }}"
                        data-edit-name="{{ (string) ($node['name'] ?? '') }}"
                        data-edit-code="{{ $serviceCode }}"
                        data-edit-floor-level="{{ (int) ($node['parent_floor_level'] ?? 0) }}"
                        title="Modifier ce service"
                    >
                        <i class="fas fa-pen"></i>
                        <span>Modifier</span>
                    </button>
                </div>
                <p class="hier-service-subtitle">{{ $serviceCode !== '' ? $serviceCode : 'Code non défini' }}</p>

                @if (!empty($equipmentPreview))
                    <details class="hier-service-preview-details">
                        <summary class="hier-service-preview-summary">Aperçu équipements ({{ count($equipmentPreview) }})</summary>
                        <ul class="hier-service-equipment-preview">
                            @foreach ($equipmentPreview as $equipment)
                                @php
                                    $equipmentName = trim((string) ($equipment['name'] ?? 'Équipement'));
                                    $inventoryCode = trim((string) ($equipment['inventory_number'] ?? ''));
                                    $statusLabel = (string) ($equipment['status_label'] ?? 'fonctionnel');
                                    $statusClass = (string) ($equipment['status_class'] ?? 'state-fonctionnel');
                                @endphp
                                <li class="hier-service-equipment-item">
                                    <span class="hier-service-equipment-name">{{ $equipmentName !== '' ? $equipmentName : 'Équipement' }}</span>
                                    <span class="hier-service-equipment-code">{{ $inventoryCode !== '' ? $inventoryCode : '-' }}</span>
                                    <span class="hier-service-equipment-state {{ $statusClass }}">{{ $statusLabel }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @else
                    <p class="hier-service-empty-equipment">Aucun équipement lié à ce service.</p>
                @endif

                <a href="{{ $equipmentsUrl }}" class="hier-service-equipments-link">
                    Voir tous les équipements
                </a>
            </div>

            <div class="hier-service-metrics">
                <span class="hier-service-chip">
                    <strong>{{ (int) ($node['equipment_count'] ?? 0) }}</strong>
                    <small>Équipements</small>
                </span>
                <span class="hier-service-chip">
                    <strong>{{ (int) ($node['affected_equipment_count'] ?? 0) }}</strong>
                    <small>Affectés</small>
                </span>
                <span class="hier-service-chip hier-service-chip-alert">
                    <strong>{{ (int) ($node['breakdown_count'] ?? 0) }}</strong>
                    <small>Pannes</small>
                </span>
                <span class="hier-service-chip hier-service-chip-ticket">
                    <strong>{{ (int) ($node['open_ticket_count'] ?? 0) }}</strong>
                    <small>Tickets ouverts</small>
                </span>
            </div>
        </article>
    @else
        <article class="hier-equipment-row">
            <i class="fas fa-microchip"></i>
            <span>{{ $node['name'] }}</span>
        </article>
    @endif

    @if ($hasChildren)
        <ul class="hierarchy-children" x-show="isExpanded()">
            @foreach ($node['children'] as $child)
                @include('pages.hierarchie.partials.node', ['node' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @elseif ($isFloor)
        <div class="hier-floor-empty" x-show="isExpanded()">
            Aucun service affecte (0)
        </div>
    @endif
</li>
