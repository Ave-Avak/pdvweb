<?php
/**
 * public/admin/securite.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Page Sécurité & Maintenance.
 *
 * Affiche l'état de la sécurité et permet la purge des données anciennes.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Traitement POST : purge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::verifierRequete()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'purger') {
        $resultats = Securite::purgerTokensExpires();

        AuditLog::enregistrer(
            'maintenance.purge',
            Auth::id(),
            null, null,
            $resultats
        );

        $total = array_sum($resultats);
        Flash::succes("Purge terminée : $total élément(s) supprimé(s).");
        header('Location: ' . url('/admin/securite.php'));
        exit;
    }
}

// Compteurs à purger
$compteurs = Securite::compterPurgeables();
$totalPurgeable = array_sum($compteurs);

// Vérifications de l'état de sécurité
$verifications = [];

// 1. Mode développement actif ?
$verifications[] = [
    'titre'   => 'Mode développement (DEV_MODE)',
    'ok'      => !DEV_MODE,
    'message' => DEV_MODE
        ? '⚠️ DEV_MODE est activé → erreurs PHP affichées à l\'écran. À désactiver en production (config.php).'
        : '✓ DEV_MODE désactivé : les erreurs ne fuitent pas.',
];

// 2. HTTPS actif ?
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$verifications[] = [
    'titre'   => 'HTTPS',
    'ok'      => $https,
    'message' => $https
        ? '✓ Connexion HTTPS active.'
        : 'ℹ️ HTTP simple (acceptable en développement local). En production, HTTPS est obligatoire.',
];

// 3. Cookies de session sécurisés ?
$sessionCfg = session_get_cookie_params();
$verifications[] = [
    'titre'   => 'Session HTTPOnly',
    'ok'      => !empty($sessionCfg['httponly']),
    'message' => !empty($sessionCfg['httponly'])
        ? '✓ Cookie de session protégé contre l\'accès JavaScript (anti-XSS).'
        : '⚠️ Cookie de session accessible en JavaScript.',
];

$verifications[] = [
    'titre'   => 'Session SameSite',
    'ok'      => !empty($sessionCfg['samesite']),
    'message' => !empty($sessionCfg['samesite'])
        ? '✓ Cookie de session avec SameSite=' . h($sessionCfg['samesite']) . ' (anti-CSRF).'
        : '⚠️ Cookie de session sans politique SameSite.',
];

// 4. Comptes admin
$nbAdmins = (int)Db::pdo()->query(
    "SELECT COUNT(*) FROM membre WHERE statut = 'admin' AND indesirable = 0 AND date_anonymisation IS NULL"
)->fetchColumn();
$verifications[] = [
    'titre'   => 'Nombre d\'administrateurs',
    'ok'      => $nbAdmins >= 1,
    'message' => $nbAdmins === 0
        ? '⚠️ Aucun administrateur actif !'
        : ($nbAdmins > 5
            ? "ℹ️ $nbAdmins administrateurs (beaucoup, principe du moindre privilège : limiter ?)"
            : "✓ $nbAdmins administrateur(s) actif(s).")
];

// 5. Membres bloqués
$nbBloques = (int)Db::pdo()->query(
    "SELECT COUNT(*) FROM membre WHERE indesirable = 1 AND date_anonymisation IS NULL"
)->fetchColumn();

// 6. Tentatives de connexion récentes (24h)
$nbTentatives24h = (int)Db::pdo()->query(
    "SELECT COUNT(*) FROM tentative_connexion
     WHERE succes = 0 AND date_tent >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
)->fetchColumn();

$titre = 'Administration — Sécurité &amp; Maintenance';
require_once VIEWS_PATH . '/admin/securite.php';
