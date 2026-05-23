-- =====================================================================
-- 09_migration_categorie_actif.sql
-- ---------------------------------------------------------------------
-- Migration : ajout d'une colonne "actif" sur les catégories.
--   Permet à l'admin de masquer une catégorie temporairement sans la
--   supprimer (utile pour les rotations saisonnières, refonte, etc.)
-- =====================================================================

USE pdvweb;

ALTER TABLE categorie
  ADD COLUMN actif TINYINT(1) NOT NULL DEFAULT 1 AFTER ordre,
  ADD INDEX idx_categorie_actif (actif);

SELECT 'Migration 09 terminée : colonne actif ajoutée à categorie.' AS message;
