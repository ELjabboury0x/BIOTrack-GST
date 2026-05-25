# 🔧 BioTrackGST — Systéme Intelligent de Gestion de Maintenance Assistée par Ordinateur et de Réclamations Hospitaliéres avec Notification en Temps Réel pour GST Tanger

> **Version 3.1** | 2026-05-22

---

## 📋 Prérequis

### Logiciels Requis
| Logiciel | Version | Notes |
|----------|---------|-------|
| **PHP** | 8.2+ | ⚠️ Utiliser WinGet ou installation standalone, PAS XAMPP PHP |
| **Composer** | 2.x | Gestionnaire de dépendances PHP |
| **Node.js** | 16+ | Pour le serveur WebSocket temps réel |
| **npm** | 8+ | Inclus avec Node.js |
| **MySQL** | 5.7+ / 8.0 | XAMPP MySQL ou standalone |

### ⚠️ Note Importante sur PHP (Windows)

Le PHP inclus dans XAMPP peut être incompatible. Il est recommandé d'installer PHP via **WinGet** :

```powershell
winget install PHP.PHP.8.2
```

Le chemin typique sera :
```
C:\Users\<Utilisateur>\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe
```

### Extensions PHP Requises
- `openssl`
- `mbstring`
- `fileinfo`
- `pdo_mysql`
- `curl`

Si les extensions ne sont pas activées dans `php.ini`, utiliser les flags `-d` :
```powershell
php -d extension_dir="<chemin>\ext" -d extension=openssl -d extension=mbstring -d extension=fileinfo -d extension=pdo_mysql artisan serve
```

---

## 📥 Installation Pas à Pas

### Étape 1 — Obtenir le Projet
```bash
# Si cloné depuis Git
git clone <url-du-repo>
cd PFE

# Si reçu en archive
# Extraire le zip et naviguer dans le dossier
```

### Étape 2 — Dépendances PHP
```bash
composer install
```

### Étape 3 — Configuration Environnement
```bash
# Copier le fichier de configuration
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

Éditer `.env` avec vos paramètres :
```env
APP_NAME="BioTrackGST"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gmao_gst
DB_USERNAME=root
DB_PASSWORD=

REALTIME_SECRET=your-secret-key
```

### Étape 4 — Base de Données

1. Créer la base de données MySQL :
```sql
CREATE DATABASE gmao_gst CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Exécuter les migrations :
```bash
php artisan migrate
```

3. Peupler la base de données :
```bash
php artisan db:seed
```

Ceci crée :
- 12 utilisateurs avec rôles (mot de passe : `123456`)
- Structure hospitalière (zones, services, salles)
- Entreprises pour les marchés
- Liaison équipement ↔ service (1177 enregistrements)
- Unités organisationnelles

### Étape 5 — Dépendances Node.js
```bash
npm install
```

---

## 🚀 Lancement

### Méthode 1 — VS Code Task (Recommandé)

Le projet inclut des tâches VS Code préconfigurées dans `.vscode/tasks.json` :

1. `Ctrl+Shift+P` → "Tasks: Run Task"
2. Sélectionner **"Start Full Stack"**

Cela lance :
- Serveur Laravel sur `0.0.0.0:8001`
- Serveur WebSocket sur port `6001`

Pour arrêter : "Tasks: Run Task" → **"Stop Full Stack"**

### Méthode 2 — Manuellement

**Terminal 1 — Serveur Laravel :**
```bash
php artisan serve --host=0.0.0.0 --port=8001
```

**Terminal 2 — Serveur WebSocket :**
```bash
npm run realtime
```

### Accès

| URL | Description |
|-----|-------------|
| `http://localhost` | Accès local via Nginx |
| `http://<IP_LAN>` | Accès réseau (LAN) via Nginx |
| `http://localhost/login` | Page de connexion |
| `http://localhost/dashboard` | Dashboard (après login) |
| `http://localhost/reclamation/{service_code}` | Formulaire réclamation public |

---

## 👤 Comptes Utilisateurs

Tous les mots de passe sont : **`123456`**

| Login | Rôle | Accès |
|-------|------|-------|
| `KHANTOUR.MOHAMED` | major | Lecture seule |
| `AHADDOUT.HANAE` | ingenieur | Accès global aux données métier |
| `JABRANE.LATIFA` | ingenieur | Accès global aux données métier |
| `NAWAL` | ingenieur | Accès global aux données métier |
| `ZERKOUNI.HOUDA` | ingenieur | Accès global aux données métier |
| `BENADDI.FATIMA` | technicien | Même données que ingénieur, actions limitées par rôle |
| `IHADJITANE.MALAK` | technicien | Même données que ingénieur, actions limitées par rôle |
| `SAKROUHI.SAID` | technicien | Même données que ingénieur, actions limitées par rôle |
| `KHALIL.HAMZA` | ingenieur | Accès global aux données métier |
| `ZOUIN.MAROUANE` | technicien | Même données que ingénieur, actions limitées par rôle |

---

## 🔄 Backups

Des sauvegardes SQL sont disponibles dans `backups/` :
- `gmao_gst-20260216-190459.sql`
- `gmao_gst-20260216-190903.sql`

Pour restaurer :
```bash
mysql -u root gmao_gst < backups/gmao_gst-20260216-190903.sql
```

---

## 🐛 Dépannage

### Erreur "Class not found"
```bash
composer dump-autoload
```

### Erreur de permissions (storage)
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Windows — Vérifier que le dossier n'est pas en lecture seule
```

### PHP extensions manquantes
Vérifier avec :
```bash
php -m
```
Activer dans `php.ini` ou utiliser les flags `-d extension=...`

### Erreur de connexion MySQL
- Vérifier que MySQL est démarré (XAMPP Control Panel)
- Vérifier les identifiants dans `.env`
- Vérifier que la base `gmao_gst` existe

### Serveur WebSocket ne démarre pas
- Vérifier que Node.js est installé : `node --version`
- Vérifier que le port 6001 n'est pas occupé
- Réinstaller les dépendances : `npm install`

---

## 📦 Déploiement en Production

### Optimisations
```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Optimisation de l'autoloader
composer install --optimize-autoloader --no-dev
```

### Variables d'Environnement Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com
```

### Serveur Web
Configurer Apache/Nginx pour pointer vers le dossier `public/` du projet.
