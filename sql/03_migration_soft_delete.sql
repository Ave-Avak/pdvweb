-- =====================================================================
-- Migration 03 : Soft delete sur billets et commentaires + anonymisation
-- ---------------------------------------------------------------------
-- À exécuter SUR VOTRE BASE pdvweb EXISTANTE (qui contient déjà les
-- tables 01_schema.sql + données de 02_seed.sql).
-- N'efface aucune donnée — ajoute juste des colonnes et des index.
-- =====================================================================

USE pdvweb;


-- ---------------------------------------------------------------------
-- 1. Soft delete sur la table commentaire
-- ---------------------------------------------------------------------
ALTER TABLE commentaire
  ADD COLUMN date_suppression DATETIME NULL DEFAULT NULL AFTER date_comm,
  ADD COLUMN id_membre_suppression INT UNSIGNED NULL DEFAULT NULL AFTER date_suppression,
  ADD INDEX idx_comm_supp (date_suppression),
  ADD CONSTRAINT fk_comm_suppression
    FOREIGN KEY (id_membre_suppression) REFERENCES membre(id_membre) ON DELETE SET NULL;


-- ---------------------------------------------------------------------
-- 2. Soft delete sur la table billet
-- ---------------------------------------------------------------------
ALTER TABLE billet
  ADD COLUMN date_suppression DATETIME NULL DEFAULT NULL AFTER date_billet,
  ADD COLUMN id_membre_suppression INT UNSIGNED NULL DEFAULT NULL AFTER date_suppression,
  ADD INDEX idx_billet_supp (date_suppression),
  ADD CONSTRAINT fk_billet_suppression
    FOREIGN KEY (id_membre_suppression) REFERENCES membre(id_membre) ON DELETE SET NULL;


-- ---------------------------------------------------------------------
-- 3. Indicateur d'anonymisation RGPD sur la table membre
-- ---------------------------------------------------------------------
-- Le compte n'est pas supprimé mais ses données personnelles sont
-- remplacées par des valeurs neutres (conformité RGPD article 17).
ALTER TABLE membre
  ADD COLUMN date_anonymisation DATETIME NULL DEFAULT NULL AFTER date_inscription,
  ADD INDEX idx_membre_anonyme (date_anonymisation);


-- ---------------------------------------------------------------------
-- Vérification : si tout s'est bien passé, ces requêtes retournent 0 ligne
-- ---------------------------------------------------------------------
-- SELECT * FROM commentaire WHERE date_suppression IS NOT NULL;
-- SELECT * FROM billet      WHERE date_suppression IS NOT NULL;
-- SELECT * FROM membre      WHERE date_anonymisation IS NOT NULL;

-- =====================================================================
-- Fin de la migration 03
-- =====================================================================
