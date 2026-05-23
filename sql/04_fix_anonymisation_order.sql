-- =====================================================================
-- Migration 04 — OBSOLÈTE (à ne pas exécuter sur une nouvelle install)
-- ---------------------------------------------------------------------
-- ⚠️  CETTE MIGRATION N'EST PLUS NÉCESSAIRE
--
-- Contexte historique :
-- Une ancienne version de Membre::anonymiser() inversait
-- nom/prenom lors de l'anonymisation RGPD :
--   Bug initial : nom='Utilisateur', prenom='supprimé'
--   Corrigé    : nom='supprimé', prenom='Utilisateur'
--                (l'affichage "$prenom $nom" donne ainsi "Utilisateur supprimé")
--
-- Cette migration corrigeait UNIQUEMENT les comptes anonymisés avec
-- l'ancienne version buggée. Depuis, le code source de la classe Membre
-- a été corrigé directement.
--
-- SUR UNE NOUVELLE INSTALLATION : cette migration n'a rien à corriger
-- (la condition WHERE ne matchera aucune ligne).
--
-- Conservée dans le repo pour la trace historique des correctifs.
-- =====================================================================

USE pdvweb;

UPDATE membre
SET nom    = 'supprimé',
    prenom = 'Utilisateur'
WHERE date_anonymisation IS NOT NULL
  AND nom    = 'Utilisateur'
  AND prenom = 'supprimé';
