<?php
/**
 * public/api/recherche.php
 * ---------------------------------------------------------------------
 * API JSON pour la recherche autocomplete (catalogue + blog).
 *
 * Paramètres :
 *   - q       : terme de recherche (min 2 caractères)
 *   - type    : 'articles' (par défaut) ou 'billets'
 *   - limite  : nombre max de résultats (défaut 8)
 *
 * Réponse JSON :
 *   {
 *     "succes": true,
 *     "resultats": [
 *       {"id": 1, "nom": "MacBook Air", "prix": 1299, "image": "...", "url": "..."},
 *       ...
 *     ]
 *   }
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$terme  = trim($_GET['q'] ?? '');
$type   = $_GET['type'] ?? 'articles';
$limite = max(1, min(20, (int)($_GET['limite'] ?? 8)));

// Au moins 2 caractères pour éviter de tout charger
if (mb_strlen($terme) < 2) {
    echo json_encode(['succes' => true, 'resultats' => []]);
    exit;
}

$resultats = [];

try {
    if ($type === 'billets') {
        // Recherche dans le blog
        $req = Db::pdo()->prepare(
            "SELECT id_billet AS id, titre, resume, image, date_billet
             FROM billet
             WHERE date_suppression IS NULL
               AND (titre LIKE ? OR corps LIKE ? OR resume LIKE ?)
             ORDER BY date_billet DESC
             LIMIT $limite"
        );
        $like = '%' . $terme . '%';
        $req->execute([$like, $like, $like]);

        foreach ($req->fetchAll() as $b) {
            $resultats[] = [
                'id'    => (int)$b['id'],
                'titre' => $b['titre'],
                'resume' => $b['resume'] ? mb_substr(strip_tags($b['resume']), 0, 80) : null,
                'image' => $b['image'] ?: null,
                'date'  => $b['date_billet'],
                'url'   => url('/billet.php?id=' . $b['id']),
            ];
        }
    } else {
        // Recherche dans les articles (catalogue) - défaut
        $req = Db::pdo()->prepare(
            "SELECT a.id_article AS id, a.nom, a.prix, a.image, a.stock,
                    c.nom AS categorie_nom
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.dispo = 1
               AND (a.nom LIKE ? OR a.description LIKE ?)
             ORDER BY
                CASE WHEN a.nom LIKE ? THEN 0 ELSE 1 END,
                a.nom ASC
             LIMIT $limite"
        );
        $like = '%' . $terme . '%';
        $likeStart = $terme . '%';
        $req->execute([$like, $like, $likeStart]);

        foreach ($req->fetchAll() as $a) {
            $resultats[] = [
                'id'         => (int)$a['id'],
                'nom'        => $a['nom'],
                'prix'       => (float)$a['prix'],
                'prix_format' => format_prix($a['prix']),
                'image'      => $a['image'] ?: null,
                'image_url'  => asset_article($a['image']),
                'categorie'  => $a['categorie_nom'],
                'en_stock'   => (int)$a['stock'] > 0,
                'url'        => url('/article.php?id=' . $a['id']),
            ];
        }
    }

    // Log de la recherche (best-effort, seulement si plus de 3 caractères)
    if (mb_strlen($terme) >= 3) {
        try {
            $contexte = $type === 'billets' ? 'blog_ajax' : 'catalogue_ajax';
            Db::pdo()->prepare(
                "INSERT INTO recherche_log (id_membre, terme, nb_resultats, contexte)
                 VALUES (?, ?, ?, ?)"
            )->execute([Auth::id(), $terme, count($resultats), $contexte]);
        } catch (Throwable $e) {
            // ignore
        }
    }

    echo json_encode([
        'succes'    => true,
        'terme'     => $terme,
        'nb'        => count($resultats),
        'resultats' => $resultats,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'succes' => false,
        'message' => DEV_MODE ? $e->getMessage() : 'Erreur serveur',
    ]);
}
