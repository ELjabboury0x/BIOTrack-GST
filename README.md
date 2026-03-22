# 🏥 GST GMAO — Système de Gestion de Maintenance Assistée par Ordinateur

> **Projet de Fin d'Études** | GST Tanger | 2026  
> **Version 3.2** — Mise à jour 2026-03-22

---

## 📋 Description

Application web complète de **Gestion de Maintenance Assistée par Ordinateur (GMAO)** développée pour **GST Tanger** (Groupe Sterilisation Tanger). Le système couvre la gestion des équipements biomédicaux, interventions, réclamations, rapports de maintenance, marchés, planification et bien plus.

### État actuel (2026-03-22)

- **Statut** : En service interne, maintenance évolutive active
- **Backend** : Laravel 9 + PHP 8.x
- **Temps réel** : WebSocket Node.js (`npm run realtime`)
- **Métriques codebase** : 31 contrôleurs, 12 middlewares, 69 migrations, 11 seeders
- **Exécution recommandée** : VS Code Tasks `Start Full Stack` / `Stop Full Stack` (+ `Start Tunnel` pour partage externe)

### Dernières mises à jour (2026-03-22)

- **Rôle major durci en lecture seule** : blocage global des actions d'écriture et des écrans d'action (`create` / `edit`)
- **UI major alignée** : suppression des boutons/actions non autorisés (cloche réclamations, actions SAV, CTA de création)
- **Temps réel étendu à la vue major** : rafraîchissement automatique des vues de consultation lors des changements OT/SAV/Réclamations
- **OT renforcé** : diffusion explicite d'événements de changement après création, modification et clôture
- **Correctif stabilité** : résolution d'une erreur 500 liée au layout Blade (`Undefined variable`)

### Fonctionnalités principales

- **Dashboard interactif** — 6 KPI cards, graphiques Chart.js, métriques en temps réel
- **Gestion des équipements** — CRUD complet, import Excel, filtrage par service/zone/salle
- **Interventions** — Création OT/DM, workflow de clôture, liaison avec réclamations
- **Réclamations** — Formulaire public par service, notifications temps réel (WebSocket)
- **Rapports de maintenance** — Cycle de vie complet (brouillon → soumis → validé → clôturé), export PDF
- **Marchés & Équipements** — Import Excel, visualisation par marché, édition en ligne
- **Zones & Services** — Gestion hiérarchique de la structure hospitalière
- **Planning sociétés externes** — Planification des interventions externes
- **Stock & pièces de rechange** — Mouvements de stock, inventaire
- **Système de rôles avancé** — 7 rôles avec filtrage par service et accès lecture seule pour le major
- **Interface moderne** — Dark mode, glassmorphism, animations, toast notifications, SweetAlert2
- **Temps réel** — Serveur Node.js WebSocket pour les notifications instantanées

---

## 🛠️ Stack Technique

| Couche      | Technologie                                                      |
|-------------|------------------------------------------------------------------|
| Backend     | **Laravel 9.52** / PHP 8.2                                       |
| Frontend    | **Blade** + Tailwind CSS (CDN) + Alpine.js 3.x                  |
| Base de données | **MySQL** via Eloquent ORM                                   |
| Temps réel  | **Node.js** + `ws` WebSocket (port 6001)                        |
| UI avancée  | SweetAlert2, Animate.css, Chart.js 3.9, Font Awesome 6.4        |
| Design      | `modern-ui.css` — Dark mode, glassmorphism, animations           |
| Export      | DomPDF (PDF), PhpSpreadsheet (Excel), jsPDF + xlsx (côté client) |

---

## 🔐 Rôles & Accès

| Rôle        | Accès                                                            |
|-------------|------------------------------------------------------------------|
| `admin`     | Accès complet + gestion utilisateurs + sécurité                  |
| `manager`   | Accès complet aux modules métier                                 |
| `major`     | Lecture seule (middleware `MajorReadOnly`)                        |
| `ingenieur` | Filtrage par service rattaché                                    |
| `technicien`| Filtrage par unité rattachée                                     |
| `technician`| Interface PLC status/logs + déclaration de pannes                |
| `operator`  | Déclaration de défauts uniquement                                |

---

