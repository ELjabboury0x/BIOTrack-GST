<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export équipements</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; font-size: 11px; color: #1f2937; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header td { text-align: center; vertical-align: middle; }
        .header img { max-height: 52px; max-width: 100%; }
        .header-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
        .header-subtitle { font-size: 10px; color: #334155; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 10px; }
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
    <table class="header">
        <tr>
            <td style="width:24%"><img src="{{ public_path('images/logo-ministry.png') }}" alt="Ministère de la Santé"></td>
            <td style="width:52%">
                <div class="header-title">Liste complète des équipements biomédicaux</div>
                <div class="header-subtitle">Groupement Sanitaire Territorial Tanger-Tétouan-Al Hoceïma</div>
            </td>
            <td style="width:24%"><img src="{{ public_path('images/logo-region.png') }}" alt="GST Région"></td>
        </tr>
    </table>

    <div class="meta">
        <h1>Export équipements</h1>
        <div>Généré le: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° inventaire</th>
                <th>Désignation</th>
                <th>N° de série</th>
                <th>Unité</th>
                <th>Secteur</th>
                <th>Description secteur</th>
                <th>Marque</th>
                <th>Modèle</th>
                <th>Marché</th>
                <th>Lot</th>
                <th>Article</th>
                <th>Date réception provisoire</th>
                <th>Durée garantie</th>
                <th>Date réception définitive</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['barcode'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['serial_number'] }}</td>
                    <td>{{ $row['unit_name'] }}</td>
                    <td>{{ $row['sector_name'] }}</td>
                    <td>{{ $row['sector_description'] }}</td>
                    <td>{{ $row['brand_name'] }}</td>
                    <td>{{ $row['model_name'] }}</td>
                    <td>{{ $row['market_label'] }}</td>
                    <td>{{ $row['lot_number'] }}</td>
                    <td>{{ $row['article'] }}</td>
                    <td>{{ $row['date_reception_provisoire'] }}</td>
                    <td>{{ $row['duree_garantie'] }}</td>
                    <td>{{ $row['date_reception_definitive'] }}</td>
                    <td>{{ $row['operational_status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">Aucun équipement trouvé.</td>
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
