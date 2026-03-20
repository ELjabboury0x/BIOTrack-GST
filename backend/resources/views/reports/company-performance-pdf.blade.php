<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des interventions des sociétés externes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>
    <h1>Rapport des interventions des sociétés externes</h1>
    <div class="meta">Généré le {{ $generatedAt ?? '-' }}</div>

    <h3>KPI par société</h3>
    <table>
        <thead>
            <tr>
                <th>Société</th>
                <th>Total interventions</th>
                <th>MTTR</th>
                <th>Pannes</th>
                <th>Pannes répétées</th>
                <th>Taux résolution</th>
                <th>Disponibilité équipements</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($kpis ?? []) as $item)
                <tr>
                    <td>{{ $item['company_name'] }}</td>
                    <td>{{ $item['total_interventions'] }}</td>
                    <td>{{ $item['mttr_label'] }}</td>
                    <td>{{ $item['pannes_count'] }}</td>
                    <td>{{ $item['repeat_failures_count'] }}</td>
                    <td>{{ $item['resolution_rate'] }}%</td>
                    <td>{{ number_format((float) $item['equipment_availability_rate'], 2) }}%</td>
                </tr>
            @empty
                <tr><td colspan="7">Aucune donnée KPI.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Interventions</h3>
    <table>
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Équipement</th>
                <th>Service</th>
                <th>Société</th>
                <th>Date panne</th>
                <th>Premier appel</th>
                <th>Résolution</th>
                <th>Temps</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($rows ?? []) as $row)
                <tr>
                    <td>{{ $row['ticket_id'] }}</td>
                    <td>{{ $row['equipment'] }}</td>
                    <td>{{ $row['hospital_service'] }}</td>
                    <td>{{ $row['external_company'] }}</td>
                    <td>{{ $row['breakdown_date'] }}</td>
                    <td>{{ $row['first_call'] }}</td>
                    <td>{{ $row['resolution_date'] }}</td>
                    <td>{{ $row['intervention_time'] }}</td>
                    <td>{{ $row['intervention_status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Aucune intervention trouvée.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
