# PDVWeb

> **Plateforme e-commerce dynamique multi-rayons — PHP 8 / MySQL / Tailwind CSS**
> Travail de Fin de Module — Cours 652-1-A Programmation Dynamique Web (PRDW) — 2025-2026

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.x-38bdf8.svg)](https://tailwindcss.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## Sommaire

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Architecture](#architecture)
- [Base de données](#base-de-données)
- [Sécurité](#sécurité)
- [Installation](#installation)
- [Captures](#captures)
- [Stats projet](#stats-projet)
- [Auteur](#auteur)

---

## Présentation

PDVWeb (Point De Vente Web) est une boutique en ligne multi-rayons permettant la vente d'articles dans trois catégories : **informatique**, **livres** et **hi-fi**.

Le site gère trois profils utilisateurs :

| Profil | Sigle | Accès |
|---|---|---|
| Visiteur | UNM | Consultation du catalogue, du blog, inscription |
| Membre | UM  | Tous les services + achat, panier, mini-chat, commentaires, profil |
| Administrateur | Admin | Gestion complète : membres, articles, billets, commandes, etc. |

---

## Fonctionnalités

### Pour les visiteurs (UNM)

- Consultation du catalogue avec filtres avancés (prix, stock, note, tags)
- **Comparateur d'articles** côte à côte (jusqu'à 4)
- Lecture du blog avec recherche par titre, filtre par tag
- Inscription en tant que membre (avec rassurance RGPD)
- Vue des fiches détaillées des articles
- Page mentions légales / RGPD

### Pour les membres (UM)

- **Authentification sécurisée** + "se souvenir de moi"
- Modification du profil + upload d'avatar (.gif / .jpeg)
- **Mini-chat communautaire** (10 derniers messages)
- Commentaires sur les billets de blog
- **Achats** : panier persistant, validation, codes promo, frais de port
- Historique des achats + détails de chaque commande
- Liste de **favoris**
- Notes et **avis** sur articles achetés
- **Messages privés** avec d'autres membres
- **Notifications internes** (statut commande, nouveau message, etc.)
- Abonnement newsletter
- Gestion d'**adresses multiples** (livraison/facturation)
- Comparateur d'articles persistant en session

### Pour l'administrateur

#### Gestion (14 sections)

- **Membres** : liste, recherche, blocage, promotion admin, **modification données**, **reset mot de passe**, **anonymisation RGPD**, **vérification email manuelle**
- **Articles & stock** : CRUD complet, prix, image, tags, disponibilité
- **Catégories** : CRUD + activation/désactivation
- **Commandes** : liste filtrée (statut, dates, recherche), changement de statut, **export CSV**, pagination
- **Billets de blog** : CRUD + résumé + image illustrative + tags
- **Codes promo** : CRUD complet avec dates, quotas, types de remise
- **Frais de port** : CRUD par pays/tranche
- **Tags** : CRUD pour catégoriser articles et billets
- **Statistiques** : top articles, top membres, connexions, recherches
- **Audit log** : journal exhaustif de toutes les actions admin
- **Sécurité & Maintenance** : état sécurité + purge données anciennes
- **Corbeille** : restauration de billets et commentaires supprimés

#### Sécurité admin

- Mode soft-delete (corbeille)
- Audit log de **toutes** les actions
- Confirmation forte pour les actions destructives (modale "tapez le login")
- Admin ne peut pas s'auto-modifier (anti-bricolage)
- Compte anonymisé : aucune action possible

---

## Architecture

### Approche : Hybride + MVC light

```
┌─────────────────────────────────────────────────────────────┐
│  public/             CONTRÔLEURS (point d'entrée HTTP)      │
│   ├── catalogue.php  reçoit la requête, orchestre           │
│   ├── article.php                                           │
│   ├── comparer.php   (Phase 3 : comparateur)                │
│   └── admin/...      (32 contrôleurs admin)                 │
│                                                             │
│  classes/            MODÈLES (logique métier & accès BDD)   │
│   ├── util/          classes utilitaires                    │
│   │   ├── Db.php     singleton PDO                          │
│   │   ├── Auth.php   sessions, login, droits                │
│   │   ├── Csrf.php   protection CSRF                        │
│   │   ├── Flash.php  messages entre pages                   │
│   │   └── Upload.php upload sécurisé                        │
│   ├── Membre.php     classes métier (26 au total)           │
│   ├── Article.php                                           │
│   ├── Billet.php                                            │
│   └── ...                                                   │
│                                                             │
│  views/              VUES (HTML pur)                        │
│   ├── auth/                                                 │
│   ├── catalogue/                                            │
│   ├── blog/                                                 │
│   └── admin/         (~30 vues admin)                       │
│                                                             │
│  includes/                                                  │
│   ├── bootstrap.php  autoloader + init + session            │
│   ├── header.php     nav + skip link + accessibility        │
│   ├── footer.php     liens RGPD + badges sécurité           │
│   └── admin_header.php                                      │
└─────────────────────────────────────────────────────────────┘
```

### Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.0+ orienté objet |
| Base de données | MySQL 5.7+ / MariaDB 10.3+ |
| Accès BDD | PDO + requêtes préparées 100% |
| Frontend | Tailwind CSS 3 (CDN) + JavaScript ES6 |
| Police | Inter (Google Fonts) |
| Sécurité | password_hash bcrypt, CSRF tokens, sessions sécurisées |
| Serveur | Apache (XAMPP / WAMP / MAMP) |

---

## Base de données

**~36 tables** organisées en 8 groupes fonctionnels.

| Groupe | Tables | Rôle |
|---|---|---|
| Identité & Sécurité | 8 | `membre`, `adresse`, `role`, `permission`, `role_permission`, `membre_role`, `token`, `tentative_connexion` |
| Audit & RGPD | 3 | `audit_log`, `consentement_rgpd`, `log_connexion` |
| Catalogue | 5 | `categorie`, `article`, `vue_article`, `note_article`, `article_tag` |
| Vente & Paiement | 7 | `statut_commande`, `achat_facture`, `ligne_facture`, `paiement`, `frais_port`, `code_promo`, `code_promo_utilisation` |
| Engagement | 3 | `favori`, `newsletter_abonne`, `like_contenu` |
| Contenu & Blog | 4 | `billet`, `commentaire`, `tag`, `billet_tag` |
| Communication | 3 | `minichat`, `message_prive`, `notification` |
| Technique | 3 | `parametre`, `recherche_log`, `audit_log` |

Le schéma respecte le **cahier des charges** (7 tables imposées) et est enrichi pour couvrir les standards e-commerce 2026. Voir `docs/justification_tables.md` et `docs/schema_reference.md` pour le détail.

### Migrations SQL

| # | Fichier | But |
|---|---|---|
| 01 | `01_schema.sql` | Schéma initial |
| 02 | `02_seed.sql` | Données de démo |
| 03 | `03_migration_soft_delete.sql` | Corbeille (date_suppression) |
| 04 | `04_fix_anonymisation_order.sql` | Correction ordre suppressions |
| 05 | `05_migration_mode_livraison.sql` | Mode de livraison sur factures |
| 06 | `06_migration_mp.sql` | Messages privés |
| 07 | `07_migration_pseudo_minichat.sql` | Pseudo dans mini-chat |
| 08 | `08_migration_billet_resume_image.sql` | Résumé + image sur billets |
| 09 | `09_migration_categorie_actif.sql` | Catégories activables |
| 10 | `10_migration_article_tag.sql` | Tags sur articles |

---

## Sécurité

### Conformité OWASP Top 10 (2021)

- ✅ **A01 Broken Access Control** : vérification des droits sur chaque page sensible
- ✅ **A02 Cryptographic Failures** : bcrypt pour les mots de passe, HTTPS recommandé
- ✅ **A03 Injection** : 100% PDO requêtes préparées, htmlspecialchars systématique
- ✅ **A04 Insecure Design** : audit log, soft-delete, RGPD natif
- ✅ **A05 Security Misconfiguration** : `display_errors=0`, headers HTTP, fichiers sensibles hors `public/`
- ✅ **A06 Outdated Components** : Tailwind CDN versionné, PHP 8+
- ✅ **A07 Identification Failures** : rate limiting, sessions sécurisées, regen ID
- ✅ **A08 Data Integrity Failures** : CSRF tokens sur tous les formulaires
- ✅ **A09 Logging Failures** : audit_log exhaustif, log_connexion
- ✅ **A10 SSRF** : non applicable (pas de fetch d'URLs externes)

### Mesures techniques

- ✅ **PDO + requêtes préparées** systématiques
- ✅ **bcrypt** via `password_hash` / `password_verify`
- ✅ **Jetons CSRF** sur tous les formulaires (POST)
- ✅ **`htmlspecialchars`** systématique à l'affichage
- ✅ **Anti brute-force** : blocage après N tentatives ratées
- ✅ **Upload sécurisé** : vérif MIME + extension + taille + renommage
- ✅ **Sessions** : `session_regenerate_id()` après login, cookies HTTPOnly + SameSite
- ✅ **Audit log** sur toutes les actions sensibles (conformité RGPD)
- ✅ **Tokens à usage unique** pour reset password et vérif email
- ✅ **Anonymisation RGPD** : article 17 (droit à l'oubli)
- ✅ **Mentions légales** : conformité RGPD belge avec lien APD

---

## Installation

### Prérequis

- PHP ≥ 8.0
- MySQL ≥ 5.7 (ou MariaDB ≥ 10.3)
- Apache (XAMPP / WAMP / MAMP)

### Méthode 1 : Installation rapide (Windows + XAMPP)

```bash
# 1. Cloner ou copier le dossier dans :
C:\xampp\htdocs\pdvweb\

# 2. Lancer XAMPP : Apache + MySQL

# 3. Double-cliquer sur install.bat
# (créé la BDD + exécute toutes les migrations automatiquement)

# 4. Ouvrir http://localhost/pdvweb/public/
```

### Méthode 2 : Installation manuelle

```bash
# 1. Cloner le repo
git clone https://github.com/<votre-username>/pdvweb.git
cd pdvweb

# 2. Configurer la BDD
cp config/config.example.php config/config.php
# Éditer config/config.php avec vos identifiants MySQL

# 3. Créer la base et exécuter toutes les migrations
mysql -u root -p < sql/01_schema.sql
mysql -u root -p < sql/02_seed.sql
mysql -u root -p < sql/03_migration_soft_delete.sql
mysql -u root -p < sql/04_fix_anonymisation_order.sql
mysql -u root -p < sql/05_migration_mode_livraison.sql
mysql -u root -p < sql/06_migration_mp.sql
mysql -u root -p < sql/07_migration_pseudo_minichat.sql
mysql -u root -p < sql/08_migration_billet_resume_image.sql
mysql -u root -p < sql/09_migration_categorie_actif.sql
mysql -u root -p < sql/10_migration_article_tag.sql

# 4. Pointer Apache sur le dossier public/
# (ou accéder à http://localhost/pdvweb/public/)
```

### Comptes de test

| Login | Mot de passe | Profil |
|---|---|---|
| `admin` | `admin2026` | Administrateur |
| `jdupont` | `test1234` | Membre |
| `smartin` | `test1234` | Membre |
| `mlambert` | `test1234` | Membre |

---

## Captures

Les captures d'écran de l'application se trouvent dans `docs/captures/`.

Principales pages à voir :
- Accueil (hero animé, KPI, coups de cœur)
- Catalogue (filtres avancés, comparateur)
- Fiche article (notes, tags, similaires)
- Dashboard admin (14 sections de gestion)
- Mentions légales RGPD
- Comparateur d'articles côte à côte

---

## Stats projet

| Critère | Valeur |
|---|---|
| **Fichiers PHP** | 154 |
| **Erreurs de syntaxe** | 0 |
| **Classes** | 26 |
| **Pages admin** | 32 |
| **Vues** | ~48 |
| **Migrations SQL** | 10 |
| **Lignes de code** | ~15 000 |
| **CRUD complets** | 6 entités (Article, Billet, Catégorie, CodePromo, FraisPort, Tag) |
| **Sections admin fonctionnelles** | 14/14 |
| **Bonus implémentés** | ~20 |

### Avancement par étapes

- [x] **Étape 1.** Architecture, schéma BDD, données de test, README
- [x] **Étape 2.** Socle commun : config, classes utilitaires, header/footer Tailwind
- [x] **Étape 3.** Accueil + inscription + connexion + déconnexion + profil
- [x] **Étape 4.** Mini-chat
- [x] **Étape 5.** Blog/News (billets, commentaires, recherche, tags, likes, RGPD)
- [x] **Étape 6.** Achats (catalogue, panier, commande, paiement, codes promo, historique, favoris, avis)
- [x] **Étape 7.** Administration de base (membres, articles, commandes, statistiques)
- [x] **Étape 8.** Administration avancée (CRUD complet 6 entités, audit log exhaustif, 4 actions admin sur membres, RGPD)
- [x] **Étape 9.** UI/UX polish (palette indigo, accessibilité, empty states, mentions légales)
- [x] **Étape 10.** Fonctionnalités avancées (filtres catalogue, tags articles, comparateur)

**Projet 100% terminé**, prêt pour le rendu du **13 juin 2026**.

---

## Auteur

Travail individuel — PRDW 2025-2026.

---

## Licence

Code source distribué sous licence MIT — voir [LICENSE](LICENSE).
