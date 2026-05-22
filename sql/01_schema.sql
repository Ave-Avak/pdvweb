-- =====================================================================
-- PDVWeb  -  Travail de Fin de Module PRDW 2025-2026
-- Schema de base de données  -  VERSION PRO COMPLÈTE
-- ---------------------------------------------------------------------
-- 34 tables organisées en 8 groupes fonctionnels.
-- Conforme au "Schema Simplifié BD Projet Dev. Web" fourni par le prof,
-- étendu pour couvrir les standards e-commerce modernes 2026 :
--   - séparation des adresses (1 membre -> N adresses)
--   - paiements multiples par commande
--   - statuts de commande
--   - frais de port, codes promo
--   - audit log (conformité RGPD)
--   - notifications internes
--   - rôles & permissions étendus
--   - "se souvenir de moi", tokens (reset password, confirm email)
--   - newsletter, likes, tags
--   - statistiques de consultation
-- =====================================================================

DROP DATABASE IF EXISTS pdvweb;

CREATE DATABASE pdvweb
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pdvweb;


-- =====================================================================
--  GROUPE 1  -  IDENTITÉ & SÉCURITÉ  (8 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. membre  [SCHÉMA PROF — Membre/Admin]
-- ---------------------------------------------------------------------
CREATE TABLE membre (
  id_membre        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom              VARCHAR(60)  NOT NULL,
  prenom           VARCHAR(60)  NOT NULL,
  date_naissance   DATE         NOT NULL,
  email            VARCHAR(150) NOT NULL UNIQUE,
  email_verifie    TINYINT(1)   NOT NULL DEFAULT 0,         -- 1 après clic sur lien de confirmation
  login            VARCHAR(50)  NOT NULL UNIQUE,
  mot_passe        VARCHAR(255) NOT NULL,                   -- hash bcrypt
  avatar           VARCHAR(255) DEFAULT NULL,               -- nom fichier .gif ou .jpeg
  statut           ENUM('membre','admin') NOT NULL DEFAULT 'membre',
  indesirable      TINYINT(1)   NOT NULL DEFAULT 0,         -- bloqué par l'admin
  derniere_connexion DATETIME   DEFAULT NULL,
  date_inscription DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_membre_statut (statut),
  INDEX idx_membre_indesirable (indesirable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 2. adresse  [STANDARD E-COMMERCE]
-- Un membre peut avoir plusieurs adresses (livraison principale,
-- adresse de facturation différente, adresses secondaires).
-- ---------------------------------------------------------------------
CREATE TABLE adresse (
  id_adresse   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre    INT UNSIGNED NOT NULL,
  libelle      VARCHAR(60)  NOT NULL,                       -- ex: 'Domicile', 'Bureau', 'Maison parents'
  nom          VARCHAR(60)  NOT NULL,                       -- peut différer du nom du compte
  prenom       VARCHAR(60)  NOT NULL,
  rue          VARCHAR(150) NOT NULL,
  numero       VARCHAR(20)  NOT NULL,
  complement   VARCHAR(100) DEFAULT NULL,                   -- boîte, étage, code interphone
  cp           VARCHAR(10)  NOT NULL,
  ville        VARCHAR(80)  NOT NULL,
  pays         VARCHAR(60)  NOT NULL DEFAULT 'Belgique',
  telephone    VARCHAR(30)  DEFAULT NULL,
  type         ENUM('livraison','facturation','les_deux') NOT NULL DEFAULT 'les_deux',
  est_defaut   TINYINT(1)   NOT NULL DEFAULT 0,             -- adresse par défaut du membre
  date_creation DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_adresse_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_adresse_membre (id_membre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 3. role  [STANDARD - permissions étendues]
-- Système de rôles + permissions, pour aller au-delà du simple ENUM statut.
-- Permet de créer demain un "modérateur" qui peut gérer les commentaires
-- mais pas les articles, sans toucher au code.
-- ---------------------------------------------------------------------
CREATE TABLE role (
  id_role     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(40)  NOT NULL UNIQUE,                 -- ex: 'admin', 'moderateur', 'membre'
  nom         VARCHAR(80)  NOT NULL,                        -- libellé affiché
  description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 4. permission  [STANDARD]
-- Une permission atomique : 'article.creer', 'membre.bloquer', etc.
-- ---------------------------------------------------------------------
CREATE TABLE permission (
  id_permission INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code          VARCHAR(60)  NOT NULL UNIQUE,               -- ex: 'article.creer', 'membre.bloquer'
  nom           VARCHAR(120) NOT NULL,                      -- libellé affiché
  groupe        VARCHAR(40)  DEFAULT NULL                   -- regroupement pour UI admin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 5. role_permission  [LIAISON]
-- Quelles permissions chaque rôle possède.
-- ---------------------------------------------------------------------
CREATE TABLE role_permission (
  id_role       INT UNSIGNED NOT NULL,
  id_permission INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_role, id_permission),
  CONSTRAINT fk_rp_role
    FOREIGN KEY (id_role) REFERENCES role(id_role) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm
    FOREIGN KEY (id_permission) REFERENCES permission(id_permission) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 6. membre_role  [LIAISON]
-- Un membre peut avoir plusieurs rôles (modérateur + comptable, par ex.).
-- ---------------------------------------------------------------------
CREATE TABLE membre_role (
  id_membre    INT UNSIGNED NOT NULL,
  id_role      INT UNSIGNED NOT NULL,
  date_attribution DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_membre, id_role),
  CONSTRAINT fk_mr_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_mr_role
    FOREIGN KEY (id_role) REFERENCES role(id_role) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 7. token  [SÉCURITÉ STANDARD]
-- Jetons à usage unique pour : reset mot de passe, vérification email,
-- invitations, etc. Hash stocké, pas le token en clair.
-- ---------------------------------------------------------------------
CREATE TABLE token (
  id_token       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre      INT UNSIGNED NOT NULL,
  type           ENUM('reset_password','verif_email','invitation') NOT NULL,
  token_hash     VARCHAR(255) NOT NULL,                     -- hash SHA-256 du token
  date_creation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_expiration DATETIME    NOT NULL,
  date_utilisation DATETIME   DEFAULT NULL,                 -- NULL = pas encore utilisé
  CONSTRAINT fk_token_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_token_hash (token_hash),
  INDEX idx_token_expir (date_expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 8. tentative_connexion  [SÉCURITÉ — anti brute-force]
-- ---------------------------------------------------------------------
CREATE TABLE tentative_connexion (
  id_tentative INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  login_essaye VARCHAR(50) NOT NULL,                        -- peut être un login inexistant
  ip           VARCHAR(45) NOT NULL,
  user_agent   VARCHAR(255) DEFAULT NULL,
  date_tent    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  succes       TINYINT(1)  NOT NULL DEFAULT 0,
  INDEX idx_tent_login_date (login_essaye, date_tent),
  INDEX idx_tent_ip_date    (ip, date_tent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 2  -  AUDIT & CONFORMITÉ RGPD  (3 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 9. audit_log  [CONFORMITÉ RGPD]
-- Trace toutes les actions sensibles : qui a modifié quoi, quand, depuis où.
-- ---------------------------------------------------------------------
CREATE TABLE audit_log (
  id_log         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre      INT UNSIGNED DEFAULT NULL,                 -- NULL = action système
  action         VARCHAR(80) NOT NULL,                      -- ex: 'article.creer', 'membre.bloquer'
  entite         VARCHAR(40) DEFAULT NULL,                  -- table affectée
  id_entite      INT UNSIGNED DEFAULT NULL,                 -- PK de l'enregistrement affecté
  details        JSON         DEFAULT NULL,                 -- avant/après en JSON
  ip             VARCHAR(45) DEFAULT NULL,
  user_agent     VARCHAR(255) DEFAULT NULL,
  date_action    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE SET NULL,
  INDEX idx_audit_date    (date_action),
  INDEX idx_audit_action  (action),
  INDEX idx_audit_entite  (entite, id_entite),
  INDEX idx_audit_membre  (id_membre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 10. consentement_rgpd  [CONFORMITÉ RGPD]
-- Trace les consentements donnés par les membres (cookies, newsletter, etc.)
-- ---------------------------------------------------------------------
CREATE TABLE consentement_rgpd (
  id_consentement INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre       INT UNSIGNED DEFAULT NULL,                -- NULL si visiteur anonyme
  ip              VARCHAR(45) NOT NULL,
  type            ENUM('cookies_essentiels','cookies_analytique','newsletter','cgv') NOT NULL,
  accepte         TINYINT(1)  NOT NULL,
  date_consent    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_consent_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE SET NULL,
  INDEX idx_consent_membre (id_membre),
  INDEX idx_consent_type   (type, date_consent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 11. log_connexion  [SCHÉMA PROF]
-- Historique des connexions réussies (stats admin).
-- ---------------------------------------------------------------------
CREATE TABLE log_connexion (
  id_log    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre INT UNSIGNED NOT NULL,
  date_log  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip        VARCHAR(45)  DEFAULT NULL,
  CONSTRAINT fk_log_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_log_membre_date (id_membre, date_log)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 3  -  CATALOGUE PRODUITS  (4 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 12. categorie
-- ---------------------------------------------------------------------
CREATE TABLE categorie (
  id_categorie INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code         VARCHAR(30)  NOT NULL UNIQUE,                -- slug technique
  nom          VARCHAR(80)  NOT NULL,
  description  VARCHAR(255) DEFAULT NULL,
  ordre        TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 13. article  [SCHÉMA PROF]
-- ---------------------------------------------------------------------
CREATE TABLE article (
  id_article    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom           VARCHAR(150) NOT NULL,
  id_categorie  INT UNSIGNED NOT NULL,
  description   TEXT         DEFAULT NULL,
  prix          DECIMAL(8,2) NOT NULL,
  image         VARCHAR(255) DEFAULT NULL,
  stock         INT UNSIGNED NOT NULL DEFAULT 0,
  dispo         TINYINT(1)   NOT NULL DEFAULT 1,
  poids_grammes INT UNSIGNED DEFAULT NULL,                  -- pour calcul frais de port
  date_ajout    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_article_categorie
    FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie),
  INDEX idx_article_categorie (id_categorie),
  INDEX idx_article_dispo (dispo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 14. vue_article  [ANALYTIQUE]
-- Compteur de vues par article par jour (agrégé).
-- ---------------------------------------------------------------------
CREATE TABLE vue_article (
  id_vue     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_article INT UNSIGNED NOT NULL,
  id_membre  INT UNSIGNED DEFAULT NULL,                     -- NULL si UNM
  ip         VARCHAR(45) DEFAULT NULL,
  date_vue   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vue_article
    FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE,
  CONSTRAINT fk_vue_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE SET NULL,
  INDEX idx_vue_article_date (id_article, date_vue),
  INDEX idx_vue_membre (id_membre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 15. note_article  [E-COMMERCE STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE note_article (
  id_membre  INT UNSIGNED NOT NULL,
  id_article INT UNSIGNED NOT NULL,
  note       TINYINT UNSIGNED NOT NULL,                     -- 1 à 5 étoiles
  avis       TEXT         DEFAULT NULL,
  date_note  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_membre, id_article),
  CONSTRAINT fk_note_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_note_article
    FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE,
  CONSTRAINT chk_note_intervalle CHECK (note BETWEEN 1 AND 5),
  INDEX idx_note_article (id_article)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 4  -  VENTE & PAIEMENT  (7 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 16. statut_commande  [STANDARD]
-- Référentiel des statuts possibles d'une commande.
-- ---------------------------------------------------------------------
CREATE TABLE statut_commande (
  id_statut INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code      VARCHAR(40)  NOT NULL UNIQUE,
  nom       VARCHAR(80)  NOT NULL,
  couleur   VARCHAR(20)  DEFAULT NULL,                      -- pour l'affichage UI (badge couleur)
  ordre     TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 17. achat_facture  [SCHÉMA PROF — enrichi]
-- ---------------------------------------------------------------------
CREATE TABLE achat_facture (
  id_facture          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre           INT UNSIGNED NOT NULL,
  id_statut           INT UNSIGNED NOT NULL,
  id_adresse_livraison INT UNSIGNED DEFAULT NULL,           -- adresse au moment de la commande
  id_adresse_facturation INT UNSIGNED DEFAULT NULL,
  reference           VARCHAR(20)  NOT NULL UNIQUE,         -- ex: 'PDV-20260613-0001'
  sous_total          DECIMAL(10,2) NOT NULL,               -- avant frais & remise
  montant_frais_port  DECIMAL(8,2)  NOT NULL DEFAULT 0,
  montant_remise      DECIMAL(8,2)  NOT NULL DEFAULT 0,     -- via code promo
  prix_total          DECIMAL(10,2) NOT NULL,               -- total final
  date_achat          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes               TEXT         DEFAULT NULL,            -- notes internes admin
  CONSTRAINT fk_facture_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_facture_statut
    FOREIGN KEY (id_statut) REFERENCES statut_commande(id_statut),
  CONSTRAINT fk_facture_addr_liv
    FOREIGN KEY (id_adresse_livraison) REFERENCES adresse(id_adresse) ON DELETE SET NULL,
  CONSTRAINT fk_facture_addr_fac
    FOREIGN KEY (id_adresse_facturation) REFERENCES adresse(id_adresse) ON DELETE SET NULL,
  INDEX idx_facture_membre_date (id_membre, date_achat),
  INDEX idx_facture_statut (id_statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 18. ligne_facture  [SCHÉMA PROF]
-- ---------------------------------------------------------------------
CREATE TABLE ligne_facture (
  id_ligne      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_facture    INT UNSIGNED NOT NULL,
  id_article    INT UNSIGNED NOT NULL,
  quantite      INT UNSIGNED NOT NULL,
  prix_unitaire DECIMAL(8,2) NOT NULL,                      -- figé au moment de l'achat
  CONSTRAINT fk_ligne_facture
    FOREIGN KEY (id_facture) REFERENCES achat_facture(id_facture) ON DELETE CASCADE,
  CONSTRAINT fk_ligne_article
    FOREIGN KEY (id_article) REFERENCES article(id_article)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 19. paiement  [E-COMMERCE STANDARD]
-- Une commande peut avoir 1 ou plusieurs paiements (acompte + solde,
-- paiement échelonné, remboursement partiel...).
-- ---------------------------------------------------------------------
CREATE TABLE paiement (
  id_paiement   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_facture    INT UNSIGNED NOT NULL,
  methode       ENUM('carte','paypal','virement','especes','cheque') NOT NULL,
  reference_ext VARCHAR(100) DEFAULT NULL,                  -- ID transaction du prestataire (Stripe, PayPal...)
  montant       DECIMAL(10,2) NOT NULL,
  statut        ENUM('en_attente','accepte','refuse','rembourse') NOT NULL DEFAULT 'en_attente',
  date_paiement DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_paiement_facture
    FOREIGN KEY (id_facture) REFERENCES achat_facture(id_facture) ON DELETE CASCADE,
  INDEX idx_paiement_facture (id_facture),
  INDEX idx_paiement_date (date_paiement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 20. frais_port  [E-COMMERCE STANDARD]
-- Grille de frais de livraison par tranche (poids ou montant).
-- ---------------------------------------------------------------------
CREATE TABLE frais_port (
  id_frais        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom             VARCHAR(80)  NOT NULL,                    -- 'Standard', 'Express', 'Point relais'
  pays            VARCHAR(60)  NOT NULL DEFAULT 'Belgique',
  montant_min_panier DECIMAL(10,2) DEFAULT NULL,            -- ne s'applique que si panier >= ce montant
  montant_max_panier DECIMAL(10,2) DEFAULT NULL,            -- ne s'applique que si panier <= ce montant
  prix            DECIMAL(8,2) NOT NULL,
  delai_jours     TINYINT UNSIGNED DEFAULT NULL,            -- délai estimé
  actif           TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 21. code_promo  [E-COMMERCE STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE code_promo (
  id_code             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code                VARCHAR(40)  NOT NULL UNIQUE,
  description         VARCHAR(255) DEFAULT NULL,
  type_remise         ENUM('pourcentage','montant_fixe','livraison_offerte') NOT NULL,
  valeur              DECIMAL(8,2) NOT NULL,                -- % ou montant fixe selon type
  montant_min_panier  DECIMAL(10,2) DEFAULT 0,
  utilisations_max    INT UNSIGNED DEFAULT NULL,            -- NULL = illimité
  utilisations_par_membre INT UNSIGNED DEFAULT 1,
  date_debut          DATETIME     NOT NULL,
  date_fin            DATETIME     NOT NULL,
  actif               TINYINT(1)   NOT NULL DEFAULT 1,
  INDEX idx_promo_actif_dates (actif, date_debut, date_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 22. code_promo_utilisation  [LIAISON N-N + données]
-- Trace chaque utilisation d'un code promo (1 code -> N utilisations).
-- ---------------------------------------------------------------------
CREATE TABLE code_promo_utilisation (
  id_utilisation INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_code        INT UNSIGNED NOT NULL,
  id_membre      INT UNSIGNED NOT NULL,
  id_facture     INT UNSIGNED NOT NULL,
  montant_remise DECIMAL(8,2) NOT NULL,
  date_utilisation DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cpu_code
    FOREIGN KEY (id_code) REFERENCES code_promo(id_code) ON DELETE CASCADE,
  CONSTRAINT fk_cpu_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_cpu_facture
    FOREIGN KEY (id_facture) REFERENCES achat_facture(id_facture) ON DELETE CASCADE,
  INDEX idx_cpu_code_membre (id_code, id_membre)            -- pour respecter utilisations_par_membre
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 5  -  ENGAGEMENT & FIDÉLITÉ  (3 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 23. favori  [E-COMMERCE STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE favori (
  id_membre  INT UNSIGNED NOT NULL,
  id_article INT UNSIGNED NOT NULL,
  date_ajout DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_membre, id_article),
  CONSTRAINT fk_favori_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_favori_article
    FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 24. newsletter_abonne  [MARKETING STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE newsletter_abonne (
  id_abonne   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(150) NOT NULL UNIQUE,
  id_membre   INT UNSIGNED DEFAULT NULL,                    -- NULL si abonné non-membre
  actif       TINYINT(1)   NOT NULL DEFAULT 1,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_desabo DATETIME     DEFAULT NULL,
  CONSTRAINT fk_news_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 25. like_contenu  [SOCIAL STANDARD - polymorphe]
-- Permet de liker un billet, un commentaire, un article — table unique
-- avec une colonne "type" pour distinguer.
-- ---------------------------------------------------------------------
CREATE TABLE like_contenu (
  id_membre  INT UNSIGNED NOT NULL,
  type       ENUM('billet','commentaire','article') NOT NULL,
  id_cible   INT UNSIGNED NOT NULL,                         -- PK de l'entité likée
  date_like  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_membre, type, id_cible),
  CONSTRAINT fk_like_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_like_cible (type, id_cible)                     -- pour compter les likes par cible
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 6  -  CONTENU & BLOG  (4 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 26. billet  [SCHÉMA PROF]
-- ---------------------------------------------------------------------
CREATE TABLE billet (
  id_billet   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre   INT UNSIGNED NOT NULL,
  titre       VARCHAR(200) NOT NULL,
  corps       TEXT         NOT NULL,
  date_billet DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_billet_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_billet_titre (titre),
  INDEX idx_billet_date  (date_billet)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 27. commentaire  [SCHÉMA PROF]
-- ---------------------------------------------------------------------
CREATE TABLE commentaire (
  id_commentaire INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_billet      INT UNSIGNED NOT NULL,
  id_membre      INT UNSIGNED NOT NULL,
  corps          TEXT         NOT NULL,
  date_comm      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comm_billet
    FOREIGN KEY (id_billet) REFERENCES billet(id_billet) ON DELETE CASCADE,
  CONSTRAINT fk_comm_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_comm_membre_date (id_membre, date_comm)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 28. tag  [BLOG STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE tag (
  id_tag INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code   VARCHAR(40) NOT NULL UNIQUE,                       -- slug : 'promo', 'nouveaute'
  nom    VARCHAR(60) NOT NULL                               -- 'Promotion', 'Nouveauté'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 29. billet_tag  [LIAISON N-N]
-- ---------------------------------------------------------------------
CREATE TABLE billet_tag (
  id_billet INT UNSIGNED NOT NULL,
  id_tag    INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_billet, id_tag),
  CONSTRAINT fk_bt_billet
    FOREIGN KEY (id_billet) REFERENCES billet(id_billet) ON DELETE CASCADE,
  CONSTRAINT fk_bt_tag
    FOREIGN KEY (id_tag) REFERENCES tag(id_tag) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 7  -  COMMUNICATION  (3 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 30. minichat  [CAHIER DES CHARGES]
-- ---------------------------------------------------------------------
CREATE TABLE minichat (
  id_message   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre    INT UNSIGNED NOT NULL,
  message      VARCHAR(255) NOT NULL,
  date_message DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_minichat_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_minichat_date (date_message)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 31. message_prive  [SOCIAL STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE message_prive (
  id_message    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_expediteur INT UNSIGNED NOT NULL,
  id_destinataire INT UNSIGNED NOT NULL,
  sujet         VARCHAR(150) DEFAULT NULL,
  corps         TEXT         NOT NULL,
  lu            TINYINT(1)   NOT NULL DEFAULT 0,
  date_envoi    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_mp_expediteur
    FOREIGN KEY (id_expediteur) REFERENCES membre(id_membre) ON DELETE CASCADE,
  CONSTRAINT fk_mp_destinataire
    FOREIGN KEY (id_destinataire) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_mp_destinataire (id_destinataire, lu),
  INDEX idx_mp_date (date_envoi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 32. notification  [STANDARD UX]
-- Notifications internes affichées sur la cloche.
-- ---------------------------------------------------------------------
CREATE TABLE notification (
  id_notification INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre       INT UNSIGNED NOT NULL,                    -- destinataire
  type            VARCHAR(40)  NOT NULL,                    -- 'commentaire.reponse', 'commande.statut', etc.
  titre           VARCHAR(150) NOT NULL,
  message         TEXT         NOT NULL,
  url_cible       VARCHAR(255) DEFAULT NULL,                -- où mener au clic
  lue             TINYINT(1)   NOT NULL DEFAULT 0,
  date_creation   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  date_lecture    DATETIME     DEFAULT NULL,
  CONSTRAINT fk_notif_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
  INDEX idx_notif_membre_lue (id_membre, lue),
  INDEX idx_notif_date (date_creation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  GROUPE 8  -  TECHNIQUE & STATS  (2 tables)
-- =====================================================================

-- ---------------------------------------------------------------------
-- 33. parametre  [CONFIG STANDARD]
-- ---------------------------------------------------------------------
CREATE TABLE parametre (
  cle         VARCHAR(60)  NOT NULL PRIMARY KEY,
  valeur      TEXT         NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  date_maj    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------
-- 34. recherche_log  [ANALYTIQUE]
-- ---------------------------------------------------------------------
CREATE TABLE recherche_log (
  id_recherche   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_membre      INT UNSIGNED DEFAULT NULL,
  terme          VARCHAR(200) NOT NULL,
  nb_resultats   INT UNSIGNED NOT NULL DEFAULT 0,
  contexte       ENUM('blog','catalogue','tout') NOT NULL DEFAULT 'blog',
  date_recherche DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_recherche_membre
    FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE SET NULL,
  INDEX idx_recherche_terme (terme),
  INDEX idx_recherche_date (date_recherche)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- Fin du schéma  —  34 tables au total
-- =====================================================================
