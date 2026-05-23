<?php
/**
 * public/404.php
 * ---------------------------------------------------------------------
 * Page d'erreur 404 personnalisée.
 *
 * Configurée via .htaccess pour intercepter les URL inexistantes.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

http_response_code(404);
$titre = 'Page introuvable';

require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-md mx-auto text-center py-16">

    <div class="text-9xl font-black text-primary-600 mb-4 leading-none">404</div>

    <h1 class="text-2xl font-bold text-gray-900 mb-3">Page introuvable</h1>

    <p class="text-gray-600 mb-8">
        La page que vous cherchez n'existe pas ou a été déplacée.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?= url('/index.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            ← Retour à l'accueil
        </a>
        <a href="<?= url('/catalogue.php') ?>"
           class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
            Voir le catalogue
        </a>
    </div>

    <div class="mt-12 text-sm text-gray-500">
        <p>Vous pensez qu'il s'agit d'une erreur ?
        <a href="mailto:contact@pdvweb.local" class="text-primary-600 hover:underline">Contactez-nous</a>.</p>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
