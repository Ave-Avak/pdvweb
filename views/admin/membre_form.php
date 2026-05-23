<?php
/**
 * views/admin/membre_form.php
 * ---------------------------------------------------------------------
 * Vue admin : modifier les données d'un membre.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — modification d'un membre.
    </div>

    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('/admin/membre_detail.php?id=' . $idMembre) ?>" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex items-center gap-3">
            <img src="<?= h(asset_avatar($membre['avatar'])) ?>"
                 alt="<?= h($membre['login']) ?>"
                 class="w-12 h-12 rounded-full object-cover border border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Modifier le membre</h1>
                <p class="text-gray-600 text-sm">
                    @<?= h($membre['login']) ?>
                    <?php if ($membre['statut'] === 'admin'): ?>
                        <span class="ml-2 px-2 py-0.5 bg-warning-100 text-warning-700 text-xs rounded font-semibold">ADMIN</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <!-- Avertissement RGPD -->
    <div class="bg-warning-50 border border-warning-200 rounded-lg p-3 mb-6 text-sm text-warning-800">
        ⚠️ <strong>Attention :</strong> modifier les données d'un membre doit être fait avec parcimonie.
        Pour les corrections de fautes de frappe, demande explicite du membre, ou correction d'email erroné.
        Cette action est tracée dans le journal d'audit.
    </div>

    <form method="post"
          action="<?= url('/admin/membre_form.php?id=' . $idMembre) ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <!-- Identité -->
        <fieldset>
            <legend class="text-sm font-semibold text-gray-700 mb-3">Identité</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                        Prénom <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="prenom" name="prenom" required maxlength="60"
                           value="<?= h($donnees['prenom']) ?>"
                           class="w-full px-4 py-2 border <?= isset($erreurs['prenom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
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
                           class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <?php if (isset($erreurs['nom'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">
                    Date de naissance <span class="text-red-500">*</span>
                </label>
                <input type="date" id="date_naissance" name="date_naissance" required
                       value="<?= h($donnees['date_naissance']) ?>"
                       class="w-full md:w-1/2 px-4 py-2 border <?= isset($erreurs['date_naissance']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <?php if (isset($erreurs['date_naissance'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['date_naissance']) ?></p>
                <?php endif; ?>
            </div>
        </fieldset>

        <!-- Identifiants -->
        <fieldset class="pt-4 border-t border-gray-100">
            <legend class="text-sm font-semibold text-gray-700 mb-3">Identifiants</legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">
                        Login <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="login" name="login" required maxlength="50"
                           value="<?= h($donnees['login']) ?>"
                           class="w-full px-4 py-2 border <?= isset($erreurs['login']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition font-mono">
                    <p class="text-xs text-gray-500 mt-1">3 à 50 caractères : lettres, chiffres, - ou _.</p>
                    <?php if (isset($erreurs['login'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['login']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required maxlength="150"
                           value="<?= h($donnees['email']) ?>"
                           class="w-full px-4 py-2 border <?= isset($erreurs['email']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <?php if (isset($erreurs['email'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="email_verifie" value="1"
                           <?= $donnees['email_verifie'] ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 border-gray-300 rounded">
                    <span class="text-sm font-medium text-gray-700">Email vérifié</span>
                    <?php if ($donnees['email_verifie']): ?>
                        <span class="px-2 py-0.5 bg-success-100 text-success-700 text-xs rounded font-semibold">✓ Vérifié</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-warning-100 text-warning-700 text-xs rounded font-semibold">⚠ Non vérifié</span>
                    <?php endif; ?>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-6">
                    Cochez pour marquer l'email comme vérifié manuellement (ex: support utilisateur).
                </p>
            </div>
        </fieldset>

        <!-- Boutons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100">
            <a href="<?= url('/admin/membre_detail.php?id=' . $idMembre) ?>"
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Enregistrer les modifications
            </button>
        </div>
    </form>

    <!-- Note sur les autres actions -->
    <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-900">
        💡 <strong>Pour d'autres actions :</strong>
        <ul class="mt-2 space-y-1 ml-5 list-disc">
            <li>Mot de passe : utiliser le bouton « Réinitialiser le mot de passe » sur la fiche membre</li>
            <li>Statut admin/membre : utiliser « Promouvoir / Dégrader » sur la fiche membre</li>
            <li>Bloquer/débloquer : depuis la fiche membre</li>
            <li>Anonymisation RGPD : depuis la fiche membre (action irréversible)</li>
        </ul>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
