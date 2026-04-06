# ⚡ GST GMAO — Démarrage Rapide

> **Version 3.1** | 2026-03-19

### État actuel
- Application active en environnement interne GST
- Démarrage standard via tâche VS Code `Start Full Stack`
- Front recommandé via Nginx `80` (Laravel direct sur `8001`) + serveur temps réel sur `6001`
- Partage externe via tâche VS Code `Start Tunnel` (URL loca.lt dynamique)

---

## 🚀 En 5 Minutes

### 1. Prérequis
- PHP 8.2+ (WinGet recommandé)
- Composer 2.x
- Node.js 16+
- MySQL 5.7+

### 2. Installation Express
```bash
cd c:\xampp\htdocs\PFE\PFE\PFE
composer install
cp .env.example .env
php artisan key:generate
# Configurer la base de données dans .env
php artisan migrate
php artisan db:seed
npm install
```

### 3. Lancer
```bash
# Option A — VS Code Task
# Ctrl+Shift+P → "Tasks: Run Task" → "Start Full Stack"

# Option B — Manuellement
php artisan serve --host=0.0.0.0 --port=8001
# Nouveau terminal :
npm run realtime
```

### 4. Se connecter
- URL : `http://localhost/login` (ou `http://localhost:8001/login` en accès direct Laravel)
- Identifiant : `admin` / Mot de passe : `123456`

---

## 📱 Navigation dans l'application

### Page de Connexion
- **Design** : Carte glassmorphique avec animation heartbeat SVG
- **Champs** : Identifiant + Mot de passe + Sélection du service
- **Dark mode** : Automatiquement supporté

### Tableau de bord principal (`/dashboard`)
Après connexion, 6 cartes KPI sont affichées :
1. **Équipements** — Nombre total d'équipements
2. **Interventions** — Interventions en cours
3. **Réclamations** — Réclamations ouvertes
4. **Rapports** — Rapports de maintenance
5. **Marchés** — Marchés actifs
6. **Techniciens** — Techniciens enregistrés

Plus des graphiques Chart.js (barres, lignes, doughnut).

### Barre latérale (menu)
Le menu est organisé par sections :

**Navigation :**
- 📊 Tableau de bord
- 🔧 Équipements
- 🛠️ Interventions
- 📋 Réclamations
- 📑 Rapports
- 📦 Marchés & Équipements

**Modules supplémentaires :**
- 🗺️ Zones & Services
- 📅 Planning sociétés externes
- 📦 Stock & mouvements
- 👨‍🔧 Techniciens
- 🔩 Pièces de rechange
- ⚙️ Maintenance préventive
- ⚙️ Paramètres

**Administration (admin uniquement) :**
- 👥 Gestion utilisateurs
- 🔒 Sécurité

### Mode sombre
- Cliquer sur l'icône 🌙/☀️ dans la navbar
- La préférence est sauvegardée automatiquement (localStorage)

---

## 🔐 Comptes Disponibles

| Identifiant | Mot de passe | Rôle (code) | Ce que vous verrez |
|-------|-------------|------|---------------------|
| `admin` | `123456` | admin | Accès complet + administration |
| `MESRAR.ASMAE` | `123456` | manager | Tous les modules |
| `KHANTOUR.MOHAMED` | `123456` | major | Lecture seule |
| `AHADDOUT.HANAE` | `123456` | ingenieur | Données filtrées par service |
| `BENADDI.FATIMA` | `123456` | technicien | Données filtrées par unité |
| `KHALIL.HAMZA` | `123456` | technician | PLC + déclaration pannes |
| `ZOUIN.MAROUANE` | `123456` | operator | Déclaration de défauts |

---

## 📡 URLs Principales

| URL | Description | Auth requise |
|-----|-------------|--------------|
| `/` | Page d'accueil | Non |
| `/login` | Connexion | Non |
| `/dashboard` | Tableau de bord principal | Oui |
| `/dashboard/equipements` | Équipements | Oui |
| `/dashboard/interventions` | Interventions | Oui |
| `/dashboard/reclamations` | Réclamations | Oui |
| `/dashboard/rapports` | Rapports | Oui |
| `/dashboard/rapports/interventions-internes` | Rapports maintenance | Oui |
| `/dashboard/marches-equipements` | Marchés | Oui |
| `/dashboard/marches-equipements/{id}` | Détail marché | Oui |
| `/dashboard/zones` | Zones | Oui |
| `/dashboard/services` | Services | Oui |
| `/dashboard/planning-societes-externes` | Planning externe | Oui |
| `/dashboard/stock-movements` | Stock | Oui |
| `/dashboard/techniciens` | Techniciens | Oui |
| `/dashboard/pieces` | Pièces de rechange | Oui |
| `/dashboard/notifications/complaints` | Notifications | Oui |
| `/dashboard/admin/users` | Gestion utilisateurs | Admin |
| `/dashboard/admin/security` | Sécurité | Admin |
| `/dashboard/profile` | Profil | Oui |
| `/dashboard/change-password` | Mot de passe | Oui |
| `/reclamation/{service_code}` | Réclamation publique | Non |

---

## 🎨 Fonctionnalités UI

- **Mode sombre** — Bascule dans la barre de navigation, persistance automatique
- **Toast notifications** — Messages temporaires en haut à droite
- **SweetAlert2** — Confirmations de suppression stylisées
- **Animations** — Entrée des cartes, hover scale, shimmer boutons
- **Glassmorphism** — Sidebar semi-transparente avec blur
- **Barre de navigation effet verre givré** — Effet visuel glassmorphism
- **Confetti** — Effet de célébration sur certaines actions
- **Page loader** — Animation de chargement avec heartbeat
- **Ripple** — Effet ripple sur les boutons principaux
