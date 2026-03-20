<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Formations PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        .meta { font-size: 11px; color: #4b5563; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Catalogue des formations PDF</h1>
    <div class="meta">
        Généré le: {{ $generatedAt ?? '-' }}
        @if (($search ?? '') !== '')
            | Filtre désignation: {{ $search }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30%;">Désignation</th>
                <th style="width: 28%;">Formation technique</th>
                <th style="width: 28%;">Formation utilisateur</th>
                <th style="width: 14%;">Mise à jour</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($rows ?? []) as $row)
                <tr>
                    <td>{{ $row['designation'] ?? '-' }}</td>
                    <td>{{ $row['technical_file'] ?? '-' }}</td>
                    <td>{{ $row['user_file'] ?? '-' }}</td>
                    <td>{{ $row['updated_at'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">Aucune formation PDF disponible.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
