<?php
/**
 * views/auth/login.php
 * ---------------------------------------------------------------------
 * Vue : formulaire de connexion.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-md mx-auto">

    <div class="mb-8 text-center">
        <div class="inline-flex w-16 h-16 bg-primary-100 rounded-full items-center justify-center mb-4">
            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Bon retour !</h1>
        <p class="text-gray-600">Connectez-vous à votre espace membre</p>
    </div>

    <form method="post" action="<?= url('/login.php') ?>"
          class="bg-white rounded-xl shadow-soft border border-gray-100 p-6 md:p-8 space-y-5">

        <?= Csrf::champ() ?>

        <!-- Login -->
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1.5">
                Login
            </label>
            <input type="text" id="login" name="login" required autofocus
                   autocomplete="username"
                   value="<?= h($login ?? '') ?>"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <!-- Mot de passe -->
        <div>
            <div class="flex justify-between mb-1.5">
                <label for="mot_passe" class="block text-sm font-medium text-gray-700">
                    Mot de passe
                </label>
                <a href="<?= url('/mdp_oublie.php') ?>" class="text-xs text-primary-600 hover:underline">
                    Oublié ?
                </a>
            </div>
            <input type="password" id="mot_passe" name="mot_passe" required
                   autocomplete="current-password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <button type="submit"
                class="w-full px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm flex items-center justify-center gap-2">
            <span>Se connecter</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-sm text-gray-600 pt-2">
            Pas encore membre ?
            <a href="<?= url('/inscription.php') ?>" class="text-primary-600 hover:underline font-semibold">
                Inscrivez-vous gratuitement
            </a>
        </p>
    </form>

    <!-- Comptes de test (utile pour la démo, à retirer en production) -->
    <?php if (DEV_MODE): ?>
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
            <p class="font-semibold text-blue-900 mb-2">Comptes de test (mode développement)</p>
            <ul class="text-blue-800 space-y-1">
                <li><strong>admin</strong> / admin2026 (administrateur)</li>
                <li><strong>jdupont</strong>, <strong>smartin</strong>, <strong>mlambert</strong> / test1234 (membres)</li>
            </ul>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
