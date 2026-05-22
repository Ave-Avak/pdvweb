<?php
/**
 * classes/Membre.php
 * ---------------------------------------------------------------------
 * Modèle "Membre" : encapsule toute la logique métier liée aux comptes
 * utilisateurs (UM et Admin).
 *
 * C'est la première classe MÉTIER du projet (les autres - Db, Auth,
 * Csrf, etc. - sont des classes UTILITAIRES).
 *
 * Toutes les méthodes sont statiques : on n'instancie jamais Membre,
 * on appelle directement Membre::trouverParLogin('jdupont').
 *
 * Cette approche garde le code procédural-friendly tout en bénéficiant
 * de l'organisation POO.
 * ---------------------------------------------------------------------
 */

class Membre
{
    // =================================================================
    // RECHERCHE
    // =================================================================

    /**
     * Trouve un membre par son ID.
     *
     * @param int $id
     * @return array|null Données du membre, ou null si introuvable
     */
    public static function trouverParId(int $id): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM membre WHERE id_membre = ?");
        $req->execute([$id]);
        $membre = $req->fetch();
        return $membre ?: null;
    }

    /**
     * Trouve un membre par son login.
     *
     * @param string $login
     * @return array|null
     */
    public static function trouverParLogin(string $login): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM membre WHERE login = ?");
        $req->execute([$login]);
        $membre = $req->fetch();
        return $membre ?: null;
    }

    /**
     * Trouve un membre par son email.
     *
     * @param string $email
     * @return array|null
     */
    public static function trouverParEmail(string $email): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM membre WHERE email = ?");
        $req->execute([$email]);
        $membre = $req->fetch();
        return $membre ?: null;
    }

    /**
     * Liste tous les membres (pour l'admin).
     *
     * @return array
     */
    public static function listerTous(): array
    {
        return Db::pdo()->query(
            "SELECT * FROM membre ORDER BY date_inscription DESC"
        )->fetchAll();
    }


    /**
     * Liste paginée avec filtres pour l'administration.
     *
     * @param array $opts
     *   - 'recherche'  ?string : LIKE sur login, email, nom, prenom
     *   - 'statut'     ?string : 'membre' | 'admin'
     *   - 'filtre'     ?string : 'tous' | 'bloques' | 'anonymises' | 'actifs'
     *   - 'page'       int
     *   - 'parPage'    int
     */
    public static function listerAvecFiltres(array $opts = []): array
    {
        $page    = max(1, (int)($opts['page']    ?? 1));
        $parPage = max(1, (int)($opts['parPage'] ?? 25));
        $offset  = ($page - 1) * $parPage;

        $where  = [];
        $params = [];

        if (!empty($opts['recherche'])) {
            $where[]  = "(m.login LIKE ? OR m.email LIKE ? OR m.nom LIKE ? OR m.prenom LIKE ?)";
            $like = '%' . $opts['recherche'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($opts['statut']) && in_array($opts['statut'], ['membre', 'admin'], true)) {
            $where[]  = "m.statut = ?";
            $params[] = $opts['statut'];
        }

        // Filtre composé
        $filtre = $opts['filtre'] ?? 'tous';
        switch ($filtre) {
            case 'bloques':
                $where[] = "m.indesirable = 1 AND m.date_anonymisation IS NULL";
                break;
            case 'anonymises':
                $where[] = "m.date_anonymisation IS NOT NULL";
                break;
            case 'actifs':
                $where[] = "m.indesirable = 0 AND m.date_anonymisation IS NULL";
                break;
            // 'tous' : pas de filtre
        }

        $clauseWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        // Total
        $reqTotal = Db::pdo()->prepare("SELECT COUNT(*) FROM membre m" . $clauseWhere);
        $reqTotal->execute($params);
        $total = (int)$reqTotal->fetchColumn();

        // Données + nb connexions sur 30j
        $sql = "SELECT m.*,
                       (SELECT COUNT(*) FROM log_connexion
                        WHERE id_membre = m.id_membre
                          AND date_log >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS nb_connexions_30j,
                       (SELECT COUNT(*) FROM achat_facture WHERE id_membre = m.id_membre) AS nb_commandes
                FROM membre m
                $clauseWhere
                ORDER BY m.date_inscription DESC
                LIMIT $parPage OFFSET $offset";

        $req = Db::pdo()->prepare($sql);
        $req->execute($params);
        $membres = $req->fetchAll();

        return [
            'membres'    => $membres,
            'total'      => $total,
            'totalPages' => (int)ceil($total / $parPage),
            'page'       => $page,
        ];
    }


    /**
     * Change le rôle d'un membre (membre <-> admin).
     * Trace l'action dans audit_log.
     */
    public static function changerRole(int $idMembre, string $nouveauStatut, int $idAdmin): bool
    {
        if (!in_array($nouveauStatut, ['membre', 'admin'], true)) {
            return false;
        }
        $req = Db::pdo()->prepare("UPDATE membre SET statut = ? WHERE id_membre = ?");
        $ok = $req->execute([$nouveauStatut, $idMembre]);

        if ($ok && class_exists('AuditLog')) {
            AuditLog::enregistrer(
                'membre.role_change',
                $idAdmin,
                'membre',
                $idMembre,
                ['nouveau_statut' => $nouveauStatut]
            );
        }

        return $ok;
    }


    // =================================================================
    // CRÉATION (INSCRIPTION)
    // =================================================================

    /**
     * Vérifie si un login est déjà pris.
     */
    public static function loginExiste(string $login): bool
    {
        $req = Db::pdo()->prepare("SELECT COUNT(*) FROM membre WHERE login = ?");
        $req->execute([$login]);
        return $req->fetchColumn() > 0;
    }

    /**
     * Vérifie si un email est déjà utilisé.
     */
    public static function emailExiste(string $email): bool
    {
        $req = Db::pdo()->prepare("SELECT COUNT(*) FROM membre WHERE email = ?");
        $req->execute([$email]);
        return $req->fetchColumn() > 0;
    }

    /**
     * Crée un nouveau membre.
     *
     * @param array $donnees Tableau associatif avec : nom, prenom, date_naissance,
     *                       email, login, mot_passe (en clair), avatar (optionnel)
     * @return int ID du nouveau membre créé
     */
    public static function creer(array $donnees): int
    {
        $pdo = Db::pdo();

        $req = $pdo->prepare(
            "INSERT INTO membre
                (nom, prenom, date_naissance, email, login, mot_passe, avatar, statut)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, 'membre')"
        );

        $req->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['email'],
            $donnees['login'],
            password_hash($donnees['mot_passe'], PASSWORD_DEFAULT),
            $donnees['avatar'] ?? null,
        ]);

        $idMembre = (int)$pdo->lastInsertId();

        // Attribuer le rôle "membre" par défaut (id_role = 3 dans le seed)
        $reqRole = $pdo->prepare(
            "INSERT INTO membre_role (id_membre, id_role)
             VALUES (?, (SELECT id_role FROM role WHERE code = 'membre'))"
        );
        $reqRole->execute([$idMembre]);

        // Audit log : trace de l'inscription
        self::ajouterAudit($idMembre, 'membre.inscription', 'membre', $idMembre,
            ['login' => $donnees['login']]);

        return $idMembre;
    }


    // =================================================================
    // CONNEXION
    // =================================================================

    /**
     * Tente la connexion d'un utilisateur.
     *
     * @param string $login
     * @param string $motPasse
     * @return array ['succes' => bool, 'membre' => array|null, 'erreur' => string|null]
     */
    public static function tenterConnexion(string $login, string $motPasse): array
    {
        // 1. Anti brute-force : ce login est-il bloqué temporairement ?
        if (Securite::estBloque($login)) {
            $minutes = Securite::minutesAvantDeblocage($login);
            return [
                'succes' => false,
                'membre' => null,
                'erreur' => "Trop de tentatives ratées. Réessayez dans environ $minutes minute(s).",
            ];
        }

        // 2. Récupération du membre
        $membre = self::trouverParLogin($login);

        // 3. Vérifications combinées (membre inexistant OU mot de passe incorrect)
        //    Pour ne pas révéler à un attaquant si le login existe ou non,
        //    on retourne le même message dans les deux cas.
        if (!$membre || !password_verify($motPasse, $membre['mot_passe'])) {
            Securite::enregistrerTentative($login, false);
            return [
                'succes' => false,
                'membre' => null,
                'erreur' => 'Login ou mot de passe incorrect.',
            ];
        }

        // 4. Le compte est-il bloqué par l'admin ?
        if ((int)$membre['indesirable'] === 1) {
            Securite::enregistrerTentative($login, false);
            return [
                'succes' => false,
                'membre' => null,
                'erreur' => 'Votre compte a été suspendu. Contactez l\'administrateur.',
            ];
        }

        // 5. Tout est OK → connexion réussie
        Securite::enregistrerTentative($login, true);

        // 6. Audit
        self::ajouterAudit((int)$membre['id_membre'], 'membre.connexion', 'membre',
            (int)$membre['id_membre'], ['ip' => Securite::ip()]);

        return [
            'succes' => true,
            'membre' => $membre,
            'erreur' => null,
        ];
    }


    // =================================================================
    // MISE À JOUR (PROFIL)
    // =================================================================

    /**
     * Met à jour les informations de profil d'un membre.
     *
     * @param int   $idMembre
     * @param array $donnees  Champs autorisés : nom, prenom, date_naissance, email
     * @return bool
     */
    public static function mettreAJour(int $idMembre, array $donnees): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE membre
             SET nom = ?, prenom = ?, date_naissance = ?, email = ?
             WHERE id_membre = ?"
        );

        $resultat = $req->execute([
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['date_naissance'],
            $donnees['email'],
            $idMembre,
        ]);

        if ($resultat) {
            self::ajouterAudit($idMembre, 'membre.modifier', 'membre', $idMembre,
                ['champs' => array_keys($donnees)]);
        }

        return $resultat;
    }

    /**
     * Change le mot de passe d'un membre.
     *
     * @param int    $idMembre
     * @param string $nouveauMotPasse
     * @return bool
     */
    public static function changerMotPasse(int $idMembre, string $nouveauMotPasse): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE membre SET mot_passe = ? WHERE id_membre = ?"
        );

        $resultat = $req->execute([
            password_hash($nouveauMotPasse, PASSWORD_DEFAULT),
            $idMembre,
        ]);

        if ($resultat) {
            self::ajouterAudit($idMembre, 'membre.changer_mdp', 'membre', $idMembre);
        }

        return $resultat;
    }

    /**
     * Met à jour l'avatar d'un membre.
     */
    public static function mettreAJourAvatar(int $idMembre, ?string $nomFichier): bool
    {
        $req = Db::pdo()->prepare("UPDATE membre SET avatar = ? WHERE id_membre = ?");
        return $req->execute([$nomFichier, $idMembre]);
    }

    /**
     * Vérifie le mot de passe d'un membre (pour confirmer une action sensible).
     */
    public static function verifierMotPasse(int $idMembre, string $motPasse): bool
    {
        $membre = self::trouverParId($idMembre);
        if (!$membre) return false;
        return password_verify($motPasse, $membre['mot_passe']);
    }


    // =================================================================
    // ADMINISTRATION
    // =================================================================

    /**
     * Bloque ou débloque un membre (modification du flag "indesirable").
     */
    public static function bloquer(int $idMembre, bool $bloque): bool
    {
        $req = Db::pdo()->prepare("UPDATE membre SET indesirable = ? WHERE id_membre = ?");
        $resultat = $req->execute([$bloque ? 1 : 0, $idMembre]);

        if ($resultat) {
            self::ajouterAudit(
                Auth::id(),
                $bloque ? 'membre.bloquer' : 'membre.debloquer',
                'membre',
                $idMembre
            );
        }
        return $resultat;
    }

    /**
     * Compte le nombre de connexions d'un membre sur N jours glissants.
     *
     * @param int $idMembre
     * @param int $nbJours  Ex: 1 (aujourd'hui), 7 (semaine)
     * @return int
     */
    public static function nbConnexions(int $idMembre, int $nbJours = 1): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM log_connexion
             WHERE id_membre = ?
               AND date_log > NOW() - INTERVAL ? DAY"
        );
        $req->execute([$idMembre, $nbJours]);
        return (int)$req->fetchColumn();
    }


    // =================================================================
    // RGPD - DROIT À L'OUBLI (anonymisation)
    // =================================================================

    /**
     * Anonymise un compte (conformité RGPD article 17 : droit à l'oubli).
     *
     * Ne supprime PAS l'enregistrement (pour préserver l'intégrité des
     * commentaires, factures, etc.) mais remplace toutes les données
     * personnelles identifiables par des valeurs neutres.
     *
     * Effets :
     *   - Le compte ne peut plus se connecter (mot de passe aléatoire impossible)
     *   - Le compte est marqué comme indésirable (verrouillé)
     *   - Les commentaires/billets passés apparaissent sous "Utilisateur supprimé"
     *   - L'avatar physique est supprimé du disque
     *   - La date d'anonymisation est enregistrée (traçabilité)
     *   - Audit log
     *
     * @param int $idMembre L'ID du membre à anonymiser
     * @return bool true si succès
     */
    public static function anonymiser(int $idMembre): bool
    {
        $membre = self::trouverParId($idMembre);
        if (!$membre) {
            return false;
        }

        // Empêcher la double anonymisation
        if (!empty($membre['date_anonymisation'])) {
            return false;
        }

        $pdo = Db::pdo();
        $pdo->beginTransaction();

        try {
            // 1. Supprimer physiquement l'avatar si présent (RGPD : effacement du fichier)
            if (!empty($membre['avatar'])) {
                Upload::supprimer($membre['avatar'], UPLOADS_PATH . '/avatars');
            }

            // 2. Construire des valeurs anonymes uniques (pour respecter les UNIQUE)
            $suffixe = $idMembre . '_' . bin2hex(random_bytes(4));

            $loginAnonyme = 'supprime_' . $suffixe;
            $emailAnonyme = 'supprime_' . $suffixe . '@anonymise.local';

            // 3. Mot de passe aléatoire impossible à deviner (60 caractères)
            $mdpAleatoire = bin2hex(random_bytes(30));
            $hashAleatoire = password_hash($mdpAleatoire, PASSWORD_DEFAULT);

            // 4. UPDATE de toutes les colonnes personnelles
            // Note : on inverse "Utilisateur" et "supprimé" pour que l'affichage
            // standard "$prenom $nom" donne bien "Utilisateur supprimé".
            $req = $pdo->prepare(
                "UPDATE membre SET
                    nom = 'supprimé',
                    prenom = 'Utilisateur',
                    date_naissance = '1900-01-01',
                    email = ?,
                    login = ?,
                    mot_passe = ?,
                    avatar = NULL,
                    indesirable = 1,
                    date_anonymisation = NOW()
                 WHERE id_membre = ?"
            );
            $req->execute([$emailAnonyme, $loginAnonyme, $hashAleatoire, $idMembre]);

            // 5. Nettoyage des données liées non essentielles
            //    - log_connexion : on supprime l'historique (pas obligatoire mais cohérent)
            //    - adresses : on supprime (pas besoin pour archivage légal)
            //    - newsletter_abonne : désabonnement automatique
            //    - tokens : tous invalidés
            //    - tentatives connexion : effacées
            //    On GARDE : commentaires, billets, achats/factures, audit_log
            $pdo->prepare("DELETE FROM adresse WHERE id_membre = ?")->execute([$idMembre]);
            $pdo->prepare("UPDATE newsletter_abonne SET actif = 0, date_desabo = NOW()
                           WHERE id_membre = ?")->execute([$idMembre]);
            $pdo->prepare("DELETE FROM token WHERE id_membre = ?")->execute([$idMembre]);
            $pdo->prepare("DELETE FROM tentative_connexion WHERE login_essaye = ?")
                ->execute([$membre['login']]);

            // 6. Audit log
            self::ajouterAudit(
                Auth::id() ?? $idMembre,  // qui a fait l'action
                'membre.anonymisation',
                'membre',
                $idMembre,
                ['ancien_login' => $membre['login']]
            );

            $pdo->commit();
            return true;

        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


    // =================================================================
    // UTILITAIRES PRIVÉS
    // =================================================================

    /**
     * Ajoute une entrée à l'audit log.
     * Méthode privée utilisée en interne par les autres méthodes.
     */
    private static function ajouterAudit(
        ?int $idMembre,
        string $action,
        ?string $entite = null,
        ?int $idEntite = null,
        ?array $details = null
    ): void
    {
        try {
            $req = Db::pdo()->prepare(
                "INSERT INTO audit_log
                    (id_membre, action, entite, id_entite, details, ip, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $req->execute([
                $idMembre,
                $action,
                $entite,
                $idEntite,
                $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                Securite::ip(),
                Securite::userAgent(),
            ]);
        } catch (Throwable $e) {
            // L'audit log est best-effort : si ça échoue, on continue normalement.
        }
    }
}
