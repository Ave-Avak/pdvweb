<?php
/**
 * views/blog/detail.php
 * ---------------------------------------------------------------------
 * Vue : détail d'un billet + ses commentaires.
 *
 * Variables :
 *   - $billet (array)
 *   - $tags (array)
 *   - $commentaires (array)
 *   - $nbLikes (int)
 *   - $aLikeBillet (bool)
 *   - $mesLikesCommentaires (array) [id_commentaire => bool]
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$peutCommenter = Auth::estConnecte();
$estAdmin      = Auth::estAdmin();
$idMembre      = Auth::id();
?>

<div class="max-w-3xl mx-auto">

    <!-- Lien retour -->
    <a href="<?= url('/blog.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour au blog
    </a>

    <!-- ==============================================================
         CARTE — Le billet
    =============================================================== -->
    <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 mb-8">

        <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= h($billet['titre']) ?></h1>

        <!-- Méta -->
        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-5">
            <div class="flex items-center gap-2">
                <img src="<?= h(asset_avatar($billet['auteur_avatar'])) ?>"
                     alt="<?= h($billet['auteur_prenom']) ?>"
                     class="w-8 h-8 rounded-full object-cover border border-gray-200">
                <span class="font-medium text-gray-700">
                    <?= h($billet['auteur_prenom']) ?> <?= h($billet['auteur_nom']) ?>
                </span>
            </div>
            <span>·</span>
            <time title="<?= h(format_date($billet['date_billet'])) ?>">
                <?= h(format_date($billet['date_billet'])) ?>
            </time>

            <?php if ($estAdmin): ?>
                <span class="ml-auto flex items-center gap-2">
                    <a href="<?= url('/admin/billet_form.php?id=' . (int)$billet['id_billet']) ?>"
                       class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">
                        Éditer
                    </a>
                    <form method="post" action="<?= url('/admin/billet_supprimer.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_billet" value="<?= (int)$billet['id_billet'] ?>">
                        <button type="submit"
                                data-confirm="Supprimer définitivement ce billet et tous ses commentaires ?"
                                class="text-xs px-2 py-1 bg-red-50 text-red-700 rounded hover:bg-red-100">
                            Supprimer
                        </button>
                    </form>
                </span>
            <?php endif; ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
            <div class="flex flex-wrap gap-2 mb-5">
                <?php foreach ($tags as $t): ?>
                    <a href="<?= url('/blog.php?tag=' . (int)$t['id_tag']) ?>"
                       class="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-primary-50 hover:text-primary-700 transition">
                        #<?= h($t['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Corps du billet (rendu Markdown) -->
        <div class="prose-billet text-gray-800 leading-relaxed">
            <?= Markdown::vers_html($billet['corps']) ?>
        </div>

        <!-- Likes -->
        <div class="mt-6 pt-5 border-t border-gray-100 flex items-center gap-3">
            <?php if (Auth::estConnecte()): ?>
                <form method="post" action="<?= url('/like_toggle.php') ?>" class="inline">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="type"      value="billet">
                    <input type="hidden" name="id_cible"  value="<?= (int)$billet['id_billet'] ?>">
                    <input type="hidden" name="id_billet" value="<?= (int)$billet['id_billet'] ?>">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition
                                   <?= $aLikeBillet ? 'bg-primary-50 text-primary-700 border-primary-300' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' ?>">
                        <span><?= $aLikeBillet ? '❤️' : '🤍' ?></span>
                        <?= $aLikeBillet ? 'Liké' : 'J\'aime' ?>
                        <span class="text-xs opacity-75">(<?= (int)$nbLikes ?>)</span>
                    </button>
                </form>
            <?php else: ?>
                <div class="text-sm text-gray-500">
                    🤍 <?= (int)$nbLikes ?> like<?= $nbLikes > 1 ? 's' : '' ?> ·
                    <a href="<?= url('/login.php') ?>" class="text-primary-600 hover:underline">Connectez-vous</a> pour liker
                </div>
            <?php endif; ?>
        </div>
    </article>


    <!-- ==============================================================
         SECTION COMMENTAIRES
    =============================================================== -->
    <section id="commentaires" class="space-y-5">
        <h2 class="text-2xl font-bold text-gray-900">
            Commentaires (<?= count($commentaires) ?>)
        </h2>

        <!-- Formulaire d'ajout (UM seulement) -->
        <?php if ($peutCommenter): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <form method="post" action="<?= url('/commentaire_post.php') ?>">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="id_billet" value="<?= (int)$billet['id_billet'] ?>">

                    <label for="corps" class="block text-sm font-medium text-gray-700 mb-2">
                        Votre commentaire
                    </label>
                    <textarea id="corps" name="corps" required rows="4"
                              maxlength="5000"
                              placeholder="Partagez votre avis... (Markdown supporté : **gras**, _italique_, [lien](url))"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none"></textarea>

                    <div class="flex justify-end mt-3">
                        <button type="submit"
                                class="px-5 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition">
                            Publier le commentaire
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                <a href="<?= url('/login.php') ?>" class="font-semibold hover:underline">Connectez-vous</a>
                ou
                <a href="<?= url('/inscription.php') ?>" class="font-semibold hover:underline">créez un compte</a>
                pour commenter ce billet.
            </div>
        <?php endif; ?>

        <!-- Liste des commentaires -->
        <?php if (empty($commentaires)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
                <p>Aucun commentaire pour l'instant. Soyez le premier à donner votre avis !</p>
            </div>
        <?php else: ?>
            <?php foreach ($commentaires as $c): ?>
                <?php
                $estAuteur = ((int)$c['id_membre'] === $idMembre);
                $peutModifier = $estAuteur || $estAdmin;
                $jAime = $mesLikesCommentaires[$c['id_commentaire']] ?? false;
                ?>
                <article id="c<?= (int)$c['id_commentaire'] ?>"
                         class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">

                    <!-- En-tête : avatar + nom + date + actions -->
                    <header class="flex items-center gap-3 mb-3">
                        <img src="<?= h(asset_avatar($c['avatar'])) ?>"
                             alt="<?= h($c['prenom']) ?>"
                             class="w-9 h-9 rounded-full object-cover border border-gray-200">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm">
                                    <?= h($c['prenom']) ?> <?= h($c['nom']) ?>
                                </span>
                                <?php if ($c['statut'] === 'admin'): ?>
                                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-semibold uppercase">Admin</span>
                                <?php endif; ?>
                            </div>
                            <time class="text-xs text-gray-500" title="<?= h(format_date($c['date_comm'])) ?>">
                                <?= h(format_date_relative($c['date_comm'])) ?>
                            </time>
                        </div>

                        <?php if ($peutModifier): ?>
                            <div class="flex gap-1">
                                <!-- Édition : ouvre le textarea -->
                                <button type="button"
                                        onclick="document.getElementById('edit_c<?= (int)$c['id_commentaire'] ?>').classList.toggle('hidden')"
                                        class="text-gray-400 hover:text-primary-600 transition p-1"
                                        title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <!-- Suppression -->
                                <form method="post" action="<?= url('/commentaire_delete.php') ?>" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="id_commentaire" value="<?= (int)$c['id_commentaire'] ?>">
                                    <button type="submit"
                                            data-confirm="Supprimer ce commentaire ?"
                                            class="text-gray-400 hover:text-red-600 transition p-1"
                                            title="<?= $estAuteur ? 'Supprimer' : 'Modérer' ?>">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </header>

                    <!-- Corps du commentaire -->
                    <div class="text-gray-700 prose-billet text-sm">
                        <?= Markdown::vers_html($c['corps']) ?>
                    </div>

                    <!-- Formulaire d'édition (caché par défaut) -->
                    <?php if ($peutModifier): ?>
                        <div id="edit_c<?= (int)$c['id_commentaire'] ?>" class="hidden mt-3 pt-3 border-t border-gray-100">
                            <form method="post" action="<?= url('/commentaire_edit.php') ?>" class="space-y-2">
                                <?= Csrf::champ() ?>
                                <input type="hidden" name="id_commentaire" value="<?= (int)$c['id_commentaire'] ?>">
                                <textarea name="corps" required rows="3"
                                          maxlength="5000"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"><?= h($c['corps']) ?></textarea>
                                <div class="flex gap-2 justify-end">
                                    <button type="button"
                                            onclick="document.getElementById('edit_c<?= (int)$c['id_commentaire'] ?>').classList.add('hidden')"
                                            class="px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            class="px-3 py-1 text-sm bg-primary-600 text-white rounded hover:bg-primary-700">
                                        Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Like sur le commentaire -->
                    <footer class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
                        <?php if (Auth::estConnecte()): ?>
                            <form method="post" action="<?= url('/like_toggle.php') ?>" class="inline">
                                <?= Csrf::champ() ?>
                                <input type="hidden" name="type"      value="commentaire">
                                <input type="hidden" name="id_cible"  value="<?= (int)$c['id_commentaire'] ?>">
                                <input type="hidden" name="id_billet" value="<?= (int)$billet['id_billet'] ?>">
                                <button type="submit"
                                        class="text-xs inline-flex items-center gap-1 px-2 py-1 rounded transition <?= $jAime ? 'text-primary-700 bg-primary-50' : 'text-gray-500 hover:bg-gray-100' ?>">
                                    <?= $jAime ? '❤️' : '🤍' ?>
                                    <?= (int)$c['nb_likes'] ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-xs text-gray-500">🤍 <?= (int)$c['nb_likes'] ?></span>
                        <?php endif; ?>
                    </footer>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</div>

<!-- Styles spécifiques au rendu Markdown -->
<style>
    .prose-billet p { margin-bottom: 0.75rem; }
    .prose-billet p:last-child { margin-bottom: 0; }
    .prose-billet ul, .prose-billet ol { margin: 0.75rem 0; padding-left: 1.5rem; }
    .prose-billet ul { list-style-type: disc; }
    .prose-billet ol { list-style-type: decimal; }
    .prose-billet li { margin-bottom: 0.25rem; }
    .prose-billet blockquote {
        border-left: 4px solid #cbd5e1;
        padding: 0.5rem 1rem;
        margin: 0.75rem 0;
        color: #475569;
        font-style: italic;
        background: #f8fafc;
        border-radius: 0 0.375rem 0.375rem 0;
    }
    .prose-billet code {
        background: #f1f5f9;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
        font-family: ui-monospace, SFMono-Regular, monospace;
    }
    .prose-billet a {
        color: #2563eb;
        text-decoration: underline;
    }
    .prose-billet a:hover { color: #1d4ed8; }
</style>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
