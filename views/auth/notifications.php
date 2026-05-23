<?php
/**
 * views/auth/notifications.php
 * ---------------------------------------------------------------------
 * Vue : liste des notifications du membre.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$nbNonLues = 0;
foreach ($notifications as $n) {
    if ((int)$n['lue'] === 0) $nbNonLues++;
}
?>

<div class="max-w-3xl mx-auto">

    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Mes notifications</h1>
            <p class="text-gray-600">
                <?= count($notifications) ?> notification<?= count($notifications) > 1 ? 's' : '' ?>
                <?php if ($nbNonLues > 0): ?>
                    · <span class="text-primary-600 font-semibold"><?= $nbNonLues ?> non lue<?= $nbNonLues > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($nbNonLues > 0): ?>
            <form method="post" class="inline">
                <?= Csrf::champ() ?>
                <input type="hidden" name="action" value="marquer_toutes_lues">
                <button type="submit"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition text-sm">
                    Tout marquer comme lu
                </button>
            </form>
        <?php endif; ?>
    </div>


    <?php if (empty($notifications)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-gray-600">Aucune notification.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($notifications as $n):
                $lue = (int)$n['lue'] === 1;
                $href = !empty($n['url_cible'])
                    ? url('/notifications.php?id=' . (int)$n['id_notification'] . '&go=1')
                    : '#';
            ?>
                <a href="<?= h($href) ?>"
                   class="block bg-white rounded-xl border <?= $lue ? 'border-gray-200' : 'border-primary-200 bg-primary-50' ?> p-4 hover:shadow-sm transition">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-2 rounded-full <?= $lue ? 'bg-gray-300' : 'bg-primary-500' ?> flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 <?= $lue ? '' : 'text-primary-900' ?>">
                                <?= h($n['titre']) ?>
                            </p>
                            <p class="text-sm text-gray-700 mt-1"><?= h($n['message']) ?></p>
                            <p class="text-xs text-gray-500 mt-2"><?= h(format_date_relative($n['date_creation'])) ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
