# 📖 BioTrackGST — Systéme Intelligent de Gestion de Maintenance Assistée par Ordinateur et de Réclamations Hospitaliéres avec Notification en Temps Réel pour GST Tanger

> **Version 3.1** | 2026-05-22

---

## 1. Architecture

### Vue d'Ensemble
```
┌─────────────────────────────────────────────┐
│                  Client                      │
│  Tailwind CSS + Alpine.js + Chart.js         │
│  SweetAlert2 + Animate.css + modern-ui.js    │
├─────────────────────────────────────────────┤
│               Laravel 9.52                   │
│  Routes → Middleware → Controllers → Views   │
│  Models → Eloquent ORM → MySQL               │
├─────────────────────────────────────────────┤
│            Services & Support                │
│  ServiceAccess │ AppSettingsService           │
│  ServiceVisibilityPolicy │ Events            │
├─────────────────────────────────────────────┤
│              Temps Réel                      │
│  Node.js WebSocket (ws) — Port 6001         │
│  Events: ComplaintCreated → Notification     │
└─────────────────────────────────────────────┘
```

### Pattern MVC
- **Models** (31) : Eloquent avec relations, scopes, accessors
- **Views** : Blade templates avec components, layouts
- **Controllers** (31) : Logique métier, validation, autorisation

### Couche Sécurité (Middlewares)
```
Request → Authenticate → PreventBackHistory → ForcePasswordChange 
        → EnforceAccountSecurity → MajorReadOnly → EnsureUserRole 
        → Controller
```

---

## 2. Système d'Authentification

### Login (`AuthController`)
- Authentification par **login + mot de passe uniquement**
- Le service est sélectionné séparément (dropdown dynamique)
- Pas de `service_id` dans `Auth::attempt()` (corrigé)

### Middlewares de Sécurité

| Middleware | Classe | Description |
|-----------|--------|-------------|
| `auth` | `Authenticate` | Vérifie l'authentification |
| `guest` | `RedirectIfAuthenticated` | Redirige si connecté |
| `role:ingenieur,technicien,major` | `EnsureUserRole` | Vérifie le rôle |
| `major-read-only` | `MajorReadOnly` | Bloque POST/PUT/PATCH/DELETE pour major |
| `force-password-change` | `ForcePasswordChange` | Oblige le changement de mot de passe |
| `enforce-account-security` | `EnforceAccountSecurity` | Contrôles de sécurité |
| `prevent-back-history` | `PreventBackHistory` | Cache-Control no-store |

### Filtrage par Rôle (`ServiceAccess`)
3 niveaux de filtrage des données :

| Rôle | Méthode | Portée |
|------|---------|--------|
| `ingenieur`, `technicien` | `hasGlobalAccess()` | Toutes les données métier |
| `major` | `MajorReadOnly` + scopes | Lecture seule |

Implémenté dans `app/Support/ServiceAccess.php` avec 4 scope methods.

### ServiceVisibilityPolicy
Gates de visibilité enregistrées dans `AuthServiceProvider` :
- Contrôle l'accès aux données par service
- Intégré dans tous les contrôleurs concernés

---

## 3. Modèles de Données

### Diagramme Relationnel Simplifié
```
User ←→ Service ←→ Zone
  ↓         ↓
Intervention  Equipment ←→ Room
  ↓              ↓
Complaint    Market ←→ Company
  ↓
MaintenanceReport
```

### Modèles Principaux

#### User
- Champs : login, password, nom, prenom, role, service_id, unit_id, is_active
- Méthodes : `isMajor()`, `hasGlobalAccess()`, `isUnitRestricted()`
- Relations : belongsTo Service, belongsTo Unit

#### Equipment
- Champs : designation, num_inventaire, num_serie, marque, modele, service_id, zone_id, room_id, market_id
- Relations : belongsTo Service, belongsTo Zone, belongsTo Room, belongsTo Market

#### Intervention
- Types : OT (Ordre de Travail), DM (Demande de Maintenance)
- Workflow : Création → En cours → Clôture
- Relations : belongsTo Equipment, belongsTo User, belongsTo Complaint

#### Complaint
- Champs : objet, description, status, service_code, email
- Statuts : pending, in_progress, resolved, closed
- Events : ComplaintCreated → WebSocket notification

#### MaintenanceReport
- Cycle : draft → submitted → validated → closed
- Export PDF via DomPDF
- Relations : belongsTo Equipment, belongsTo User

