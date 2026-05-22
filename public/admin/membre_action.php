<?php
/**
 * public/admin/membre_action.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Actions sur un membre.
 *
 * Actions supportées :
 *   - 'bloquer'      : bascule indesirable à 1
 *   - 'debloquer'    : bascule indesirable à 0
 *   - 'promouvoir'   : passe statut à 'admin'
 *   - 'degrader'     : passe statut à 'membre'
 *
 * Sécurités :
 *   - POST + CSRF obligatoire
 *   - admin ne peut pas se modifier lui-même (anti-bricolage)
 *   - on ne peut pas modifier un compte anonymisé
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

$idMembre = (int)($_POST['id_membre'] ?? 0);
$action   = trim($_POST['action'] ?? '');
$retour   = $_POST['retour'] ?? url('/admin/membres.php');

if ($idMembre <= 0) {
    Flash::erreur('Membre invalide.');
    header('Location: ' . $retour);
    exit;
}

// Sécurité : admin ne peut pas se modifier lui-même
if ($idMembre === Auth::id()) {
    Flash::erreur('Vous ne pouvez pas effectuer cette action sur votre propre compte.');
    header('Location: ' . $retour);
    exit;
}

$membre = Membre::trouverParId($idMembre);
if (!$membre) {
    Flash::erreur('Membre introuvable.');
    header('Location: ' . $retour);
    exit;
}

// On ne touche pas aux comptes anonymisés
if (!empty($membre['date_anonymisation'])) {
    Flash::erreur('Action impossible sur un compte anonymisé.');
    header('Location: ' . $retour);
    exit;
}

switch ($action) {
    case 'bloquer':
        Membre::bloquer($idMembre, true);
        AuditLog::enregistrer('membre.bloquer', Auth::id(), 'membre', $idMembre);
        Flash::succes('Membre bloqué.');
        break;

    case 'debloquer':
        Membre::bloquer($idMembre, false);
        AuditLog::enregistrer('membre.debloquer', Auth::id(), 'membre', $idMembre);
        Flash::succes('Membre débloqué.');
        break;

    case 'promouvoir':
        Membre::changerRole($idMembre, 'admin', Auth::id());
        Flash::succes($membre['login'] . ' est maintenant administrateur.');
        break;

    case 'degrader':
        Membre::changerRole($idMembre, 'membre', Auth::id());
        Flash::succes($membre['login'] . ' n\'est plus administrateur.');
        break;

    default:
        Flash::erreur('Action inconnue.');
}

header('Location: ' . $retour);
exit;
