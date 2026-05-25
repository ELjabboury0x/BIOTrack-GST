# 🎨 BioTrackGST Dashboard - Guide Visuel & Présentation

_Mise à jour : 2026-05-22_

## 📐 Vue d'Ensemble de l'Interface

```
┌─────────────────────────────────────────────────────────────────┐
│                  BioTrackGST Dashboard - GST Tanger              │
├──────────────┬───────────────────────────────────────────────────┤
│              │                                                    │
│  SIDEBAR     │              CONTENU PRINCIPAL                    │
│  (264px)     │                                                    │
│              │  ┌──────────────────────────────────────────────┐ │
│  🏠 Tableau  │  │ 📊 Tableau de Bord                           │ │
│     de Bord  │  │                                              │ │
│              │  │ ┌─────┬─────┬─────┬─────┬──────┐            │ │
│  🔧 Équipe.  │  │ │KPI 1│KPI 2│KPI 3│KPI 4│ KPI 5│            │ │
│              │  │ │     │     │     │     │      │            │ │
│  🩺 OT/DM    │  │ └─────┴─────┴─────┴─────┴──────┘            │ │
│              │  │                                              │ │
│  📅 Maint.   │  │ ┌──────────────────┬────────────────────┐   │ │
│              │  │ │   BAR CHART      │    PIE CHART      │   │ │
│  👥 Techn.   │  │ │ OT/DM            │  Types Maint.     │   │ │
│              │  │ │                  │                   │   │ │
│  📦 Pièces   │  │ └──────────────────┴────────────────────┘   │ │
│              │  │                                              │ │
│  📊 Rapports │  │ ┌─────────────────────────────────────────┐ │ │
│              │  │ │     LINE CHART - Coûts Mensuels        │ │ │
│  ⚙️ Paramèt. │  │ │                                         │ │ │
│              │  │ └─────────────────────────────────────────┘ │ │
│              │  └──────────────────────────────────────────────┘ │
└──────────────┴───────────────────────────────────────────────────┘
```

---

## 🎯 Zones Principales

### 1. SIDEBAR (Barre Latérale)

