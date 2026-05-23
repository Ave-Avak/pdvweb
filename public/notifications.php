<?php
/**
 * public/notifications.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Liste des notifications du membre + clic = mark as read.
 *
 * Si on accède via /notifications.php?id=N&go=1
 *   → marque la notif comme lue + redirige vers url_cible.
 * Sinon, affiche la liste complète.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();

// Action POST : tout marquer comme lu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::verifierRequete()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'marquer_toutes_lues') {
        $nb = Notification::marquerToutesLues($idMembre);
        Flash::succes($nb . ' notification' . ($nb > 1 ? 's' : '') . ' marquée' . ($nb > 1 ? 's' : '') . ' comme lue' . ($nb > 1 ? 's' : '') . '.');
    }
    header('Location: ' . url('/notifications.php'));
    exit;
}

// Redirection sur clic notification
$idNotif = (int)($_GET['id'] ?? 0);
$go      = !empty($_GET['go']);

if ($idNotif > 0 && $go) {
    $notif = Notification::trouverParId($idNotif, $idMembre);
    if ($notif) {
        Notification::marquerLue($idNotif, $idMembre);

        // Détermine la destination : url_cible peut être stockée comme
        //   - "/facture.php?id=N"     → relatif au site, on ajoute le préfixe via url()
        //   - "http://..."            → URL absolue, on laisse tel quel
        $cible = $notif['url_cible'] ?? '';
        if ($cible === '') {
            $destination = url('/notifications.php');
        } elseif (preg_match('#^https?://#i', $cible)) {
            // URL absolue : on garde tel quel
            $destination = $cible;
        } else {
            // Chemin relatif type "/facture.php?id=N" → ajouter le préfixe
            $destination = url($cible);
        }

        header('Location: ' . $destination);
        exit;
    }
}

// Liste complète
$notifications = Notification::listerDuMembre($idMembre, 50);

$titre = 'Mes notifications';
require_once VIEWS_PATH . '/auth/notifications.php';
