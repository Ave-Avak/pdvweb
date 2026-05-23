-- =====================================================================
-- 07_migration_pseudo_minichat.sql
-- ---------------------------------------------------------------------
-- Migration : conformité cahier des charges minichat
--   Le cahier exige : "pseudo utilisé au choix par chaque UM tout au long
--   de sa connexion"
--   → on ajoute une colonne pseudo à minichat pour stocker le pseudo
--     choisi par le membre lors de la POSTE du message.
-- =====================================================================

USE pdvweb;

-- Ajouter la colonne pseudo (snapshot au moment du post)
ALTER TABLE minichat
  ADD COLUMN pseudo VARCHAR(50) DEFAULT NULL AFTER id_membre;

-- Pour les messages existants : on met le login du membre par défaut
UPDATE minichat mc
INNER JOIN membre m ON m.id_membre = mc.id_membre
SET mc.pseudo = m.login
WHERE mc.pseudo IS NULL;

SELECT 'Migration 07 terminée : colonne pseudo ajoutée à minichat.' AS message;
