<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export maintenance préventive</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; font-size: 11px; color: #1f2937; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 10px; align-items: baseline; }
        h1 { font-size: 16px; margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; word-wrap: break-word; }
        th { background: #f3f4f6; text-align: left; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="meta">
        <h1>Export maintenance préventive</h1>
        <div>Généré le: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Équipement</th>
                <th>Périodicité</th>
                <th>Dernière maintenance</th>
                <th>Prochaine maintenance</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['code'] ?? '-' }}</td>
                    <td>{{ $row['equipment'] ?? '-' }}</td>
                    <td>{{ $row['periodicite'] ?? '-' }}</td>
                    <td>{{ $row['dernier'] ?? '-' }}</td>
                    <td>{{ $row['prochain'] ?? '-' }}</td>
                    <td>{{ $row['statut'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Aucune maintenance préventive trouvée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 250);
        });
    </script>
</body>
</html>
