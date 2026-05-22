# API / Intégration - État Réel du Projet

## Version
- Date: 2026-05-22
- Scope: Endpoints réellement implémentés

## Notes importantes
- Le projet utilise majoritairement des routes web Laravel (Blade + contrôleurs).
- Le temps réel KPI est assuré par WebSocket local avec fallback HTTP.

## Endpoints implémentés

### Dashboard
- `GET /dashboard`
- `GET /dashboard/live-metrics`

### Équipements
- `GET /dashboard/equipements`
- `GET /dashboard/equipements/create`
- `POST /dashboard/equipements`
- `GET /dashboard/equipements/services?zone_id={id}`
- `GET /dashboard/equipements/salles?service_id={id}`

### OT/DM (PM-BIO)
- `GET /dashboard/interventions`
- `GET /dashboard/interventions/create`
- `POST /dashboard/interventions`
- `GET /dashboard/interventions/codes` (IW38-BM)
- `GET /dashboard/interventions/{id}/cloture`
- `POST /dashboard/interventions/{id}/cloture`

## Payload live KPI
Réponse `GET /dashboard/live-metrics`:
```json
{
  "kpi": {
    "total_equipements": 0,
    "interventions_en_cours": 0,
    "interventions_en_retard": 0,
    "disponibilite": 0,
    "cout_total": 0
  },
  "charts": {
    "interventions": { "labels": [], "preventive": [], "curative": [] },
    "maintenance_types": { "labels": [], "data": [] },
    "equipments_added": { "labels": [], "data": [] }
  },
  "hasData": false,
  "updated_at": "2026-05-22 00:00:00"
}
```

## WebSocket Realtime
- URL: `ws://127.0.0.1:6001/ws`
- Channel: `dashboard.metrics`
- Message:
```json
{
  "channel": "dashboard.metrics",
  "payload": {
    "kpi": {},
    "charts": {},
    "hasData": true,
    "updated_at": "2026-05-22 00:00:00"
  }
}
```

## Dépannage
- Si le socket est indisponible, le dashboard utilise le fallback HTTP (`/dashboard/live-metrics`).
- Vérifier le serveur WS:
```bash
npm run realtime
# puis
curl http://127.0.0.1:6001/health
```
