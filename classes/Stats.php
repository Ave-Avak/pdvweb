<?php
/**
 * classes/Stats.php
 * ---------------------------------------------------------------------
 * Statistiques agrégées pour le tableau de bord admin (étape 7).
 *
 * Toutes les méthodes sont READ-ONLY et ne font que des SELECT.
 * Pas de pagination ici : on retourne des top N (10 par défaut).
 * ---------------------------------------------------------------------
 */

class Stats
{
    // ===================================================================
    //  CONNEXIONS
    // ===================================================================

    /**
     * Nombre de connexions sur les N derniers jours.
     */
    public static function nbConnexionsRecentes(int $jours = 1): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM log_connexion WHERE date_log >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $req->execute([$jours]);
        return (int)$req->fetchColumn();
    }

    /**
     * Nombre de membres uniques connectés sur les N derniers jours.
     */
    public static function nbMembresActifs(int $jours = 7): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(DISTINCT id_membre) FROM log_connexion
             WHERE date_log >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $req->execute([$jours]);
        return (int)$req->fetchColumn();
    }

    /**
     * Activité des connexions par jour sur N derniers jours.
     * @return array Liste de ['jour' => 'YYYY-MM-DD', 'nb' => N]
     */
    public static function connexionsParJour(int $jours = 30): array
    {
        $req = Db::pdo()->prepare(
            "SELECT DATE(date_log) AS jour, COUNT(*) AS nb
             FROM log_connexion
             WHERE date_log >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(date_log)
             ORDER BY jour ASC"
        );
        $req->execute([$jours]);
        return $req->fetchAll();
    }

    /**
     * Top membres par nb de connexions sur N jours.
     */
    public static function topMembresConnexion(int $jours = 30, int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.statut, m.date_anonymisation,
                    COUNT(l.id_log) AS nb_connexions,
                    MAX(l.date_log) AS derniere_connexion
             FROM log_connexion l
             INNER JOIN membre m ON m.id_membre = l.id_membre
             WHERE l.date_log >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.statut, m.date_anonymisation
             ORDER BY nb_connexions DESC
             LIMIT ?"
        );
        $req->bindValue(1, $jours,  PDO::PARAM_INT);
        $req->bindValue(2, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }


    // ===================================================================
    //  RECHERCHES
    // ===================================================================

    /**
     * Top termes recherchés (toutes recherches confondues).
     */
    public static function topRecherches(int $jours = 30, int $limite = 20): array
    {
        $req = Db::pdo()->prepare(
            "SELECT terme, COUNT(*) AS nb, AVG(nb_resultats) AS moy_resultats,
                    contexte
             FROM recherche_log
             WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY terme, contexte
             ORDER BY nb DESC
             LIMIT ?"
        );
        $req->bindValue(1, $jours,  PDO::PARAM_INT);
        $req->bindValue(2, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Recherches sans aucun résultat (opportunités produit/contenu).
     */
    public static function recherchesSansResultat(int $jours = 30, int $limite = 20): array
    {
        $req = Db::pdo()->prepare(
            "SELECT terme, COUNT(*) AS nb, contexte, MAX(date_recherche) AS derniere
             FROM recherche_log
             WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL ? DAY)
               AND nb_resultats = 0
             GROUP BY terme, contexte
             ORDER BY nb DESC
             LIMIT ?"
        );
        $req->bindValue(1, $jours,  PDO::PARAM_INT);
        $req->bindValue(2, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Nombre total de recherches sur N jours (KPI).
     */
    public static function nbRecherchesTotales(int $jours = 30): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM recherche_log
             WHERE date_recherche >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $req->execute([$jours]);
        return (int)$req->fetchColumn();
    }


    // ===================================================================
    //  ARTICLES (top)
    // ===================================================================

    /**
     * Top articles par quantité vendue.
     */
    public static function topArticlesVendus(int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT a.id_article, a.nom, a.prix, a.image, a.dispo,
                    c.nom AS categorie_nom,
                    SUM(lf.quantite)              AS qte_vendue,
                    SUM(lf.quantite * lf.prix_unitaire) AS ca_genere
             FROM ligne_facture lf
             INNER JOIN article a   ON a.id_article = lf.id_article
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             GROUP BY a.id_article, a.nom, a.prix, a.image, a.dispo, c.nom
             ORDER BY qte_vendue DESC
             LIMIT ?"
        );
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Top articles par nombre de vues.
     */
    public static function topArticlesVus(int $jours = 30, int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT a.id_article, a.nom, a.prix, a.image, a.dispo,
                    c.nom AS categorie_nom,
                    COUNT(v.id_vue) AS nb_vues
             FROM vue_article v
             INNER JOIN article a   ON a.id_article = v.id_article
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE v.date_vue >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY a.id_article, a.nom, a.prix, a.image, a.dispo, c.nom
             ORDER BY nb_vues DESC
             LIMIT ?"
        );
        $req->bindValue(1, $jours,  PDO::PARAM_INT);
        $req->bindValue(2, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Top articles par note moyenne (min 3 notes).
     */
    public static function topArticlesNotes(int $limite = 10): array
    {
        // Note : la table note_article a une PK composite (id_membre, id_article),
        // pas de colonne id_note. On utilise COUNT(*) pour compter les notes.
        $req = Db::pdo()->prepare(
            "SELECT a.id_article, a.nom, a.prix, a.image, a.dispo,
                    c.nom AS categorie_nom,
                    ROUND(AVG(n.note), 1) AS moyenne,
                    COUNT(*)              AS nb_notes
             FROM note_article n
             INNER JOIN article a   ON a.id_article = n.id_article
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             GROUP BY a.id_article, a.nom, a.prix, a.image, a.dispo, c.nom
             HAVING nb_notes >= 3
             ORDER BY moyenne DESC, nb_notes DESC
             LIMIT ?"
        );
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Articles dont le stock est inférieur à un seuil.
     */
    public static function articlesStockBas(int $seuil = 5): array
    {
        $req = Db::pdo()->prepare(
            "SELECT a.id_article, a.nom, a.stock, a.prix, c.nom AS categorie_nom
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.dispo = 1 AND a.stock <= ?
             ORDER BY a.stock ASC, a.nom"
        );
        $req->execute([$seuil]);
        return $req->fetchAll();
    }


    // ===================================================================
    //  MEMBRES (top)
    // ===================================================================

    /**
     * Top membres acheteurs par chiffre d'affaires.
     */
    public static function topMembresAcheteurs(int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.date_anonymisation,
                    COUNT(f.id_facture)   AS nb_commandes,
                    SUM(f.prix_total)     AS ca_total
             FROM achat_facture f
             INNER JOIN membre m ON m.id_membre = f.id_membre
             GROUP BY m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.date_anonymisation
             ORDER BY ca_total DESC
             LIMIT ?"
        );
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Top membres actifs sur le blog (commentaires).
     */
    public static function topMembresBlog(int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.date_anonymisation,
                    COUNT(*) AS nb_commentaires
             FROM commentaire c
             INNER JOIN membre m ON m.id_membre = c.id_membre
             WHERE c.date_suppression IS NULL
             GROUP BY m.id_membre, m.login, m.prenom, m.nom, m.avatar, m.date_anonymisation
             ORDER BY nb_commentaires DESC
             LIMIT ?"
        );
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }


    // ===================================================================
    //  COMMERCE (KPI globaux pour dashboard)
    // ===================================================================

    /**
     * Ventes par jour sur N derniers jours (pour graphique).
     * @return array ['jour' => 'YYYY-MM-DD', 'nb' => N, 'ca' => €]
     */
    public static function ventesParJour(int $jours = 30): array
    {
        $req = Db::pdo()->prepare(
            "SELECT DATE(date_achat) AS jour,
                    COUNT(*)         AS nb,
                    SUM(prix_total)  AS ca
             FROM achat_facture
             WHERE date_achat >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY DATE(date_achat)
             ORDER BY jour ASC"
        );
        $req->execute([$jours]);
        return $req->fetchAll();
    }

    /**
     * CA sur N derniers jours.
     */
    public static function caRecent(int $jours = 30): float
    {
        $req = Db::pdo()->prepare(
            "SELECT COALESCE(SUM(prix_total), 0)
             FROM achat_facture
             WHERE date_achat >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $req->execute([$jours]);
        return (float)$req->fetchColumn();
    }

    /**
     * Nb commandes sur N derniers jours.
     */
    public static function nbCommandesRecentes(int $jours = 30): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM achat_facture
             WHERE date_achat >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $req->execute([$jours]);
        return (int)$req->fetchColumn();
    }
}
