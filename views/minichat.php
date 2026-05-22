<?php
/**
 * views/minichat.php
 * ---------------------------------------------------------------------
 * Vue : interface du mini-chat.
 *
 * Variables :
 *   - $titre       string
 *   - $messages    array  (10 derniers messages, du plus ancien au plus récent)
 *   - $membre      array  (membre connecté)
 *   - $erreur      string|null
 *   - $idMembre    int
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$longueurMax = Minichat::longueurMax();
$nbAffiches  = Minichat::nbAffiches();
?>

<div class="max-w-3xl mx-auto">

    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mini-chat</h1>
        <p class="text-gray-600">
            Discutez en direct avec la communauté.
            Les <?= (int)$nbAffiches ?> derniers messages sont affichés.
        </p>
    </div>

    <!-- ==============================================================
         CARTE — Liste des messages
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                Conversation
            </h2>
            <span class="text-xs text-gray-500">
                <?= count($messages) ?> message<?= count($messages) > 1 ? 's' : '' ?>
            </span>
        </div>

        <div id="liste-messages" class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
            <?php if (empty($messages)): ?>
                <!-- État vide -->
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p>Aucun message pour l'instant.</p>
                    <p class="text-sm">Soyez le premier à dire bonjour !</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $m): ?>
                    <?php
                    $estAuteur = ((int)$m['id_membre'] === $idMembre);
                    $peutSupprimer = $estAuteur || Auth::estAdmin();
                    ?>
                    <article class="px-5 py-4 flex gap-3 hover:bg-gray-50 transition group">

                        <!-- Avatar -->
                        <img src="<?= h(asset_avatar($m['avatar'])) ?>"
                             alt="<?= h($m['prenom']) ?>"
                             class="w-10 h-10 rounded-full object-cover border border-gray-200 flex-shrink-0"
                             onerror="this.src='<?= asset('assets/img/avatar-defaut.svg') ?>'">

                        <!-- Contenu -->
                        <div class="flex-1 min-w-0">
                            <!-- Ligne du dessus : nom + date + bouton supprimer -->
                            <div class="flex items-baseline gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm">
                                    <?= h($m['prenom']) ?> <?= h($m['nom']) ?>
                                </span>

                                <?php if ($m['statut'] === 'admin'): ?>
                                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-semibold uppercase">
                                        Admin
                                    </span>
                                <?php endif; ?>

                                <span class="text-xs text-gray-500">
                                    <?= h(format_date_relative($m['date_message'])) ?>
                                </span>

                                <?php if ($peutSupprimer): ?>
                                    <form method="post" action="<?= url('/minichat_supprimer.php') ?>"
                                          class="ml-auto opacity-0 group-hover:opacity-100 transition">
                                        <?= Csrf::champ() ?>
                                        <input type="hidden" name="id_message" value="<?= (int)$m['id_message'] ?>">
                                        <button type="submit"
                                                data-confirm="Supprimer ce message ?"
                                                title="<?= $estAuteur ? 'Supprimer mon message' : 'Modérer ce message' ?>"
                                                class="text-gray-400 hover:text-red-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/>
                                            </svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <!-- Texte du message -->
                            <!-- nl2br + h() : on autorise les retours à la ligne mais on échappe le HTML -->
                            <p class="text-gray-700 text-sm mt-0.5 break-words whitespace-pre-wrap">
                                <?= nl2br(h($m['message'])) ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>


    <!-- ==============================================================
         CARTE — Formulaire d'envoi
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
            Envoyer un message
        </h2>

        <?php if ($erreur): ?>
            <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-3 mb-4 rounded-r-md text-sm">
                <?= h($erreur) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/minichat.php') ?>" class="space-y-3">
            <?= Csrf::champ() ?>

            <div class="flex gap-3 items-start">
                <!-- Avatar de l'auteur -->
                <img src="<?= h(asset_avatar($membre['avatar'])) ?>"
                     alt="Vous"
                     class="w-10 h-10 rounded-full object-cover border border-gray-200 flex-shrink-0">

                <!-- Zone de saisie -->
                <div class="flex-1">
                    <textarea id="message" name="message" required
                              maxlength="<?= (int)$longueurMax ?>"
                              rows="2"
                              placeholder="Tapez votre message..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none"
                              autofocus><?= h($_POST['message'] ?? '') ?></textarea>

                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-500">
                            <span id="compteur">0</span> / <?= (int)$longueurMax ?> caractères
                        </p>
                        <button type="submit"
                                class="px-5 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Envoyer
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>


<!-- ==============================================================
     JS local : compteur en direct + auto-scroll vers le bas
============================================================== -->
<script>
(function () {
    'use strict';

    const textarea = document.getElementById('message');
    const compteur = document.getElementById('compteur');
    const limite   = <?= (int)$longueurMax ?>;

    // Compteur en direct
    function majCompteur() {
        const nb = textarea.value.length;
        compteur.textContent = nb;
        // Changement de couleur si on approche de la limite
        if (nb > limite * 0.9) {
            compteur.classList.add('text-orange-600', 'font-semibold');
        } else {
            compteur.classList.remove('text-orange-600', 'font-semibold');
        }
    }
    textarea.addEventListener('input', majCompteur);
    majCompteur(); // initialisation

    // Auto-scroll vers le dernier message au chargement
    const liste = document.getElementById('liste-messages');
    if (liste) {
        liste.scrollTop = liste.scrollHeight;
    }
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
