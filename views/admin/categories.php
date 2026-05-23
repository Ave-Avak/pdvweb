<?php
/**
 * views/admin/categories.php
 * ---------------------------------------------------------------------
 * Vue admin : gestion des catégories.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — catégories.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Catégories</h1>
            <p class="text-gray-600"><?= count($categories) ?> catégorie<?= count($categories) > 1 ? 's' : '' ?></p>
        </div>
        <a href="<?= url('/admin/categorie_form.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle catégorie
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Ordre</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Code</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Nom</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Description</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-700">Articles</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($categories as $c): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500"><?= (int)$c['ordre'] ?></td>
                        <td class="px-4 py-3 font-mono text-xs"><?= h($c['code']) ?></td>
                        <td class="px-4 py-3 font-medium text-gray-900"><?= h($c['nom']) ?></td>
                        <td class="px-4 py-3 text-gray-600 text-xs"><?= h($c['description'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="<?= (int)$c['nb_articles'] === 0 ? 'text-gray-400' : 'text-gray-700' ?>">
                                <?= (int)$c['nb_articles'] ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="<?= url('/admin/categorie_form.php?id=' . (int)$c['id_categorie']) ?>"
                               class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">Éditer</a>
                            <?php if ((int)$c['nb_articles'] === 0): ?>
                                <form method="post" action="<?= url('/admin/categorie_supprimer.php') ?>" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="id_categorie" value="<?= (int)$c['id_categorie'] ?>">
                                    <button type="submit"
                                            data-confirm="Supprimer cette catégorie ?"
                                            class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 text-xs text-gray-500">
        💡 Une catégorie ne peut être supprimée que si elle ne contient aucun article.
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
