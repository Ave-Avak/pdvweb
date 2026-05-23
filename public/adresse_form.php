<?php
/**
 * public/adresse_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Création ou édition d'une adresse.
 *
 * Paramètres :
 *  - ?id=N pour éditer une adresse existante (sinon création)
 *  - ?retour=commande pour rediriger vers /commande.php après création
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$idAdresse = (int)($_GET['id'] ?? 0);
$retour = trim($_GET['retour'] ?? '');
$modeEdition = $idAdresse > 0;

$adresse = null;
if ($modeEdition) {
    $adresse = Adresse::trouverParId($idAdresse);
    if (!$adresse || (int)$adresse['id_membre'] !== $idMembre) {
        Flash::erreur('Adresse introuvable ou non autorisée.');
        header('Location: ' . url('/adresses.php'));
        exit;
    }
}

// Pré-remplissage : adresse existante OU données du membre par défaut
$membre = Membre::trouverParId($idMembre);

$donnees = [
    'libelle'    => $adresse['libelle']    ?? '',
    'nom'        => $adresse['nom']        ?? ($membre['nom']    ?? ''),
    'prenom'     => $adresse['prenom']     ?? ($membre['prenom'] ?? ''),
    'rue'        => $adresse['rue']        ?? '',
    'numero'     => $adresse['numero']     ?? '',
    'complement' => $adresse['complement'] ?? '',
    'cp'         => $adresse['cp']         ?? '',
    'ville'      => $adresse['ville']      ?? '',
    'pays'       => $adresse['pays']       ?? 'Belgique',
    'telephone'  => $adresse['telephone']  ?? '',
    'type'       => $adresse['type']       ?? 'les_deux',
    'est_defaut' => (int)($adresse['est_defaut'] ?? 0),
];
$erreurs = [];


// =====================================================================
// TRAITEMENT POST
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    foreach ($donnees as $k => $_) {
        if ($k === 'est_defaut') continue;
        $donnees[$k] = trim($_POST[$k] ?? '');
    }
    $donnees['est_defaut'] = !empty($_POST['est_defaut']) ? 1 : 0;

    // Validation
    if ($donnees['libelle'] === '')  $erreurs['libelle'] = 'Le libellé est obligatoire.';
    if ($donnees['nom']     === '')  $erreurs['nom']     = 'Le nom est obligatoire.';
    if ($donnees['prenom']  === '')  $erreurs['prenom']  = 'Le prénom est obligatoire.';
    if ($donnees['rue']     === '')  $erreurs['rue']     = 'La rue est obligatoire.';
    if ($donnees['numero']  === '')  $erreurs['numero']  = 'Le numéro est obligatoire.';
    if ($donnees['cp']      === '')  $erreurs['cp']      = 'Le code postal est obligatoire.';
    if ($donnees['ville']   === '')  $erreurs['ville']   = 'La ville est obligatoire.';
    if ($donnees['pays']    === '')  $erreurs['pays']    = 'Le pays est obligatoire.';

    if (!in_array($donnees['type'], ['livraison', 'facturation', 'les_deux'], true)) {
        $donnees['type'] = 'les_deux';
    }

    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                // Mise à jour
                if (!empty($donnees['est_defaut'])) {
                    Db::pdo()->prepare("UPDATE adresse SET est_defaut = 0 WHERE id_membre = ?")
                             ->execute([$idMembre]);
                }
                $req = Db::pdo()->prepare(
                    "UPDATE adresse SET
                        libelle = ?, nom = ?, prenom = ?, rue = ?, numero = ?,
                        complement = ?, cp = ?, ville = ?, pays = ?, telephone = ?,
                        type = ?, est_defaut = ?
                     WHERE id_adresse = ? AND id_membre = ?"
                );
                $req->execute([
                    $donnees['libelle'], $donnees['nom'], $donnees['prenom'],
                    $donnees['rue'], $donnees['numero'], $donnees['complement'] ?: null,
                    $donnees['cp'], $donnees['ville'], $donnees['pays'],
                    $donnees['telephone'] ?: null, $donnees['type'], $donnees['est_defaut'],
                    $idAdresse, $idMembre,
                ]);
                Flash::succes('Adresse mise à jour.');
            } else {
                // Création (Adresse::creer gère est_defaut + retire le défaut des autres)
                Adresse::creer($idMembre, $donnees);
                Flash::succes('Adresse ajoutée.');
            }

            // Redirection : si on venait de la commande, retourner vers commande
            if ($retour === 'commande') {
                header('Location: ' . url('/commande.php'));
            } else {
                header('Location: ' . url('/adresses.php'));
            }
            exit;

        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur d\'enregistrement.';
            if (DEV_MODE) $erreurs['general'] .= ' [' . $e->getMessage() . ']';
        }
    }
}

$titre = $modeEdition ? 'Modifier une adresse' : 'Nouvelle adresse';
require_once VIEWS_PATH . '/auth/adresse_form.php';
