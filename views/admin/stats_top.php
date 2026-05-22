<?php
/**
 * views/admin/stats_top.php
 * ---------------------------------------------------------------------
 * Vue admin : top articles (vendus / vus / notés) + top membres.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — top articles et membres.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tops &amp; Classements</h1>


    <!-- Alerte stock bas -->
    <?php if (!empty($stockBas)): ?>
        <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-md p-4 mb-6">
            <h3 class="font-bold text-amber-900 mb-2">⚠️ Alertes stock bas (<?= count($stockBas) ?>)</h3>
            <ul class="text-sm text-amber-800 space-y-1">
                <?php foreach ($stockBas as $a): ?>
                    <li>
                        <a href="<?= url('/admin/article_form.php?id=' . (int)$a['id_article']) ?>" class="hover:underline">
                            <strong><?= h($a['nom']) ?></strong>
                        </a>
                        — <?= h($a['categorie_nom']) ?> — il reste <strong><?= (int)$a['stock'] ?></strong> exemplaire<?= (int)$a['stock'] > 1 ? 's' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>


    <!-- ========================= TOP ARTICLES ========================= -->
    <h2 class="text-xl font-bold text-gray-900 mb-3">Top articles</h2>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">

        <!-- Plus vendus -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">🏆 Plus vendus</h3>
            <?php if (empty($topVendus)): ?>
                <p class="text-gray-500 text-sm">Aucune vente.</p>
            <?php else: ?>
                <ol class="space-y-2 text-sm">
                    <?php foreach ($topVendus as $idx => $a): ?>
                        <li class="flex items-center gap-3 py-1">
                            <span class="font-bold text-gray-400 w-5"><?= $idx + 1 ?></span>
                            <div class="w-8 h-8 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                <img src="<?= h(asset_article($a['image'])) ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                   class="font-medium text-gray-900 hover:text-primary-600 line-clamp-1"><?= h($a['nom']) ?></a>
                                <p class="text-xs text-gray-500">
                                    <?= (int)$a['qte_vendue'] ?> vendus · <?= format_prix($a['ca_genere']) ?>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>


        <!-- Plus vus -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">👁️ Plus vus (30j)</h3>
            <?php if (empty($topVus)): ?>
                <p class="text-gray-500 text-sm">Aucune vue.</p>
            <?php else: ?>
                <ol class="space-y-2 text-sm">
                    <?php foreach ($topVus as $idx => $a): ?>
                        <li class="flex items-center gap-3 py-1">
                            <span class="font-bold text-gray-400 w-5"><?= $idx + 1 ?></span>
                            <div class="w-8 h-8 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                <img src="<?= h(asset_article($a['image'])) ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                   class="font-medium text-gray-900 hover:text-primary-600 line-clamp-1"><?= h($a['nom']) ?></a>
                                <p class="text-xs text-gray-500"><?= (int)$a['nb_vues'] ?> vues</p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>


        <!-- Mieux notés -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">⭐ Mieux notés</h3>
            <p class="text-xs text-gray-500 mb-3">Minimum 3 avis</p>
            <?php if (empty($topNotes)): ?>
                <p class="text-gray-500 text-sm">Pas assez d'avis pour classer.</p>
            <?php else: ?>
                <ol class="space-y-2 text-sm">
                    <?php foreach ($topNotes as $idx => $a): ?>
                        <li class="flex items-center gap-3 py-1">
                            <span class="font-bold text-gray-400 w-5"><?= $idx + 1 ?></span>
                            <div class="w-8 h-8 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                <img src="<?= h(asset_article($a['image'])) ?>" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                   class="font-medium text-gray-900 hover:text-primary-600 line-clamp-1"><?= h($a['nom']) ?></a>
                                <p class="text-xs text-amber-500">
                                    <?= str_repeat('★', (int)round((float)$a['moyenne'])) ?>
                                    <span class="text-gray-500"><?= number_format((float)$a['moyenne'], 1, ',', '') ?>/5 (<?= (int)$a['nb_notes'] ?>)</span>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </div>


    <!-- ========================= TOP MEMBRES ========================= -->
    <h2 class="text-xl font-bold text-gray-900 mb-3">Top membres</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- Acheteurs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">💰 Plus gros acheteurs</h3>
            <?php if (empty($topAcheteurs)): ?>
                <p class="text-gray-500 text-sm">Aucun achat encore.</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($topAcheteurs as $idx => $m): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 pr-2 font-bold text-gray-400 w-8"><?= $idx + 1 ?></td>
                                <td class="py-2 pr-2">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= h(asset_avatar($m['avatar'])) ?>"
                                             class="w-7 h-7 rounded-full object-cover border border-gray-200">
                                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$m['id_membre']) ?>"
                                           class="font-medium hover:text-primary-600"><?= h(nom_membre($m)) ?></a>
                                    </div>
                                </td>
                                <td class="py-2 px-2 text-xs text-gray-500"><?= (int)$m['nb_commandes'] ?> cmd</td>
                                <td class="py-2 pl-2 text-right font-semibold"><?= format_prix($m['ca_total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>


        <!-- Blog -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">💬 Plus actifs sur le blog</h3>
            <?php if (empty($topBlog)): ?>
                <p class="text-gray-500 text-sm">Aucun commentaire encore.</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($topBlog as $idx => $m): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 pr-2 font-bold text-gray-400 w-8"><?= $idx + 1 ?></td>
                                <td class="py-2 pr-2">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= h(asset_avatar($m['avatar'])) ?>"
                                             class="w-7 h-7 rounded-full object-cover border border-gray-200">
                                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$m['id_membre']) ?>"
                                           class="font-medium hover:text-primary-600"><?= h(nom_membre($m)) ?></a>
                                    </div>
                                </td>
                                <td class="py-2 pl-2 text-right text-gray-700">
                                    <strong><?= (int)$m['nb_commentaires'] ?></strong> com.
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
