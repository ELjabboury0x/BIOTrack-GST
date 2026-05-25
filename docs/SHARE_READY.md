# Share Readiness Guide

_Mise à jour : 2026-05-25_

## Architecture Snapshot

- **Framework:** Laravel 9 (PHP backend)
- **UI:** Blade + Tailwind CSS + Alpine.js
- **Database:** MySQL via Eloquent models/migrations
- **Realtime:** Node.js WebSocket server at `realtime/server.js` (`ws`)
- **Domain Modules:**
  - Authentication + role-based dashboard
  - Équipements management
  - OT/DM interventions and close workflow
  - Complaints and notifications
  - Reports (including intervention internal reports + PDF export)
  - Marchés & Équipements with Excel import/edit
- **Excel data source:** `excel/` (inside project root)

## Why sharing failed before

You had the Excel files outside the project tree (`Downloads/.../excel`). Only folders inside the shared project root travel with a repo/zip.

## Ready-to-share method (recommended)

Run from project root:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\prepare-share.ps1
```

This generates:

- `dist/share/PFE-share-<timestamp>/` (sanitized folder)
- `dist/share/PFE-share-<timestamp>.zip` (sanitized archive)

## What is excluded automatically

- `.env`, `.env.local`
- `.git`, `.vscode`
- `vendor/`, `node_modules/`
- runtime/debug artifacts under `storage/` and `bootstrap/cache/`
- generated logs (`*.log`)

## Recipient setup steps

1. Extract shared zip.
2. Copy `.env.example` to `.env`.
3. Set DB + app values (`APP_KEY`, DB credentials, `REALTIME_SECRET`).
4. Install dependencies:
   - `composer install`
   - `npm install`
5. Run migrations and start services:
   - `php artisan key:generate`
   - `php artisan migrate`
   - `php artisan serve`
   - `npm run realtime`

## Final pre-share checklist

- [ ] `excel/` exists in project root
- [ ] `.env` is not included in package
- [ ] No logs/debug runtime files in package
- [ ] `vendor/` and `node_modules/` excluded
- [ ] Recipient can run setup from README/this guide
