<?php
/**
 * views/admin/frais_port_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire d'une grille tarifaire de frais de port.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'une grille tarifaire.
    </div>

    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('/admin/frais_port.php') ?>" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $modeEdition ? 'Modifier la grille' : 'Nouvelle grille tarifaire' ?>
            </h1>
            <p class="text-gray-600 text-sm">
                Une grille s'applique à un pays et peut être restreinte à une fourchette de montants de panier.
            </p>
        </div>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          action="<?= $modeEdition ? url('/admin/frais_port_form.php?id=' . $idFrais) : url('/admin/frais_port_form.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <!-- Nom + Pays -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom de la grille <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nom" name="nom" required maxlength="80"
                       value="<?= h($donnees['nom']) ?>"
                       placeholder="ex: Standard, Express, Point relais"
                       class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <?php if (isset($erreurs['nom'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="pays" class="block text-sm font-medium text-gray-700 mb-1">
                    Pays <span class="text-red-500">*</span>
                </label>
                <input type="text" id="pays" name="pays" required maxlength="60"
                       value="<?= h($donnees['pays']) ?>"
                       list="liste_pays"
                       placeholder="ex: Belgique"
                       class="w-full px-4 py-2 border <?= isset($erreurs['pays']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <datalist id="liste_pays">
                    <option value="Belgique"></option>
                    <option value="France"></option>
                    <option value="Pays-Bas"></option>
                    <option value="Luxembourg"></option>
                    <option value="Allemagne"></option>
                    <option value="Italie"></option>
                    <option value="Espagne"></option>
                    <option value="Suisse"></option>
                </datalist>
                <?php if (isset($erreurs['pays'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['pays']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Prix + délai -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="prix" class="block text-sm font-medium text-gray-700 mb-1">
                    Prix (€) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="prix" name="prix" required step="0.01" min="0"
                       value="<?= h($donnees['prix']) ?>"
                       placeholder="ex: 4.95"
                       class="w-full px-4 py-2 border <?= isset($erreurs['prix']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">
                    0 € pour livraison offerte.
                </p>
                <?php if (isset($erreurs['prix'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['prix']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="delai_jours" class="block text-sm font-medium text-gray-700 mb-1">
                    Délai estimé (jours)
                </label>
                <input type="number" id="delai_jours" name="delai_jours" min="0" max="60"
                       value="<?= h($donnees['delai_jours']) ?>"
                       placeholder="ex: 3"
                       class="w-full px-4 py-2 border <?= isset($erreurs['delai_jours']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">
                    Affiché au client lors de la commande.
                </p>
                <?php if (isset($erreurs['delai_jours'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['delai_jours']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Conditions de montant -->
        <fieldset>
            <legend class="text-sm font-semibold text-gray-700 mb-3">
                Conditions sur le montant du panier (optionnel)
            </legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="montant_min_panier" class="block text-sm font-medium text-gray-700 mb-1">
                        Panier minimum (€)
                    </label>
                    <input type="number" id="montant_min_panier" name="montant_min_panier"
                           step="0.01" min="0"
                           value="<?= h($donnees['montant_min_panier']) ?>"
                           placeholder="aucun"
                           class="w-full px-4 py-2 border <?= isset($erreurs['montant_min_panier']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <p class="text-xs text-gray-500 mt-1">
                        Ex : 50€ pour "livraison gratuite dès 50€" → mettre 50 ici et prix à 0.
                    </p>
                    <?php if (isset($erreurs['montant_min_panier'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['montant_min_panier']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="montant_max_panier" class="block text-sm font-medium text-gray-700 mb-1">
                        Panier maximum (€)
                    </label>
                    <input type="number" id="montant_max_panier" name="montant_max_panier"
                           step="0.01" min="0"
                           value="<?= h($donnees['montant_max_panier']) ?>"
                           placeholder="aucun"
                           class="w-full px-4 py-2 border <?= isset($erreurs['montant_max_panier']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <p class="text-xs text-gray-500 mt-1">
                        Pour limiter cette grille aux petits paniers.
                    </p>
                    <?php if (isset($erreurs['montant_max_panier'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['montant_max_panier']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>

        <!-- Actif -->
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="actif" value="1"
                       <?= $donnees['actif'] ? 'checked' : '' ?>
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded">
                <span class="text-sm font-medium text-gray-700">Grille active (proposée aux clients)</span>
            </label>
        </div>

        <!-- Aide -->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-800">
            💡 <strong>Exemple :</strong> Pour offrir la livraison dès 50€ en Belgique : créez 2 grilles :
            <br>• « Standard » Belgique, prix 4,95€, montant max = 49,99€
            <br>• « Livraison offerte » Belgique, prix 0€, montant min = 50€
        </div>

        <!-- Boutons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100">
            <a href="<?= url('/admin/frais_port.php') ?>"
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer les modifications' : 'Créer la grille' ?>
            </button>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
