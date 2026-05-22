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

    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Connexion</h1>
        <p class="text-gray-600">Accédez à votre espace membre</p>
    </div>

    <form method="post" action="<?= url('/login.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">

        <?= Csrf::champ() ?>

        <!-- Login -->
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                Login
            </label>
            <input type="text" id="login" name="login" required autofocus
                   autocomplete="username"
                   value="<?= h($login ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <!-- Mot de passe -->
        <div>
            <label for="mot_passe" class="block text-sm font-medium text-gray-700 mb-1">
                Mot de passe
            </label>
            <input type="password" id="mot_passe" name="mot_passe" required
                   autocomplete="current-password"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <button type="submit"
                class="w-full px-4 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            Se connecter
        </button>

        <p class="text-center text-sm text-gray-600">
            Pas encore membre ?
            <a href="<?= url('/inscription.php') ?>" class="text-primary-600 hover:underline font-medium">
                Inscrivez-vous
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
