<?php
/**
 * views/admin/article_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire création/édition d'article.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'un article.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">
        <?= $modeEdition ? 'Modifier l\'article' : 'Nouvel article' ?>
    </h1>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md text-sm"><?= h($erreurs['general']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $modeEdition ? url('/admin/article_form.php?id=' . $idArticle) : url('/admin/article_form.php') ?>"
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <div>
            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
            <input type="text" id="nom" name="nom" required maxlength="200"
                   value="<?= h($donnees['nom']) ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <?php if (isset($erreurs['nom'])): ?><p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p><?php endif; ?>
        </div>

        <div>
            <label for="id_categorie" class="block text-sm font-medium text-gray-700 mb-1">Catégorie <span class="text-red-500">*</span></label>
            <select id="id_categorie" name="id_categorie" required
                    class="w-full px-4 py-2 border <?= isset($erreurs['id_categorie']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id_categorie'] ?>"
                            <?= (int)$donnees['id_categorie'] === (int)$c['id_categorie'] ? 'selected' : '' ?>>
                        <?= h($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="5" maxlength="2000"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition resize-y"><?= h($donnees['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">Prix (€) <span class="text-red-500">*</span></label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" required
                       value="<?= h($donnees['prix']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['prix']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
            <div>
                <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input type="number" id="stock" name="stock" min="0"
                       value="<?= h($donnees['stock']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
            <div>
                <label for="poids_grammes" class="block text-sm font-medium text-gray-700 mb-1">Poids (g)</label>
                <input type="number" id="poids_grammes" name="poids_grammes" min="0"
                       value="<?= h($donnees['poids_grammes']) ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image</label>
            <?php if (!empty($donnees['image'])): ?>
                <div class="mb-2">
                    <img src="<?= h(asset_article($donnees['image'])) ?>"
                         class="w-24 h-24 object-cover rounded-lg border border-gray-200">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.gif"
                   class="block text-sm">
            <p class="text-xs text-gray-500 mt-1">Formats acceptés : JPG, JPEG, GIF.</p>
            <?php if (isset($erreurs['image'])): ?><p class="text-sm text-red-600 mt-1"><?= h($erreurs['image']) ?></p><?php endif; ?>
        </div>

        <!-- Tags (Phase 3.2) -->
        <?php if (!empty($tousTags)): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tags
                </label>
                <div class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <?php foreach ($tousTags as $tg): ?>
                        <?php $coche = in_array((int)$tg['id_tag'], $donnees['tags'], true); ?>
                        <label class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-gray-300 rounded-full text-xs cursor-pointer hover:bg-primary-50 hover:border-primary-300 transition <?= $coche ? 'bg-primary-100 border-primary-400 text-primary-800' : '' ?>">
                            <input type="checkbox" name="tags[]" value="<?= (int)$tg['id_tag'] ?>"
                                   <?= $coche ? 'checked' : '' ?>
                                   class="w-3 h-3 text-primary-600 rounded">
                            <span>#<?= h($tg['nom']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Marquez cet article avec des tags (ex: Promotion, Nouveauté).
                    <a href="<?= url('/admin/tags.php') ?>" class="text-primary-600 hover:underline">Gérer les tags →</a>
                </p>
            </div>
        <?php else: ?>
            <div class="bg-info-50 border border-info-200 rounded-lg p-3 text-sm text-info-800">
                💡 Aucun tag n'existe encore. <a href="<?= url('/admin/tags.php') ?>" class="font-semibold underline">Créez-en depuis la page Tags →</a>
            </div>
        <?php endif; ?>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="dispo" value="1" <?= (int)$donnees['dispo'] === 1 ? 'checked' : '' ?>>
                <span class="text-sm">Article visible dans le catalogue</span>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-3">
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer' : 'Créer l\'article' ?>
            </button>
            <a href="<?= url('/admin/articles.php') ?>"
               class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
