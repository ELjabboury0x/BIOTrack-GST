# GMAO Dashboard - Guide Technique (État Actuel)

## Version
- Date: 2026-03-22
- Statut: Backend connecté + KPI temps réel + vue major synchronisée

## Fonctionnalités actives
- Auth Laravel (accès dashboard protégé)
- Module Équipements connecté MySQL
- Hiérarchie Équipements: Zone → Service → Salle
- Module OT/DM (PM-BIO) connecté MySQL
- Génération automatique des codes OT/DM: `INT-AAAA-0001`
- Registre IW38-BM (liste des codes OT/DM)
- Clôture PM-BIO (SAP-PM): `TECO` / `CLSD`
- KPI + Charts alimentés depuis la base
- Mise à jour temps réel WebSocket + fallback HTTP
- Rôle major en lecture seule stricte (actions métiers bloquées)
- Rafraîchissement automatique des vues major sur changements OT/SAV/Réclamations

## Routes clés
- `GET /dashboard`
- `GET /dashboard/live-metrics`
- `GET /dashboard/equipements`
- `POST /dashboard/equipements`
- `GET /dashboard/interventions`
- `POST /dashboard/interventions`
- `GET /dashboard/interventions/codes` (IW38-BM)
- `GET /dashboard/interventions/{id}/cloture`
- `POST /dashboard/interventions/{id}/cloture`

## Fichiers principaux
- Dashboard: `resources/views/pages/dashboard.blade.php`
- KPI: `resources/views/components/kpi-cards.blade.php`
- Charts: `resources/views/components/charts.blade.php`
- OT/DM: `resources/views/pages/interventions.blade.php`
- IW38-BM: `resources/views/pages/interventions-codes.blade.php`
- Clôture PM-BIO: `resources/views/pages/forms/interventions-close.blade.php`
- Controller Dashboard: `app/Http/Controllers/BiomedDataController.php`
- Controller OT/DM: `app/Http/Controllers/InterventionController.php`
- Service KPI: `app/Services/DashboardMetricsService.php`
- Broadcast KPI: `app/Services/RealtimeMetricsBroadcaster.php`
- Middleware major: `app/Http/Middleware/MajorReadOnly.php`

## Realtime (WebSocket)
- Serveur local: `realtime/server.js`
- WebSocket URL: `ws://127.0.0.1:6001/ws`
- Broadcast bridge: `http://127.0.0.1:6001/broadcast`
- Health check: `http://127.0.0.1:6001/health`

## Démarrage
### Option 1 (recommandée)
- VS Code Task: `Start Full Stack`
- VS Code Task: `Stop Full Stack`

### Option 2 (manuel)
```bash
C:\xampp\php\php.exe artisan serve
npm run realtime
```

## Nomenclature santé
- OT: Ordre de Travail
- DM: Dispositif Médical
- PM-BIO: Maintenance biomédicale
- IW38-BM: Registre des OT/DM (style SAP IW38)
