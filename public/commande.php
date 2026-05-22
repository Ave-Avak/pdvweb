<?php
/**
 * public/commande.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Étape récapitulatif avant paiement.
 *
 * Demande : adresse de livraison, code promo éventuel, méthode de paiement.
 * Réservé aux UM connectés.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$panier = Panier::detail();

if (empty($panier['lignes'])) {
    Flash::erreur('Votre panier est vide.');
    header('Location: ' . url('/panier.php'));
    exit;
}

// Adresses du membre
$adresses = Adresse::listerDuMembre($idMembre);
$adresseDefaut = Adresse::defautDuMembre($idMembre);

// Si pas d'adresse, on ne peut pas commander
if (empty($adresses)) {
    Flash::info('Veuillez ajouter au moins une adresse de livraison.');
    header('Location: ' . url('/adresse_form.php?retour=commande'));
    exit;
}

// Application éventuelle d'un code promo (GET pour persistance lors d'un refresh)
$codePromoInput = trim($_POST['code_promo'] ?? $_GET['code_promo'] ?? '');
$resultatPromo = ['valide' => false, 'remise' => 0, 'message' => '', 'code' => null];

if ($codePromoInput !== '') {
    $resultatPromo = CodePromo::appliquer($codePromoInput, $idMembre, $panier['sous_total']);
    if ($resultatPromo['valide']) {
        Flash::succes($resultatPromo['message']);
    }
    // si invalide, on affichera $resultatPromo['message'] dans la vue
}

// Adresse sélectionnée (par défaut = celle par défaut)
$idAdresseSel = (int)($_POST['id_adresse_livraison']
                     ?? $_GET['id_adresse_livraison']
                     ?? $adresseDefaut['id_adresse']);
$adresseLiv = Adresse::trouverParId($idAdresseSel);
if (!$adresseLiv || (int)$adresseLiv['id_membre'] !== $idMembre) {
    $adresseLiv = $adresseDefaut;
    $idAdresseSel = (int)$adresseDefaut['id_adresse'];
}

// Liste de TOUS les modes de livraison disponibles pour ce pays et ce panier
$modesLivraison = FraisPort::listerDisponibles($adresseLiv['pays'], $panier['sous_total']);

// Mode de livraison sélectionné
//  - via POST/GET si le client a déjà choisi
//  - sinon par défaut le moins cher (premier de la liste, déjà triée ASC par prix)
$idFraisSel = (int)($_POST['id_frais_port'] ?? $_GET['id_frais_port'] ?? 0);
$modeChoisi = null;
foreach ($modesLivraison as $m) {
    if ((int)$m['id_frais'] === $idFraisSel) {
        $modeChoisi = $m;
        break;
    }
}
if (!$modeChoisi && !empty($modesLivraison)) {
    $modeChoisi = $modesLivraison[0];   // fallback : moins cher
    $idFraisSel = (int)$modeChoisi['id_frais'];
}

$montantFraisPort = $modeChoisi ? (float)$modeChoisi['prix'] : 0.0;

// Si code promo "livraison_offerte"
if ($resultatPromo['valide'] && $resultatPromo['code']['type_remise'] === 'livraison_offerte') {
    $montantFraisPort = 0.0;
}

// Total final
$totalFinal = $panier['sous_total'] + $montantFraisPort - $resultatPromo['remise'];
$totalFinal = max(0, $totalFinal);

$titre = 'Validation de la commande';
require_once VIEWS_PATH . '/panier/commande.php';