**Couleur**: Gradient Bleu (#1e40af → #1e3a8a)
**Largeur**: 264px
**Position**: Fixed, Scrollable

```
┌─────────────────┐
│  📦 BioTrackGST │  ← Logo + Titre "BioTrackGST"
│  Maintenance    │     Sous-titre "Maintenance"
├─────────────────┤
│ 🏠 Tableau de   │  ← Élément actif (surligné en bleu #3b82f6)
│    Bord         │
├─────────────────┤
│ 🔧 Équipements  │  ← Élément inactif
├─────────────────┤
│ 🩺 OT/DM (PM-BIO)
├─────────────────┤
│ 📅 PM-BIO       │
│    Préventive   │
├─────────────────┤
│ 👥 Techniciens  │
├─────────────────┤
│ 📦 Pièces de    │
│    Rechange     │
├─────────────────┤
│ 📊 Rapports     │
├─────────────────┤
│ ⚙️ Paramètres   │
├─────────────────┤
│ © GST Tanger    │  ← Pied de page
│ BioTrackGST     │
└─────────────────┘
```

**Styles Appliqués**:
- Hover: Fond bleu foncé (#1e40af)
- Actif: Surbrillance + ombre
- Icônes: Font Awesome colorées
- Texte: Blanc (#ffffff)

---

### 2. NAVBAR (Barre Supérieure)

**Hauteur**: 80px
**Fond**: Blanc (#ffffff)
**Ombre**: Subtile bordure grise

```
┌────────────────────────────────────────────────────────────────┐
│ 📄 Tableau de Bord                      🔔  👤    ▼           │
│ Bienvenue dans votre système...         (notifications)       │
└────────────────────────────────────────────────────────────────┘
```

**Composants**:
- **Gauche**: Titre + Sous-titre de la page
- **Droite**:
  - 🔔 Bouton notifications (avec badge rouge)
  - 👤 Profil utilisateur (nom + rôle)
  - ▼ Menu dropdown

---

### 3. KPI CARDS (Cartes Indicateurs)

**Nombre**: 5 cartes en grille responsive
**Disposition**: 
- Desktop: 5 colonnes
- Tablet: 2-3 colonnes
- Mobile: 1 colonne

```
┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ...
│ 📊 Équipe.   │  │ 🩺 OT/DM     │  │ ⚠️ Retard    │
│              │  │              │  │              │
│     156      │  │      23      │  │       5      │
│              │  │              │  │              │
│ ↑ 12% ce mois│  │ 3 urgentes   │  │ ⚠️ Action req│
└──────────────┘  └──────────────┘  └──────────────┘
```

**Styles par Carte**:
- Contour gauche stylisé (4px)
- Couleur contour: Bleu (#3b82f6) ou autre
- Hover: Légère élévation + ombre
- Animation compteur: 2 secondes

---

### 4. CHARTS (Graphiques)

#### 4.1 BAR CHART - OT/DM par Mois
```
OT/DM par Mois
┌─────────────────────────────────────────┐
│ 80│                                       │
│   │                 ▲ Préventive (bleu)  │
│ 60│  ▓  ▓           │ Curative (ciel)    │
│   │  ▓  ▓  ▓  ▓  ▓ ▓                    │
│ 40│  ░  ░  ░  ░  ░ ░                    │
│   │  ░  ░  ░  ░  ░ ░                    │
│ 20│  ░  ░  ░  ░  ░ ░                    │
│   ├─────────────────────────────────────│
│ 0 │  J  F  M  A  M  J  J  A  S  O  N  D│
│   └─────────────────────────────────────┘
```

#### 4.2 PIE CHART - Types de Maintenance
```
Types de Maintenance (Doughnut)
        ╱─────────╲
      ╱     45%    ╲
    │   Préventive   │
    │                │
    │    ╱────────╲ │
    │  │  35%     │ │
    └─ │ Curative │ │
        │ 20%     │
        │Correct. │
         ╲─────────╱
```

#### 4.3 LINE CHART - Coûts Mensuels
```
Coûts de Maintenance Mensuel (DH)
62,000│                      • •
      │                    •   • •
50,000│                  •       •
      │                •         •
40,000│              •           •
      │            •             •
32,000│•••••••••••             • •
      │ J F M A M J J A S O N D
```

**Styles Charts**:
- Couleurs: Palette blue (#3b82f6, #60a5fa)
- Animations: Chargement progressif
- Responsive: Adaptation hauteur
- Interaction: Hover sur données

---

### 5. TABLEAU DYNAMIQUE

```
┌─ Rechercher... ───── [Colonnes] [Importer] [Exporter] [+ Ajouter] ──┐
├──────────────────────────────────────────────────────────────────────┤
│ CODE    │ NOM              │ TYPE         │ LOCATION  │ STATUT │ ... │
├─────────┼──────────────────┼──────────────┼───────────┼────────┼─────┤
│ EQ-001  │ Pompe Hydrauliq. │ Hydraulique  │ Atelier A │ ✓ Actif│ ✎ ✗ │
│ EQ-002  │ Moteur Élec.     │ Électrique   │ Atelier B │ ✓ Actif│ ✎ ✗ │
│ EQ-003  │ Compresseur Air  │ Mécanique    │ Atelier C │ ⚙ Main │ ✎ ✗ │
├─────────┴──────────────────┴──────────────┴───────────┴────────┴─────┤
│ 1-3 de 156  résultats  [◄] [1] [2] [3] [►]                           │
└──────────────────────────────────────────────────────────────────────┘
```

**Fonctionnalités**:
- Recherche: "EQ-", "Pompe", etc.
- Tri: Clic sur en-têtes
- Pagination: 10 rows par défaut
- Actions: Éditer (✎) / Supprimer (✗)
- Statuts: Badges colorés

---

### 6. MODALS

#### 6.1 Modal Ajouter Enregistrement

```
╔════════════════════════════════════════════════════════════╗
║ ➕ Ajouter un nouvel enregistrement          [✕ Close]    ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  Nom *                      Code *                        ║
║  [___________________]      [___________________]         ║
║                                                            ║
║  Catégorie                  Statut                        ║
║  [v Sélectionner...]        [v Actif]                    ║
║                                                            ║
║  Description                                              ║
║  [_________________________________________]             ║
║  [_________________________________________]             ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║  [Annuler]                       [✓ Ajouter]             ║
╚════════════════════════════════════════════════════════════╝
```

**Styles Modal**:
- Header: Gradient bleu
- Backdrop: Fond sombre semi-transparent
- Animations: Scale + Fade
- Validations: Champs requis marqués (*)

#### 6.2 Modal Importer Excel

```
╔════════════════════════════════════════════════════════════╗
║ ⬆️ Importer Excel                            [✕ Close]    ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  Étape: 1️⃣ Télécharger → 2️⃣ Mapper → 3️⃣ Importer      ║
║  ════════════════════════                                ║
║                                                            ║
║  📋 Glissez le fichier ici                                ║
║     ou cliquez pour sélectionner                          ║
║                                                            ║
║  ✓ Fichier sélectionné: equipements.xlsx                ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║  [ Annuler ]                    [ Suivant ► ]             ║
╚════════════════════════════════════════════════════════════╝
```

---

## 🎨 Palette de Couleurs

```
Primaire (Bleu):
  - #3b82f6 (Principal)      ████
  - #60a5fa (Secondaire)     ████
  - #1e40af (Foncé)          ████
  - #dbeafe (Clair)          ████

Secondaires:
  - #10b981 (Succès/Vert)    ████
  - #f59e0b (Warning/Orange) ████
  - #ef4444 (Danger/Rouge)   ████
  - #8b5cf6 (Purple)         ████

Neutres:
  - #ffffff (Blanc)          ████
  - #f9fafb (Gris très clair)████
  - #374151 (Gris foncé)     ████
  - #1f2937 (Noir doux)      ████
```

---

## 🎬 Animations & Transitions

### Animations Principales

1. **Fade In** - Éléments entrant (300ms)
2. **Slide** - Notifications (300ms)
3. **Scale** - Modals (300ms)
4. **Pulse** - Indicateurs actifs (2s)
5. **Compteurs** - KPI Cards (2s) + refresh temps réel WebSocket

### Transitions Hover

- Boutons: Fond + Shadow (300ms)
- Cartes: Élévation + Shadow (300ms)
- Lignes tableau: Fond gris (200ms)
- Icônes: Couleur + Rotation (200ms)

---

## 📱 Responsive Layouts

### Desktop (1024px+)
```
┌────────────────────────────────────────┐
│ Sidebar 264px │ Contenu Principal     │
│               │                        │
│ KPI: 5 col    │ Charts: 3 cols        │
└────────────────────────────────────────┘
```

### Tablet (640px-1023px)
```
┌────────────────────────────────────┐
│ Sidebar │ Contenu Principal        │
│ (réduit)│                          │
│         │ KPI: 2-3 cols            │
│         │ Charts: 2 cols           │
└────────────────────────────────────┘
```

### Mobile (< 640px)
```
┌──────────────────────────┐
│ Contenu (Sidebar hidden) │
│                          │
│ KPI: 1 colonnes         │
│ Charts: Fullwidth       │
│ Table: Scroll horizontal│
└──────────────────────────┘
```

---

## 🔄 Flux d'Interaction Typique

### Scénario: Ajouter un équipement

```
1. User : Clic sur "+ Ajouter"
   ↓
2. Modal : Scale In Animation (300ms)
   ├─ Champs : Input focus
   └─ Validations : En attente
   ↓
3. User : Remplit le formulaire
   ├─ Champ actif : Ring bleu
   └─ Focus-out : Validation
   ↓
4. User : Clic "Ajouter"
   ├─ Modal : Fade out
   └─ Table : Ligne ajoutée (animation)
   ↓
5. Notification : ✓ "Créé avec succès" (3s)
   ↓
6. Table : Rafraîchit les données
```

---

## 🎯 Points Forts Visuels

✨ **Ce qui rend le dashboard impressionnant**:

1. **Cohérence Visuelle**
   - Une seule palette de couleurs
   - Alignement parfait (Tailwind grid)
   - Harmonies de formes (radius 8-12px)

2. **Animations Fluides**
   - Aucune animation brutale
   - Transitions douce (300-600ms)
   - Loading states clairs

3. **Hiérarchie Visuelle**
   - Sidebar fixe pour navigation
   - Navbar claire et claire
   - Contenu focal au centre
   - Footer discret

4. **Accessibilité**
   - Contraste suffisant
   - Textes lisibles
   - Espacements généreux
   - Icônes explicites

5. **Responsivité**
   - Breakpoints bien définis
   - Aucune scrollbar horizontal
   - Touch-friendly sur mobile
   - Performance optimale

---

## 🏆 Design Patterns Appliqués

- **Card Pattern** - KPI & Sections
- **Table Pattern** - Données tabulées
- **Modal Pattern** - Dialogues
- **Dropdown Pattern** - Menus
- **Badge Pattern** - Statuts
- **Button Pattern** - Actions
- **Animated Counters** - KPI values
- **Progressive Enhancement** - Fallbacks

---

## 💡 Conseils de Présentation

1. **Animations en premier** - Montrez les carrés KPI animés
2. **Navigation fluide** - Changez entre modules
3. **Importation Excel** - Démonstration complète
4. **Graphiques interactifs** - Montrez les hover effects
5. **Responsive** - Testez sur mobile
6. **Recherche** - Filtrage en temps réel
7. **Export** - Téléchargement Excel

---

**Cette interface vise à être impressionnante, professionnelle et fonctionnelle!** 🚀
