# 📊 Référence du schéma de base de données

Document **critique** qui liste **toutes les colonnes** de chaque table, avec leur **vrai nom** tel qu'il existe en BDD.

À consulter avant d'écrire toute requête SQL pour éviter d'inventer des colonnes.

---

## ⚠️ AVERTISSEMENT

**Toute requête SQL doit utiliser les noms exacts listés ici.**

Bugs récurrents évités :
- `article.actif` ❌ → `article.dispo` ✓
- `commentaire.contenu` ❌ → `commentaire.corps` ✓
- `note_article.commentaire` ❌ → `note_article.avis` ✓
- `tentative_connexion.date_tentative` ❌ → `tentative_connexion.date_tent` ✓

---

## Tables principales (par groupe fonctionnel)

### 1. Identité & Sécurité

#### `membre`

```
id_membre              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
nom                    VARCHAR(60)  NOT NULL
prenom                 VARCHAR(60)  NOT NULL
date_naissance         DATE         NOT NULL
email                  VARCHAR(150) NOT NULL UNIQUE
email_verifie          TINYINT(1)   NOT NULL DEFAULT 0
login                  VARCHAR(50)  NOT NULL UNIQUE
mot_passe              VARCHAR(255) NOT NULL          -- hash bcrypt
avatar                 VARCHAR(255) DEFAULT NULL      -- nom fichier .gif/.jpeg
statut                 ENUM('membre','admin') NOT NULL DEFAULT 'membre'
indesirable            TINYINT(1)   NOT NULL DEFAULT 0
derniere_connexion     DATETIME     DEFAULT NULL
date_inscription       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
date_anonymisation     DATETIME     DEFAULT NULL
```

#### `adresse`

```
id_adresse        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre         INT UNSIGNED NOT NULL
nom_destinataire  VARCHAR(120) NOT NULL
ligne1            VARCHAR(150) NOT NULL
ligne2            VARCHAR(150) DEFAULT NULL
code_postal       VARCHAR(20)  NOT NULL
ville             VARCHAR(80)  NOT NULL
pays              VARCHAR(60)  NOT NULL DEFAULT 'Belgique'
est_defaut        TINYINT(1)   NOT NULL DEFAULT 0
```

#### `role`, `permission`, `role_permission`, `membre_role`

Tables relationnelles pour le système de rôles.

#### `token`

```
id_token          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre         INT UNSIGNED NOT NULL
type              VARCHAR(40)  NOT NULL    -- 'reset_password', 'verif_email'
token_hash        VARCHAR(255) NOT NULL    -- SHA-256 du token
date_creation     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
date_expiration   DATETIME     NOT NULL
date_utilisation  DATETIME     DEFAULT NULL
```

#### `tentative_connexion`

```
id_tentative      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
login_essaye      VARCHAR(100) NOT NULL          -- ⚠ pas "login"
ip                VARCHAR(45)  NOT NULL
date_tent         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP  -- ⚠ pas "date_tentative"
succes            TINYINT(1)   NOT NULL DEFAULT 0
```

---

### 2. Audit & RGPD

#### `audit_log`

```
id_audit          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
date_action       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
id_acteur         INT UNSIGNED DEFAULT NULL          -- id_membre (null si système)
action            VARCHAR(80)  NOT NULL              -- 'membre.bloquer', 'article.creer'
entite            VARCHAR(60)  DEFAULT NULL          -- nom de la table concernée
id_entite         INT UNSIGNED DEFAULT NULL
contexte_json     JSON         DEFAULT NULL          -- détails libres
ip                VARCHAR(45)  DEFAULT NULL
user_agent        VARCHAR(255) DEFAULT NULL
```

#### `consentement_rgpd`, `log_connexion`

Tables RGPD pour conformité.

---

### 3. Catalogue

#### `categorie`

```
id_categorie      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code              VARCHAR(40)  NOT NULL UNIQUE       -- 'informatique', 'livres'
nom               VARCHAR(80)  NOT NULL
description       TEXT         DEFAULT NULL
ordre             INT          NOT NULL DEFAULT 0
actif             TINYINT(1)   NOT NULL DEFAULT 1    -- migration 09
```

#### `article`

