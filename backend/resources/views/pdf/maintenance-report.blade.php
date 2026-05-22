<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport intervention interne</title>
    <style>
        @page { margin: 14mm 11mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #0f172a; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header td { vertical-align: middle; text-align: center; }
        .header img { max-height: 54px; max-width: 100%; }
        .header-title { font-size: 14px; font-weight: 700; letter-spacing: .2px; }
        .header-subtitle { font-size: 10px; color: #334155; margin-top: 2px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { border: 1px solid #334155; padding: 6px; }
        .meta-label { display: block; font-size: 9px; color: #475569; margin-bottom: 2px; }
        .meta-value { font-size: 11px; font-weight: 700; }

        .section { margin-top: 8px; }
        .section-title {
            background: #e2e8f0;
            border: 1px solid #334155;
            border-bottom: none;
            padding: 5px 7px;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid td, .grid th { border: 1px solid #334155; padding: 6px; vertical-align: top; }
        .label { font-weight: 700; color: #1e293b; display: block; margin-bottom: 2px; }
        .value { color: #111827; }

        .check { display: inline-block; width: 12px; height: 12px; border: 1px solid #0f172a; text-align: center; line-height: 12px; font-size: 9px; margin-right: 5px; }
        .muted { color: #64748b; font-size: 9px; }

        .text-block { min-height: 95px; white-space: pre-wrap; line-height: 1.35; }
        .text-block-lg { min-height: 130px; white-space: pre-wrap; line-height: 1.35; }

        .sign-box { height: 74px; border: 1px solid #94a3b8; margin-top: 5px; text-align: center; }
        .sign-box img { max-height: 70px; max-width: 95%; }

        .footer-note { margin-top: 8px; text-align: right; color: #475569; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $hospitalName = $report->hospital_name ?: 'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger';
        $reportStatus = strtoupper((string) ($report->status ?: '-'));
        $scope = ucfirst((string) ($report->intervention_scope ?: 'interne'));
        $type = (string) ($report->intervention_type ?: 'curative');
        $isPreventive = $type === 'preventive';
        $isCurative = $type === 'curative';
        $reportLogoPath = public_path('images/logo gst.png');
    @endphp

    <table class="header">
        <tr>
            <td style="width:24%">
                <img src="{{ $reportLogoPath }}" alt="Logo GST">
            </td>
            <td style="width:52%">
                <div class="header-title">Rapport d'intervention de maintenance</div>
                <div class="header-subtitle">Groupement Sanitaire Territorial Tanger-Tétouan-Al Hoceïma</div>
            </td>
            <td style="width:24%">
                <img src="{{ $reportLogoPath }}" alt="Logo GST">
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td style="width:26%">
                <span class="meta-label">N° Rapport</span>
                <span class="meta-value">{{ $report->report_number }}</span>
            </td>
            <td style="width:20%">
                <span class="meta-label">Date intervention</span>
                <span class="meta-value">{{ optional($report->intervention_date)->format('d/m/Y') ?: '-' }}</span>
            </td>
            <td style="width:22%">
                <span class="meta-label">Statut</span>
                <span class="meta-value">{{ $reportStatus }}</span>
            </td>
            <td style="width:16%">
                <span class="meta-label">Type</span>
                <span class="meta-value">{{ $isPreventive ? 'Préventive' : 'Corrective' }}</span>
            </td>
            <td style="width:16%">
                <span class="meta-label">Périmètre</span>
                <span class="meta-value">{{ $scope }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Localisation et organisation</div>
    <table class="grid">
        <tr>
            <td style="width:40%"><span class="label">Hôpital</span><span class="value">{{ $hospitalName }}</span></td>
            <td style="width:35%"><span class="label">Service</span><span class="value">{{ $report->service?->name ?: '-' }}</span></td>
            <td style="width:25%"><span class="label">Code unité / secteur</span><span class="value">{{ $report->unit_code ?: '-' }}</span></td>
        </tr>
    </table>

    <div class="section-title">Identification de l'équipement</div>
    <table class="grid">
        <tr>
            <td colspan="2"><span class="label">Désignation</span><span class="value">{{ $report->equipment_designation ?: $report->equipment?->designation ?: '-' }}</span></td>
        </tr>
        <tr>
            <td style="width:50%"><span class="label">N° série</span><span class="value">{{ $report->equipment_serial_number ?: $report->equipment?->serial_number ?: '-' }}</span></td>
            <td style="width:50%"><span class="label">N° inventaire</span><span class="value">{{ $report->equipment_inventory_number ?: $report->equipment?->inventory_number_current ?: '-' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Fournisseur</span><span class="value">{{ $report->supplier_name ?: '-' }}</span></td>
            <td>
                <span class="label">Marque / Modèle</span>
                <span class="value">{{ ($report->brand_name ?: '-') . ' / ' . ($report->model_name ?: '-') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Chronologie d'intervention</div>
    <table class="grid">
        <tr>
            <td style="width:25%"><span class="label">Début</span><span class="value">{{ optional($report->started_at)->format('d/m/Y H:i') ?: '-' }}</span></td>
            <td style="width:25%"><span class="label">Fin</span><span class="value">{{ optional($report->ended_at)->format('d/m/Y H:i') ?: '-' }}</span></td>
            <td style="width:20%"><span class="label">Durée</span><span class="value">{{ $report->duration_minutes ? $report->duration_minutes . ' min' : '-' }}</span></td>
            <td style="width:30%"><span class="label">Validation / Clôture</span><span class="value">{{ optional($report->validated_at)->format('d/m/Y H:i') ?: '-' }} / {{ optional($report->closed_at)->format('d/m/Y H:i') ?: '-' }}</span></td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">Nature de l'intervention</span>
                <span class="check">{{ $isPreventive ? 'X' : '' }}</span> Préventive
                &nbsp;&nbsp;&nbsp;
                <span class="check">{{ $isCurative ? 'X' : '' }}</span> Corrective
                &nbsp;&nbsp;&nbsp;
                <span class="check">{{ $scope === 'Externe' ? 'X' : '' }}</span> Externe
                &nbsp;&nbsp;&nbsp;
                <span class="check">{{ $scope === 'Interne' ? 'X' : '' }}</span> Interne
            </td>
        </tr>
    </table>

    <div class="section-title">Description du problème</div>
    <table class="grid">
        <tr>
            <td class="text-block">{{ $report->problem_description ?: '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Opérations effectuées</div>
    <table class="grid">
        <tr>
            <td class="text-block-lg">{{ $report->operations_performed ?: '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Signatures</div>
    <table class="grid">
        <tr>
            <td style="width:50%">
                <span class="label">Administrateur biomédical</span>
                <span class="value">{{ $report->technician?->name ?: $report->technician?->login ?: '-' }}</span>
                @php
                    $techSigPath = ltrim((string) ($report->technician_signature_path ?? ''), '/');
                    if (str_starts_with($techSigPath, 'public/')) {
                        $techSigPath = substr($techSigPath, strlen('public/'));
                    }
                    $techSigFullPath = $techSigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($techSigPath)
                        ? \Illuminate\Support\Facades\Storage::disk('public')->path($techSigPath)
                        : null;
                @endphp
                <div class="sign-box">
                    @if($techSigFullPath)
                        <img src="{{ $techSigFullPath }}" alt="">
                    @endif
                </div>
            </td>
            <td style="width:50%">
                <span class="label">Major / Responsable service</span>
                <span class="value">{{ $report->engineer?->name ?: $report->engineer?->login ?: '-' }}</span>
                @php
                    $engSigPath = ltrim((string) ($report->engineer_signature_path ?? ''), '/');
                    if (str_starts_with($engSigPath, 'public/')) {
                        $engSigPath = substr($engSigPath, strlen('public/'));
                    }
                    $engSigFullPath = $engSigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($engSigPath)
                        ? \Illuminate\Support\Facades\Storage::disk('public')->path($engSigPath)
                        : null;
                @endphp
                <div class="sign-box">
                    @if($engSigFullPath)
                        <img src="{{ $engSigFullPath }}" alt="">
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @if(is_array($report->photo_paths) && count($report->photo_paths) > 0)
        <div class="section-title">Pièces jointes (photos)</div>
        <table class="grid">
            <tr>
                <td class="muted">{{ implode(' | ', $report->photo_paths) }}</td>
            </tr>
        </table>
    @endif

    <div class="footer-note">Document généré le {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
