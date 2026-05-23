-- =====================================================================
-- 00_install_complet.sql
-- ---------------------------------------------------------------------
-- Script d'installation complète de la base PDVWeb.
--
-- À exécuter UNE SEULE FOIS lors de l'installation initiale.
-- Pour mettre à jour une installation existante, voir les scripts
-- de migration (03_, 04_, 05_).
--
-- Ce script :
--   1. Supprime la base si elle existe (ATTENTION DESTRUCTIF)
--   2. La recrée
--   3. Crée le schéma (34 tables)
--   4. Insère les données de test
--   5. Applique les migrations cumulées (soft delete, RGPD, mode livraison)
--
-- Pour exécuter depuis phpMyAdmin : importer ce fichier.
-- Pour exécuter en ligne de commande :
--   mysql -u root < sql/00_install_complet.sql
-- =====================================================================

-- ---------------------------------------------------------------------
-- DESTRUCTION (à commenter en production !)
-- ---------------------------------------------------------------------
DROP DATABASE IF EXISTS pdvweb;

-- ---------------------------------------------------------------------
-- CRÉATION DE LA BASE
-- ---------------------------------------------------------------------
CREATE DATABASE pdvweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pdvweb;

-- ---------------------------------------------------------------------
-- 1. SCHÉMA (34 tables)
-- ---------------------------------------------------------------------
-- À ce stade, exécutez 01_schema.sql
-- (ce fichier est volontairement séparé pour clarté)

-- ---------------------------------------------------------------------
-- IMPORTANT
-- ---------------------------------------------------------------------
-- Ce script unifié n'est qu'un GUIDE.
-- L'installation se fait en exécutant DANS L'ORDRE :
--   1. 01_schema.sql              (création des 34 tables)
--   2. 02_donnees_test.sql        (données de démo)
--   3. 03_migration_soft_delete.sql
--   4. 04_fix_anonymisation_order.sql
--   5. 05_migration_mode_livraison.sql
--
-- Soit directement phpMyAdmin → onglet Importer → un par un.
--
-- Le script bash install.sh à la racine du projet automatise ce processus.
