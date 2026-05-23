<?php
/**
 * classes/Facture.php
 * ---------------------------------------------------------------------
 * Modèle "Facture" (commandes/achats).
 *
 * Méthodes :
 *   - creer() : crée une facture depuis le panier, en transaction
 *   - changerStatut() : admin uniquement
 *   - listerDuMembre(), trouverParId(), lignesDe()
 *
 * IMPORTANT : la création de facture est entièrement transactionnelle :
 *   - création de la facture
 *   - création des lignes_facture (snapshot des prix)
 *   - décrémentation du stock
 *   - enregistrement du code promo si utilisé
 *   - création du paiement (simulé)
 * Si quoi que ce soit échoue, ROLLBACK total.
 * ---------------------------------------------------------------------
 */

class Facture
{
    /**
     * Crée une facture complète depuis le panier en session.
     *
     * @param int   $idMembre
     * @param array $donnees Doit contenir :
     *   - 'id_adresse_livraison'
     *   - 'id_adresse_facturation'
     *   - 'methode_paiement' (carte, paypal, virement, ...)
     *   - 'code_promo' (optionnel)
     * @return array ['succes' => bool, 'id_facture' => int|null, 'erreur' => string|null]
     */
    public static function creerDepuisPanier(int $idMembre, array $donnees): array
    {
        $panier = Panier::detail();
        if (empty($panier['lignes'])) {
            return ['succes' => false, 'id_facture' => null,
                    'erreur' => 'Votre panier est vide.'];
        }

        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            // 1. Vérification du stock (encore une fois, juste avant la transaction)
            foreach ($panier['lignes'] as $ligne) {
                $article = $ligne['article'];
                if ((int)$article['stock'] < $ligne['quantite']) {
                    throw new RuntimeException(
                        'Stock insuffisant pour : ' . $article['nom']
                    );
                }
            }

            // 2. Récupération de l'adresse de livraison pour le pays
            $adresseLiv = Adresse::trouverParId((int)$donnees['id_adresse_livraison']);
            if (!$adresseLiv || (int)$adresseLiv['id_membre'] !== $idMembre) {
                throw new RuntimeException('Adresse de livraison invalide.');
            }

            // 3. Frais de port — selon le mode choisi par le client (ou auto)
            $fraisPort = null;
            $modeLivraison = null;
            if (!empty($donnees['id_frais_port'])) {
                // Mode choisi explicitement : on VÉRIFIE qu'il est encore applicable
                // (anti-tampering : le client ne peut pas envoyer n'importe quel ID)
                $fraisPort = FraisPort::trouverApplicable(
                    (int)$donnees['id_frais_port'],
                    $adresseLiv['pays'],
                    $panier['sous_total']
                );
                if (!$fraisPort) {
                    throw new RuntimeException('Mode de livraison invalide.');
                }
            } else {
                // Pas de choix → fallback : on prend le moins cher
                $fraisPort = FraisPort::calculer($adresseLiv['pays'], $panier['sous_total']);
            }
            $montantFraisPort = $fraisPort ? (float)$fraisPort['prix'] : 0.0;
            $modeLivraison    = $fraisPort ? $fraisPort['nom']         : null;

            // 4. Code promo
            $montantRemise = 0.0;
            $codePromoUtilise = null;
            if (!empty($donnees['code_promo'])) {
                $resultatPromo = CodePromo::appliquer(
                    $donnees['code_promo'], $idMembre, $panier['sous_total']
                );
                if ($resultatPromo['valide']) {
                    $montantRemise = $resultatPromo['remise'];
                    $codePromoUtilise = $resultatPromo['code'];
                    // Livraison offerte ?
                    if ($codePromoUtilise['type_remise'] === 'livraison_offerte') {
                        $montantFraisPort = 0;
                    }
                }
            }

            // 5. Calcul du total final
            $prixTotal = $panier['sous_total'] + $montantFraisPort - $montantRemise;
            $prixTotal = max(0, $prixTotal); // pas de prix négatif

            // 6. Statut initial : "payée" (paiement simulé, validation immédiate)
            $reqStatut = $pdo->prepare(
                "SELECT id_statut FROM statut_commande WHERE code = 'paye' LIMIT 1"
            );
            $reqStatut->execute();
            $idStatut = (int)$reqStatut->fetchColumn();
            if ($idStatut <= 0) {
                throw new RuntimeException('Statut "payée" introuvable.');
            }

            // 7. Génération d'une référence unique
            $reference = 'PDV-' . date('Ymd') . '-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

            // 8. Insertion de la facture
            $reqFacture = $pdo->prepare(
                "INSERT INTO achat_facture
                    (id_membre, id_statut, id_adresse_livraison, id_adresse_facturation,
                     reference, sous_total, montant_frais_port, mode_livraison,
                     montant_remise, prix_total)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $reqFacture->execute([
                $idMembre, $idStatut,
                $donnees['id_adresse_livraison'],
                $donnees['id_adresse_facturation'] ?? $donnees['id_adresse_livraison'],
                $reference,
                $panier['sous_total'],
                $montantFraisPort,
                $modeLivraison,
                $montantRemise,
                $prixTotal,
            ]);
            $idFacture = (int)$pdo->lastInsertId();

            // 9. Insertion des lignes + décrément stock
            $reqLigne = $pdo->prepare(
                "INSERT INTO ligne_facture (id_facture, id_article, quantite, prix_unitaire)
                 VALUES (?, ?, ?, ?)"
            );
            $reqDecrement = $pdo->prepare(
                "UPDATE article SET stock = stock - ? WHERE id_article = ? AND stock >= ?"
            );

            foreach ($panier['lignes'] as $ligne) {
                $reqLigne->execute([
                    $idFacture,
                    $ligne['article']['id_article'],
                    $ligne['quantite'],
                    $ligne['prix_unitaire'],
                ]);
                $reqDecrement->execute([
                    $ligne['quantite'],
                    $ligne['article']['id_article'],
                    $ligne['quantite'],
                ]);
                if ($reqDecrement->rowCount() === 0) {
                    throw new RuntimeException(
                        'Stock insuffisant pour : ' . $ligne['article']['nom']
                    );
                }
            }

            // 10. Enregistrement du paiement
            $methodePaiement = $donnees['methode_paiement'] ?? 'carte';
            $reqPaiement = $pdo->prepare(
                "INSERT INTO paiement
                    (id_facture, methode, montant, statut, reference_ext)
                 VALUES (?, ?, ?, 'accepte', ?)"
            );
            $reqPaiement->execute([
                $idFacture,
                $methodePaiement,
                $prixTotal,
                'SIM-' . bin2hex(random_bytes(8)),  // référence simulée
            ]);

            // 11. Enregistrement du code promo s'il a été utilisé
            if ($codePromoUtilise !== null) {
                CodePromo::enregistrerUtilisation(
                    (int)$codePromoUtilise['id_code'],
                    $idMembre,
                    $idFacture,
                    $montantRemise
                );
            }

            // 12. Commit
            $pdo->commit();

            // 13. Vider le panier
            Panier::vider();

            return ['succes' => true, 'id_facture' => $idFacture, 'erreur' => null];

        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['succes' => false, 'id_facture' => null,
                    'erreur' => $e->getMessage()];
        }
    }

