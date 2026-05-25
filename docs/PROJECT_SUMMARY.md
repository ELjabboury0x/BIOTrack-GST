# 📊 GST GMAO — Résumé du Projet

> Mise à jour : 2026-05-25  
> **Statut** : ✅ Production-ready

## 🔄 État actuel consolidé (2026-05-25)

- Architecture confirmée : Laravel + Blade + MySQL + WebSocket Node.js
- Démarrage normalisé via scripts `scripts/start-full-stack.ps1` et VS Code Tasks
- Base projet actuelle : **31 contrôleurs**, **13 middlewares**, **73 migrations**, **11 seeders**
- Documentation alignée sur les fichiers réellement présents dans le dépôt

## 📝 Release Note (2026-03-23)

- **Module Pièces de Rechange refactorisé (workflow 2 phases)**
	- Nouveau flux: `Décharge` / `Réception-Retour`
	- Nouveau mode de saisie: `Importer un PDF` ou `Remplir le formulaire`
	- Validation dynamique: champs requis seulement en mode formulaire, PDF requis en mode import
	- Upload des justificatifs PDF en stockage public (`storage/app/public/spare-parts/documents`)
	- UI modernisée: tabs de phase, cartes, sections métier et styles de champs renforcés
- **Stock / Pièces: simplification métier**
	- Suppression du champ `prix unitaire` dans la saisie et l'affichage du module
- **Authentification et service de connexion**
	- Correction `APP_KEY` manquante
	- Service `Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger` visible uniquement à la connexion
	- Suppression de la redondance `Pédiatrie` dans les services

## 📝 Release Note (2026-03-22)

- **Rôle major renforcé (lecture seule stricte)**
	- Blocage global des opérations d'écriture côté middleware
	- Blocage des écrans d'action (`create`, `edit`) pour éviter les accès non autorisés
- **Interface major alignée avec les permissions**
	- Masquage de la cloche de notifications réclamations
	- Masquage des actions SAV (`Nouveau`, `Éditer`, `Supprimer`)
	- Suppression des CTA de création non autorisés
- **Temps réel étendu à la vue major**
	- Ajout d'un flux global de changement GMAO (`gmao.changed`)
	- Rafraîchissement automatique des vues de consultation major hors dashboard
	- Renforcement OT: diffusion explicite après `create`, `update`, `close`
- **Stabilité**
	- Correctif d'une erreur 500 Blade (`Undefined variable`) sur le layout dashboard

---

## 🎯 Vue d'ensemble

Le système **GST GMAO** est une application web Laravel complète dédiée à la gestion de maintenance assistée par ordinateur pour le **Groupement Sanitaire Territorial (GST)**, établissement hospitalier public doté d'une autonomie financière et administrative. L'application gère l'intégralité du cycle de vie de la maintenance biomédicale hospitalière.

---

## ✅ Modules Implémentés

### 1. Authentification & Sécurité
- Login personnalisé avec sélection du service
- 3 rôles actifs : major, ingénieur, technicien
- Middleware `MajorReadOnly` — Accès lecture seule pour le rôle major
- Middleware `ForcePasswordChange` — Changement de mot de passe obligatoire
- Middleware `EnforceAccountSecurity` — Contrôle de sécurité des comptes
- Middleware `PreventBackHistory` — Protection contre le retour arrière
- Portée des données : ingénieur et technicien (visibilité globale), major (lecture seule) via `ServiceAccess`

### 2. Dashboard
- 6 cartes KPI animées (équipements, interventions, réclamations, etc.)
- Graphiques interactifs Chart.js (barres, lignes, doughnut)
- Métriques en temps réel via endpoint `/dashboard/live-metrics`
- Notifications temps réel WebSocket

### 3. Équipements
- Liste paginée avec recherche et filtres (zone, service, salle)
- CRUD complet (création, modification, suppression)
- Import Excel avec détection de doublons (messages créatifs)
- Fiche détaillée par équipement
- Liaison automatique équipement ↔ service (1177 équipements liés)

### 4. Interventions
- Création OT (Ordre de Travail) et DM (Demande de Maintenance)
- Workflow de clôture avec formulaire dédié
- Liaison avec réclamations
- Codes d'intervention de référence

### 5. Réclamations
- Formulaire public accessible par code service (sans authentification)
- Dashboard des réclamations avec mise à jour de statut
- Notifications temps réel via WebSocket (événement `ComplaintCreated`)
- Throttling pour protection contre le spam

### 6. Rapports de Maintenance
- Cycle de vie : Brouillon → Soumis → Validé → Clôturé
- Rapports d'interventions internes
- Export PDF via DomPDF
- Édition en ligne

