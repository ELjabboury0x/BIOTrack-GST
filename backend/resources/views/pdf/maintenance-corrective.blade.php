<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Maintenance corrective - Export</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111827; }
        h1 { font-size: 13px; margin: 0 0 6px 0; }
        .meta { font-size: 9px; color: #4b5563; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #1f2937; padding: 4px 5px; vertical-align: top; word-break: break-word; }
        th { background: #e5e7eb; font-weight: 700; text-align: center; }
        td { text-align: left; }
    </style>
</head>
<body>
    <h1>Maintenance corrective - Export</h1>
    <div class="meta">Généré le {{ $generatedAt ?? '' }}</div>

    <table>
        <thead>
            <tr>
                <th>Société</th>
                <th>Désignation de l'équipement</th>
                <th>Marque</th>
                <th>Modèle</th>
                <th>N° de série</th>
                <th>N° de Marché/Contrat de maintenance</th>
                <th>Détails de la panne</th>
                <th>Observations</th>
                <th>Service(s)</th>
                <th>Date d'intervention</th>
                <th>Activité achevée OUI / NON</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows ?? [] as $row)
                <tr>
                    <td>{{ $row->company_name ?? '-' }}</td>
                    <td>{{ $row->equipment_designation ?? '-' }}</td>
                    <td>{{ $row->brand_name ?? '-' }}</td>
                    <td>{{ $row->model_name ?? '-' }}</td>
                    <td>{{ $row->serial_number ?? '-' }}</td>
                    <td>{{ $row->market_or_contract_ref ?? '-' }}</td>
                    <td>{{ $row->failure_details ?? '-' }}</td>
                    <td>{{ $row->observations ?? '-' }}</td>
                    <td>{{ $row->service_names ?? '-' }}</td>
                    <td>{{ $row->intervention_date_text ?? '-' }}</td>
                    <td>
                        @if (is_null($row->activity_completed))
                            -
                        @elseif ($row->activity_completed)
                            OUI
                        @else
                            NON
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">Aucune donnée disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
