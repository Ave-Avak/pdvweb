<?php
/**
 * views/auth/messages.php
 * ---------------------------------------------------------------------
 * Vue : boîte de réception (liste des conversations).
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Messagerie</h1>
            <p class="text-gray-600">
                <?= count($conversations) ?> conversation<?= count($conversations) > 1 ? 's' : '' ?>
            </p>
        </div>
        <a href="<?= url('/messages_nouveau.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau message
        </a>
    </div>


    <?php if (empty($conversations) && empty($bloques)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucune conversation</h2>
            <p class="text-gray-600 mb-6">
                Démarrez une nouvelle conversation avec un autre membre.
            </p>
            <a href="<?= url('/messages_nouveau.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Démarrer une conversation
            </a>
        </div>
    <?php endif; ?>


    <?php if (!empty($conversations)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <?php foreach ($conversations as $i => $c):
                $estAnonyme = !empty($c['date_anonymisation']);
                $aReponse = (int)$c['dernier_expediteur'] !== $idMembre;
                $nonLu = (int)$c['nb_non_lus'] > 0;
            ?>
                <a href="<?= url('/messages_thread.php?with=' . (int)$c['id_membre']) ?>"
                   class="block px-4 py-3 hover:bg-gray-50 transition <?= $i > 0 ? 'border-t border-gray-100' : '' ?> <?= $nonLu ? 'bg-primary-50/30' : '' ?>">
                    <div class="flex items-start gap-3">
                        <div class="relative flex-shrink-0">
                            <img src="<?= h(asset_avatar($estAnonyme ? null : $c['avatar'])) ?>"
                                 alt="Avatar"
                                 class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                            <?php if ($nonLu): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[20px] h-[20px] flex items-center justify-center px-1.5">
                                    <?= (int)$c['nb_non_lus'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <p class="font-semibold text-gray-900 <?= $nonLu ? '' : 'text-gray-700' ?>">
                                    <?= $estAnonyme ? 'Utilisateur supprimé' : h($c['prenom'] . ' ' . $c['nom']) ?>
                                </p>
                                <?php if ($c['statut'] === 'admin'): ?>
                                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded uppercase font-bold">Admin</span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-400 ml-auto">
                                    <?= h(format_date_relative($c['dernier_message_date'])) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-1">
                                <?php if (!$aReponse): ?>
                                    <span class="text-gray-400">Vous : </span>
                                <?php endif; ?>
                                <?= h($c['dernier_message']) ?>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <?php if (!empty($bloques)): ?>
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Membres bloqués</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <?php foreach ($bloques as $i => $b): ?>
                    <div class="px-4 py-3 flex items-center gap-3 <?= $i > 0 ? 'border-t border-gray-100' : '' ?>">
                        <img src="<?= h(asset_avatar($b['avatar'])) ?>"
                             alt="Avatar"
                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 grayscale">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-700"><?= h($b['prenom']) ?> <?= h($b['nom']) ?></p>
                            <p class="text-xs text-gray-500">Bloqué le <?= h(format_date($b['date_blocage'])) ?></p>
                        </div>
                        <form method="post" action="<?= url('/messages_action.php') ?>" class="inline">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="action" value="debloquer">
                            <input type="hidden" name="id_cible" value="<?= (int)$b['id_membre'] ?>">
                            <button type="submit" class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                                Débloquer
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
