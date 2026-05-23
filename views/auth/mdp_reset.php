<?php
/**
 * views/auth/mdp_reset.php
 * ---------------------------------------------------------------------
 * Vue : formulaire de saisie du nouveau mot de passe.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-md mx-auto">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8">

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Nouveau mot de passe</h1>
        <p class="text-gray-600 text-sm mb-6">
            Choisissez un nouveau mot de passe pour le compte <strong><?= h($tokenRow['login']) ?></strong>.
        </p>

        <?php if (!empty($erreurs['general'])): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-3 mb-4 rounded-r-md text-sm"><?= h($erreurs['general']) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <?= Csrf::champ() ?>
            <input type="hidden" name="token" value="<?= h($token) ?>">

            <div>
                <label for="mot_passe" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                <input type="password" id="mot_passe" name="mot_passe" required minlength="8"
                       class="w-full px-4 py-2 border <?= isset($erreurs['mot_passe']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">8 caractères minimum.</p>
                <?php if (isset($erreurs['mot_passe'])): ?><p class="text-xs text-red-600 mt-1"><?= h($erreurs['mot_passe']) ?></p><?php endif; ?>
            </div>

            <div>
                <label for="confirmation_mdp" class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
                <input type="password" id="confirmation_mdp" name="confirmation_mdp" required minlength="8"
                       class="w-full px-4 py-2 border <?= isset($erreurs['confirmation_mdp']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <?php if (isset($erreurs['confirmation_mdp'])): ?><p class="text-xs text-red-600 mt-1"><?= h($erreurs['confirmation_mdp']) ?></p><?php endif; ?>
            </div>

            <button type="submit"
                    class="w-full px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Réinitialiser
            </button>

        </form>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
