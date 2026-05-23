# 📖 Manuel utilisateur PDVWeb

Guide pratique pour découvrir et utiliser l'application PDVWeb.

---

## Sommaire

1. [Qui peut faire quoi ?](#1-qui-peut-faire-quoi-)
2. [Premiers pas — Visiteur](#2-premiers-pas--visiteur)
3. [Créer un compte](#3-créer-un-compte)
4. [Acheter un article](#4-acheter-un-article)
5. [Gérer son compte](#5-gérer-son-compte)
6. [Le blog](#6-le-blog)
7. [Le mini-chat](#7-le-mini-chat)
8. [Administration](#8-administration)
9. [FAQ](#9-faq)

---

## 1. Qui peut faire quoi ?

| Action | Visiteur (non connecté) | Membre | Administrateur |
|---|:---:|:---:|:---:|
| Parcourir le catalogue | ✅ | ✅ | ✅ |
| Voir le détail d'un article | ✅ | ✅ | ✅ |
| Mettre dans le panier | ✅ | ✅ | ✅ |
| Lire le blog | ✅ | ✅ | ✅ |
| Lire le mini-chat | ❌ | ✅ | ✅ |
| Envoyer un message privé | ❌ | ✅ | ✅ |
| Acheter | ❌ | ✅ | ✅ |
| Commenter un billet | ❌ | ✅ | ✅ |
| Poster sur le mini-chat | ❌ | ✅ | ✅ |
| Noter un article (acheté) | ❌ | ✅ | ✅ |
| Mettre en favori | ❌ | ✅ | ✅ |
| Espace admin | ❌ | ❌ | ✅ |

---

## 2. Premiers pas — Visiteur

**Page d'accueil** : `http://localhost/pdvweb/public/`

Sans être connecté, vous pouvez déjà :
- Cliquer sur **Catalogue** dans le menu pour voir tous les produits
- Filtrer par catégorie (Informatique, Hi-Fi, Livres)
- Trier par prix, popularité, etc.
- Mettre des articles dans le panier (le panier est conservé tant que vous restez sur le site)
- Lire le **Blog** (articles publiés par les administrateurs)

Pour faire plus, il faut un compte.

---

## 3. Créer un compte

1. Cliquez sur **Inscription** en haut à droite
2. Remplissez : prénom, nom, date de naissance, email, login, mot de passe
3. (Optionnel) Choisissez un avatar (.gif ou .jpg, max 5 Mo)
4. Cliquez sur **Créer mon compte**

Une fois connecté, le menu en haut à droite vous donne accès à :
- **Mon profil** — modifier vos infos, changer mot de passe
- **Mes adresses** — gérer vos adresses de livraison
- **Mes achats** — historique des commandes
- **Mes favoris** — articles que vous suivez
- **Mes notifications** (cloche) — alertes sur vos commandes

### Mot de passe oublié ?

Sur la page de connexion, cliquez sur **Mot de passe oublié ?**. Vous recevrez un lien valable 1 heure pour réinitialiser votre mot de passe.

### Comptes de test

En mode développement, des comptes prêts à l'emploi sont affichés sur la page de connexion :

| Login | Mot de passe | Rôle |
|---|---|---|
| `admin` | `admin2026` | Administrateur |
| `jdupont` | `test1234` | Membre |
| `smartin` | `test1234` | Membre |
| `mlambert` | `test1234` | Membre |

---

## 4. Acheter un article

### Mettre dans le panier

1. Sur n'importe quel article du catalogue, choisissez la quantité (1 à 10)
2. Cliquez sur **Ajouter au panier** — le badge en haut s'incrémente

### Passer commande

1. Cliquez sur l'icône 🛒 en haut à droite
2. Vérifiez vos articles, ajustez les quantités si besoin
3. Cliquez sur **Passer commande**
4. Choisissez :
   - Une **adresse de livraison** (si vous n'en avez pas, ajoutez-en une)
   - Un **mode de livraison** (Standard, Express, etc.)
   - (Optionnel) Un **code promo** — essayez `BIENVENUE10`
   - Le **mode de paiement** (carte, PayPal, virement — tous simulés pour le TFM)
5. Cliquez sur **Payer**
6. Vous arrivez sur la **page de confirmation** avec votre facture (imprimable)

### Le panier persiste...
- ✅ pendant toute votre session (vous pouvez vous balader sur le site)
- ❌ à la prochaine connexion (le panier est vidé au logout/login — conforme au cahier des charges)

### Consulter mes commandes

**Menu utilisateur → Mes achats** : liste de toutes vos commandes avec leur statut (Payée, En préparation, Expédiée, Livrée, etc.). Cliquez sur une commande pour voir la facture détaillée.

---

## 5. Gérer son compte

### Mon profil
- Modifier prénom, nom, email, date de naissance
- Changer l'avatar
- Changer le mot de passe (nécessite l'ancien)

### Mes adresses
- Ajouter plusieurs adresses (Domicile, Bureau, etc.)
- Définir une adresse par défaut
- Modifier ou supprimer une adresse existante

### Supprimer mon compte (RGPD)
**Mon profil → Zone dangereuse → Supprimer mon compte**

- Saisissez votre mot de passe + tapez `SUPPRIMER` en majuscules
- **Vos données personnelles sont anonymisées** (login, email, nom remplacés par des valeurs anonymes)
- Vos commandes et commentaires restent visibles mais comme **« Utilisateur supprimé »**
- Cette action est **irréversible**

> 📌 Conformité RGPD article 17 (droit à l'oubli) : anonymisation au lieu de suppression pure pour conserver l'intégrité des données comptables et historiques.

---

## 6. Le blog

Le **Blog** est alimenté par les administrateurs. En tant que membre, vous pouvez :

- **Lire** tous les billets
- **Filtrer** par tag ou rechercher par mot-clé
- **Trier** par récents / populaires / les plus commentés
- **Commenter** un billet
- **Modifier vos propres commentaires** (l'admin ne peut pas — c'est votre discours)
- **Aimer** un billet ou un commentaire (icône cœur)

### Modération admin

L'administrateur peut **supprimer** un commentaire jugé inapproprié, mais ne peut **jamais le modifier** — pour préserver l'intégrité du discours.

---

## 7. Le mini-chat

**Menu → Mini-chat** (membres connectés uniquement)

- Affiche les **10 derniers messages** de la communauté
- Compteur de caractères en temps réel (orange à 90%)
- Vous pouvez supprimer vos propres messages
- L'admin peut supprimer tous les messages
- Lien **"message"** à côté de chaque pseudo pour démarrer une **conversation privée** avec ce membre


## 7bis. La messagerie privée

**Menu utilisateur → Ma messagerie** ou icône ✉️ dans la navbar

La messagerie privée permet d'écrire à un autre membre en boucle fermée (les messages ne sont visibles que par expéditeur et destinataire).

### Démarrer une conversation

Trois façons :
- **Depuis la messagerie** : bouton "Nouveau message" → rechercher un membre par login/prénom/nom
- **Depuis un commentaire de blog** : lien "message" à côté du pseudo de l'auteur
- **Depuis le mini-chat** : lien "message" à côté du pseudo

### Dans une conversation

- Fil chronologique style WhatsApp (vos messages à droite, les siens à gauche)
- Indicateur "lu" sur vos messages quand l'autre les a consultés
- Possibilité d'envoyer une réponse directement depuis le fil

### Bloquer un membre

Dans une conversation, menu **⋮ → Bloquer ce membre** :
- Il ne pourra plus vous envoyer de message
- Le blocage est **unidirectionnel** (vous pouvez le débloquer à tout moment)
- Un admin peut toujours vous écrire (modération)

### Supprimer une conversation

Menu **⋮ → Supprimer la conversation** : efface tous les messages des deux côtés. **Irréversible.**

### Notifications

Quand vous recevez un MP :
- L'icône ✉️ affiche un badge rouge avec le nombre de non lus
- Une notification apparaît aussi dans la cloche 🔔
- Au clic, vous arrivez directement dans le fil concerné

---

## 8. Administration

L'espace administrateur (**Menu utilisateur → Administration**) regroupe :

### 📊 Tableau de bord
Vue d'ensemble : KPI principaux, graphique CA 30 jours, alerte stock bas.

### 👥 Membres
- Liste avec recherche, filtres (Actifs / Bloqués / Anonymisés / Admin)
- Fiche détaillée : profil + commandes + commentaires + connexions
- Actions : Bloquer / Débloquer / Promouvoir admin / Dégrader
- Sécurité : impossible de modifier votre propre compte

### 📦 Articles & Stock
- CRUD complet avec upload d'image
- Soft delete (dispo = 0) pour préserver l'historique des commandes

### 📂 Catégories
- CRUD complet (création/édition/suppression)
- Une catégorie ne peut être supprimée que si elle est vide

### 🛒 Commandes
- Liste de toutes les commandes
- Changement de statut (le client est notifié automatiquement)

### 📰 Billets de blog
- Création/édition avec éditeur Markdown
- Gestion des tags
- Aperçu en direct

### 🎟️ Codes promo
- Activation / désactivation
- Suivi des utilisations

### 🚚 Frais de port
- Grille de livraison par pays
- Activation par option (Standard, Express, etc.)

### 🏆 Tops & classements
- Top articles (vendus / vus / notés)
- Top membres (acheteurs / blogueurs)
- Alertes stock bas

### 📊 Statistiques de connexion
- KPI 24h / 7j / 30j
- Graphique d'activité sur 30 jours
- Top 10 membres les plus connectés

### 🔍 Recherches utilisateurs
- Top termes recherchés
- Recherches sans résultat (opportunités produit/contenu)

### 📝 Journal d'audit
- Toutes les actions sensibles tracées
- Filtres : action, membre, entité, période
- Détails JSON consultables

### 🔒 Sécurité & Maintenance
- État de la configuration (DEV_MODE, HTTPS, cookies, etc.)
- Liste des headers HTTP envoyés
- Purge des données anciennes (RGPD)

### 🗑️ Corbeille
- Restauration des billets et commentaires supprimés

---

## 9. FAQ

### Le paiement est-il réel ?
**Non.** C'est un paiement simulé pour les besoins du TFM. Aucune vraie carte n'est requise. Une bannière le rappelle sur la page de commande.

### Mon panier a disparu après reconnexion, normal ?
**Oui.** Le cahier des charges précise : *« panier persistant jusqu'à la prochaine connexion »*. Le panier est volontairement vidé à chaque nouvelle session.

### Quels formats d'image sont acceptés ?
Uniquement **.gif**, **.jpg** et **.jpeg**, 5 Mo max. Cette restriction stricte protège contre les uploads malveillants.

### Pourquoi je ne peux pas noter un article ?
Vous devez avoir **acheté** l'article au moins une fois pour pouvoir le noter. C'est un système d'avis vérifiés.

### Comment savoir si une commande a été expédiée ?
Vous recevez une **notification dans la cloche** dès que l'admin change le statut. Cliquez dessus pour voir la facture mise à jour.

### Puis-je récupérer mon compte après suppression ?
**Non.** L'anonymisation est irréversible. Vos commandes restent enregistrées sous "Utilisateur supprimé" pour conformité comptable, mais vous ne pouvez plus accéder au compte.

### Pourquoi le site bloque mes connexions ?
Après **5 mauvais mots de passe** sur le même login → blocage 15 min.
Après **10 tentatives** d'une même IP → blocage 15 min de toute connexion depuis cette IP.

C'est une protection anti brute-force.

---

*Manuel utilisateur — Version finale (étape 9, mai 2026).*