#### Market
- Champs : numero_marche, objet, source_file, company_id
- Relations : hasMany Equipment, belongsTo Company

---

## 4. Routes

### Routes Publiques (sans auth)
```
GET  /                                    → HomeController@index
GET  /login                               → AuthController@showLogin
POST /login                               → AuthController@login
POST /logout                              → AuthController@logout
GET  /reclamation/{service_code}          → PublicComplaintController@create
POST /reclamation/{service_code}          → PublicComplaintController@store
```

### Routes Dashboard (auth + middlewares)
```
GET  /dashboard                           → BiomedDataController@dashboard
GET  /dashboard/live-metrics              → BiomedDataController@liveMetrics

# Équipements
GET  /dashboard/equipements               → EquipmentController@index
GET  /dashboard/equipements/create        → EquipmentController@create
GET  /dashboard/equipements/{id}          → EquipmentController@show
GET  /dashboard/equipements/{id}/edit     → EquipmentController@edit
POST /dashboard/equipements               → EquipmentController@store
PUT  /dashboard/equipements/{id}          → EquipmentController@update
DEL  /dashboard/equipements/{id}          → EquipmentController@destroy
POST /dashboard/equipements/import-excel  → BiomedDataController@importEquipements

# Interventions
GET  /dashboard/interventions             → InterventionController@index
GET  /dashboard/interventions/codes       → InterventionController@codes
GET  /dashboard/interventions/create      → InterventionController@create
POST /dashboard/interventions             → InterventionController@store
GET  /dashboard/interventions/{id}        → InterventionController@show
GET  /dashboard/interventions/{id}/cloture → InterventionController@closeForm
POST /dashboard/interventions/{id}/cloture → InterventionController@close

# Réclamations
GET  /dashboard/reclamations              → ComplaintController@index
PATCH /dashboard/reclamations/{id}/status → ComplaintController@updateStatus

# Rapports de maintenance
GET  /dashboard/rapports/interventions-internes        → MaintenanceReportController@index
GET  /dashboard/rapports/interventions-internes/create → MaintenanceReportController@create
POST /dashboard/rapports/interventions-internes        → MaintenanceReportController@store
GET  /dashboard/rapports/interventions-internes/{id}/edit → MaintenanceReportController@edit
PUT  /dashboard/rapports/interventions-internes/{id}   → MaintenanceReportController@update
PATCH /dashboard/rapports/.../submit                   → MaintenanceReportController@submit
PATCH /dashboard/rapports/.../validate                 → MaintenanceReportController@validateReport
PATCH /dashboard/rapports/.../close                    → MaintenanceReportController@close
GET  /dashboard/rapports/.../pdf                       → MaintenanceReportController@exportPdf

# Marchés
GET  /dashboard/marches-equipements                    → BiomedDataController@marketsEquipments
GET  /dashboard/marches-equipements/{market}            → BiomedDataController@showMarket
POST /dashboard/marches-equipements/import-excel       → BiomedDataController@importMarketsEquipmentsExcel
PATCH /dashboard/marches-equipements/equipment/{id}    → BiomedDataController@updateMarketEquipment

# Zones & Services (resource)
GET|POST|PUT|DELETE /dashboard/zones       → ZoneController (CRUD)
GET|POST|PUT|DELETE /dashboard/services    → ServiceController (CRUD)

# Planning & Stock
GET  /dashboard/planning-societes-externes → PlanningController@index
GET  /dashboard/stock-movements            → StockMovementController@movements

# Notifications
GET  /dashboard/notifications/complaints              → DashboardNotificationController@complaints
GET  /dashboard/notifications/complaints/{id}         → DashboardNotificationController@showComplaint
PATCH /dashboard/notifications/complaints/{id}/close  → DashboardNotificationController@closeComplaint
POST /dashboard/notifications/complaints/read-all     → DashboardNotificationController@markAllComplaintAsRead

# Administration (ingénieur)
GET  /dashboard/admin/security             → AdminSecurityController@index
GET|POST|PUT|DELETE /dashboard/admin/users → AdminUserController (CRUD)
PATCH /dashboard/admin/users/{id}/toggle-active → AdminUserController@toggleActive
POST /dashboard/admin/users/{id}/reset-password → AdminUserController@resetPassword

# Profil & Mot de passe
GET|PUT /dashboard/profile                 → AccountProfileController
GET|POST /dashboard/change-password        → AccountPasswordController

# Déclaration pannes (technicien)
GET  /dashboard/operator/defects/create    → OperatorDefectController@create
POST /dashboard/operator/defects           → OperatorDefectController@store

# PLC (technicien)
GET  /dashboard/technician/plc-status      → TechnicianPlcController@status
GET  /dashboard/technician/plc-logs        → TechnicianPlcController@logs
```

