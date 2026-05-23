<?php
/**
 * views/admin/code_promo_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire de création / édition d'un code promo.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <!-- Bandeau admin -->
    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'un code promo.
    </div>

    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('/admin/codes_promo.php') ?>" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $modeEdition ? 'Modifier le code promo' : 'Nouveau code promo' ?>
            </h1>
            <p class="text-gray-600 text-sm">
                Définissez la remise, sa validité et ses limites d'utilisation.
            </p>
        </div>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          action="<?= $modeEdition ? url('/admin/code_promo_form.php?id=' . $idCode) : url('/admin/code_promo_form.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <!-- Code -->
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Code <span class="text-red-500">*</span>
            </label>
            <input type="text" id="code" name="code" required maxlength="40"
                   value="<?= h($donnees['code']) ?>"
                   placeholder="ex: BIENVENUE10"
                   style="text-transform: uppercase"
                   class="w-full px-4 py-2 border <?= isset($erreurs['code']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition font-mono">
            <p class="text-xs text-gray-500 mt-1">
                3 à 40 caractères : lettres MAJUSCULES, chiffres, _ ou -.
                Sera automatiquement converti en majuscules.
            </p>
            <?php if (isset($erreurs['code'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['code']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                Description <span class="text-gray-400 text-xs">(optionnel)</span>
            </label>
            <input type="text" id="description" name="description" maxlength="255"
                   value="<?= h($donnees['description']) ?>"
                   placeholder="ex: Bienvenue chez PDVWeb — 10 % sur votre première commande"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">
                Description interne (non visible par les membres).
            </p>
        </div>

        <!-- Type de remise + valeur -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="type_remise" class="block text-sm font-medium text-gray-700 mb-1">
                    Type de remise <span class="text-red-500">*</span>
                </label>
                <select id="type_remise" name="type_remise" required
                        onchange="document.getElementById('aide_valeur').textContent = this.value === 'pourcentage' ? 'Pourcentage de réduction (0-100)' : (this.value === 'montant_fixe' ? 'Montant fixe à déduire en €' : 'Aucune valeur nécessaire'); document.getElementById('valeur').disabled = this.value === 'livraison_offerte';"
                        class="w-full px-4 py-2 border <?= isset($erreurs['type_remise']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <option value="pourcentage" <?= $donnees['type_remise'] === 'pourcentage' ? 'selected' : '' ?>>Pourcentage (%)</option>
                    <option value="montant_fixe" <?= $donnees['type_remise'] === 'montant_fixe' ? 'selected' : '' ?>>Montant fixe (€)</option>
                    <option value="livraison_offerte" <?= $donnees['type_remise'] === 'livraison_offerte' ? 'selected' : '' ?>>Livraison offerte</option>
                </select>
            </div>

            <div>
                <label for="valeur" class="block text-sm font-medium text-gray-700 mb-1">
                    Valeur <span class="text-red-500">*</span>
                </label>
                <input type="number" id="valeur" name="valeur" step="0.01" min="0"
                       <?= $donnees['type_remise'] === 'livraison_offerte' ? 'disabled' : 'required' ?>
                       value="<?= h($donnees['valeur']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['valeur']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition disabled:bg-gray-100">
                <p id="aide_valeur" class="text-xs text-gray-500 mt-1">
                    <?php
                    echo $donnees['type_remise'] === 'pourcentage' ? 'Pourcentage de réduction (0-100)'
                        : ($donnees['type_remise'] === 'montant_fixe' ? 'Montant fixe à déduire en €'
                        : 'Aucune valeur nécessaire');
                    ?>
                </p>
                <?php if (isset($erreurs['valeur'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['valeur']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Montant min panier -->
        <div>
            <label for="montant_min_panier" class="block text-sm font-medium text-gray-700 mb-1">
                Montant minimum du panier (€)
            </label>
            <input type="number" id="montant_min_panier" name="montant_min_panier"
                   step="0.01" min="0"
                   value="<?= h($donnees['montant_min_panier']) ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['montant_min_panier']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">
                0 = pas de montant minimum.
            </p>
            <?php if (isset($erreurs['montant_min_panier'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['montant_min_panier']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Quotas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="utilisations_max" class="block text-sm font-medium text-gray-700 mb-1">
                    Utilisations max (global)
                </label>
                <input type="number" id="utilisations_max" name="utilisations_max"
                       min="1"
                       value="<?= h($donnees['utilisations_max']) ?>"
                       placeholder="illimité"
                       class="w-full px-4 py-2 border <?= isset($erreurs['utilisations_max']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">
                    Laissez vide pour illimité.
                </p>
                <?php if (isset($erreurs['utilisations_max'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['utilisations_max']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="utilisations_par_membre" class="block text-sm font-medium text-gray-700 mb-1">
                    Utilisations par membre
                </label>
                <input type="number" id="utilisations_par_membre" name="utilisations_par_membre"
                       min="1"
                       value="<?= h($donnees['utilisations_par_membre']) ?>"
                       placeholder="illimité"
                       class="w-full px-4 py-2 border <?= isset($erreurs['utilisations_par_membre']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <p class="text-xs text-gray-500 mt-1">
                    Combien de fois un même membre peut l'utiliser.
                </p>
                <?php if (isset($erreurs['utilisations_par_membre'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['utilisations_par_membre']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dates de validité -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">
                    Date de début <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" id="date_debut" name="date_debut" required
                       value="<?= h($donnees['date_debut']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['date_debut']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <?php if (isset($erreurs['date_debut'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['date_debut']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">
                    Date de fin <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" id="date_fin" name="date_fin" required
                       value="<?= h($donnees['date_fin']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['date_fin']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <?php if (isset($erreurs['date_fin'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['date_fin']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actif -->
        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="actif" value="1"
                       <?= $donnees['actif'] ? 'checked' : '' ?>
                       class="w-4 h-4 text-primary-600 border-gray-300 rounded">
                <span class="text-sm font-medium text-gray-700">Code actif (utilisable par les membres)</span>
            </label>
            <p class="text-xs text-gray-500 mt-1 ml-6">
                Désactivez pour suspendre temporairement le code sans le supprimer.
            </p>
        </div>

        <!-- Aperçu en édition -->
        <?php if ($modeEdition && $nbUtilisations > 0): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                ⚠️ Ce code a déjà été utilisé <strong><?= $nbUtilisations ?></strong> fois.
                Modifier la valeur ou les dates n'affecte pas les utilisations passées.
            </div>
        <?php endif; ?>

        <!-- Boutons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100">
            <a href="<?= url('/admin/codes_promo.php') ?>"
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer les modifications' : 'Créer le code promo' ?>
            </button>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
