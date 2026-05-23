-- =====================================================================
-- Migration 10 : Tags sur articles (Phase 3.2)
-- ---------------------------------------------------------------------
-- Ajoute une table de liaison many-to-many entre article et tag.
-- Permet de marquer un article avec plusieurs tags
-- ("Promotion", "Nouveauté", "Best-seller", etc.).
--
-- Les tags sont déjà partagés avec les billets (table tag commune).
-- Cette migration ajoute juste la liaison article ↔ tag.
-- =====================================================================

CREATE TABLE IF NOT EXISTS article_tag (
    id_article INT UNSIGNED NOT NULL,
    id_tag     INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_article, id_tag),
    CONSTRAINT fk_at_article
        FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE,
    CONSTRAINT fk_at_tag
        FOREIGN KEY (id_tag)     REFERENCES tag(id_tag)         ON DELETE CASCADE,
    INDEX idx_at_tag (id_tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
