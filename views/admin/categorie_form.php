<?php
/**
 * views/admin/categorie_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire création/édition catégorie.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-2xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'une catégorie.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">
        <?= $modeEdition ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?>
    </h1>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md text-sm"><?= h($erreurs['general']) ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
            <input type="text" id="code" name="code" required maxlength="40"
                   value="<?= h($donnees['code']) ?>"
                   placeholder="ex: informatique, hifi, livres"
                   class="w-full px-4 py-2 border <?= isset($erreurs['code']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition font-mono">
            <p class="text-xs text-gray-500 mt-1">Slug technique. Lettres minuscules, chiffres, tirets et underscores uniquement.</p>
            <?php if (isset($erreurs['code'])): ?><p class="text-xs text-red-600 mt-1"><?= h($erreurs['code']) ?></p><?php endif; ?>
        </div>

        <div>
            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
            <input type="text" id="nom" name="nom" required maxlength="80"
                   value="<?= h($donnees['nom']) ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <?php if (isset($erreurs['nom'])): ?><p class="text-xs text-red-600 mt-1"><?= h($erreurs['nom']) ?></p><?php endif; ?>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="3" maxlength="500"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition resize-y"><?= h($donnees['description']) ?></textarea>
        </div>

        <div>
            <label for="ordre" class="block text-sm font-medium text-gray-700 mb-1">Ordre d'affichage</label>
            <input type="number" id="ordre" name="ordre" min="0" max="999"
                   value="<?= (int)$donnees['ordre'] ?>"
                   class="w-32 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">Plus petit = affiché en premier.</p>
        </div>

        <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-3">
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer' : 'Créer la catégorie' ?>
            </button>
            <a href="<?= url('/admin/categories.php') ?>"
               class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
