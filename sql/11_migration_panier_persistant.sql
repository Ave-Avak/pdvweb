-- =====================================================================
-- Migration 11 : Panier persistant entre les sessions
-- ---------------------------------------------------------------------
-- Permet à un membre de retrouver son panier après déconnexion / 
-- reconnexion sur un autre appareil.
--
-- Le panier reste en session pour la rapidité d'accès, mais est
-- synchronisé en BDD :
--   - À la connexion : on charge le panier BDD dans la session
--   - À la déconnexion : on sauvegarde la session en BDD
--   - À chaque modification : on synchronise en temps réel
--   - À la validation de commande : on vide le panier BDD
-- =====================================================================

CREATE TABLE IF NOT EXISTS panier_persistant (
    id_membre   INT UNSIGNED NOT NULL,
    id_article  INT UNSIGNED NOT NULL,
    quantite    INT          NOT NULL DEFAULT 1,
    date_ajout  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modif  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_membre, id_article),
    CONSTRAINT fk_panier_membre
        FOREIGN KEY (id_membre)  REFERENCES membre(id_membre)   ON DELETE CASCADE,
    CONSTRAINT fk_panier_article
        FOREIGN KEY (id_article) REFERENCES article(id_article) ON DELETE CASCADE,
    INDEX idx_panier_membre (id_membre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
