<?php
/**
 * classes/util/Csrf.php
 * ---------------------------------------------------------------------
 * Protection anti-CSRF (Cross-Site Request Forgery).
 *
 * Le CSRF est une attaque où un site malveillant fait exécuter
 * une action sur notre site en se faisant passer pour un utilisateur
 * authentifié (via ses cookies de session).
 *
 * Parade : on génère un jeton aléatoire à chaque session, on l'inclut
 * dans tous les formulaires, et on vérifie sa présence + validité côté
 * serveur avant d'accepter la requête.
 *
 * Utilisation dans un formulaire :
 *
 *      <form method="post" action="...">
 *          <?= Csrf::champ() ?>
 *          ... autres champs ...
 *      </form>
 *
 * Et côté traitement du POST :
 *
 *      if (!Csrf::verifier($_POST['csrf_token'] ?? '')) {
 *          die('Jeton CSRF invalide.');
 *      }
 * ---------------------------------------------------------------------
 */

class Csrf
{
    /**
     * Nom du champ caché dans les formulaires.
     */
    private const CHAMP = 'csrf_token';

    /**
     * Nom de la clé en session.
     */
    private const SESSION_KEY = '_csrf_token';

    /**
     * Génère un jeton et le stocke en session.
     * Si un jeton existe déjà pour cette session, on le réutilise.
     *
     * @return string Le jeton (64 caractères hexadécimaux)
     */
    public static function jeton(): string
    {
        // S'il n'y a pas encore de jeton en session, on en crée un
        if (empty($_SESSION[self::SESSION_KEY])) {
            // random_bytes(32) génère 32 octets aléatoires cryptographiquement sûrs
            // bin2hex les convertit en chaîne hexadécimale (64 caractères)
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Renvoie le HTML d'un champ caché à inclure dans un formulaire.
     *
     * @return string HTML du champ
     */
    public static function champ(): string
    {
        return '<input type="hidden" name="' . self::CHAMP . '" value="'
             . htmlspecialchars(self::jeton(), ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Vérifie qu'un jeton soumis correspond bien à celui en session.
     *
     * @param string|null $jetonSoumis Jeton récupéré dans $_POST
     * @return bool true si valide, false sinon
     */
    public static function verifier(?string $jetonSoumis): bool
    {
        // Pas de jeton soumis ou pas de jeton en session : refus immédiat
        if (empty($jetonSoumis) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // hash_equals fait une comparaison "à temps constant" qui empêche
        // certaines attaques par chronométrage (timing attack).
        // À PRÉFÉRER à un simple === pour comparer des secrets.
        return hash_equals($_SESSION[self::SESSION_KEY], $jetonSoumis);
    }

    /**
     * Récupère le jeton soumis dans la requête POST et le vérifie.
     * Méthode de confort pour ne pas avoir à écrire $_POST['csrf_token'] ?? ''.
     *
     * @return bool
     */
    public static function verifierRequete(): bool
    {
        return self::verifier($_POST[self::CHAMP] ?? null);
    }

    /**
     * Régénère un nouveau jeton (à appeler après une action sensible
     * comme la connexion, pour invalider l'ancien).
     */
    public static function regenerer(): void
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }
}
