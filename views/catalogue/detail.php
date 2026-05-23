<?php
/**
 * views/catalogue/detail.php
 * ---------------------------------------------------------------------
 * Vue : détail d'un article + avis + articles similaires.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$stockOk = (int)$article['stock'] > 0;
$qteMax = min((int)$article['stock'], Panier::quantiteMaxParArticle());
?>

<div class="max-w-5xl mx-auto">

    <a href="<?= url('/catalogue.php?categorie=' . (int)$article['id_categorie']) ?>"
       class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <?= h($article['categorie_nom']) ?>
    </a>


    <!-- En-tête article -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Galerie d'images (carrousel) -->
            <?php
            // Construit la liste : image principale + galerie
            $toutesImages = [];
            if (!empty($article['image'])) {
                $toutesImages[] = $article['image'];
            }
            foreach ($imagesGalerie as $img) {
                $toutesImages[] = $img['fichier'];
            }
            // S'il n'y a aucune image, on garde un placeholder
            if (empty($toutesImages)) {
                $toutesImages[] = null;  // déclenchera l'asset par défaut
            }
            ?>
            <div class="p-4" data-carrousel>
                <!-- Image principale (active) -->
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3 relative">
                    <?php foreach ($toutesImages as $idx => $img): ?>
                        <img src="<?= h(asset_article($img)) ?>"
                             alt="<?= h($article['nom']) ?> - vue <?= $idx + 1 ?>" loading="lazy"
                             data-carrousel-image="<?= $idx ?>"
                             class="w-full h-full object-cover absolute inset-0 transition-opacity duration-300 <?= $idx === 0 ? 'opacity-100' : 'opacity-0' ?>">
                    <?php endforeach; ?>

                    <?php if (count($toutesImages) > 1): ?>
                        <!-- Boutons précédent/suivant -->
                        <button type="button" data-carrousel-prev
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur rounded-full shadow flex items-center justify-center hover:bg-white transition"
                                aria-label="Image précédente">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button type="button" data-carrousel-next
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/90 backdrop-blur rounded-full shadow flex items-center justify-center hover:bg-white transition"
                                aria-label="Image suivante">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- Compteur "1 / 4" -->
                        <div class="absolute bottom-3 right-3 px-2 py-1 bg-black/60 text-white text-xs rounded backdrop-blur">
                            <span data-carrousel-current>1</span> / <?= count($toutesImages) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Miniatures -->
                <?php if (count($toutesImages) > 1): ?>
                    <div class="grid grid-cols-4 gap-2">
                        <?php foreach ($toutesImages as $idx => $img): ?>
                            <button type="button"
                                    data-carrousel-thumb="<?= $idx ?>"
                                    class="aspect-square bg-gray-100 rounded-md overflow-hidden border-2 transition <?= $idx === 0 ? 'border-primary-500' : 'border-transparent hover:border-gray-300' ?>">
                                <img src="<?= h(asset_article($img)) ?>"
                                     alt="Miniature <?= $idx + 1 ?>" loading="lazy"
                                     class="w-full h-full object-cover">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Infos -->
            <div class="p-6 md:p-8 flex flex-col">
                <p class="text-sm text-gray-500 mb-1"><?= h($article['categorie_nom']) ?></p>
                <h1 class="text-2xl font-bold text-gray-900 mb-3"><?= h($article['nom']) ?></h1>

                <!-- Tags (Phase 3.2) -->
                <?php if (!empty($tagsArticle)): ?>
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <?php foreach ($tagsArticle as $tg): ?>
                            <a href="<?= url('/catalogue.php?tag=' . (int)$tg['id_tag']) ?>"
                               class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-50 text-primary-700 text-xs font-semibold rounded hover:bg-primary-100 transition">
                                #<?= h($tg['nom']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Étoiles -->
                <?php if ($statsNotes['nb_notes'] > 0): ?>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-amber-500">
                            <?= str_repeat('★', (int)round($statsNotes['moyenne'])) ?><?= str_repeat('☆', 5 - (int)round($statsNotes['moyenne'])) ?>
                        </span>
                        <span class="text-sm text-gray-600">
                            <?= number_format($statsNotes['moyenne'], 1, ',', '') ?>/5
                            (<?= $statsNotes['nb_notes'] ?> avis)
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Prix -->
                <p class="text-3xl font-bold text-gray-900 mb-4"><?= format_prix($article['prix']) ?></p>

                <!-- Description -->
                <?php if (!empty($article['description'])): ?>
                    <p class="text-gray-700 mb-6 leading-relaxed"><?= nl2br(h($article['description'])) ?></p>
                <?php endif; ?>

                <!-- Stock -->
                <div class="mb-5 text-sm">
                    <?php if ($stockOk): ?>
                        <span class="text-green-700">✓ En stock</span>
                        <?php if ((int)$article['stock'] <= 5): ?>
                            <span class="text-amber-600">(seulement <?= (int)$article['stock'] ?> restants)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-red-600 font-semibold">✗ Épuisé</span>
                    <?php endif; ?>
                </div>

                <!-- Boutons d'action -->
                <div class="space-y-3 mt-auto">
                    <?php if ($stockOk): ?>
                        <form method="post" action="<?= url('/panier_add.php') ?>" class="flex gap-2">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="id_article" value="<?= (int)$article['id_article'] ?>">
                            <input type="hidden" name="retour" value="<?= h(url('/article.php?id=' . $article['id_article'])) ?>">

                            <select name="quantite" class="px-3 py-2 border border-gray-300 rounded-lg">
                                <?php for ($i = 1; $i <= $qteMax; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>

                            <button type="submit"
                                    class="flex-1 px-5 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                                Ajouter au panier
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (Auth::estConnecte()): ?>
                        <form method="post" action="<?= url('/favori_toggle.php') ?>" data-favori-ajax>
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="id_article" value="<?= (int)$article['id_article'] ?>">
                            <input type="hidden" name="retour" value="<?= h(url('/article.php?id=' . $article['id_article'])) ?>">
                            <button type="submit"
                                    class="w-full px-5 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                                <?= $estFavori ? '❤️ Retirer des favoris' : '🤍 Ajouter aux favoris' ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <!-- Bouton comparer (Phase 3.3) -->
                    <?php
                    $dansCompar = !empty($_SESSION['comparateur'])
                                  && in_array((int)$article['id_article'], $_SESSION['comparateur'], true);
                    ?>
                    <?php if ($dansCompar): ?>
                        <a href="<?= url('/comparer.php') ?>"
                           class="w-full block text-center px-5 py-2 bg-primary-100 text-primary-700 font-semibold rounded-lg hover:bg-primary-200 transition">
                            ⚖️ Voir le comparateur (<?= count($_SESSION['comparateur']) ?>)
                        </a>
                    <?php else: ?>
                        <a href="<?= url('/comparer.php?add=' . (int)$article['id_article'] . '&retour=' . urlencode($_SERVER['REQUEST_URI'])) ?>"
                           class="w-full block text-center px-5 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
                            ⚖️ Ajouter au comparateur
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <!-- ====================================================
         SECTION AVIS
    ===================================================== -->
    <section id="avis" class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">
            Avis clients (<?= $statsNotes['nb_notes'] ?>)
        </h2>

        <!-- Formulaire d'avis (si membre a acheté) -->
        <?php if ($peutNoter): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
                    <?= $saNote ? 'Modifier votre avis' : 'Laisser un avis' ?>
                </h3>
                <form method="post" action="<?= url('/note_add.php') ?>" class="space-y-3">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="id_article" value="<?= (int)$article['id_article'] ?>">

                    <!-- Étoiles cliquables -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700">Note :</label>
                        <div class="flex gap-1" id="etoiles-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="note" value="<?= $i ?>"
                                           <?= $saNote && (int)$saNote['note'] === $i ? 'checked' : '' ?>
                                           required class="hidden peer">
                                    <span class="text-3xl text-gray-300 peer-checked:text-amber-400 hover:text-amber-400 transition" data-etoile="<?= $i ?>">★</span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <textarea name="commentaire" rows="3" maxlength="2000"
                              placeholder="Votre avis (facultatif)..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition resize-none"><?= h($saNote['commentaire'] ?? '') ?></textarea>

                    <button type="submit"
                            class="px-5 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition text-sm">
                        <?= $saNote ? 'Mettre à jour' : 'Publier mon avis' ?>
                    </button>
                </form>
            </div>
        <?php elseif (Auth::estConnecte()): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 text-sm text-amber-800">
                💡 Vous devez avoir acheté cet article pour laisser un avis.
            </div>
        <?php endif; ?>

        <!-- Liste des avis -->
        <?php if (empty($notes)): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-500 text-sm">
                Aucun avis pour cet article.
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($notes as $n): ?>
                    <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="<?= h(asset_avatar($n['avatar'])) ?>"
                                 alt="<?= h(nom_membre($n)) ?>" loading="lazy"
                                 class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            <div class="flex-1">
                                <p class="font-semibold text-sm <?= !empty($n['date_anonymisation']) ? 'italic text-gray-500' : 'text-gray-900' ?>">
                                    <?= h(nom_membre($n)) ?>
                                </p>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-amber-500">
                                        <?= str_repeat('★', (int)$n['note']) ?><?= str_repeat('☆', 5 - (int)$n['note']) ?>
                                    </span>
                                    <span class="text-gray-500"><?= h(format_date_courte($n['date_note'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($n['commentaire'])): ?>
                            <p class="text-sm text-gray-700 mt-2"><?= nl2br(h($n['commentaire'])) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>


    <!-- ====================================================
         ARTICLES SIMILAIRES
    ===================================================== -->
    <?php if (!empty($articlesSimilaires)): ?>
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Vous pourriez aimer aussi</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($articlesSimilaires as $s): ?>
                    <a href="<?= url('/article.php?id=' . (int)$s['id_article']) ?>"
                       class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition group">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img src="<?= h(asset_article($s['image'])) ?>"
                                 alt="<?= h($s['nom']) ?>" loading="lazy"
                                 class="w-full h-full object-cover">
                        </div>
                        <div class="p-3">
                            <p class="text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-primary-600 transition"><?= h($s['nom']) ?></p>
                            <p class="text-base font-bold text-gray-900 mt-1"><?= format_prix($s['prix']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<!-- JS : étoiles interactives -->
<script>
(function() {
    const etoiles = document.querySelectorAll('#etoiles-input span');
    etoiles.forEach((etoile, idx) => {
        etoile.addEventListener('mouseenter', () => {
            etoiles.forEach((e, i) => {
                e.classList.toggle('text-amber-400', i <= idx);
                e.classList.toggle('text-gray-300', i > idx);
            });
        });
    });
    document.getElementById('etoiles-input')?.addEventListener('mouseleave', () => {
        // Restaure l'état basé sur le radio coché
        const checked = document.querySelector('#etoiles-input input:checked');
        const val = checked ? parseInt(checked.value) : 0;
        etoiles.forEach((e, i) => {
            e.classList.toggle('text-amber-400', i < val);
            e.classList.toggle('text-gray-300', i >= val);
        });
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
