<?php
/**
 * classes/Panier.php
 * ---------------------------------------------------------------------
 * Modèle "Panier" : gestion du panier d'achat.
 *
 * Le panier est stocké en SESSION sous la forme :
 *   $_SESSION['panier'] = [
 *      id_article => quantite,
 *      ...
 *   ]
 *
 * "Persistance jusqu'à la prochaine connexion" du cahier des charges :
 *   - À chaque connexion, on RESET le panier (comportement attendu par le prof)
 *   - Pendant une même session, le panier persiste entre les pages
 *
 * Cahier des charges : MAX 10 ARTICLES IDENTIQUES par panier.
 * ---------------------------------------------------------------------
 */

class Panier
{
    /**
     * Quantité maximale par article (cahier des charges).
     */
    public static function quantiteMaxParArticle(): int
    {
        return (int)parametre('achat.qte_max_par_article', 10);
    }

    /**
     * Récupère le panier brut (id_article => quantite).
     */
    public static function obtenir(): array
    {
        return $_SESSION['panier'] ?? [];
    }

    /**
     * Récupère le panier "hydraté" avec les détails des articles.
     * Retourne aussi le total et le poids.
     *
     * @return array ['lignes' => [...], 'sous_total' => float, 'poids' => int, 'nb_articles' => int]
     */
    public static function detail(): array
    {
        $panier = self::obtenir();
        if (empty($panier)) {
            return ['lignes' => [], 'sous_total' => 0.0, 'poids' => 0, 'nb_articles' => 0];
        }

        $ids = array_keys($panier);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $req = Db::pdo()->prepare(
            "SELECT a.*, c.nom AS categorie_nom
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.id_article IN ($placeholders)"
        );
        $req->execute($ids);
        $articles = $req->fetchAll();

        $lignes = [];
        $sousTotal = 0.0;
        $poids = 0;
        $nbArticles = 0;

        foreach ($articles as $article) {
            $qte = (int)($panier[$article['id_article']] ?? 0);
            if ($qte <= 0) continue;

            $sousLigne = (float)$article['prix'] * $qte;
            $lignes[] = [
                'article'       => $article,
                'quantite'      => $qte,
                'prix_unitaire' => (float)$article['prix'],
                'sous_total'    => $sousLigne,
            ];
            $sousTotal += $sousLigne;
            $poids     += (int)($article['poids_grammes'] ?? 0) * $qte;
            $nbArticles += $qte;
        }

        return [
            'lignes'      => $lignes,
            'sous_total'  => $sousTotal,
            'poids'       => $poids,
            'nb_articles' => $nbArticles,
        ];
    }

    /**
     * Ajoute un article au panier (incrémente si déjà présent).
     *
     * @return array ['succes' => bool, 'message' => string]
     */
    public static function ajouter(int $idArticle, int $quantite = 1): array
    {
        if ($quantite < 1) {
            return ['succes' => false, 'message' => 'Quantité invalide.'];
        }

        $article = Article::trouverParId($idArticle);
        if (!$article) {
            return ['succes' => false, 'message' => 'Article introuvable.'];
        }

        $panier = self::obtenir();
        $qteActuelle = (int)($panier[$idArticle] ?? 0);
        $qteFuture = $qteActuelle + $quantite;
        $qteMax = self::quantiteMaxParArticle();

        // Cahier des charges : max 10 articles identiques
        if ($qteFuture > $qteMax) {
            return [
                'succes'  => false,
                'message' => "Quantité maximale atteinte ($qteMax par article).",
            ];
        }

        // Vérification du stock
        if ($qteFuture > (int)$article['stock']) {
            return [
                'succes'  => false,
                'message' => "Stock insuffisant (reste " . $article['stock'] . ").",
            ];
        }

        $panier[$idArticle] = $qteFuture;
        $_SESSION['panier'] = $panier;

        return [
            'succes'  => true,
            'message' => "« " . $article['nom'] . " » ajouté au panier.",
        ];
    }

    /**
     * Modifie la quantité d'un article (remplace, ne s'additionne pas).
     */
    public static function modifierQuantite(int $idArticle, int $nouvelleQuantite): array
    {
        if ($nouvelleQuantite <= 0) {
            return self::retirer($idArticle);
        }

        $qteMax = self::quantiteMaxParArticle();
        if ($nouvelleQuantite > $qteMax) {
            return ['succes' => false, 'message' => "Maximum $qteMax par article."];
        }

        $article = Article::trouverParId($idArticle);
        if (!$article) {
            return ['succes' => false, 'message' => 'Article introuvable.'];
        }

        if ($nouvelleQuantite > (int)$article['stock']) {
            return ['succes' => false, 'message' => 'Stock insuffisant.'];
        }

        $panier = self::obtenir();
        $panier[$idArticle] = $nouvelleQuantite;
        $_SESSION['panier'] = $panier;

        return ['succes' => true, 'message' => 'Panier mis à jour.'];
    }

    /**
     * Retire complètement un article du panier.
     */
    public static function retirer(int $idArticle): array
    {
        $panier = self::obtenir();
        if (!isset($panier[$idArticle])) {
            return ['succes' => false, 'message' => 'Article non présent dans le panier.'];
        }
        unset($panier[$idArticle]);
        $_SESSION['panier'] = $panier;
        return ['succes' => true, 'message' => 'Article retiré du panier.'];
    }

    /**
     * Vide complètement le panier (après achat, ou à la connexion).
     */
    public static function vider(): void
    {
        unset($_SESSION['panier']);
    }

    /**
     * Nombre total d'articles dans le panier (pour le badge navbar).
     */
    public static function nbArticles(): int
    {
        $panier = self::obtenir();
        return array_sum($panier);
    }

    /**
     * Le panier est-il vide ?
     */
    public static function estVide(): bool
    {
        return empty($_SESSION['panier']);
    }
}
