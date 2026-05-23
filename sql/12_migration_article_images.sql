-- =====================================================================
-- Migration 12 : Galerie d'images par article
-- ---------------------------------------------------------------------
-- Permet d'avoir plusieurs images pour un article (image principale +
-- galerie). L'image "principale" reste dans article.image pour la
-- rétro-compatibilité ; les images supplémentaires vont dans cette table.
-- =====================================================================

CREATE TABLE IF NOT EXISTS article_image (
    id_image      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_article    INT UNSIGNED NOT NULL,
    fichier       VARCHAR(255) NOT NULL,
    ordre         INT          NOT NULL DEFAULT 0,
    date_ajout    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_article
        FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE,
    INDEX idx_ai_article (id_article, ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
