<?php
/**
 * views/admin/billets.php
 * ---------------------------------------------------------------------
 * Vue admin : tableau de gestion des billets.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <!-- Bandeau admin -->
    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — vous gérez actuellement les billets de blog.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Gestion des billets</h1>
            <p class="text-gray-600"><?= count($billets) ?> billet<?= count($billets) > 1 ? 's' : '' ?> au total</p>
        </div>
        <a href="<?= url('/admin/billet_form.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau billet
        </a>
    </div>

    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Titre</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Auteur</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Date</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">💬</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">❤️</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($billets)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Aucun billet pour l'instant. <a href="<?= url('/admin/billet_form.php') ?>" class="text-primary-600 hover:underline">Créez le premier !</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($billets as $b): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="<?= url('/billet.php?id=' . (int)$b['id_billet']) ?>"
                                       class="font-medium text-gray-900 hover:text-primary-600">
                                        <?= h($b['titre']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <?= h($b['auteur_prenom']) ?> <?= h($b['auteur_nom']) ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    <?= h(format_date_courte($b['date_billet'])) ?>
                                </td>
                                <td class="px-4 py-3 text-center"><?= (int)$b['nb_commentaires'] ?></td>
                                <td class="px-4 py-3 text-center"><?= (int)$b['nb_likes'] ?></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= url('/admin/billet_form.php?id=' . (int)$b['id_billet']) ?>"
                                       class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">
                                        Éditer
                                    </a>
                                    <form method="post" action="<?= url('/admin/billet_supprimer.php') ?>" class="inline">
                                        <?= Csrf::champ() ?>
                                        <input type="hidden" name="id_billet" value="<?= (int)$b['id_billet'] ?>">
                                        <button type="submit"
                                                data-confirm="Supprimer définitivement ce billet et tous ses commentaires ?"
                                                class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100">
                                            Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
