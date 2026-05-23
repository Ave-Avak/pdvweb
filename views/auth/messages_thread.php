<?php
/**
 * views/auth/messages_thread.php
 * ---------------------------------------------------------------------
 * Vue : fil de discussion avec un autre membre.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <a href="<?= url('/messages.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Toutes mes conversations
    </a>

    <!-- En-tête du thread -->
    <div class="bg-white rounded-t-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
        <img src="<?= h(asset_avatar($autre['avatar'])) ?>"
             alt="Avatar"
             class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
                <?= h($autre['prenom']) ?> <?= h($autre['nom']) ?>
                <?php if ($autre['statut'] === 'admin'): ?>
                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded uppercase font-bold">Admin</span>
                <?php endif; ?>
            </p>
            <p class="text-xs text-gray-500">@<?= h($autre['login']) ?></p>
        </div>

        <!-- Actions -->
        <div class="relative" data-menu>
            <button type="button" data-menu-toggle
                    class="p-2 hover:bg-gray-100 rounded-lg transition" title="Options">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                </svg>
            </button>
            <div data-menu-panel
                 class="hidden absolute right-0 top-full mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                <?php if ($jaiBloque): ?>
                    <form method="post" action="<?= url('/messages_action.php') ?>" class="block">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="debloquer">
                        <input type="hidden" name="id_cible" value="<?= (int)$autre['id_membre'] ?>">
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Débloquer ce membre
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/messages_action.php') ?>" class="block">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="bloquer">
                        <input type="hidden" name="id_cible" value="<?= (int)$autre['id_membre'] ?>">
                        <button type="submit"
                                data-confirm="Bloquer ce membre ? Il ne pourra plus vous envoyer de messages."
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Bloquer ce membre
                        </button>
                    </form>
                <?php endif; ?>

                <form method="post" action="<?= url('/messages_action.php') ?>" class="block border-t border-gray-100">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="action" value="supprimer_thread">
                    <input type="hidden" name="id_cible" value="<?= (int)$autre['id_membre'] ?>">
                    <button type="submit"
                            data-confirm="Supprimer toute la conversation ? Cette action est irréversible et supprime les messages des deux côtés."
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        Supprimer la conversation
                    </button>
                </form>
            </div>
        </div>
    </div>


    <!-- Avertissements blocage -->
    <?php if ($jaiBloque): ?>
        <div class="bg-red-50 border-x border-red-200 p-3 text-sm text-red-800">
            🚫 Vous avez bloqué ce membre. Il ne peut plus vous envoyer de messages.
        </div>
    <?php elseif ($ilMaBloque && !Auth::estAdmin()): ?>
        <div class="bg-amber-50 border-x border-amber-200 p-3 text-sm text-amber-800">
            ℹ️ Vous ne pouvez plus envoyer de message à ce membre pour le moment.
        </div>
    <?php endif; ?>


    <!-- Fil des messages -->
    <div class="bg-gray-50 border-x border-gray-200 p-4 space-y-3 min-h-[300px] max-h-[600px] overflow-y-auto">
        <?php if (empty($messages)): ?>
            <p class="text-center text-gray-500 text-sm py-8">
                Aucun message échangé. Soyez le premier à écrire !
            </p>
        <?php else:
            $jourPrec = '';
            foreach ($messages as $m):
                $estMoi = (int)$m['id_expediteur'] === $idMembre;
                $jourActuel = date('Y-m-d', strtotime($m['date_envoi']));
                $estAnonymeExp = !empty($m['exp_anonyme']);
        ?>
            <?php if ($jourActuel !== $jourPrec): ?>
                <div class="flex items-center gap-2 my-4">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-500 px-2"><?= h(format_date($m['date_envoi'])) ?></span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>
                <?php $jourPrec = $jourActuel; endif; ?>

            <div class="flex gap-2 <?= $estMoi ? 'flex-row-reverse' : '' ?>">
                <img src="<?= h(asset_avatar($estAnonymeExp ? null : $m['exp_avatar'])) ?>"
                     alt="Avatar"
                     class="w-8 h-8 rounded-full object-cover border border-gray-200 flex-shrink-0">
                <div class="<?= $estMoi ? 'bg-primary-600 text-white' : 'bg-white border border-gray-200 text-gray-800' ?> rounded-2xl px-4 py-2 max-w-[75%]">
                    <p class="text-sm whitespace-pre-wrap break-words"><?= h($m['corps']) ?></p>
                    <p class="text-[10px] mt-1 <?= $estMoi ? 'text-primary-100' : 'text-gray-400' ?>">
                        <?= h(date('H:i', strtotime($m['date_envoi']))) ?>
                        <?php if ($estMoi && (int)$m['lu'] === 1): ?>
                            · lu
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>


    <!-- Zone d'envoi -->
    <?php if (!$jaiBloque && (!$ilMaBloque || Auth::estAdmin())): ?>
        <form method="post" class="bg-white border-x border-b border-gray-200 rounded-b-xl p-3 flex gap-2 items-end">
            <?= Csrf::champ() ?>
            <input type="hidden" name="with" value="<?= (int)$autre['id_membre'] ?>">
            <textarea name="corps" required rows="2" maxlength="5000"
                      placeholder="Votre message..."
                      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition resize-none text-sm"></textarea>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Envoyer
            </button>
        </form>
    <?php else: ?>
        <div class="bg-gray-100 border-x border-b border-gray-200 rounded-b-xl p-4 text-center text-sm text-gray-500">
            L'envoi de message est désactivé pour cette conversation.
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