```
id_article        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
nom               VARCHAR(150) NOT NULL
id_categorie      INT UNSIGNED NOT NULL
description       TEXT         DEFAULT NULL
prix              DECIMAL(10,2) NOT NULL
image             VARCHAR(255) DEFAULT NULL
stock             INT          NOT NULL DEFAULT 0
dispo             TINYINT(1)   NOT NULL DEFAULT 1    -- ⚠ pas "actif"
poids_grammes     INT          DEFAULT NULL
date_ajout        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

#### `article_tag` (migration 10)

```
id_article        INT UNSIGNED NOT NULL
id_tag            INT UNSIGNED NOT NULL
PRIMARY KEY (id_article, id_tag)
FK ON DELETE CASCADE sur les deux
```

#### `vue_article`

```
id_vue            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_article        INT UNSIGNED NOT NULL
id_membre         INT UNSIGNED DEFAULT NULL
date_vue          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

#### `note_article`

```
id_membre         INT UNSIGNED NOT NULL              -- PK composite
id_article        INT UNSIGNED NOT NULL              -- PK composite
note              TINYINT UNSIGNED NOT NULL          -- 1 à 5
avis              TEXT         DEFAULT NULL          -- ⚠ pas "commentaire"
date_note         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

---

### 4. Vente & Paiement

#### `statut_commande`

```
id_statut         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code              VARCHAR(40)  NOT NULL UNIQUE   -- 'en_attente', 'payee'
nom               VARCHAR(80)  NOT NULL
couleur           VARCHAR(20)  DEFAULT NULL
ordre             INT          NOT NULL DEFAULT 0
```

#### `achat_facture`

```
id_facture              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre               INT UNSIGNED NOT NULL
id_statut               INT UNSIGNED NOT NULL
id_adresse_livraison    INT UNSIGNED DEFAULT NULL
id_adresse_facturation  INT UNSIGNED DEFAULT NULL
reference               VARCHAR(20)  NOT NULL UNIQUE
sous_total              DECIMAL(10,2) NOT NULL
montant_frais_port      DECIMAL(8,2)  NOT NULL DEFAULT 0   -- ⚠ pas "frais_port"
montant_remise          DECIMAL(8,2)  NOT NULL DEFAULT 0   -- ⚠ pas "remise"
prix_total              DECIMAL(10,2) NOT NULL
date_achat              DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
notes                   TEXT         DEFAULT NULL
mode_livraison          VARCHAR(80)  DEFAULT NULL   -- migration 05
```

#### `ligne_facture`

```
id_ligne          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_facture        INT UNSIGNED NOT NULL
id_article        INT UNSIGNED NOT NULL
prix_unitaire     DECIMAL(10,2) NOT NULL    -- snapshot au moment de l'achat
quantite          INT          NOT NULL
```

#### `paiement`

```
id_paiement       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_facture        INT UNSIGNED NOT NULL
montant           DECIMAL(10,2) NOT NULL
mode              VARCHAR(40)  NOT NULL   -- 'carte', 'virement', 'paypal'
reference_externe VARCHAR(120) DEFAULT NULL
date_paiement     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

#### `frais_port`

```
id_frais           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
nom                VARCHAR(80)  NOT NULL
pays               VARCHAR(60)  NOT NULL
montant_min_panier DECIMAL(10,2) DEFAULT NULL
montant_max_panier DECIMAL(10,2) DEFAULT NULL
prix               DECIMAL(8,2)  NOT NULL
delai_jours        INT          DEFAULT NULL
actif              TINYINT(1)   NOT NULL DEFAULT 1
```

#### `code_promo`

```
id_code                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code                      VARCHAR(40)  NOT NULL UNIQUE
description               TEXT         DEFAULT NULL
type_remise               ENUM('pourcentage','montant_fixe','frais_port') NOT NULL
valeur                    DECIMAL(8,2) NOT NULL
montant_min_panier        DECIMAL(10,2) DEFAULT NULL
utilisations_max          INT          DEFAULT NULL
utilisations_par_membre   INT          DEFAULT NULL
date_debut                DATETIME     DEFAULT NULL
date_fin                  DATETIME     DEFAULT NULL
actif                     TINYINT(1)   NOT NULL DEFAULT 1
```

#### `code_promo_utilisation`

```
id_utilisation    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_code           INT UNSIGNED NOT NULL
id_membre         INT UNSIGNED NOT NULL
id_facture        INT UNSIGNED NOT NULL
date_utilisation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

---

### 5. Engagement

#### `favori`

```
id_membre   INT UNSIGNED NOT NULL    -- PK composite
id_article  INT UNSIGNED NOT NULL    -- PK composite
date_ajout  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

#### `newsletter_abonne`

