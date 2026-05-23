<?php
/**
 * includes/bootstrap.php
 * ---------------------------------------------------------------------
 * Fichier d'amorçage de l'application.
 * À inclure EN PREMIER dans chaque page d'entrée web :
 *
 *      require_once __DIR__ . '/../includes/bootstrap.php';
 *
 * Il s'occupe de :
 *  1. Charger la configuration
 *  2. Démarrer la session
 *  3. Mettre en place l'autoload des classes
 *  4. Charger les fonctions helpers
 * ---------------------------------------------------------------------
 */

// -----------------------------------------------------------------
// 1. Chargement de la configuration
// -----------------------------------------------------------------
require_once __DIR__ . '/../config/config.php';


// -----------------------------------------------------------------
// 2. Démarrage de la session avec paramètres sécurisés
// -----------------------------------------------------------------
// On configure la session AVANT de la démarrer.
// Ces options renforcent la sécurité contre le vol de session.

if (session_status() === PHP_SESSION_NONE) {
    // Cookie HTTPOnly : pas accessible par JavaScript (anti-XSS)
    ini_set('session.cookie_httponly', '1');

    // SameSite=Lax : le cookie n'est pas envoyé sur les requêtes cross-site
    // (anti-CSRF en complément de notre jeton)
    ini_set('session.cookie_samesite', 'Lax');

    // Utiliser uniquement les cookies (pas l'URL) pour propager la session
    ini_set('session.use_only_cookies', '1');

    // Cookie Secure si HTTPS détecté (anti vol via réseau non chiffré)
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    // Renforcer l'entropie de l'ID de session (anti prédiction)
    ini_set('session.use_strict_mode', '1');

    session_start();
}


// -----------------------------------------------------------------
// 3. Autoload des classes
// -----------------------------------------------------------------
// L'autoload évite d'avoir à faire un require_once pour chaque classe.
// Quand le code rencontre "new Db()", PHP appelle automatiquement
// cette fonction qui va chercher le fichier correspondant.
//
// On supporte deux conventions de localisation :
//   - classes/util/Db.php       (classes utilitaires)
//   - classes/Membre.php        (classes métier — à venir étape 3+)

spl_autoload_register(function ($nomClasse) {
    // Liste des chemins où chercher la classe
    $cheminsCandidats = [
        CLASSES_PATH . '/util/' . $nomClasse . '.php',
        CLASSES_PATH . '/' . $nomClasse . '.php',
    ];

    foreach ($cheminsCandidats as $chemin) {
        if (is_file($chemin)) {
            require_once $chemin;
            return;
        }
    }
    // Si on arrive ici, la classe n'a pas été trouvée.
    // PHP lèvera une Error "Class 'XXX' not found" qui sera utile au debug.
});


// -----------------------------------------------------------------
// 4. Chargement des fonctions globales (helpers)
// -----------------------------------------------------------------
require_once __DIR__ . '/helpers.php';


// -----------------------------------------------------------------
// 5. Headers HTTP de sécurité (étape 8)
// -----------------------------------------------------------------
// Envoyés en début de réponse pour chaque page web.
// Protection contre XSS, clickjacking, MIME-sniffing, etc.
// Voir classes/util/Securite.php pour le détail de chaque header.
Securite::envoyerHeadersSecurite();
