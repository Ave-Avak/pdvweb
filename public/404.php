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

    <!-- Illustration animée -->
    <div class="relative inline-block mb-6">
        <div class="text-9xl font-black text-primary-600 leading-none animate-pulse-soft">404</div>
        <div class="absolute -top-4 -right-4 text-3xl">🔍</div>
    </div>

    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Page introuvable</h1>

    <p class="text-gray-600 mb-8">
        La page que vous cherchez n'existe pas, a été déplacée, ou n'est plus disponible.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="<?= url('/index.php') ?>"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à l'accueil
        </a>
        <a href="<?= url('/catalogue.php') ?>"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Voir le catalogue
        </a>
    </div>

    <div class="mt-12 text-sm text-gray-500">
        <p>Vous pensez qu'il s'agit d'une erreur ?
        <a href="mailto:contact@pdvweb.local" class="text-primary-600 hover:underline">Contactez-nous</a>.</p>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
