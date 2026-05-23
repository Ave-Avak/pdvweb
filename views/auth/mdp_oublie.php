<?php
/**
 * views/auth/mdp_oublie.php
 * ---------------------------------------------------------------------
 * Vue : formulaire de demande de réinitialisation.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-md mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Mot de passe oublié ?</h1>
        <p class="text-gray-600 text-sm mb-6">
            Saisissez votre adresse email. Si elle est associée à un compte, vous recevrez un lien
            de réinitialisation valable 1 heure.
        </p>

        <form method="post" class="space-y-4">
            <?= Csrf::champ() ?>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                <input type="email" id="email" name="email" required maxlength="150"
                       value="<?= h($email) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>

            <button type="submit"
                    class="w-full px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Envoyer le lien
            </button>

            <p class="text-center text-sm text-gray-500">
                <a href="<?= url('/login.php') ?>" class="text-primary-600 hover:underline">← Retour à la connexion</a>
            </p>
        </form>

        <?php if ($lienReset && DEV_MODE): ?>
            <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-xs font-bold text-amber-900 mb-2 uppercase tracking-wider">
                    🛠 Mode développement
                </p>
                <p class="text-xs text-amber-800 mb-2">
                    En production, ce lien serait envoyé par email. En dev, il est affiché ici pour faciliter les tests :
                </p>
                <a href="<?= h($lienReset) ?>"
                   class="text-xs break-all text-primary-600 hover:underline font-mono bg-white p-2 rounded block border border-amber-200">
                    <?= h($lienReset) ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
