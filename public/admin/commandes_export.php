<?php
/**
 * public/admin/commandes_export.php
 * ---------------------------------------------------------------------
 * Export CSV des commandes admin (avec mêmes filtres que /admin/commandes.php).
 *
 * Format compatible Excel français : séparateur ;, encodage UTF-8 avec BOM.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Récupération des filtres (mêmes que la liste)
$filtres = [
    'statut'    => (int)($_GET['statut']    ?? 0),
    'recherche' => trim($_GET['q']          ?? ''),
    'date_min'  => trim($_GET['date_min']   ?? ''),
    'date_max'  => trim($_GET['date_max']   ?? ''),
    'page'      => 1,
    'parPage'   => 100000,  // pas de pagination pour l'export
];

$resultat  = Facture::listerToutes($filtres);
$commandes = $resultat['commandes'];

// Trace dans l'audit log
AuditLog::enregistrer('commandes.export_csv', Auth::id(), null, null, [
    'nb_lignes' => count($commandes),
    'filtres'   => array_filter([
        'statut'    => $filtres['statut'] ?: null,
        'recherche' => $filtres['recherche'] ?: null,
        'date_min'  => $filtres['date_min'] ?: null,
        'date_max'  => $filtres['date_max'] ?: null,
    ]),
]);

// Nom de fichier
$nomFichier = 'commandes_' . date('Y-m-d_His') . '.csv';

// Headers HTTP pour téléchargement
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Ouvre la sortie standard
$out = fopen('php://output', 'w');

// BOM UTF-8 pour qu'Excel reconnaisse les accents
fwrite($out, "\xEF\xBB\xBF");

// En-têtes
fputcsv($out, [
    'Référence',
    'Date',
    'Client (login)',
    'Client (nom)',
    'Client (prénom)',
    'Mode livraison',
    'Lignes',
    'Sous-total',
    'Frais port',
    'Remise',
    'Total TTC',
    'Statut',
], ';');

// Données
foreach ($commandes as $c) {
    fputcsv($out, [
        $c['reference'],
        $c['date_achat'],
        $c['login'],
        $c['nom'],
        $c['prenom'],
        $c['mode_livraison'] ?? '',
        $c['nb_articles'],
        number_format((float)($c['sous_total']         ?? 0), 2, ',', ''),
        number_format((float)($c['montant_frais_port'] ?? 0), 2, ',', ''),
        number_format((float)($c['montant_remise']     ?? 0), 2, ',', ''),
        number_format((float)$c['prix_total'], 2, ',', ''),
        $c['statut_nom'],
    ], ';');
}

fclose($out);
exit;
