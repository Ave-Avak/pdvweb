-- =====================================================================
-- 06_migration_mp.sql
-- ---------------------------------------------------------------------
-- Migration : messagerie privée
--   - Ajoute une table mp_blocage pour permettre à un membre de bloquer
--     un autre membre (qui ne pourra plus lui envoyer de MP).
--   - La table message_prive existait déjà dans 01_schema.sql.
-- =====================================================================

USE pdvweb;

-- Table de blocage entre membres pour les MP
CREATE TABLE IF NOT EXISTS mp_blocage (
    id_membre        INT UNSIGNED NOT NULL,  -- celui qui bloque
    id_membre_bloque INT UNSIGNED NOT NULL,  -- celui qui est bloqué
    date_blocage     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_membre, id_membre_bloque),
    CONSTRAINT fk_mpb_membre
        FOREIGN KEY (id_membre) REFERENCES membre(id_membre) ON DELETE CASCADE,
    CONSTRAINT fk_mpb_bloque
        FOREIGN KEY (id_membre_bloque) REFERENCES membre(id_membre) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note : pas de date_suppression sur message_prive pour le moment.
-- Si besoin, ajout futur.

-- Confirmation
SELECT 'Migration 06 terminée : table mp_blocage créée.' AS message;
