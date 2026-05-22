<?php
/**
 * public/admin/index.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Tableau de bord (provisoire à l'étape 5).
 *
 * Sera remplacé par un vrai tableau de bord à l'étape 7.
 * Pour l'instant, redirige vers la liste des billets.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$titre = 'Administration';

// Quelques statistiques rapides
$stats = [
    'nb_membres'      => count(Membre::listerTous()),
    'nb_billets'      => Billet::compterTous(),
    'nb_commentaires' => Commentaire::compterTous(),
];

require_once VIEWS_PATH . '/admin/index.php';
