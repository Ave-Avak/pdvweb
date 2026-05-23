<?php
/**
 * classes/Article.php
 * ---------------------------------------------------------------------
 * Modèle "Article" : produits vendus dans le catalogue.
 *
 * Méthodes : recherche, filtres, CRUD, gestion du stock, notation.
 * ---------------------------------------------------------------------
 */

class Article
{
    /**
     * Récupère un article par son ID, avec nom de la catégorie.
     *
     * @param int  $id
     * @param bool $inclureIndisponibles  Si false (par défaut), exclut les articles dispo=0
     */
    public static function trouverParId(int $id, bool $inclureIndisponibles = false): ?array
    {
        $clauseDispo = $inclureIndisponibles ? '' : ' AND a.dispo = 1';
        $req = Db::pdo()->prepare(
            "SELECT a.*, c.code AS categorie_code, c.nom AS categorie_nom
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.id_article = ?" . $clauseDispo
        );
        $req->execute([$id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste les articles avec pagination et filtres.
     *
     * @param array $opts
     *   - 'categorie' int     : filtrer par id_categorie
     *   - 'recherche' string  : terme à chercher dans nom + description
     *   - 'tri'       string  : 'recents', 'prix_asc', 'prix_desc', 'populaires'
     *   - 'page'      int
     *   - 'parPage'   int
     *   - 'incluraIndisponibles' bool (admin only)
     * @return array
     */
    /**
     * Liste les articles avec filtres, tri, pagination.
     *
     * @param array $opts Options possibles :
     *   - 'categorie'  int      : filtre par catégorie
     *   - 'recherche'  string   : recherche dans nom + description
     *   - 'tri'        string   : 'recents'|'prix_asc'|'prix_desc'|'populaires'|'note_desc'|'alpha'
     *   - 'page'       int
     *   - 'parPage'    int
     *   - 'inclureIndisponibles' bool (admin only)
     *
     *   --- Filtres avancés (Phase 3.1) ---
     *   - 'prix_min'   float    : prix minimum
     *   - 'prix_max'   float    : prix maximum
     *   - 'en_stock'   bool     : true = stock > 0 uniquement
     *   - 'note_min'   int      : note moyenne minimale (1-5)
     *   - 'id_tag'     int      : filtre par tag (Phase 3.2)
     *
     * @return array ['articles', 'total', 'totalPages', 'page']
     */
    public static function lister(array $opts = []): array
    {
        $idCategorie = (int)($opts['categorie'] ?? 0);
        $recherche   = trim($opts['recherche'] ?? '');
        $tri         = $opts['tri']           ?? 'recents';
        $page        = max(1, (int)($opts['page'] ?? 1));
        $parPage     = max(1, (int)($opts['parPage'] ?? 12));
        $offset      = ($page - 1) * $parPage;
        $inclureIndispo = !empty($opts['inclureIndisponibles']);

        // Filtres avancés (Phase 3.1)
        $prixMin     = isset($opts['prix_min']) && $opts['prix_min'] !== '' ? (float)$opts['prix_min'] : null;
        $prixMax     = isset($opts['prix_max']) && $opts['prix_max'] !== '' ? (float)$opts['prix_max'] : null;
        $enStock     = !empty($opts['en_stock']);
        $noteMin     = isset($opts['note_min']) ? (int)$opts['note_min'] : 0;
        $idTag       = (int)($opts['id_tag'] ?? 0);

        $where  = [];
        $params = [];

        if (!$inclureIndispo) {
            $where[] = "a.dispo = 1";
        }

        if ($idCategorie > 0) {
            $where[]  = "a.id_categorie = ?";
            $params[] = $idCategorie;
        }

        if ($recherche !== '') {
            $where[]  = "(a.nom LIKE ? OR a.description LIKE ?)";
            $params[] = '%' . $recherche . '%';
            $params[] = '%' . $recherche . '%';
        }

        // Filtres avancés
        if ($prixMin !== null && $prixMin >= 0) {
            $where[]  = "a.prix >= ?";
            $params[] = $prixMin;
        }
        if ($prixMax !== null && $prixMax > 0) {
            $where[]  = "a.prix <= ?";
            $params[] = $prixMax;
        }
        if ($enStock) {
            $where[] = "a.stock > 0";
        }
        if ($noteMin > 0 && $noteMin <= 5) {
            $where[]  = "(SELECT AVG(note) FROM note_article WHERE id_article = a.id_article) >= ?";
            $params[] = $noteMin;
        }
        // Filtre par tag (Phase 3.2)
        if ($idTag > 0) {
            $where[]  = "a.id_article IN (SELECT id_article FROM article_tag WHERE id_tag = ?)";
            $params[] = $idTag;
        }

        $clauseWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $orderBy = match ($tri) {
            'prix_asc'    => 'a.prix ASC',
            'prix_desc'   => 'a.prix DESC',
            'populaires'  => 'nb_ventes DESC, a.date_ajout DESC',
            'note_desc'   => 'note_moyenne DESC, nb_notes DESC, a.date_ajout DESC',
            'alpha'       => 'a.nom ASC',
            default       => 'a.date_ajout DESC',
        };

        // Comptage total
        $reqTotal = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM article a" . $clauseWhere
        );
        $reqTotal->execute($params);
        $total = (int)$reqTotal->fetchColumn();

        // Requête principale avec sous-requêtes pour stats
        $sql = "SELECT a.*, c.code AS categorie_code, c.nom AS categorie_nom,
                       (SELECT COALESCE(SUM(quantite), 0) FROM ligne_facture WHERE id_article = a.id_article) AS nb_ventes,
                       (SELECT ROUND(AVG(note), 1) FROM note_article WHERE id_article = a.id_article) AS note_moyenne,
                       (SELECT COUNT(*) FROM note_article WHERE id_article = a.id_article) AS nb_notes
                FROM article a
                INNER JOIN categorie c ON c.id_categorie = a.id_categorie
                $clauseWhere
                ORDER BY $orderBy
                LIMIT $parPage OFFSET $offset";

        $req = Db::pdo()->prepare($sql);
        $req->execute($params);
        $articles = $req->fetchAll();

        return [
            'articles'   => $articles,
            'total'      => $total,
            'totalPages' => (int)ceil($total / $parPage),
            'page'       => $page,
        ];
    }

    /**
     * Crée un nouvel article.
     */
    public static function creer(array $donnees): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO article
                (nom, id_categorie, description, prix, stock, image, poids_grammes, dispo)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $req->execute([
            $donnees['nom'],
            $donnees['id_categorie'],
            $donnees['description'] ?? null,
            $donnees['prix'],
            $donnees['stock']  ?? 0,
            $donnees['image']  ?? null,
            $donnees['poids_grammes'] ?? null,
            $donnees['dispo']  ?? 1,
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Modifie un article existant.
     */
    public static function modifier(int $idArticle, array $donnees): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE article SET
                nom = ?, id_categorie = ?, description = ?, prix = ?,
                stock = ?, image = ?, poids_grammes = ?, dispo = ?
             WHERE id_article = ?"
        );
        return $req->execute([
            $donnees['nom'],
            $donnees['id_categorie'],
            $donnees['description'] ?? null,
            $donnees['prix'],
            $donnees['stock'] ?? 0,
            $donnees['image'] ?? null,
            $donnees['poids_grammes'] ?? null,
            $donnees['dispo'] ?? 1,
            $idArticle,
        ]);
    }

    /**
     * "Supprime" un article = soft delete via dispo = 0
     * (on ne fait jamais de hard delete car les factures référencent l'article).
     */
    public static function supprimer(int $idArticle): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE article SET dispo = 0 WHERE id_article = ?"
        );
        return $req->execute([$idArticle]);
    }

