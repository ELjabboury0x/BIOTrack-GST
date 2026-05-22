# 📘 GST GMAO — Index Complet du Projet

> **Version 3.1** | 2026-05-22

---

## 📚 Documentation

| Fichier | Description |
|---------|-------------|
| [README.md](README.md) | Vue d'ensemble et démarrage rapide |
| [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) | Résumé complet, modules, statistiques |
| [SETUP_GUIDE.md](SETUP_GUIDE.md) | Installation pas à pas |
| [QUICK_START.md](QUICK_START.md) | Démarrage rapide |
| [DOCUMENTATION.md](DOCUMENTATION.md) | Documentation technique complète |
| [DASHBOARD_GUIDE.md](DASHBOARD_GUIDE.md) | Routes & modules du dashboard |
| [API_INTEGRATION.md](API_INTEGRATION.md) | Endpoints API & intégration |
| [VISUAL_GUIDE.md](VISUAL_GUIDE.md) | Guide visuel de l'interface |
| [COMPLETION_SUMMARY.md](COMPLETION_SUMMARY.md) | Résumé d'achèvement |
| [INDEX.md](INDEX.md) | Ce fichier |
| [SHARE_READY.md](SHARE_READY.md) | Guide de partage |

---

## 🗂️ Structure Complète des Fichiers

### 📄 Configuration
```
PFE/
├── .env                              # Variables d'environnement (créer depuis .env.example)
├── .env.example                      # Template .env
├── composer.json                     # Dépendances PHP
├── package.json                      # Dépendances Node.js
├── artisan                           # CLI Laravel
└── .vscode/tasks.json                # Tâches VS Code (Start/Stop Full Stack)
```

### 🔧 Application Laravel (`app/`)
```
app/
├── Console/
│   ├── Kernel.php                    # Planification des commandes
│   └── Commands/                     # Commandes Artisan personnalisées
├── Events/
│   └── ComplaintCreated.php          # Événement création réclamation
├── Exceptions/
│   └── Handler.php                   # Gestionnaire d'exceptions
├── Http/
│   ├── Kernel.php                    # Kernel HTTP (middlewares)
│   ├── Controllers/
│   │   ├── AccountPasswordController.php    # Changement mot de passe
│   │   ├── AccountProfileController.php     # Profil utilisateur
│   │   ├── AdminSecurityController.php      # Sécurité
│   │   ├── AdminUserController.php          # Gestion utilisateurs admin
│   │   ├── AuthController.php               # Authentification (login/logout)
│   │   ├── BiomedDataController.php         # Dashboard, marchés, import Excel
│   │   ├── ComplaintController.php          # Réclamations dashboard
│   │   ├── DashboardNotificationController.php  # Notifications
│   │   ├── EquipmentController.php          # CRUD équipements
│   │   ├── HomeController.php               # Page d'accueil
│   │   ├── InterventionController.php       # CRUD interventions
│   │   ├── MaintenanceReportController.php  # Rapports maintenance (cycle de vie)
│   │   ├── OperatorDefectController.php     # Déclaration pannes technicien
│   │   ├── PlanningController.php           # Planning sociétés externes
│   │   ├── PreventiveMaintenanceController.php  # Maintenance préventive
│   │   ├── PublicComplaintController.php    # Réclamation publique
│   │   ├── ServiceController.php            # CRUD services
│   │   ├── SettingsController.php           # Paramètres
│   │   ├── SparePartController.php          # Pièces de rechange
│   │   ├── StockMovementController.php      # Mouvements de stock
│   │   ├── TechnicianController.php         # Gestion techniciens
│   │   ├── TechnicianPlcController.php      # Interface PLC
│   │   └── ZoneController.php               # CRUD zones
│   ├── Middleware/
│   │   ├── Authenticate.php                 # Auth guard
│   │   ├── EncryptCookies.php               # Chiffrement cookies
│   │   ├── EnforceAccountSecurity.php       # Sécurité comptes
│   │   ├── EnsureUserRole.php               # Vérification rôle
│   │   ├── ForcePasswordChange.php          # Mot de passe obligatoire
│   │   ├── MajorReadOnly.php               # Lecture seule rôle major
│   │   ├── PreventBackHistory.php           # Anti-retour arrière
│   │   ├── RedirectIfAuthenticated.php      # Redirection si connecté
│   │   ├── TrimStrings.php                  # Nettoyage chaînes
│   │   └── VerifyCsrfToken.php              # Protection CSRF
│   └── Requests/                     # Form Requests (validation)
├── Listeners/
│   └── SendComplaintCreatedNotification.php
├── Models/
│   ├── Company.php                   # Sociétés (marchés)
│   ├── Complaint.php                 # Réclamations
│   ├── Equipment.php                 # Équipements biomédicaux
│   ├── EquipmentVerification.php     # Vérifications d'équipement
│   ├── EquipmentVerificationLog.php  # Logs de vérification
│   ├── Hospital.php                  # Hôpital
│   ├── Intervention.php              # Interventions OT/DM
│   ├── InventoryNumberRectification.php  # Rectifications inventaire
│   ├── MaintenanceReport.php         # Rapports de maintenance
│   ├── Market.php                    # Marchés
│   ├── Room.php                      # Salles
│   ├── Service.php                   # Services hospitaliers
│   ├── Store.php                     # Magasins
│   ├── User.php                      # Utilisateurs (isMajor, hasGlobalAccess, isUnitRestricted)
│   └── Zone.php                      # Zones hospitalières
├── Notifications/
│   └── ComplaintCreatedNotification.php
├── Policies/
│   └── ServiceVisibilityPolicy.php   # Gates de visibilité par service
├── Providers/
│   ├── AuthServiceProvider.php       # Gates & policies
│   ├── EventServiceProvider.php      # Event/Listener bindings
│   └── RouteServiceProvider.php      # Routes, throttling
├── Services/
│   └── AppSettingsService.php        # Service de paramètres
└── Support/
    └── ServiceAccess.php             # Filtrage 3 niveaux (admin→service→unité)
```