## 🚀 Démarrage Rapide

### Prérequis

- **PHP 8.2+** (WinGet recommandé sous Windows, PAS XAMPP)
- **Composer** 2.x
- **Node.js** 16+ & npm
- **MySQL** 5.7+

### Installation

```bash
# 1. Cloner / extraire le projet
cd c:\xampp\htdocs\PFE\PFE\PFE

# 2. Installer les dépendances PHP
composer --working-dir=backend install

# 3. Configuration
copy backend\.env.example backend\.env
php backend\artisan key:generate
# Configurer DB_DATABASE, DB_USERNAME, DB_PASSWORD dans backend/.env

# 4. Base de données
php backend\artisan migrate
php backend\artisan db:seed

# 5. Dépendances Node.js (pour le serveur temps réel)
npm --prefix backend install
```

### Lancement

**Via VS Code Task (recommandé) :**
- `Ctrl+Shift+P` → "Tasks: Run Task" → "Start Full Stack"

**Manuellement :**
```bash
# Terminal 1 — Serveur Laravel
php backend\artisan serve --host=0.0.0.0 --port=8001

# Terminal 2 — Serveur WebSocket
npm --prefix backend run realtime
```

Accès : `http://localhost` (Nginx:80) ou `http://localhost:8001` (Laravel direct)

### Comptes utilisateurs

Tous les mots de passe sont : `123456`

| Login               | Rôle       |
|---------------------|------------|
| ADMIN               | admin      |
| AHADDOUT.HANAE      | ingenieur  |
| BENADDI.FATIMA      | technicien |
| IHADJITANE.MALAK    | technicien |
| JABRANE.LATIFA      | ingenieur  |
| KHALIL.HAMZA        | technician |
| KHANTOUR.MOHAMED    | major      |
| MESRAR.ASMAE        | manager    |
| NAWAL               | ingenieur  |
| SAKROUHI.SAID       | technicien |
| ZERKOUNI.HOUDA      | ingenieur  |
| ZOUIN.MAROUANE      | operator   |

---

## 📂 Structure du Projet

```
PFE/
├── backend/                           # Laravel (API, logique métier, DB, vues Blade)
│   ├── app/
│   ├── config/
│   ├── database/                      # Migrations + seeders
│   ├── resources/                     # Vues + assets source
│   ├── routes/
│   └── storage/
├── public/
│   ├── index.php                      # Point d'entrée HTTP
│   ├── build/                         # Assets compilés
│   └── images/
├── realtime/                          # Serveur temps réel Node.js (port 6001)
├── docs/                              # Documentation projet
├── scripts/                           # Scripts d'exécution/maintenance
├── data/                              # Données source (Excel manuels)
└── tools/                             # Outils internes (tests manuels)
```

---

## 📚 Documentation

| Fichier                                          | Description                               |
|--------------------------------------------------|-------------------------------------------|
| [README.md](README.md)                           | Ce fichier — vue d'ensemble               |
| [docs/PROJECT_SUMMARY.md](docs/PROJECT_SUMMARY.md)         | Résumé complet du projet                  |
| [docs/SETUP_GUIDE.md](docs/SETUP_GUIDE.md)                 | Guide d'installation détaillé             |
| [docs/QUICK_START.md](docs/QUICK_START.md)                 | Démarrage rapide                          |
| [docs/DOCUMENTATION.md](docs/DOCUMENTATION.md)             | Documentation technique complète          |
| [docs/DASHBOARD_GUIDE.md](docs/DASHBOARD_GUIDE.md)         | Guide des routes & modules                |
| [docs/API_INTEGRATION.md](docs/API_INTEGRATION.md)         | Intégration API & endpoints               |
| [docs/VISUAL_GUIDE.md](docs/VISUAL_GUIDE.md)               | Guide visuel de l'interface               |
| [docs/COMPLETION_SUMMARY.md](docs/COMPLETION_SUMMARY.md)   | Résumé d'achèvement                       |
| [docs/INDEX.md](docs/INDEX.md)                             | Index complet des ressources              |
| [docs/SHARE_READY.md](docs/SHARE_READY.md)                 | Guide de partage du projet                |

---

## 📄 Licence

Projet académique — PFE 2026 | GST Tanger
