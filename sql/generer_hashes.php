<?php
/**
 * sql/generer_hashes.php
 * ---------------------------------------------------------------------
 * Met à jour les mots de passe des comptes de test avec de vrais hashes
 * bcrypt. À exécuter UNE SEULE FOIS après 01_schema.sql et 02_seed.sql.
 *
 * Usage en ligne de commande (recommandé) :
 *   php sql/generer_hashes.php
 *
 * Ou via le navigateur (à supprimer après) :
 *   http://localhost/pdvweb/sql/generer_hashes.php
 *
 * Comptes mis à jour :
 *   - admin / admin2026
 *   - jdupont, smartin, mlambert / test1234
 * ---------------------------------------------------------------------
 */

require __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage() . PHP_EOL);
}

// Comptes à mettre à jour : login => mot de passe en clair
$comptes = [
    'admin'    => 'admin2026',
    'jdupont'  => 'test1234',
    'smartin'  => 'test1234',
    'mlambert' => 'test1234',
];

$req = $pdo->prepare("UPDATE membre SET mot_passe = :hash WHERE login = :login");

$saut = PHP_SAPI === 'cli' ? PHP_EOL : '<br/>';

echo "Mise à jour des hashes bcrypt..." . $saut . $saut;

foreach ($comptes as $login => $motPasseEnClair) {
    $hash = password_hash($motPasseEnClair, PASSWORD_DEFAULT);
    $req->execute([':hash' => $hash, ':login' => $login]);
    echo "  ✓ '$login' : mot de passe '$motPasseEnClair'" . $saut;
}

echo $saut . "Terminé. Vous pouvez maintenant vous connecter avec ces comptes." . $saut;
echo "Pensez à supprimer ce fichier sur un serveur en production." . $saut;
