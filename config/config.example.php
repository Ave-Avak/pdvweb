<?php
/**
 * config/config.example.php
 * ---------------------------------------------------------------------
 * EXEMPLE de configuration. À copier vers config/config.php et adapter
 * à votre environnement local. Ce fichier-ci est versionné, le vrai
 * config.php est ignoré par Git.
 * ---------------------------------------------------------------------
 */

// Base de données
define('DB_HOST',    'localhost');
define('DB_NAME',    'pdvweb');
define('DB_USER',    'root');
define('DB_PASS',    '');             // à modifier selon votre installation
define('DB_CHARSET', 'utf8mb4');

// URL et nom
define('SITE_URL',  'http://localhost/pdvweb/public');
define('SITE_NAME', 'PDVWeb');
date_default_timezone_set('Europe/Brussels');

// Chemins
define('ROOT_PATH',     dirname(__DIR__));
define('CLASSES_PATH',  ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('VIEWS_PATH',    ROOT_PATH . '/views');
define('PUBLIC_PATH',   ROOT_PATH . '/public');
define('UPLOADS_PATH',  PUBLIC_PATH . '/uploads');

// Sécurité
define('MAX_TENTATIVES_LOGIN', 5);
define('DUREE_BLOCAGE_MINUTES', 15);
define('TOKEN_DUREE_HEURES', 24);

// Upload
define('UPLOAD_TAILLE_MAX', 2 * 1024 * 1024);
define('UPLOAD_EXTENSIONS', ['gif', 'jpg', 'jpeg']);

// Dev/Prod
define('DEV_MODE', true);
if (DEV_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
