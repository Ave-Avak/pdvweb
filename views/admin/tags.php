<?php
/**
 * views/admin/tags.php
 * ---------------------------------------------------------------------
 * Vue admin : liste des tags avec CRUD complet.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — gestion des tags du blog.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Tags du blog</h1>
            <p class="text-gray-600 text-sm">
                <?= count($tags) ?> tag<?= count($tags) > 1 ? 's' : '' ?> au total.
                Les tags permettent de catégoriser les billets.
            </p>
        </div>
        <a href="<?= url('/admin/tag_form.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau tag
        </a>
    </div>

    <?php if (empty($tags)): ?>
        <!-- Empty state -->
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucun tag</h2>
            <p class="text-gray-600 mb-6">Créez votre premier tag pour catégoriser les billets.</p>
            <a href="<?= url('/admin/tag_form.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Créer un tag
            </a>
        </div>
    <?php else: ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Tag</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Code</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-700">Billets associés</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($tags as $t): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900">
                                    <span class="inline-block px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full text-xs font-semibold">
                                        #<?= h($t['nom']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                    <?= h($t['code']) ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ((int)$t['nb_billets'] > 0): ?>
                                        <span class="font-semibold text-gray-900"><?= (int)$t['nb_billets'] ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Modifier -->
                                        <a href="<?= url('/admin/tag_form.php?id=' . (int)$t['id_tag']) ?>"
                                           title="Modifier"
                                           class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100 transition">
                                            ✏️
                                        </a>

                                        <!-- Supprimer -->
                                        <form method="post"
                                              action="<?= url('/admin/tag_supprimer.php') ?>"
                                              class="inline">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="id_tag" value="<?= (int)$t['id_tag'] ?>">
                                            <button type="submit"
                                                    title="Supprimer"
                                                    data-confirm="Supprimer définitivement le tag « <?= h($t['nom']) ?> » ?<?= $t['nb_billets'] > 0 ? '\nIl est associé à ' . (int)$t['nb_billets'] . ' billet(s).' : '' ?>"
                                                    class="text-xs px-2 py-1 bg-danger-50 text-danger-700 rounded hover:bg-danger-100 transition">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