### 7. Marchés & Équipements
- Import Excel des marchés avec équipements associés
- Vue liste des marchés avec sociétés (noms d'entreprises)
- Vue détail par marché
- Édition en ligne des équipements de marché

### 8. Zones & Services
- CRUD complet pour les zones hospitalières
- CRUD complet pour les services
- Structure hiérarchique zone → service → salle

### 9. Autres Modules
- **Planning sociétés externes** — Planification des interventions
- **Stock & mouvements** — Gestion des pièces de rechange
- **Techniciens** — Gestion du personnel technique
- **Maintenance préventive** — Module de planification
- **Paramètres** — Configuration générale et panneau
- **Déclaration de pannes** (technicien) — Signalement rapide
- **PLC Status/Logs** (technicien) — Interface automates

### 10. Administration
- **Gestion des utilisateurs** — CRUD, activation/désactivation, reset mot de passe
- **Sécurité** — Tableau de bord de sécurité
- **Profil** — Modification du profil utilisateur

---

## 🎨 Interface Utilisateur (v3.0)

### Design System `modern-ui.css` (1000+ lignes)
- **Dark Mode complet** — Toggle avec persistance localStorage, overrides pour tous les composants Tailwind
- **Glassmorphism** — Sidebar avec fond semi-transparent et backdrop-blur
- **Animations** — Entrée en fondu des cartes, stagger, shimmer sur les boutons, slide-in des lignes de tableau
- **Toast Notifications** — Système personnalisé (success, error, warning, info)
- **Page Loader** — Animation de chargement avec logo heartbeat
- **Confetti** — Effet de célébration pour les actions réussies

### Composants Principaux
- **Sidebar** — Logo GST avec motif zellige marocain, navigation glow hover, indicateur barre gauche, avatar utilisateur
- **Navbar** — Verre givré (frosted glass), toggle dark mode (lune/soleil), badge notification avec pulse
- **Login** — Carte glassmorphique, animation heartbeat SVG, cercles décoratifs flous
- **Tables** — Cartes arrondies 2xl, recherche moderne, boutons action arrondis
- **En-têtes de module** — Breadcrumb avec icône home, boutons dégradés

### Assets
- `modern-ui.css` — Design system complet
- `modern-ui.js` — GSTDarkMode, GSTToast, GSTAlert, ripple, confetti (334 lignes)
- `logo-gst.svg` — Logo SVG avec motif zellige diamant, ligne ECG, texte "GST"
- `favicon.svg` — Favicon SVG dégradé bleu avec lettre "G" et ligne heartbeat
- `dashboard.css` — Styles spécifiques dashboard (543 lignes)

---

## 🗃️ Base de Données

### Modèles (15)
`User`, `Company`, `Complaint`, `Equipment`, `EquipmentVerification`, `EquipmentVerificationLog`, `Hospital`, `Intervention`, `InventoryNumberRectification`, `MaintenanceReport`, `Market`, `Room`, `Service`, `Store`, `Zone`

### Migrations (30)
De la création des tables utilisateurs jusqu'aux unités et politique de mots de passe.

### Seeders (7)
- `DatabaseSeeder` — Orchestrateur principal
- `BdProfilesUsersSeeder` — 12 utilisateurs avec rôles
- `HospitalStructureSeeder` — Structure hospitalière (zones, services, salles)
- `ZonesSeeder` — Zones hospitalières
- `MarketCompaniesSeeder` — Noms d'entreprises pour les marchés
- `EquipmentServiceLinkerSeeder` — Liaison équipement ↔ service (1177 enregistrements)
- `UnitsSeeder` — Unités organisationnelles

---

## 🔧 Configuration Technique

### PHP
- **Version** : 8.2.30 (WinGet, PAS XAMPP)
- **Chemin** : `C:\Users\Dell\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe`
- **Extensions requises** : openssl, mbstring, fileinfo, pdo_mysql

### Serveurs
- **Laravel** : `0.0.0.0:8001` (accès LAN)
- **WebSocket** : Port 6001 (Node.js + ws)

### CDN Frontend
- Tailwind CSS 3.x
- Alpine.js 3.x
- Font Awesome 6.4.0
- Chart.js 3.9.1
- SweetAlert2 @11
- Animate.css 4.1.1
- Google Fonts (Inter)
- xlsx 0.18.5, jsPDF 2.5.1

---

## 📈 Statistiques

| Métrique                  | Valeur      |
|---------------------------|-------------|
| Contrôleurs               | 31          |
| Modèles Eloquent          | 15          |
| Migrations                | 69          |
| Seeders                   | 11          |
| Middlewares                | 12          |
| Routes web                | 100+        |
| Composants Blade          | 10          |
| Pages de module           | 20+         |
| Équipements liés          | 1177        |
| Utilisateurs              | 12          |
| Rôles                     | 7           |
| Fichiers CSS personnalisés| 3           |
| Fichiers JS personnalisés | 5           |
