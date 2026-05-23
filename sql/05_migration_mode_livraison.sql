-- =====================================================================
-- Migration 05 : ajout du champ mode_livraison à achat_facture
-- ---------------------------------------------------------------------
-- Pour permettre au client de choisir son mode de livraison (Standard,
-- Express, Point relais, etc.) et conserver ce choix dans l'historique.
--
-- Snapshot textuel : on ne crée pas de FK vers frais_port car on veut
-- garder l'historique même si la grille est modifiée plus tard.
-- =====================================================================

USE pdvweb;

ALTER TABLE achat_facture
    ADD COLUMN mode_livraison VARCHAR(80) DEFAULT NULL
    AFTER montant_frais_port;

-- Mise à jour des commandes existantes (au cas où il y en a déjà)
UPDATE achat_facture SET mode_livraison = 'Livraison standard' WHERE mode_livraison IS NULL;
