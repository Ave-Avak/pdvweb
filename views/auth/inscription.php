<?php
/**
 * views/auth/inscription.php
 * ---------------------------------------------------------------------
 * Vue : formulaire d'inscription.
 *
 * Variables reçues du contrôleur :
 *   - $titre    string
 *   - $donnees  array (nom, prenom, date_naissance, email, login)
 *   - $erreurs  array (associatif : champ => message d'erreur)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-2xl mx-auto">

    <div class="mb-6 text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Créer un compte</h1>
        <p class="text-gray-600">Rejoignez la communauté PDVWeb en quelques clics</p>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= url('/inscription.php') ?>"
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">

        <?= Csrf::champ() ?>

        <!-- Nom + Prénom (sur la même ligne en desktop) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                    Prénom <span class="text-red-500">*</span>
                </label>
                <input type="text" id="prenom" name="prenom" required maxlength="60"
                       value="<?= h($donnees['prenom']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['prenom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['prenom'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['prenom']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom" required maxlength="60"
                       value="<?= h($donnees['nom']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['nom'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Date de naissance -->
        <div>
            <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">
                Date de naissance <span class="text-red-500">*</span>
            </label>
            <input type="date" id="date_naissance" name="date_naissance" required
                   value="<?= h($donnees['date_naissance']) ?>"
                   max="<?= date('Y-m-d') ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['date_naissance']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <?php if (isset($erreurs['date_naissance'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['date_naissance']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" id="email" name="email" required maxlength="150"
                   value="<?= h($donnees['email']) ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['email']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <?php if (isset($erreurs['email'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['email']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Login -->
        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                Login <span class="text-red-500">*</span>
            </label>
            <input type="text" id="login" name="login" required maxlength="50"
                   value="<?= h($donnees['login']) ?>"
                   pattern="[a-zA-Z0-9_-]{3,50}"
                   class="w-full px-4 py-2 border <?= isset($erreurs['login']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">3-50 caractères : lettres, chiffres, _ ou -</p>
            <?php if (isset($erreurs['login'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['login']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Mot de passe + confirmation -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="mot_passe" class="block text-sm font-medium text-gray-700 mb-1">
                    Mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" id="mot_passe" name="mot_passe" required minlength="8"
                       autocomplete="new-password"
                       class="w-full px-4 py-2 border <?= isset($erreurs['mot_passe']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">8 caractères minimum</p>
                <?php if (isset($erreurs['mot_passe'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['mot_passe']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="mot_passe_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirmer le mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" id="mot_passe_confirm" name="mot_passe_confirm" required minlength="8"
                       autocomplete="new-password"
                       class="w-full px-4 py-2 border <?= isset($erreurs['mot_passe_confirm']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['mot_passe_confirm'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['mot_passe_confirm']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avatar (optionnel) -->
        <div>
            <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">
                Photo de profil <span class="text-gray-400">(optionnel)</span>
            </label>
            <div class="flex items-center gap-4">
                <img id="avatar_preview" src="<?= asset('assets/img/avatar-defaut.svg') ?>"
                     alt="Aperçu"
                     class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                <div class="flex-1">
                    <input type="file" id="avatar" name="avatar"
                           accept=".gif,.jpg,.jpeg,image/gif,image/jpeg"
                           class="block w-full text-sm text-gray-700
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-primary-50 file:text-primary-700
                                  hover:file:bg-primary-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">.gif ou .jpeg, max 2 Mo</p>
                </div>
            </div>
            <?php if (isset($erreurs['avatar'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['avatar']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Boutons -->
        <div class="pt-4 border-t border-gray-100">
            <button type="submit"
                    class="w-full px-4 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Créer mon compte
            </button>
            <p class="text-center text-sm text-gray-600 mt-4">
                Déjà membre ?
                <a href="<?= url('/login.php') ?>" class="text-primary-600 hover:underline font-medium">
                    Connectez-vous
                </a>
            </p>
        </div>

    </form>
</div>

<!-- Petit JS : aperçu de l'avatar avant upload -->
<script>
document.getElementById('avatar')?.addEventListener('change', function (e) {
    const fichier = e.target.files[0];
    if (fichier) {
        const lecteur = new FileReader();
        lecteur.onload = function (event) {
            document.getElementById('avatar_preview').src = event.target.result;
        };
        lecteur.readAsDataURL(fichier);
    }
});
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
