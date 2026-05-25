# 📅 BioTrackGST — Timeline projet sur 4 mois

> Base de travail pour un diagramme de Gantt. Les dates ci-dessous reprennent les horodatages visibles dans les migrations, les notes de version et l’historique git de la documentation du projet.

## 1) Finalité du projet

**BioTrackGST** est une application web de **Gestion de Maintenance Assistée par Ordinateur** et de **réclamations hospitalières** pour le **GST Tanger**. Le projet centralise les équipements, les interventions, les réclamations, les rapports de maintenance, les marchés, les services et les stocks dans un seul système.

L’évolution du projet suit une logique progressive : d’abord la base technique, ensuite la sécurité, puis les modules métier, enfin l’interface, le temps réel et la stabilisation.

## 2) Sources de dates utilisées

Les dates visibles dans la documentation et l’historique local sont les suivantes :

- 2026-02-15 à 2026-02-16 : premières migrations datées dans l’arborescence du projet
- 2026-03-20 : import initial du projet dans l’historique git
- 2026-03-22 : gros correctifs sécurité, temps réel et cohérence d’interface
- 2026-03-23 : refactor du module pièces de rechange et ajustements d’authentification
- 2026-04-07 : harmonisation du modèle de rôles et de la visibilité technicien / ingénieur / major
- 2026-05-25 : état consolidé final de la documentation et du projet

## 3) Planning Gantt sur 4 mois

### Phase A — Socle technique et données de base

**Période estimée : 2026-02-15 → 2026-03-20**

| Tâche | Début | Fin | Dépendance | Résultat |
|---|---:|---:|---|---|
| Création des tables principales | 2026-02-15 | 2026-02-16 | Aucune | Base utilisateur, hôpital, entreprises, marchés, équipements |
| Mise en place du modèle de données | 2026-02-15 | 2026-02-16 | Création des tables | Structure relationnelle du projet |
| Seeders de structure hospitalière | 2026-02-16 | 2026-03-20 | Modèle de données | Zones, services, salles, utilisateurs de test |
| Import initial du projet | 2026-03-20 | 2026-03-20 | Socle technique existant | Point de départ versionné du projet |

Lien logique : cette phase prépare toutes les dépendances des modules métier. Sans les tables, les seeders et les relations, les écrans et les règles d’accès ne peuvent pas fonctionner correctement.

### Phase B — Sécurité, accès et règles de visibilité

**Période estimée : 2026-03-20 → 2026-04-07**

| Tâche | Début | Fin | Dépendance | Résultat |
|---|---:|---:|---|---|
| Authentification personnalisée | 2026-03-20 | 2026-03-22 | Phase A | Connexion avec choix du service |
| Mise en place des rôles | 2026-03-20 | 2026-04-07 | Phase A | `ingenieur`, `technicien`, `major` |
| Middlewares de sécurité | 2026-03-22 | 2026-03-22 | Authentification | Protection des actions et des routes |
| Lecture seule du rôle major | 2026-03-22 | 2026-03-22 | Rôles + middleware | Blocage des actions d’écriture |
| Visibilité des services | 2026-03-22 | 2026-04-07 | Rôles + policies | Données filtrées selon le profil |

Lien logique : le projet passe d’une base de données brute à une application contrôlée. Cette étape est indispensable pour éviter que tous les utilisateurs aient les mêmes droits.

### Phase C — Développement des modules métier

**Période estimée : 2026-03-22 → 2026-04-30**

| Tâche | Début | Fin | Dépendance | Résultat |
|---|---:|---:|---|---|
| Module équipements | 2026-03-22 | 2026-04-30 | Phase A + B | CRUD, filtres, import Excel |
| Module interventions | 2026-03-22 | 2026-04-30 | Phase A + B | OT / DM, clôture, liaison réclamation |
| Module réclamations | 2026-03-22 | 2026-04-30 | Phase A + B | Formulaire public + suivi dashboard |
| Rapports de maintenance | 2026-03-23 | 2026-04-30 | Phase A + B | Cycle brouillon → clôturé, export PDF |
| Zones, services, salles | 2026-03-22 | 2026-04-30 | Phase A | Structure hospitalière exploitable |
| Marchés et équipements | 2026-03-22 | 2026-04-30 | Phase A | Liaison marchés ↔ équipements |
| Pièces de rechange | 2026-03-23 | 2026-04-30 | Phase A + B | Workflow décharge / réception-retour |

Lien logique : les modules métier consomment les données créées en phase A et les autorisations de phase B. Ils représentent le cœur fonctionnel du projet.

### Phase D — Interface moderne, temps réel et consolidation

**Période estimée : 2026-03-22 → 2026-05-25**

| Tâche | Début | Fin | Dépendance | Résultat |
|---|---:|---:|---|---|
| Alignement interface major | 2026-03-22 | 2026-03-22 | Phase B | Masquage des actions non autorisées |
| Synchronisation temps réel | 2026-03-22 | 2026-03-22 | Phase B + C | Flux `gmao.changed` et refresh des vues |
| Corrections de stabilité Blade | 2026-03-22 | 2026-03-22 | Interface principale | Résolution du 500 layout |
| Refactor pièces de rechange | 2026-03-23 | 2026-03-23 | Phase C | Validation PDF/formulaire, suppression prix unitaire |
| Harmonisation des rôles | 2026-04-07 | 2026-04-07 | Phase B | Cohérence entre docs, vues et accès |
| Consolidation documentaire | 2026-05-25 | 2026-05-25 | Toutes phases | État final prêt pour soutenance |

Lien logique : cette phase transforme le projet fonctionnel en projet présentable, stable et utilisable. Elle améliore la lisibilité, la fluidité et la fiabilité.

## 4) Lecture chronologique simple pour le diagramme de Gantt

Si tu veux le représenter visuellement, l’ordre conseillé est :

1. **Février 2026** : socle technique et base de données
2. **20 mars 2026** : import initial du projet
3. **22 mars 2026** : sécurité, temps réel et corrections majeures
4. **23 mars 2026** : refactor du module pièces de rechange
5. **7 avril 2026** : harmonisation du modèle de rôles
6. **22 mai 2026** : consolidation finale de la version documentaire

## 5) Relation entre les étapes

Chaque bloc dépend du précédent :

- la base de données permet de stocker les informations
- les rôles et middlewares permettent de contrôler l’accès
- les modules métier exploitent ces données et ces règles
- l’interface et le temps réel rendent le système exploitable au quotidien

Sans la phase A, les autres phases n’ont pas de support.
Sans la phase B, les utilisateurs et les actions ne sont pas sécurisés.
Sans la phase C, il n’y a pas de valeur métier.
Sans la phase D, le projet reste difficile à utiliser et à présenter.

## 6) Résumé prêt pour soutenance

En 4 mois, le projet BioTrackGST a évolué d’un socle technique vers une application complète de maintenance hospitalière. La progression a été mesurée par dates réelles : import initial le 20/03, correctifs majeurs les 22/03 et 23/03, harmonisation des rôles le 07/04, puis stabilisation finale le 22/05.

Cette timeline peut être utilisée directement comme base d’un diagramme de Gantt, car elle contient les phases, les dates, les dépendances et les résultats attendus.