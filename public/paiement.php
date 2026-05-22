<?php
/**
 * public/paiement.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Paiement (simulé) et création de la facture.
 *
 * Reçoit les choix faits dans commande.php et crée la facture en
 * transaction via Facture::creerDepuisPanier().
 *
 * IMPORTANT : c'est un paiement SIMULÉ (cahier des charges).
 * En production, on intégrerait Stripe/PayPal/Mollie ici.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    Flash::erreur('Session expirée. Veuillez réessayer.');
    header('Location: ' . url('/panier.php'));
    exit;
}

$idMembre = Auth::id();

// Vérification du membre (pas bloqué)
$membre = Membre::trouverParId($idMembre);
if ($membre && (int)$membre['indesirable'] === 1) {
    Flash::erreur('Votre compte est suspendu, impossible de commander.');
    header('Location: ' . url('/panier.php'));
    exit;
}

// Préparation des données pour Facture::creerDepuisPanier
$donnees = [
    'id_adresse_livraison'   => (int)($_POST['id_adresse_livraison'] ?? 0),
    'id_adresse_facturation' => (int)($_POST['id_adresse_facturation'] ?? $_POST['id_adresse_livraison'] ?? 0),
    'id_frais_port'          => (int)($_POST['id_frais_port'] ?? 0),
    'methode_paiement'       => $_POST['methode_paiement'] ?? 'carte',
    'code_promo'             => trim($_POST['code_promo'] ?? ''),
];

// Vérifier que l'adresse appartient au membre (sécurité)
if (!Adresse::appartientAuMembre($donnees['id_adresse_livraison'], $idMembre)) {
    Flash::erreur('Adresse de livraison invalide.');
    header('Location: ' . url('/commande.php'));
    exit;
}

// Validation de la méthode de paiement
$methodesValides = ['carte', 'paypal', 'virement'];
if (!in_array($donnees['methode_paiement'], $methodesValides, true)) {
    Flash::erreur('Méthode de paiement invalide.');
    header('Location: ' . url('/commande.php'));
    exit;
}

// Création de la facture (transaction)
$resultat = Facture::creerDepuisPanier($idMembre, $donnees);

if (!$resultat['succes']) {
    Flash::erreur('Erreur lors du paiement : ' . $resultat['erreur']);
    header('Location: ' . url('/commande.php'));
    exit;
}

// Succès : redirection vers la page de confirmation
Flash::succes('Paiement validé ! Votre commande a été confirmée.');
header('Location: ' . url('/facture.php?id=' . $resultat['id_facture'] . '&confirmation=1'));
exit;
