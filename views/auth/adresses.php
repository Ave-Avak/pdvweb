<?php
/**
 * views/auth/adresses.php
 * ---------------------------------------------------------------------
 * Vue : liste des adresses du membre, avec actions (édition/suppression).
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-1">Mes adresses</h1>
            <p class="text-gray-600">
                <?= count($adresses) ?> adresse<?= count($adresses) > 1 ? 's' : '' ?> enregistrée<?= count($adresses) > 1 ? 's' : '' ?>
            </p>
        </div>
        <a href="<?= url('/adresse_form.php') ?>"
           class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter une adresse
        </a>
    </div>


    <?php if (empty($adresses)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucune adresse enregistrée</h2>
            <p class="text-gray-600 mb-6">
                Ajoutez une adresse pour pouvoir passer vos commandes plus rapidement.
            </p>
            <a href="<?= url('/adresse_form.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter ma première adresse
            </a>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($adresses as $a): ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 <?= (int)$a['est_defaut'] === 1 ? 'ring-2 ring-primary-300' : '' ?>">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2 flex-wrap">
                                <?= h($a['libelle']) ?>
                                <?php if ((int)$a['est_defaut'] === 1): ?>
                                    <span class="text-[10px] bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded uppercase font-bold">Défaut</span>
                                <?php endif; ?>
                                <?php if ($a['type'] === 'livraison'): ?>
                                    <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded uppercase">Livraison</span>
                                <?php elseif ($a['type'] === 'facturation'): ?>
                                    <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded uppercase">Facturation</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 mb-4 leading-relaxed">
                        <?= h($a['prenom']) ?> <?= h($a['nom']) ?><br>
                        <?= h($a['rue']) ?> <?= h($a['numero']) ?>
                        <?php if (!empty($a['complement'])): ?>
                            <br><?= h($a['complement']) ?>
                        <?php endif; ?>
                        <br>
                        <?= h($a['cp']) ?> <?= h($a['ville']) ?><br>
                        <?= h($a['pays']) ?>
                        <?php if (!empty($a['telephone'])): ?>
                            <br><span class="text-gray-500">☎ <?= h($a['telephone']) ?></span>
                        <?php endif; ?>
                    </p>

                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <a href="<?= url('/adresse_form.php?id=' . (int)$a['id_adresse']) ?>"
                           class="text-xs px-3 py-1.5 bg-primary-50 text-primary-700 rounded hover:bg-primary-100 transition">
                            ✏️ Modifier
                        </a>
                        <form method="post" action="<?= url('/adresse_supprimer.php') ?>" class="inline">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="id_adresse" value="<?= (int)$a['id_adresse'] ?>">
                            <button type="submit"
                                    data-confirm="Supprimer cette adresse ?"
                                    class="text-xs px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100 transition">
                                🗑️ Supprimer
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
