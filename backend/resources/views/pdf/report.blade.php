<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'intervention interne</title>
    <style>
        @page { size: A4 portrait; margin: 5mm; }

        body {
            margin: 0;
            padding: 0;
            font-family: "Times New Roman", DejaVu Serif, serif;
            font-size: 10pt;
            color: #000;
        }

        .sheet {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
            page-break-inside: avoid;
        }

        .sheet td {
            border: 1px solid #000;
            vertical-align: middle;
            padding: 1.4mm 1.8mm;
            word-break: break-word;
            overflow-wrap: anywhere;
            page-break-inside: avoid;
        }

        .no-border { border: none !important; }
        .center { text-align: center; }
        .left { text-align: left; }
        .bold { font-weight: 700; }
        .u { text-decoration: underline; }

        .logo-cell {
            height: 20mm;
            text-align: center;
            vertical-align: middle;
        }

        .logo {
            max-height: 16mm;
            max-width: 96%;
            width: auto;
            height: auto;
        }

        .title {
            font-size: 11.5pt;
            font-weight: 700;
            text-align: center;
            padding: 1.2mm 0;
        }

        .field-row td { font-size: 9.5pt; line-height: 1.1; }
        .field-label { font-weight: 700; }
        .field-value { font-weight: 400; }

        .section-title {
            font-size: 9.8pt;
            font-weight: 700;
            text-decoration: underline;
            text-align: left;
            padding: 1.1mm 1.8mm;
        }

        .text-wrap {
            font-size: 9.3pt;
            line-height: 1.2;
            white-space: pre-wrap;
            vertical-align: top !important;
        }

        .checkbox {
            display: inline-block;
            width: 5.2mm;
            height: 5.2mm;
            border: 1px solid #000;
            text-align: center;
            line-height: 5.2mm;
            font-size: 9pt;
            margin-left: 1.2mm;
            vertical-align: middle;
        }

        .ops-title {
            text-align: center;
            font-size: 9.8pt;
            font-weight: 700;
            text-decoration: underline;
            padding: 1.1mm 0;
        }

        .sig-header {
            text-align: center;
            font-size: 9.8pt;
            font-weight: 700;
            text-decoration: underline;
            padding: 1.1mm 0;
        }

        .sig-box {
            height: 26mm;
            text-align: center;
            vertical-align: middle;
            padding: 1mm;
        }

        .sig-box img {
            max-height: 22mm;
            max-width: 92%;
            width: auto;
            height: auto;
        }

        .description-area { height: 20mm; vertical-align: top !important; }
        .operations-area { height: 28mm; vertical-align: top !important; }
    </style>
</head>
<body>
@php
    $hospitalName = $report->hospital_name ?: 'Hôpital Universitaire Mère-Enfant Mohammed VI - Tanger';
    $isPreventive = (string) $report->intervention_type === 'preventive';
    $isCurative = (string) $report->intervention_type === 'curative';
    $isDiagnostic = (string) $report->intervention_type === 'diagnostic';
    $reportDate = optional($report->intervention_date)->format('d/m/Y') ?: optional($report->started_at)->format('d/m/Y') ?: '-';
    $reportLogoPathLeft = public_path('images/logos/logo1.png');
    $reportLogoPathCenter = public_path('images/logos/report-logo.png');
    $reportLogoPathRight = public_path('images/logos/logo2.png');
    $hasCenterLogo = is_file($reportLogoPathCenter);
@endphp

