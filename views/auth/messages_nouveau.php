<?php
/**
 * views/auth/messages_nouveau.php
 * ---------------------------------------------------------------------
 * Vue : démarrer une nouvelle conversation.
 *
 * Trois modes :
 *  - destinataire pré-sélectionné via ?to=ID  → directement formulaire
 *  - recherche en cours via ?q=mots          → résultats à choisir
 *  - vide                                     → invite à chercher
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-2xl mx-auto">

    <a href="<?= url('/messages.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Messagerie
    </a>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Nouveau message</h1>


    <?php if ($destinataire): ?>
        <!-- DESTINATAIRE CHOISI : formulaire d'envoi -->
        <form method="post" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <?= Csrf::champ() ?>
            <input type="hidden" name="id_destinataire" value="<?= (int)$destinataire['id_membre'] ?>">

            <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                <img src="<?= h(asset_avatar($destinataire['avatar'])) ?>"
                     alt="Avatar"
                     class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                <div>
                    <p class="text-xs text-gray-500">À :</p>
                    <p class="font-semibold text-gray-900">
                        <?= h($destinataire['prenom']) ?> <?= h($destinataire['nom']) ?>
                        <span class="text-gray-400 font-normal">@<?= h($destinataire['login']) ?></span>
                    </p>
                </div>
            </div>

            <div>
                <label for="sujet" class="block text-sm font-medium text-gray-700 mb-1">Sujet (optionnel)</label>
                <input type="text" id="sujet" name="sujet" maxlength="150"
                       value="<?= h($_POST['sujet'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>

            <div>
                <label for="corps" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                <textarea id="corps" name="corps" required rows="6" maxlength="5000"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition resize-y"><?= h($_POST['corps'] ?? '') ?></textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 5000 caractères.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                    Envoyer
                </button>
                <a href="<?= url('/messages.php') ?>"
                   class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Annuler
                </a>
            </div>
        </form>

    <?php else: ?>
        <!-- RECHERCHE DE DESTINATAIRE -->
        <form method="get" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4">
            <label for="q" class="block text-sm font-medium text-gray-700 mb-2">Rechercher un membre</label>
            <div class="flex gap-2">
                <input type="text" id="q" name="q" required minlength="2"
                       value="<?= h($rechercheQ) ?>"
                       placeholder="Login, prénom ou nom..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <button type="submit"
                        class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                    Chercher
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Au moins 2 caractères. Les membres anonymisés ou bloqués n'apparaissent pas.</p>
        </form>

        <?php if ($rechercheQ !== ''): ?>
            <?php if (empty($resultatsRecherche)): ?>
                <div class="bg-white border border-gray-200 rounded-xl p-6 text-center text-gray-500 text-sm">
                    Aucun membre trouvé pour « <?= h($rechercheQ) ?> ».
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <p class="px-4 py-2 text-xs text-gray-500 bg-gray-50 border-b border-gray-200">
                        <?= count($resultatsRecherche) ?> résultat<?= count($resultatsRecherche) > 1 ? 's' : '' ?>
                    </p>
                    <?php foreach ($resultatsRecherche as $r): ?>
                        <a href="<?= url('/messages_nouveau.php?to=' . (int)$r['id_membre']) ?>"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-t border-gray-100 first:border-t-0">
                            <img src="<?= h(asset_avatar($r['avatar'])) ?>"
                                 alt="Avatar"
                                 class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900">
                                    <?= h($r['prenom']) ?> <?= h($r['nom']) ?>
                                </p>
                                <p class="text-xs text-gray-500">@<?= h($r['login']) ?></p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