### 🗄️ Base de Données (`database/`)
```
database/
├── migrations/                       # 73 migrations
│   ├── 2026_02_15_000000_create_users_table.php
│   ├── 2026_02_15_000001_create_hospitals_table.php
│   ├── 2026_02_15_000002_create_companies_table.php
│   ├── 2026_02_15_000003_create_markets_table.php
│   ├── 2026_02_15_000004_create_stores_table.php
│   ├── 2026_02_15_000005_create_equipments_table.php
│   ├── ... (29 fichiers au total)
│   └── 2026_02_16_200001_add_unit_id_to_users_table.php
└── seeders/
    ├── DatabaseSeeder.php            # Orchestrateur
    ├── BdProfilesUsersSeeder.php     # 12 utilisateurs
    ├── HospitalStructureSeeder.php   # Structure hospitalière
    ├── ZonesSeeder.php               # Zones
    ├── MarketCompaniesSeeder.php     # Entreprises des marchés
    ├── EquipmentServiceLinkerSeeder.php  # Liaison équipement↔service (1177)
    └── UnitsSeeder.php               # Unités
```

### 🎨 Frontend Public (`public/`)
```
public/
├── index.php                         # Point d'entrée
├── web.config                        # Config IIS
├── favicon.svg                       # Favicon SVG (dégradé bleu, "G", heartbeat)
├── css/
│   ├── custom.css                    # Styles personnalisés
│   ├── dashboard.css                 # Dashboard (543 lignes)
│   └── modern-ui.css                # Design system moderne (1000+ lignes)
│                                      # Dark mode, glassmorphism, animations,
│                                      # toast, page loader, confetti
├── js/
│   ├── app.js                        # Scripts principaux
│   ├── charts.js                     # Chart.js graphiques
│   ├── dashboard.js                  # Logique dashboard
│   ├── modern-ui.js                 # GSTDarkMode, GSTToast, GSTAlert,
│                                      # ripple, confetti (334 lignes)
│   └── table.js                      # Gestion tableaux
└── images/
    ├── logo-gst.svg                  # Logo GST (zellige, ECG, "GST")
    ├── favicon.svg                   # Favicon
    └── btata.webp                    # Image décorative
```