<table class="sheet" cellspacing="0" cellpadding="0" border="1">
    <tr>
        <td class="logo-cell" style="width:33.33%;"><img class="logo" src="{{ $reportLogoPathLeft }}" alt="Logo 1"></td>
        <td class="logo-cell" style="width:33.33%;">
            @if($hasCenterLogo)
                <img class="logo" src="{{ $reportLogoPathCenter }}" alt="Logo GST">
            @endif
        </td>
        <td class="logo-cell" style="width:33.33%;"><img class="logo" src="{{ $reportLogoPathRight }}" alt="Logo 2"></td>
    </tr>

    <tr>
        <td class="title" colspan="3">Rapport d'intervention interne</td>
    </tr>

    <tr class="field-row">
        <td colspan="3"><span class="field-label">N° du rapport d'intervention:</span> <span class="field-value">{{ $report->report_number ?: '-' }}</span></td>
    </tr>

    <tr class="field-row">
        <td colspan="2"><span class="field-label">Hôpital:</span> <span class="field-value">{{ $hospitalName }}</span></td>
        <td><span class="field-label">Date:</span> <span class="field-value">{{ $reportDate }}</span></td>
    </tr>

    <tr class="field-row">
        <td colspan="3"><span class="field-label">Service:</span> <span class="field-value">{{ $report->service?->name ?: '-' }}</span></td>
    </tr>

    <tr class="field-row">
        <td colspan="3"><span class="field-label">Code Unité/Secteur/Local (GMAO):</span> <span class="field-value">{{ $report->unit_code ?: '-' }}</span></td>
    </tr>

    <tr class="field-row">
        <td colspan="3"><span class="field-label">Désignation de l'équipement:</span> <span class="field-value">{{ $report->equipment_designation ?: $report->equipment?->designation ?: '-' }}</span></td>
    </tr>

    <tr class="field-row">
        <td><span class="field-label">Numéro de série:</span> <span class="field-value">{{ $report->equipment_serial_number ?: $report->equipment?->serial_number ?: '-' }}</span></td>
        <td colspan="2"><span class="field-label">Numéro d'inventaire:</span> <span class="field-value">{{ $report->equipment_inventory_number ?: $report->equipment?->inventory_number_current ?: '-' }}</span></td>
    </tr>

    <tr class="field-row">
        <td><span class="field-label">Fournisseur:</span> <span class="field-value">{{ $report->supplier_name ?: '-' }}</span></td>
        <td><span class="field-label">Marque:</span> <span class="field-value">{{ $report->brand_name ?: '-' }}</span></td>
        <td><span class="field-label">Modèle:</span> <span class="field-value">{{ $report->model_name ?: '-' }}</span></td>
    </tr>

    <tr>
        <td colspan="3" class="section-title">Description du problème:</td>
    </tr>
    <tr>
        <td colspan="3" class="text-wrap description-area">
            {{ $report->problem_description ?: '-' }}
        </td>
    </tr>

    <tr>
        <td colspan="3" class="section-title">Type d'intervention</td>
    </tr>
    <tr class="field-row">
        <td class="center">Préventive <span class="checkbox">{{ $isPreventive ? 'X' : '' }}</span></td>
        <td class="center">Corrective <span class="checkbox">{{ $isCurative ? 'X' : '' }}</span></td>
        <td class="center">Diagnostic <span class="checkbox">{{ $isDiagnostic ? 'X' : '' }}</span></td>
    </tr>

    <tr>
        <td colspan="3" class="ops-title">Opérations effectuées</td>
    </tr>
    <tr>
        <td colspan="3" class="text-wrap operations-area">
            {{ $report->operations_performed ?: '-' }}
        </td>
    </tr>

    <tr>
        <td class="sig-header">Administrateur biomédical</td>
        <td colspan="2" class="sig-header">Major du service (ou intérim)</td>
    </tr>
    <tr>
        @php
            $techSigPath = ltrim((string) ($report->technician_signature_path ?? ''), '/');
            $engSigPath = ltrim((string) ($report->engineer_signature_path ?? ''), '/');
            if (str_starts_with($techSigPath, 'public/')) {
                $techSigPath = substr($techSigPath, strlen('public/'));
            }
            if (str_starts_with($engSigPath, 'public/')) {
                $engSigPath = substr($engSigPath, strlen('public/'));
            }
            $techSigFullPath = $techSigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($techSigPath)
                ? \Illuminate\Support\Facades\Storage::disk('public')->path($techSigPath)
                : null;
            $engSigFullPath = $engSigPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($engSigPath)
                ? \Illuminate\Support\Facades\Storage::disk('public')->path($engSigPath)
                : null;
        @endphp
        <td class="sig-box">
            @if($techSigFullPath)
                <img src="{{ $techSigFullPath }}" alt="">
            @endif
        </td>
        <td colspan="2" class="sig-box">
            @if($engSigFullPath)
                <img src="{{ $engSigFullPath }}" alt="">
            @endif
        </td>
    </tr>
</table>
</body>
</html>
