<?php
/**
 * public/index.php
 * ---------------------------------------------------------------------
 * Page d'accueil du site PDVWeb.
 *
 * Selon le cahier des charges :
 *   - Description des services accessibles aux UM connectés vs UNM
 *   - Zone de saisie login / mot de passe pour s'authentifier
 *   - Accessible à tous (UNM et UM)
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$titre = 'Accueil';

// On affiche le formulaire de login uniquement si l'utilisateur n'est pas connecté
$afficherLogin = !Auth::estConnecte();
$membre = Auth::membre();

// Stats pour la section "chiffres clés" (visibles par tous)
// IMPORTANT : la table article a la colonne 'dispo' (pas 'actif'),
//             la table categorie n'a PAS de colonne actif (toujours active).
$pdo = Db::pdo();

$statsAccueil = [
    'articles'   => (int)$pdo->query("SELECT COUNT(*) FROM article WHERE dispo = 1")->fetchColumn(),
    'categories' => (int)$pdo->query("SELECT COUNT(*) FROM categorie")->fetchColumn(),
    'membres'    => (int)$pdo->query(
        "SELECT COUNT(*) FROM membre
         WHERE indesirable = 0 AND date_anonymisation IS NULL"
    )->fetchColumn(),
    'billets'    => (int)$pdo->query(
        "SELECT COUNT(*) FROM billet WHERE date_suppression IS NULL"
    )->fetchColumn(),
];

// Derniers billets pour aperçu sur la home (3 max)
// Billet::lister() retourne ['billets' => [...], 'total' => N, ...]
$resultatBillets = Billet::lister(['parPage' => 3]);
$derniersBillets = $resultatBillets['billets'] ?? [];

// Articles les mieux notés (pour donner envie de cliquer)
// Note : la table article n'a PAS de colonne actif → on utilise 'dispo'
$articlesVedettes = $pdo->query(
    "SELECT a.id_article, a.nom, a.prix, a.image,
            c.nom AS categorie_nom,
            COALESCE(AVG(n.note), 0) AS note_moyenne,
            COUNT(DISTINCT n.id_membre) AS nb_notes
     FROM article a
     INNER JOIN categorie c ON c.id_categorie = a.id_categorie
     LEFT JOIN note_article n ON n.id_article = a.id_article
     WHERE a.dispo = 1
     GROUP BY a.id_article, a.nom, a.prix, a.image, c.nom
     ORDER BY note_moyenne DESC, nb_notes DESC, a.date_ajout DESC
     LIMIT 3"
)->fetchAll();

// Catégories indexées par code, pour générer les liens des 3 rayons
// Le filtre catalogue.php attend un id_categorie (entier), pas un code.
$categoriesParCode = [];
foreach ($pdo->query("SELECT id_categorie, code, nom FROM categorie ORDER BY ordre, nom")->fetchAll() as $cat) {
    $categoriesParCode[$cat['code']] = $cat;
}

require_once VIEWS_PATH . '/accueil.php';
