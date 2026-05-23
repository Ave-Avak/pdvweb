<?php
/**
 * views/auth/historique.php
 * ---------------------------------------------------------------------
 * Vue : historique des commandes du membre connecté.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes commandes</h1>
        <p class="text-gray-600">
            <?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?> au total
        </p>
    </div>

    <?php if (empty($commandes)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucun achat pour le moment</h2>
            <p class="text-gray-600 mb-6">Découvrez notre catalogue et passez votre première commande.</p>
            <a href="<?= url('/catalogue.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Découvrir le catalogue
                <span aria-hidden="true">→</span>
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($commandes as $c): ?>
                <a href="<?= url('/facture.php?id=' . (int)$c['id_facture']) ?>"
                   class="block bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 flex-wrap">
                                <p class="font-semibold text-gray-900"><?= h($c['reference']) ?></p>
                                <?php
                                $couleur = $c['statut_couleur'] ?: '#6b7280';
                                ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      style="background-color: <?= h($couleur) ?>20; color: <?= h($couleur) ?>;">
                                    <?= h($c['statut_nom']) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <?= h(format_date($c['date_achat'])) ?>
                                · <?= (int)$c['nb_articles'] ?> ligne<?= $c['nb_articles'] > 1 ? 's' : '' ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg"><?= format_prix($c['prix_total']) ?></p>
                            <p class="text-xs text-primary-600 hover:underline">Voir le détail →</p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