### 📐 Vues Blade (`resources/views/`)
```
resources/views/
├── layouts/
│   ├── dashboard.blade.php           # Layout dashboard (Inter, SweetAlert2, Animate.css, modern-ui)
│   └── app.blade.php                 # Layout principal
├── components/
│   ├── sidebar-dashboard.blade.php   # Sidebar glassmorphique, logo GST, navigation glow
│   ├── navbar-dashboard.blade.php    # Navbar frosted glass, dark mode toggle
│   ├── module-page-header.blade.php  # Breadcrumb + bouton dégradé
│   ├── table.blade.php               # Tableau moderne rounded-2xl
│   ├── kpi-cards.blade.php           # 6 KPI cards animées
│   ├── charts.blade.php              # Graphiques
│   ├── modal-add-record.blade.php    # Modal ajout
│   ├── modal-import-excel.blade.php  # Modal import Excel
│   ├── navbar.blade.php              # Navbar publique
│   └── footer.blade.php              # Footer
├── login.blade.php                   # Login glassmorphique (heartbeat SVG, animation)
├── home.blade.php                    # Page d'accueil
├── dashboard.blade.php               # Page dashboard principal
└── pages/
    ├── dashboard.blade.php           # Dashboard KPI + graphiques
    ├── equipements.blade.php         # Liste équipements
    ├── interventions.blade.php       # Liste interventions
    ├── interventions-codes.blade.php # Codes d'intervention
    ├── reclamations.blade.php        # Réclamations
    ├── markets-equipments.blade.php  # Marchés liste
    ├── market-show.blade.php         # Détail marché
    ├── rapports.blade.php            # Rapports
    ├── maintenance-reports/          # CRUD rapports maintenance
    ├── techniciens.blade.php         # Techniciens
    ├── pieces.blade.php              # Pièces de rechange
    ├── maintenance-preventive.blade.php  # Maintenance préventive
    ├── parametres.blade.php          # Paramètres
    ├── planning-societes-externes.blade.php  # Planning externe
    ├── stock-movements.blade.php     # Mouvements stock
    ├── zones/                        # CRUD zones
    ├── services/                     # CRUD services
    ├── notifications/                # Notifications réclamations
    ├── account/                      # Profil, mot de passe
    ├── admin/                        # Users, sécurité
    ├── operator/                     # Déclaration pannes technicien
    ├── technician/                   # PLC status/logs technicien
    └── forms/                        # Formulaires divers
```

### ⚡ Temps Réel (`realtime/`)
```
realtime/
└── server.js                         # Serveur WebSocket Node.js (port 6001)
```

### 📊 Données Excel
```
excel/
└── marches-equipements/              # Fichiers Excel (marchés + équipements)
```

---

## 🔗 Liens Rapides par Tâche

### Pour démarrer le projet
1. [SETUP_GUIDE.md](SETUP_GUIDE.md) — Installation complète
2. [QUICK_START.md](QUICK_START.md) — Démarrage rapide
3. `Ctrl+Shift+P` → "Start Full Stack" dans VS Code

### Pour comprendre l'architecture
1. [DOCUMENTATION.md](DOCUMENTATION.md) — Architecture technique
2. [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) — Vue fonctionnelle consolidée
3. [API_INTEGRATION.md](API_INTEGRATION.md) — Endpoints

### Pour personnaliser l'interface
1. [VISUAL_GUIDE.md](VISUAL_GUIDE.md) — Guide visuel
2. `public/css/modern-ui.css` — Variables CSS & design system
3. `public/js/modern-ui.js` — Interactions JS

### Pour partager le projet
1. [SHARE_READY.md](SHARE_READY.md) — Guide de partage
2. `scripts/prepare-share.ps1` — Script de préparation
