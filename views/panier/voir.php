<?php
/**
 * views/panier/voir.php
 * ---------------------------------------------------------------------
 * Vue : contenu du panier avec gestion des quantités.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$qteMax = Panier::quantiteMaxParArticle();
?>

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mon panier</h1>
        <?php if (!empty($detail['lignes'])): ?>
            <p class="text-gray-600"><?= $detail['nb_articles'] ?> article<?= $detail['nb_articles'] > 1 ? 's' : '' ?></p>
        <?php endif; ?>
    </div>


    <?php if (empty($detail['lignes'])): ?>
        <!-- Panier vide -->
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Votre panier est vide</h2>
            <p class="text-gray-600 mb-6">
                Parcourez le catalogue et ajoutez vos articles préférés.
            </p>
            <a href="<?= url('/catalogue.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Découvrir le catalogue
            </a>
        </div>
    <?php else: ?>
        <!-- Tableau des articles -->
        <form method="post" action="<?= url('/panier_update.php') ?>" class="space-y-4">
            <?= Csrf::champ() ?>

            <?php foreach ($detail['lignes'] as $ligne):
                $a = $ligne['article']; ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row gap-4 items-center">
                    <!-- Image -->
                    <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                       class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        <img src="<?= h(asset_article($a['image'])) ?>"
                             alt="<?= h($a['nom']) ?>"
                             class="w-full h-full object-cover">
                    </a>

                    <!-- Infos -->
                    <div class="flex-1 min-w-0">
                        <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                           class="font-semibold text-gray-900 hover:text-primary-600 transition">
                            <?= h($a['nom']) ?>
                        </a>
                        <p class="text-xs text-gray-500 mt-0.5"><?= h($a['categorie_nom']) ?></p>
                        <p class="text-sm text-gray-700 mt-1"><?= format_prix($ligne['prix_unitaire']) ?> / unité</p>
                    </div>

                    <!-- Quantité -->
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700">Qté :</label>
                        <select name="quantites[<?= (int)$a['id_article'] ?>]"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                            <?php for ($i = 1; $i <= min((int)$a['stock'], $qteMax); $i++): ?>
                                <option value="<?= $i ?>" <?= $i === $ligne['quantite'] ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Sous-total -->
                    <div class="text-right">
                        <p class="font-bold text-gray-900 text-lg whitespace-nowrap">
                            <?= format_prix($ligne['sous_total']) ?>
                        </p>
                    </div>

                    <!-- Suppression -->
                    <button type="button"
                            onclick="document.getElementById('remove_<?= (int)$a['id_article'] ?>').submit()"
                            class="text-gray-400 hover:text-red-600 transition p-1"
                            title="Retirer du panier">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/>
                        </svg>
                    </button>
                </article>
            <?php endforeach; ?>

            <!-- Boutons d'action -->
            <div class="flex flex-wrap gap-3">
                <button type="submit"
                        class="px-4 py-2 bg-gray-100 text-gray-800 font-semibold rounded-lg hover:bg-gray-200 transition text-sm">
                    Mettre à jour
                </button>
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition text-sm">
                    Continuer mes achats
                </a>
            </div>
        </form>

        <!-- Formulaires cachés pour retirer un article -->
        <?php foreach ($detail['lignes'] as $ligne): ?>
            <form id="remove_<?= (int)$ligne['article']['id_article'] ?>"
                  method="post" action="<?= url('/panier_remove.php') ?>" class="hidden">
                <?= Csrf::champ() ?>
                <input type="hidden" name="id_article" value="<?= (int)$ligne['article']['id_article'] ?>">
            </form>
        <?php endforeach; ?>


        <!-- Récapitulatif -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex justify-between items-center text-sm mb-2">
                <span class="text-gray-600">Sous-total</span>
                <span class="font-semibold"><?= format_prix($detail['sous_total']) ?></span>
            </div>
            <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                <span>Frais de port</span>
                <span>calculés à l'étape suivante</span>
            </div>

            <?php if (Auth::estConnecte()): ?>
                <a href="<?= url('/commande.php') ?>"
                   class="block w-full px-5 py-3 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                    Passer commande →
                </a>
            <?php else: ?>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800 mb-3">
                    Vous devez être connecté pour finaliser votre achat.
                </div>
                <a href="<?= url('/login.php?retour=panier.php') ?>"
                   class="block w-full px-5 py-3 bg-primary-600 text-white text-center font-semibold rounded-lg hover:bg-primary-700 transition">
                    Se connecter pour commander
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
