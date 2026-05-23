<?php
/**
 * config/config.php
 * ---------------------------------------------------------------------
 * Configuration centrale de l'application PDVWeb.
 * Ce fichier ne contient QUE des constantes — aucune logique.
 *
 * À adapter selon votre environnement local (XAMPP / WAMP / MAMP).
 * ---------------------------------------------------------------------
 */

// =====================================================================
// PARAMÈTRES DE LA BASE DE DONNÉES
// =====================================================================
define('DB_HOST',    'localhost');
define('DB_NAME',    'pdvweb');
define('DB_USER',    'root');        // Sur WAMP/XAMPP : root par défaut
define('DB_PASS',    '');            // Sur WAMP/XAMPP : vide par défaut ; MAMP : 'root'
define('DB_CHARSET', 'utf8mb4');


// =====================================================================
// PARAMÈTRES DE L'APPLICATION
// =====================================================================

// URL racine du site (sans slash final)
// Permet de générer des liens absolus dans les vues
define('SITE_URL', 'http://localhost/pdvweb/public');

// Nom du site (peut être surchargé par la table `parametre`)
define('SITE_NAME', 'PDVWeb');

// Fuseau horaire pour les dates affichées
date_default_timezone_set('Europe/Brussels');


// =====================================================================
// CHEMINS PHYSIQUES SUR LE SERVEUR
// =====================================================================
// __DIR__ = dossier du fichier courant, donc /chemin/vers/pdvweb/config
// dirname(__DIR__) = /chemin/vers/pdvweb  (racine du projet)

define('ROOT_PATH',     dirname(__DIR__));
define('CLASSES_PATH',  ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('VIEWS_PATH',    ROOT_PATH . '/views');
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('UPLOADS_PATH',  PUBLIC_PATH . '/uploads');


// =====================================================================
// PARAMÈTRES DE SÉCURITÉ
// =====================================================================

// Nombre maximum de tentatives de connexion ratées avant blocage
define('MAX_TENTATIVES_LOGIN', 5);

// Durée du blocage (en minutes) après le seuil de tentatives ratées
define('DUREE_BLOCAGE_MINUTES', 15);

// Durée de validité d'un token (reset password, vérif email) en heures
define('TOKEN_DUREE_HEURES', 24);


// =====================================================================
// PARAMÈTRES D'UPLOAD
// =====================================================================

// Taille max d'un avatar uploadé (2 Mo)
define('UPLOAD_TAILLE_MAX', 2 * 1024 * 1024);

// Extensions autorisées pour les avatars (cf. cahier des charges : .gif ou .jpeg)
define('UPLOAD_EXTENSIONS', ['gif', 'jpg', 'jpeg']);


// =====================================================================
// MODE DE DÉVELOPPEMENT
// =====================================================================
// En dev : on affiche les erreurs PHP pour faciliter le débogage.
// En production : à passer à false (les erreurs vont dans le log).

define('DEV_MODE', true);

if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