---

## 5. Événements & Temps Réel

### Architecture WebSocket
```
Complaint créée → ComplaintCreated Event
  → SendComplaintCreatedNotification Listener
    → ComplaintCreatedNotification (DB + WebSocket)
      → Node.js server (port 6001)
        → Client (notification badge + toast)
```

### Serveur WebSocket (`realtime/server.js`)
- Librairie : `ws` (WebSocket natif Node.js)
- Port : 6001
- Gère les connexions clients et broadcast les notifications
- Lancement : `npm run realtime`

---

## 6. Import Excel

### Marchés & Équipements
- Contrôleur : `BiomedDataController@importMarketsEquipmentsExcel`
- Librairie : PhpSpreadsheet
- Fonctionnalité : Détection de doublons (fichier déjà importé) avec messages créatifs
- Fichiers source : `excel/marches-equipements/`

### Équipements Individuels
- Contrôleur : `EquipmentController@importExcelFile`
- Import direct dans la table equipments

---

## 7. Export

### PDF
- Librairie : DomPDF
- Usage : `MaintenanceReportController@exportPdf`
- Templates : `resources/views/pdf/`

### Excel / CSV (côté client)
- Librairies : xlsx 0.18.5, jsPDF 2.5.1
- Exportation des tableaux depuis le navigateur

---

## 8. Interface Utilisateur

### Design System (`modern-ui.css`)
Le fichier `modern-ui.css` (1000+ lignes) définit un design system complet :

#### Variables CSS
```css
:root {
  --gst-primary: #2563eb;        /* Bleu principal */
  --gst-primary-hover: #1d4ed8;
  --gst-sidebar-bg: rgba(255,255,255,0.85);
  --gst-navbar-bg: rgba(255,255,255,0.8);
  /* ... et beaucoup d'autres */
}

.dark {
  --gst-sidebar-bg: rgba(30,41,59,0.95);
  --gst-navbar-bg: rgba(30,41,59,0.9);
  /* ... overrides dark mode */
}
```

#### Composants CSS
- `.gst-page-loader` — Animation de chargement
- `.gst-sidebar` — Sidebar glassmorphique
- `.gst-nav-link` — Liens de navigation avec glow hover
- `.gst-navbar` — Navbar frosted glass
- `.gst-toast-container` / `.gst-toast` — Toast notifications
- `.gst-hover-scale` — Effet scale au survol
- `.gst-shimmer` — Effet brillant sur les boutons

### JavaScript (`modern-ui.js`)
- `GSTDarkMode` — Gestion du dark mode (toggle, persistance localStorage)
- `GSTToast` — Système de notifications toast (success, error, warning, info)
- `GSTAlert` — Wrapper SweetAlert2 (confirmDelete, success, error, fallback toast)
- Ripple effect — Sur les boutons blue/green
- `GSTConfetti` — Effet confetti de célébration
- Bell shake — Animation de la cloche de notification
- Flash data bridge — Conversion des messages flash Laravel en toast

### Composants Blade (10)
| Composant | Fichier | Description |
|-----------|---------|-------------|
| Sidebar | `sidebar-dashboard.blade.php` | Navigation, logo GST, avatar, sections |
| Navbar | `navbar-dashboard.blade.php` | Frosted glass, dark mode toggle, notifications |
| Page Header | `module-page-header.blade.php` | Breadcrumb + bouton action |
| Table | `table.blade.php` | Tableau moderne rounded-2xl, recherche |
| KPI Cards | `kpi-cards.blade.php` | 6 cartes statistiques animées |
| Charts | `charts.blade.php` | Graphiques Chart.js |
| Modal Add | `modal-add-record.blade.php` | Modal d'ajout |
| Modal Import | `modal-import-excel.blade.php` | Modal import Excel |
| Navbar Public | `navbar.blade.php` | Navbar page d'accueil |
| Footer | `footer.blade.php` | Pied de page |
