<?php
/**
 * classes/util/Auth.php
 * ---------------------------------------------------------------------
 * Gestion centralisée de l'authentification et des droits d'accès.
 *
 * Toutes les méthodes sont statiques pour pouvoir être appelées
 * facilement depuis n'importe quelle page :
 *
 *      Auth::requireLogin();           // bloque les UNM
 *      Auth::requireAdmin();           // bloque les UM et UNM
 *      if (Auth::estConnecte()) ...    // teste sans bloquer
 *      $membre = Auth::membre();       // récupère l'utilisateur courant
 *      Auth::deconnecter();            // logout
 * ---------------------------------------------------------------------
 */

class Auth
{
    /**
     * Vérifie si un utilisateur est connecté.
     */
    public static function estConnecte(): bool
    {
        return isset($_SESSION['id_membre']);
    }

    /**
     * Vérifie si l'utilisateur connecté est administrateur.
     */
    public static function estAdmin(): bool
    {
        return self::estConnecte()
            && isset($_SESSION['statut'])
            && $_SESSION['statut'] === 'admin';
    }

    /**
     * Retourne les données du membre connecté, ou null si non connecté.
     * Données stockées en session lors du login : id, login, prenom, nom,
     * email, avatar, statut.
     *
     * @return array|null
     */
    public static function membre(): ?array
    {
        if (!self::estConnecte()) {
            return null;
        }

        return [
            'id_membre' => $_SESSION['id_membre'],
            'login'     => $_SESSION['login']     ?? '',
            'prenom'    => $_SESSION['prenom']    ?? '',
            'nom'       => $_SESSION['nom']       ?? '',
            'email'     => $_SESSION['email']     ?? '',
            'avatar'    => $_SESSION['avatar']    ?? null,
            'statut'    => $_SESSION['statut']    ?? 'membre',
        ];
    }

    /**
     * Retourne l'ID du membre connecté, ou null.
     */
    public static function id(): ?int
    {
        return $_SESSION['id_membre'] ?? null;
    }

    /**
     * Force la connexion : redirige vers login.php si non connecté.
     * À appeler en tête des pages réservées aux membres.
     */
    public static function requireLogin(): void
    {
        if (!self::estConnecte()) {
            Flash::avertissement('Vous devez être connecté pour accéder à cette page.');
            // On mémorise l'URL demandée pour rediriger l'utilisateur après login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? SITE_URL;
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }

    /**
     * Force le statut admin : redirige vers l'accueil si non admin.
     * À appeler en tête des pages d'administration.
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();   // d'abord vérifier qu'il est connecté

        if (!self::estAdmin()) {
            Flash::erreur('Accès refusé : cette page est réservée aux administrateurs.');
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    }

    /**
     * Effectue la connexion d'un membre identifié.
     * Régénère l'ID de session pour éviter la fixation de session
     * (vulnérabilité OWASP).
     *
     * @param array $membre Données du membre récupérées en BDD
     */
    public static function connecter(array $membre): void
    {
        // Régénération de l'ID de session : essentiel à la sécurité
        // (empêche un attaquant d'utiliser un ID de session connu avant login)
        session_regenerate_id(true);

        // Stockage des infos minimales en session
        $_SESSION['id_membre'] = (int)$membre['id_membre'];
        $_SESSION['login']     = $membre['login'];
        $_SESSION['prenom']    = $membre['prenom'];
        $_SESSION['nom']       = $membre['nom'];
        $_SESSION['email']     = $membre['email'];
        $_SESSION['avatar']    = $membre['avatar'] ?? null;
        $_SESSION['statut']    = $membre['statut'];

        // Le cahier des charges précise : "panier persistant jusqu'à la
        // prochaine connexion". À chaque connexion, on REPART d'un panier vide.
        // (Si le membre était déjà connecté avec un panier en cours, on
        // n'efface pas — mais ici on est dans le cadre d'une nouvelle connexion.)
        unset($_SESSION['panier']);

        // Régénération du jeton CSRF après connexion
        Csrf::regenerer();

        // Mise à jour de derniere_connexion en BDD + insertion dans log_connexion
        $pdo = Db::pdo();

        $pdo->prepare("UPDATE membre SET derniere_connexion = NOW() WHERE id_membre = ?")
            ->execute([$_SESSION['id_membre']]);

        $pdo->prepare("INSERT INTO log_connexion (id_membre, ip) VALUES (?, ?)")
            ->execute([$_SESSION['id_membre'], $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    /**
     * Déconnecte l'utilisateur courant : vide la session et détruit le cookie.
     */
    public static function deconnecter(): void
    {
        // Vidage du tableau $_SESSION
        $_SESSION = [];

        // Suppression du cookie de session côté navigateur
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destruction de la session côté serveur
        session_destroy();
    }
}