    /**
     * Trouve une facture par ID, avec infos statut, adresse, paiement.
     */
    public static function trouverParId(int $idFacture): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT f.*, s.code AS statut_code, s.nom AS statut_nom, s.couleur AS statut_couleur,
                    m.prenom, m.nom, m.email, m.date_anonymisation
             FROM achat_facture f
             INNER JOIN statut_commande s ON s.id_statut = f.id_statut
             INNER JOIN membre m ON m.id_membre = f.id_membre
             WHERE f.id_facture = ?"
        );
        $req->execute([$idFacture]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste les commandes d'un membre.
     */
    public static function listerDuMembre(int $idMembre): array
    {
        $req = Db::pdo()->prepare(
            "SELECT f.*, s.code AS statut_code, s.nom AS statut_nom, s.couleur AS statut_couleur,
                    (SELECT COUNT(*) FROM ligne_facture WHERE id_facture = f.id_facture) AS nb_articles
             FROM achat_facture f
             INNER JOIN statut_commande s ON s.id_statut = f.id_statut
             WHERE f.id_membre = ?
             ORDER BY f.date_achat DESC"
        );
        $req->execute([$idMembre]);
        return $req->fetchAll();
    }

    /**
     * Liste toutes les commandes (admin).
     */
    /**
     * Liste toutes les commandes (admin) avec filtres et pagination.
     *
     * @param array $opts
     *   - 'statut'    : id_statut pour filtrer (0 = tous)
     *   - 'recherche' : recherche dans login/prenom/nom du membre
     *   - 'date_min'  : date min (YYYY-MM-DD)
     *   - 'date_max'  : date max (YYYY-MM-DD)
     *   - 'page'      : page courante (défaut 1)
     *   - 'parPage'   : nombre par page (défaut 20)
     *
     * @return array ['commandes' => [...], 'total' => N, 'totalPages' => N, 'page' => N]
     *
     * Note de compatibilité : ancien appel `listerToutes()` sans args
     * retourne directement le tableau de commandes (legacy).
     */
    public static function listerToutes(array $opts = []): array
    {
        $statut    = (int)($opts['statut']    ?? 0);
        $recherche = trim($opts['recherche']  ?? '');
        $dateMin   = trim($opts['date_min']   ?? '');
        $dateMax   = trim($opts['date_max']   ?? '');
        $page      = max(1, (int)($opts['page']    ?? 1));
        $parPage   = max(1, (int)($opts['parPage'] ?? 20));
        $modeListe = !empty($opts);  // appelée avec args → mode pagination

        $where  = [];
        $params = [];

        if ($statut > 0) {
            $where[]  = 'f.id_statut = ?';
            $params[] = $statut;
        }
        if ($recherche !== '') {
            $where[]  = '(m.login LIKE ? OR m.nom LIKE ? OR m.prenom LIKE ? OR f.id_facture = ?)';
            $like = '%' . $recherche . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = (int)$recherche;  // permet aussi recherche par id
        }
        if ($dateMin !== '') {
            $where[]  = 'f.date_achat >= ?';
            $params[] = $dateMin . ' 00:00:00';
        }
        if ($dateMax !== '') {
            $where[]  = 'f.date_achat <= ?';
            $params[] = $dateMax . ' 23:59:59';
        }

        $clauseWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        // Count total
        $reqCount = Db::pdo()->prepare(
            "SELECT COUNT(*)
             FROM achat_facture f
             INNER JOIN statut_commande s ON s.id_statut = f.id_statut
             INNER JOIN membre m ON m.id_membre = f.id_membre"
            . $clauseWhere
        );
        $reqCount->execute($params);
        $total = (int)$reqCount->fetchColumn();

        // Liste paginée
        $offset = ($page - 1) * $parPage;
        $req = Db::pdo()->prepare(
            "SELECT f.*, s.code AS statut_code, s.nom AS statut_nom, s.couleur AS statut_couleur,
                    m.prenom, m.nom, m.login, m.date_anonymisation,
                    (SELECT COUNT(*) FROM ligne_facture WHERE id_facture = f.id_facture) AS nb_articles
             FROM achat_facture f
             INNER JOIN statut_commande s ON s.id_statut = f.id_statut
             INNER JOIN membre m ON m.id_membre = f.id_membre"
            . $clauseWhere
            . " ORDER BY f.date_achat DESC
                LIMIT $parPage OFFSET $offset"
        );
        $req->execute($params);
        $commandes = $req->fetchAll();

        // Compatibilité ascendante : si appel sans args, on retourne juste le tableau
        if (!$modeListe) {
            return $commandes;
        }

        return [
            'commandes'  => $commandes,
            'total'      => $total,
            'totalPages' => (int)ceil($total / $parPage),
            'page'       => $page,
            'parPage'    => $parPage,
        ];
    }

    /**
     * Récupère les lignes d'une facture, avec infos articles.
     */
    public static function lignesDe(int $idFacture): array
    {
        $req = Db::pdo()->prepare(
            "SELECT lf.*, a.nom AS article_nom, a.image AS article_image, c.nom AS categorie_nom
             FROM ligne_facture lf
             INNER JOIN article a ON a.id_article = lf.id_article
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE lf.id_facture = ?
             ORDER BY lf.id_ligne"
        );
        $req->execute([$idFacture]);
        return $req->fetchAll();
    }

    /**
     * Récupère les paiements d'une facture.
     */
    public static function paiementsDe(int $idFacture): array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM paiement WHERE id_facture = ? ORDER BY date_paiement"
        );
        $req->execute([$idFacture]);
        return $req->fetchAll();
    }

    /**
     * Change le statut d'une commande (admin).
     * Crée automatiquement une notification pour le client.
     */
    public static function changerStatut(int $idFacture, int $idStatut): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE achat_facture SET id_statut = ? WHERE id_facture = ?"
        );
        $ok = $req->execute([$idStatut, $idFacture]);

        if ($ok) {
            // Récupérer les infos pour la notification
            $infos = self::trouverParId($idFacture);
            if ($infos && class_exists('Notification')) {
                Notification::creer(
                    (int)$infos['id_membre'],
                    'commande.statut',
                    'Votre commande ' . $infos['reference'],
                    'Le statut de votre commande est maintenant : ' . $infos['statut_nom'],
                    '/facture.php?id=' . $idFacture
                );
            }
        }

        return $ok;
    }

    /**
     * Compte total des commandes (stats).
     */
    public static function compterToutes(): int
    {
        return (int)Db::pdo()->query("SELECT COUNT(*) FROM achat_facture")->fetchColumn();
    }

    /**
     * Chiffre d'affaires total (stats admin).
     */
    public static function chiffreAffairesTotal(): float
    {
        return (float)Db::pdo()->query(
            "SELECT COALESCE(SUM(prix_total), 0) FROM achat_facture
             WHERE id_statut IN (SELECT id_statut FROM statut_commande
                                  WHERE code IN ('paye', 'preparation', 'expedie', 'livre'))"
        )->fetchColumn();
    }
}
