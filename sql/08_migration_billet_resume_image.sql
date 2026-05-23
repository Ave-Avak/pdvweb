-- =====================================================================
-- 08_migration_billet_resume_image.sql
-- ---------------------------------------------------------------------
-- Migration : enrichissement des billets de blog.
--   Ajout d'un résumé court (pour les listes/cartes) et d'une image
--   illustrative (pour donner un aspect plus moderne au blog).
--
-- Compatibilité ascendante :
--   - Les deux colonnes sont NULLABLE → les billets existants restent valides
--   - Aucune migration de données nécessaire
-- =====================================================================

USE pdvweb;

-- Ajout du champ résumé (texte court d'introduction)
ALTER TABLE billet
  ADD COLUMN resume VARCHAR(500) DEFAULT NULL AFTER corps,
  ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER resume;

-- Confirmation
SELECT 'Migration 08 terminée : colonnes resume et image ajoutées à billet.' AS message;
