<?php
/**
 * public/facture.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Détail d'une facture.
 *
 * Visible par le membre (sa propre facture) ou par l'admin.
 * Affiche aussi un mode "confirmation" si on vient du paiement.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idFacture = (int)($_GET['id'] ?? 0);
$confirmation = !empty($_GET['confirmation']);

if ($idFacture <= 0) {
    Flash::erreur('Facture introuvable.');
    header('Location: ' . url('/historique.php'));
    exit;
}

$facture = Facture::trouverParId($idFacture);
if (!$facture) {
    Flash::erreur('Cette facture n\'existe pas.');
    header('Location: ' . url('/historique.php'));
    exit;
}

// Vérification des droits : le membre ne peut voir QUE ses propres factures
if ((int)$facture['id_membre'] !== Auth::id() && !Auth::estAdmin()) {
    Flash::erreur('Vous n\'avez pas accès à cette facture.');
    header('Location: ' . url('/historique.php'));
    exit;
}

$lignes    = Facture::lignesDe($idFacture);
$paiements = Facture::paiementsDe($idFacture);
$adresseLiv  = Adresse::trouverParId((int)$facture['id_adresse_livraison']);
$adresseFact = Adresse::trouverParId((int)$facture['id_adresse_facturation']);

$titre = 'Facture ' . $facture['reference'];
require_once VIEWS_PATH . '/auth/facture.php';