```
id_abonne   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
email       VARCHAR(150) NOT NULL UNIQUE
id_membre   INT UNSIGNED DEFAULT NULL
date_inscr  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
desabonne   TINYINT(1)   NOT NULL DEFAULT 0
```

#### `like_contenu`

```
id_like      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre    INT UNSIGNED NOT NULL
type_contenu VARCHAR(40)  NOT NULL   -- 'billet', 'commentaire'
id_contenu   INT UNSIGNED NOT NULL
date_like    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

---

### 6. Contenu & Blog

#### `billet`

```
id_billet              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre              INT UNSIGNED DEFAULT NULL   -- auteur
titre                  VARCHAR(200) NOT NULL
corps                  TEXT         NOT NULL
date_billet            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
date_suppression       DATETIME     DEFAULT NULL   -- soft delete
id_membre_suppression  INT UNSIGNED DEFAULT NULL
resume                 TEXT         DEFAULT NULL   -- migration 08
image                  VARCHAR(255) DEFAULT NULL   -- migration 08
```

#### `commentaire`

```
id_commentaire    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_billet         INT UNSIGNED NOT NULL
id_membre         INT UNSIGNED DEFAULT NULL
corps             TEXT         NOT NULL          -- ⚠ pas "contenu"
date_comm         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP   -- ⚠ pas "date_commentaire"
date_suppression  DATETIME     DEFAULT NULL
```

#### `tag`

```
id_tag    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code      VARCHAR(40)  NOT NULL UNIQUE
nom       VARCHAR(60)  NOT NULL
```

#### `billet_tag`

```
id_billet  INT UNSIGNED NOT NULL
id_tag     INT UNSIGNED NOT NULL
PRIMARY KEY (id_billet, id_tag)
```

---

### 7. Communication

#### `minichat`

```
id_message    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre     INT UNSIGNED NOT NULL
pseudo        VARCHAR(50)  DEFAULT NULL   -- migration 07
message       VARCHAR(300) NOT NULL
date_message  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

#### `message_prive` (migration 06)

```
id_message      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_expediteur   INT UNSIGNED NOT NULL
id_destinataire INT UNSIGNED NOT NULL
corps           TEXT         NOT NULL
date_envoi      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
date_lecture    DATETIME     DEFAULT NULL
```

#### `notification`

```
id_notification  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre        INT UNSIGNED NOT NULL
type             VARCHAR(40)  NOT NULL   -- 'commande.statut', 'mp.recu', etc.
titre            VARCHAR(150) NOT NULL
message          TEXT         NOT NULL
url_cible        VARCHAR(255) DEFAULT NULL
lue              TINYINT(1)   NOT NULL DEFAULT 0
date_creation    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
date_lecture     DATETIME     DEFAULT NULL
```

---

### 8. Système

#### `parametre`

```
id_parametre   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
cle            VARCHAR(80)  NOT NULL UNIQUE   -- 'site.nom', 'site.slogan'
valeur         TEXT         DEFAULT NULL
description    VARCHAR(255) DEFAULT NULL
date_modif     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

#### `recherche_log`

```
id_recherche   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
id_membre      INT UNSIGNED DEFAULT NULL
terme          VARCHAR(200) NOT NULL
nb_resultats   INT          NOT NULL DEFAULT 0
contexte       VARCHAR(40)  NOT NULL   -- 'catalogue', 'blog'
date_recherche DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
```

---

## Procédure avant d'écrire une requête SQL

1. **Identifier la table** concernée
2. **Vérifier** les colonnes exactes ci-dessus
3. **Écrire** la requête en utilisant les noms exacts
4. **Tester** en local avant de commiter

### Commande utile pour vérifier rapidement

```bash
# Voir la définition complète d'une table
grep -A 15 "^CREATE TABLE article " sql/01_schema.sql
```

### Erreurs typiques à éviter

| ❌ Erreur fréquente | ✓ Bon nom |
|---|---|
| `article.actif` | `article.dispo` |
| `commentaire.contenu` | `commentaire.corps` |
| `commentaire.date_commentaire` | `commentaire.date_comm` |
| `note_article.commentaire` | `note_article.avis` |
| `tentative_connexion.login` | `tentative_connexion.login_essaye` |
| `tentative_connexion.date_tentative` | `tentative_connexion.date_tent` |
| `achat_facture.frais_port` | `achat_facture.montant_frais_port` |
| `achat_facture.remise` | `achat_facture.montant_remise` |

---

**Document maintenu manuellement. Mettre à jour à chaque migration SQL.**
