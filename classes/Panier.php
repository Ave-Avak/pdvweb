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

        // Synchronisation BDD pour persistance entre sessions
        self::sauvegarderSiConnecte();

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

        self::sauvegarderSiConnecte();

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

        self::sauvegarderSiConnecte();

        return ['succes' => true, 'message' => 'Article retiré du panier.'];
    }

    /**
     * Vide complètement le panier (après achat, ou à la connexion).
     * NOTE : ne vide PAS le panier BDD (pour préserver l'inter-session).
     * Pour vider aussi en BDD, utiliser viderTout().
     */
    public static function vider(): void
    {
        unset($_SESSION['panier']);
    }

    /**
     * Vide TOTALEMENT le panier (session ET BDD).
     * À appeler après validation d'une commande.
     */
    public static function viderTout(): void
    {
        unset($_SESSION['panier']);

        if (class_exists('Auth') && Auth::estConnecte()) {
            try {
                $req = Db::pdo()->prepare(
                    "DELETE FROM panier_persistant WHERE id_membre = ?"
                );
                $req->execute([Auth::id()]);
            } catch (Throwable $e) {
                // Table inexistante (migration 11 pas appliquée) → silencieux
            }
        }
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


    // =================================================================
    // PERSISTANCE BDD (panier persistant entre sessions)
    // =================================================================

    /**
     * Sauvegarde le panier en BDD si le membre est connecté.
     * Méthode "best-effort" : silencieuse en cas d'erreur (table inexistante).
     */
    public static function sauvegarderSiConnecte(): void
    {
        if (!class_exists('Auth') || !Auth::estConnecte()) {
            return;
        }

        $idMembre = Auth::id();
        $panier = self::obtenir();
        $pdo = Db::pdo();

        try {
            $pdo->beginTransaction();

            // On efface l'ancien panier BDD pour repartir propre
            $pdo->prepare("DELETE FROM panier_persistant WHERE id_membre = ?")
                ->execute([$idMembre]);

            // On insère les lignes actuelles
            if (!empty($panier)) {
                $req = $pdo->prepare(
                    "INSERT INTO panier_persistant (id_membre, id_article, quantite)
                     VALUES (?, ?, ?)"
                );
                foreach ($panier as $idArticle => $qte) {
                    $idArticle = (int)$idArticle;
                    $qte = (int)$qte;
                    if ($idArticle > 0 && $qte > 0) {
                        $req->execute([$idMembre, $idArticle, $qte]);
                    }
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            // Migration 11 pas appliquée → silencieux pour ne pas casser l'app
        }
    }

    /**
     * Charge le panier depuis la BDD vers la session.
     * Appelé à la connexion d'un membre.
     *
     * Logique de fusion :
     *   - Si la session a déjà un panier (ajouts anonymes) → on FUSIONNE
     *     (somme des quantités, en respectant le max et le stock)
     *   - Sinon → on prend tel quel le panier BDD
     *
     * @param int $idMembre
     */
    public static function chargerDepuisBdd(int $idMembre): void
    {
        try {
            $req = Db::pdo()->prepare(
                "SELECT pp.id_article, pp.quantite
                 FROM panier_persistant pp
                 INNER JOIN article a ON a.id_article = pp.id_article
                 WHERE pp.id_membre = ?
                   AND a.dispo = 1"
            );
            $req->execute([$idMembre]);
            $panierBdd = [];
            foreach ($req->fetchAll() as $ligne) {
                $panierBdd[(int)$ligne['id_article']] = (int)$ligne['quantite'];
            }
        } catch (Throwable $e) {
            // Table inexistante → on ne fait rien
            return;
        }

        if (empty($panierBdd)) {
            return;  // Rien à charger
        }

        $panierSession = self::obtenir();
        $qteMax = self::quantiteMaxParArticle();

        // Fusion : on additionne les quantités (en respectant les limites)
        foreach ($panierBdd as $idArticle => $qteBdd) {
            $qteSession = (int)($panierSession[$idArticle] ?? 0);
            $qteFusion = $qteSession + $qteBdd;

            // Vérifier le max par article
            if ($qteFusion > $qteMax) {
                $qteFusion = $qteMax;
            }

            // Vérifier le stock actuel
            try {
                $article = Article::trouverParId($idArticle);
                if ($article && $qteFusion > (int)$article['stock']) {
                    $qteFusion = (int)$article['stock'];
                }
            } catch (Throwable $e) {
                continue;
            }

            if ($qteFusion > 0) {
                $panierSession[$idArticle] = $qteFusion;
            }
        }

        $_SESSION['panier'] = $panierSession;

        // On re-sauvegarde en BDD pour refléter la fusion
        self::sauvegarderSiConnecte();
    }

    /**
     * Date de dernière modification du panier en BDD.
     * Utile pour afficher "Sauvegardé il y a X minutes".
     *
     * @param int $idMembre
     * @return string|null Format datetime, ou null si pas de panier sauvé
     */
    public static function dateDerniereSauvegarde(int $idMembre): ?string
    {
        try {
            $req = Db::pdo()->prepare(
                "SELECT MAX(date_modif) FROM panier_persistant WHERE id_membre = ?"
            );
            $req->execute([$idMembre]);
            $date = $req->fetchColumn();
            return $date ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}
