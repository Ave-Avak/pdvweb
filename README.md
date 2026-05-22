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
- [Avancement](#avancement)
- [Auteur](#auteur)

---

## Présentation

PDVWeb (Point De Vente Web) est une boutique en ligne multi-rayons permettant la vente d'articles dans trois catégories : **informatique**, **livres** et **hi-fi**.

Le site gère trois profils utilisateurs :

| Profil | Sigle | Accès |
|---|---|---|
| Visiteur | UNM | Consultation du catalogue, du blog, inscription |
| Membre | UM  | Tous les services + achat, panier, mini-chat, commentaires, profil |
| Administrateur | Admin | Gestion complète : membres, articles, billets, commandes, paramètres |

## Fonctionnalités

### Pour les visiteurs (UNM)
- Consultation du catalogue (3 catégories, 18 articles)
- Lecture du blog avec recherche par titre
- Inscription en tant que membre
- Vue des fiches détaillées des articles

### Pour les membres (UM)
- Authentification sécurisée + "se souvenir de moi"
- Modification du profil + upload d'avatar (gif/jpeg)
- Mini-chat communautaire (10 derniers messages)
- Commentaires sur les billets de blog
- Achats : panier persistant, validation, codes promo, frais de port
- Historique des achats + détails de chaque commande
- Liste de favoris
- Notes et avis sur articles
- Messages privés avec d'autres membres
- Notifications internes
- Abonnement newsletter
- Gestion d'adresses multiples (livraison/facturation)

### Pour l'administrateur
- Tableau de bord avec statistiques
- Gestion des membres : profils, blocage, historiques de connexion
- Gestion des articles : ajout, modification, suppression, stock
- Gestion des billets et commentaires
- Gestion des commandes : suivi de statut (en attente → payée → expédiée → livrée)
- Codes promo : création, suivi des utilisations
- Frais de port : grille par pays/tranche
- Audit log : trace de toutes les actions sensibles (conformité RGPD)
- Statistiques : recherches, vues articles, tops
- Paramètres applicatifs modifiables sans toucher au code
- Système de rôles & permissions

## Architecture

### Approche : Hybride + MVC light

```
┌─────────────────────────────────────────────────────────────┐
│  public/             CONTRÔLEURS (point d'entrée HTTP)      │
│   ├── blog.php       reçoit la requête, orchestre           │
│   ├── article.php                                           │
│   └── admin/...                                             │
│                                                             │
│  classes/            MODÈLES (logique métier & accès BDD)   │
│   ├── util/          classes utilitaires                    │
│   │   ├── Db.php     singleton PDO                          │
│   │   ├── Auth.php   sessions, login, droits                │
│   │   ├── Csrf.php   protection CSRF                        │
│   │   ├── Flash.php  messages entre pages                   │
│   │   └── Upload.php upload sécurisé                        │
│   ├── Membre.php     classes métier                         │
│   ├── Article.php                                           │
│   ├── Billet.php                                            │
│   └── ...                                                   │
│                                                             │
│  views/              VUES (HTML pur)                        │
│   ├── blog/                                                 │
│   ├── catalogue/                                            │
│   └── admin/                                                │
└─────────────────────────────────────────────────────────────┘
```

### Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.0+ |
| Base de données | MySQL 5.7+ / MariaDB 10.3+ |
| Accès BDD | PDO + requêtes préparées |
| Frontend | Tailwind CSS (CDN) + JavaScript vanilla |
| Sécurité | password_hash bcrypt, CSRF tokens, sessions |
| Serveur | Apache (XAMPP / WAMP / MAMP) |

## Base de données

**34 tables** organisées en 8 groupes fonctionnels.

| Groupe | Tables | Rôle |
|---|---|---|
| Identité & Sécurité | 8 | membre, adresse, role, permission, role_permission, membre_role, token, tentative_connexion |
| Audit & RGPD | 3 | audit_log, consentement_rgpd, log_connexion |
| Catalogue | 4 | categorie, article, vue_article, note_article |
| Vente & Paiement | 7 | statut_commande, achat_facture, ligne_facture, paiement, frais_port, code_promo, code_promo_utilisation |
| Engagement | 3 | favori, newsletter_abonne, like_contenu |
| Contenu & Blog | 4 | billet, commentaire, tag, billet_tag |
| Communication | 3 | minichat, message_prive, notification |
| Technique | 2 | parametre, recherche_log |

> Le schéma respecte le cahier des charges du professeur (les 7 tables imposées) et est enrichi pour couvrir les standards e-commerce 2026. Voir `docs/justification_tables.md` pour le détail.

## Sécurité

- ✅ **PDO + requêtes préparées** systématiques (aucune injection SQL possible)
- ✅ **bcrypt** via `password_hash` / `password_verify`
- ✅ **Jetons CSRF** sur tous les formulaires
- ✅ **`htmlspecialchars`** systématique à l'affichage
- ✅ **Anti brute-force** : blocage après N tentatives ratées
- ✅ **Upload sécurisé** : vérif MIME + extension + taille + renommage
- ✅ **Sessions** : `session_regenerate_id()` après login, cookies HTTPOnly
- ✅ **Vérification de droits** sur chaque page sensible
- ✅ **Audit log** conformité RGPD
- ✅ **Tokens à usage unique** pour reset password et vérif email
- ✅ **Consentements RGPD** tracés

## Installation

### Prérequis
- PHP >= 8.0
- MySQL >= 5.7 (ou MariaDB >= 10.3)
- Apache (XAMPP / WAMP / MAMP)

### Étapes
```bash
# 1. Cloner le repo
git clone https://github.com/<votre-username>/pdvweb.git
cd pdvweb

# 2. Configurer la BDD
cp config/config.example.php config/config.php
# Éditer config/config.php avec vos identifiants MySQL

# 3. Créer la base
mysql -u root -p < sql/01_schema.sql
mysql -u root -p < sql/02_seed.sql

# 4. Générer les hashes de mot de passe pour les comptes de test
php sql/generer_hashes.php

# 5. Pointer Apache sur le dossier public/
# (ou accéder à http://localhost/pdvweb/public/)
```

### Comptes de test

| Login | Mot de passe | Profil |
|---|---|---|
| `admin` | `admin2026` | Administrateur |
| `jdupont` | `test1234` | Membre |
| `smartin` | `test1234` | Membre |
| `mlambert` | `test1234` | Membre |

## Captures

> *Captures à venir au fil du développement — voir `docs/captures/`*

## Avancement

- [x] **Étape 1.** Architecture, schéma BDD (34 tables), données de test, README
- [x] **Étape 2.** Socle commun : config, classes utilitaires, header/footer Tailwind
- [x] **Étape 3.** Accueil + inscription + connexion + déconnexion + profil
- [x] **Étape 4.** Mini-chat
- [ ] **Étape 5.** Blog/News (billets, commentaires, recherche, tags, likes)
- [ ] **Étape 6.** Achats (catalogue, panier, commande, paiement, codes promo, historique)
- [ ] **Étape 7.** Administration (tableau de bord, gestion membres, articles, statistiques)
- [ ] **Étape 8.** Sécurité finale : CSRF, audit log, anti brute-force
- [ ] **Étape 9.** Documentation finale + captures + manuel utilisateur

## Auteur

Travail individuel — PRDW 2025-2026.

## Licence

Code source distribué sous licence MIT — voir [LICENSE](LICENSE).
