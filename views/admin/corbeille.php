<?php
/**
 * views/admin/corbeille.php
 * ---------------------------------------------------------------------
 * Vue : corbeille des contenus supprimés (billets et commentaires).
 *
 * Variables :
 *   - $billetsSupprimes
 *   - $commentairesSupprimes
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <!-- Bandeau admin -->
    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — corbeille des éléments supprimés (restauration possible).
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Corbeille</h1>
        <p class="text-gray-600">
            Les billets et commentaires supprimés sont conservés ici pour audit et restauration éventuelle.
        </p>
    </div>


    <!-- ============================================
         BILLETS SUPPRIMÉS
    ============================================= -->
    <section class="mb-10">
        <h2 class="text-xl font-bold text-gray-900 mb-3">
            Billets supprimés
            <span class="text-sm font-normal text-gray-500">(<?= count($billetsSupprimes) ?>)</span>
        </h2>

        <?php if (empty($billetsSupprimes)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-500 text-sm">
                Aucun billet dans la corbeille.
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Titre</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Auteur</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Supprimé par</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Date suppression</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($billetsSupprimes as $b): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900"><?= h($b['titre']) ?></td>
                                <td class="px-4 py-3 text-gray-700">
                                    <?= h($b['auteur_prenom']) ?> <?= h($b['auteur_nom']) ?>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <?= $b['supp_prenom'] ? h($b['supp_prenom']) . ' ' . h($b['supp_nom']) : '—' ?>
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    <?= h(format_date($b['date_suppression'])) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="post" class="inline">
                                        <?= Csrf::champ() ?>
                                        <input type="hidden" name="action" value="restaurer_billet">
                                        <input type="hidden" name="id_billet" value="<?= (int)$b['id_billet'] ?>">
                                        <button type="submit"
                                                class="text-xs px-3 py-1.5 bg-green-50 text-green-700 rounded hover:bg-green-100 font-semibold">
                                            Restaurer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>


    <!-- ============================================
         COMMENTAIRES SUPPRIMÉS
    ============================================= -->
    <section>
        <h2 class="text-xl font-bold text-gray-900 mb-3">
            Commentaires supprimés
            <span class="text-sm font-normal text-gray-500">(<?= count($commentairesSupprimes) ?>)</span>
        </h2>

        <?php if (empty($commentairesSupprimes)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-500 text-sm">
                Aucun commentaire dans la corbeille.
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($commentairesSupprimes as $c): ?>
                    <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center gap-3 mb-2 text-sm flex-wrap">
                            <span class="font-medium text-gray-900">
                                <?= h($c['prenom']) ?> <?= h($c['nom']) ?>
                            </span>
                            <span class="text-gray-400">sur</span>
                            <a href="<?= url('/billet.php?id=' . (int)$c['id_billet']) ?>"
                               class="text-primary-600 hover:underline italic">
                                <?= h($c['titre_billet']) ?>
                            </a>
                            <span class="text-gray-400 ml-auto text-xs">
                                Supprimé par
                                <?= $c['supp_prenom'] ? h($c['supp_prenom']) . ' ' . h($c['supp_nom']) : '—' ?>
                                le <?= h(format_date($c['date_suppression'])) ?>
                            </span>
                        </div>
                        <div class="text-sm text-gray-700 italic bg-gray-50 p-3 rounded mb-3">
                            <?= nl2br(h(tronquer($c['corps'], 300))) ?>
                        </div>
                        <form method="post" class="inline">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="action" value="restaurer_commentaire">
                            <input type="hidden" name="id_commentaire" value="<?= (int)$c['id_commentaire'] ?>">
                            <button type="submit"
                                    class="text-xs px-3 py-1.5 bg-green-50 text-green-700 rounded hover:bg-green-100 font-semibold">
                                Restaurer
                            </button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