    /**
     * Décrémente le stock après achat.
     * Utilise une transaction sécurisée pour éviter le sous-stockage.
     */
    public static function decrementerStock(int $idArticle, int $quantite): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE article
             SET stock = stock - ?
             WHERE id_article = ? AND stock >= ?"
        );
        $req->execute([$quantite, $idArticle, $quantite]);
        return $req->rowCount() > 0;
    }

    /**
     * Liste les articles les plus vus (page d'accueil / recommandations).
     */
    public static function lesPlusVus(int $limite = 4): array
    {
        $req = Db::pdo()->prepare(
            "SELECT a.*, c.nom AS categorie_nom,
                    (SELECT COUNT(*) FROM vue_article WHERE id_article = a.id_article) AS nb_vues
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.dispo = 1
             ORDER BY nb_vues DESC
             LIMIT ?"
        );
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Enregistre une vue d'article.
     */
    public static function enregistrerVue(int $idArticle, ?int $idMembre = null): void
    {
        try {
            $req = Db::pdo()->prepare(
                "INSERT INTO vue_article (id_article, id_membre, ip) VALUES (?, ?, ?)"
            );
            $req->execute([$idArticle, $idMembre, Securite::ip()]);
        } catch (Throwable $e) {
            // Best effort
        }
    }

    /**
     * Compte tous les articles (admin).
     */
    public static function compterTous(): int
    {
        return (int)Db::pdo()->query("SELECT COUNT(*) FROM article")->fetchColumn();
    }


    // =================================================================
    // TAGS SUR ARTICLES (Phase 3.2)
    // =================================================================

    /**
     * Retourne la liste des tags associés à un article.
     *
     * @param int $idArticle
     * @return array Liste de tags [['id_tag', 'code', 'nom'], ...]
     */
    public static function tagsDe(int $idArticle): array
    {
        try {
            $req = Db::pdo()->prepare(
                "SELECT t.* FROM tag t
                 INNER JOIN article_tag at ON at.id_tag = t.id_tag
                 WHERE at.id_article = ?
                 ORDER BY t.nom"
            );
            $req->execute([$idArticle]);
            return $req->fetchAll();
        } catch (Throwable $e) {
            // Table pas encore créée → tableau vide
            return [];
        }
    }

    /**
     * Met à jour les tags associés à un article.
     * Supprime tous les liens existants puis recrée selon la nouvelle liste.
     *
     * @param int   $idArticle
     * @param array $idsTags Liste des id_tag à associer
     * @return bool
     */
    public static function associerTags(int $idArticle, array $idsTags): bool
    {
        $pdo = Db::pdo();
        try {
            $pdo->beginTransaction();

            // Supprime les liens existants
            $pdo->prepare("DELETE FROM article_tag WHERE id_article = ?")
                ->execute([$idArticle]);

            // Ajoute les nouveaux
            if (!empty($idsTags)) {
                $req = $pdo->prepare(
                    "INSERT IGNORE INTO article_tag (id_article, id_tag) VALUES (?, ?)"
                );
                foreach ($idsTags as $idTag) {
                    $idTag = (int)$idTag;
                    if ($idTag > 0) {
                        $req->execute([$idArticle, $idTag]);
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Récupère les IDs des tags associés à un article (utile pour le form admin).
     *
     * @param int $idArticle
     * @return array Liste d'IDs [3, 7, 12]
     */
    public static function idsTagsDe(int $idArticle): array
    {
        try {
            $req = Db::pdo()->prepare(
                "SELECT id_tag FROM article_tag WHERE id_article = ?"
            );
            $req->execute([$idArticle]);
            return array_map('intval', $req->fetchAll(PDO::FETCH_COLUMN));
        } catch (Throwable $e) {
            return [];
        }
    }


    // =================================================================
    // COMPARATEUR D'ARTICLES (Phase 3.3)
    // =================================================================

    /**
     * Récupère plusieurs articles à comparer (max 4) avec leurs stats.
     *
     * @param array $idsArticles Liste d'IDs à comparer
     * @return array Liste d'articles avec stats complètes (note moyenne, nb ventes, etc.)
     */
    public static function pourComparaison(array $idsArticles): array
    {
        // Filtre + déduplique + max 4
        $idsArticles = array_unique(array_filter(array_map('intval', $idsArticles)));
        $idsArticles = array_slice($idsArticles, 0, 4);

        if (empty($idsArticles)) {
            return [];
        }

        // Placeholders pour la requête préparée
        $placeholders = implode(',', array_fill(0, count($idsArticles), '?'));

        $sql = "SELECT a.*, c.code AS categorie_code, c.nom AS categorie_nom,
                       (SELECT COALESCE(SUM(quantite), 0) FROM ligne_facture WHERE id_article = a.id_article) AS nb_ventes,
                       (SELECT ROUND(AVG(note), 1) FROM note_article WHERE id_article = a.id_article) AS note_moyenne,
                       (SELECT COUNT(*) FROM note_article WHERE id_article = a.id_article) AS nb_notes
                FROM article a
                INNER JOIN categorie c ON c.id_categorie = a.id_categorie
                WHERE a.id_article IN ($placeholders) AND a.dispo = 1
                ORDER BY FIELD(a.id_article, $placeholders)";

        // Doubler les params (pour le IN ET pour le FIELD du tri)
        $params = array_merge($idsArticles, $idsArticles);

        $req = Db::pdo()->prepare($sql);
        $req->execute($params);
        return $req->fetchAll();
    }
}
