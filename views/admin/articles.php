<?php
/**
 * views/admin/articles.php
 * ---------------------------------------------------------------------
 * Vue admin : tableau de gestion des articles.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — gestion du catalogue.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Articles</h1>
            <p class="text-gray-600"><?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?></p>
        </div>
        <a href="<?= url('/admin/article_form.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvel article
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Article</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Catégorie</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Prix</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Stock</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">État</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($articles as $a): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                        <img src="<?= h(asset_article($a['image'])) ?>"
                                             alt=""
                                             class="w-full h-full object-cover">
                                    </div>
                                    <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                       class="font-medium text-gray-900 hover:text-primary-600">
                                        <?= h($a['nom']) ?>
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700"><?= h($a['categorie_nom']) ?></td>
                            <td class="px-4 py-3 text-right font-medium"><?= format_prix($a['prix']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="<?= (int)$a['stock'] <= 5 ? 'text-amber-600 font-semibold' : 'text-gray-700' ?>">
                                    <?= (int)$a['stock'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ((int)$a['dispo'] === 1): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold">En ligne</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-semibold">Retiré</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="<?= url('/admin/article_form.php?id=' . (int)$a['id_article']) ?>"
                                   class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">Éditer</a>
                                <?php if ((int)$a['dispo'] === 1): ?>
                                    <form method="post" action="<?= url('/admin/article_supprimer.php') ?>" class="inline">
                                        <?= Csrf::champ() ?>
                                        <input type="hidden" name="id_article" value="<?= (int)$a['id_article'] ?>">
                                        <button type="submit"
                                                data-confirm="Retirer cet article du catalogue ?"
                                                class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100">Retirer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
