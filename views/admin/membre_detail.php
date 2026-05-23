<?php
/**
 * views/admin/membre_detail.php
 * ---------------------------------------------------------------------
 * Vue admin : fiche complète d'un membre.
 *
 * Sections : profil, stats, actions, commandes, commentaires, adresses, connexions.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$anonymise = !empty($membre['date_anonymisation']);
$bloque    = !$anonymise && (int)$membre['indesirable'] === 1;
$estMoi    = (int)$membre['id_membre'] === Auth::id();
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — fiche membre.
    </div>

    <a href="<?= url('/admin/membres.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour à la liste
    </a>


    <!-- En-tête : profil + actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-5 flex-wrap">
            <img src="<?= h(asset_avatar($membre['avatar'])) ?>"
                 alt=""
                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap mb-1">
                    <h1 class="text-2xl font-bold text-gray-900"><?= h($membre['login']) ?></h1>
                    <?php if ($membre['statut'] === 'admin'): ?>
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded font-semibold uppercase">Admin</span>
                    <?php endif; ?>
                    <?php if ($anonymise): ?>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded font-semibold uppercase">Anonymisé</span>
                    <?php elseif ($bloque): ?>
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded font-semibold uppercase">Bloqué</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold uppercase">Actif</span>
                    <?php endif; ?>
                </div>
                <p class="text-gray-700">
                    <?php if ($anonymise): ?>
                        <em>Données personnelles supprimées (RGPD)</em>
                    <?php else: ?>
                        <?= h($membre['prenom']) ?> <?= h($membre['nom']) ?>
                        — <a href="mailto:<?= h($membre['email']) ?>" class="text-primary-600 hover:underline"><?= h($membre['email']) ?></a>
                    <?php endif; ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Inscrit le <?= h(format_date($membre['date_inscription'])) ?>
                    <?php if (!empty($membre['derniere_connexion'])): ?>
                        · Dernière connexion <?= h(format_date_relative($membre['derniere_connexion'])) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Actions admin -->
        <?php if (!$estMoi && !$anonymise): ?>
            <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-2">
                <!-- MODIFIER DONNÉES (nouveau) -->
                <a href="<?= url('/admin/membre_form.php?id=' . (int)$membre['id_membre']) ?>"
                   class="px-3 py-1.5 bg-primary-600 text-white text-sm font-semibold rounded-md hover:bg-primary-700 transition inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>

                <?php if ($bloque): ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="debloquer">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition">
                            Débloquer
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="bloquer">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Bloquer ce membre ? Il ne pourra plus se connecter."
                                class="px-3 py-1.5 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition">
                            Bloquer
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($membre['statut'] === 'admin'): ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="degrader">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Retirer les droits administrateur ?"
                                class="px-3 py-1.5 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                            Dégrader (membre)
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="promouvoir">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Promouvoir ce membre au rang d'administrateur ?"
                                class="px-3 py-1.5 bg-purple-600 text-white text-sm font-semibold rounded-md hover:bg-purple-700 transition">
                            Promouvoir admin
                        </button>
                    </form>
                <?php endif; ?>

                <!-- RESET MOT DE PASSE (nouveau) -->
                <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                    <input type="hidden" name="action" value="reset_mdp">
                    <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                    <button type="submit"
                            data-confirm="Réinitialiser le mot de passe de ce membre ? Un nouveau mot de passe temporaire vous sera affiché à communiquer au membre."
                            class="px-3 py-1.5 bg-warning-600 text-white text-sm font-semibold rounded-md hover:bg-warning-700 transition inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Reset mdp
                    </button>
                </form>

                <!-- VÉRIFIER EMAIL (nouveau, uniquement si non vérifié) -->
                <?php if (empty($membre['email_verifie']) || (int)$membre['email_verifie'] === 0): ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="verifier_email">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Marquer l'email de ce membre comme vérifié manuellement ?"
                                class="px-3 py-1.5 bg-info-600 text-white text-sm font-semibold rounded-md hover:bg-info-700 transition inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Vérifier email
                        </button>
                    </form>
                <?php endif; ?>

                <!-- ANONYMISER (nouveau) - séparé visuellement -->
                <button type="button"
                        onclick="document.getElementById('modal-anonymiser').classList.remove('hidden')"
                        class="px-3 py-1.5 bg-danger-600 text-white text-sm font-semibold rounded-md hover:bg-danger-700 transition inline-flex items-center gap-1 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/>
                    </svg>
                    Anonymiser (RGPD)
                </button>
            </div>

            <!-- Affichage du mot de passe temporaire (si reset effectué) -->
            <?php if (!empty($_SESSION['mdp_temp_genere'])
                        && $_SESSION['mdp_temp_genere']['expire'] > time()
                        && (int)$_SESSION['mdp_temp_genere']['id'] === (int)$membre['id_membre']): ?>
                <?php
                $info = $_SESSION['mdp_temp_genere'];
                unset($_SESSION['mdp_temp_genere']); // ne s'affiche qu'une fois
                ?>
                <div class="mt-4 bg-warning-50 border-2 border-warning-300 rounded-lg p-4">
                    <h3 class="text-sm font-bold text-warning-800 mb-2 flex items-center gap-2">
                        🔑 Mot de passe temporaire généré
                    </h3>
                    <p class="text-sm text-warning-800 mb-3">
                        Communiquez ce mot de passe au membre <strong><?= h($info['login']) ?></strong>
                        par un canal sécurisé. Le membre devra le changer dès sa prochaine connexion.
                    </p>
                    <div class="bg-white border border-warning-300 rounded p-3 flex items-center gap-3">
                        <code class="font-mono text-lg font-bold text-warning-900 select-all flex-1">
                            <?= h($info['mdp']) ?>
                        </code>
                        <button type="button"
                                onclick="navigator.clipboard.writeText('<?= h($info['mdp']) ?>'); this.textContent='✓ Copié';"
                                class="px-3 py-1.5 bg-warning-100 text-warning-800 text-xs font-semibold rounded hover:bg-warning-200 transition">
                            Copier
                        </button>
                    </div>
                    <p class="text-xs text-warning-700 mt-2">
                        ⚠️ Ce mot de passe ne sera affiché qu'une seule fois. Cliquez sur "Copier" pour le sauvegarder.
                    </p>
                </div>
            <?php endif; ?>

            <!-- MODALE ANONYMISATION (RGPD) -->
            <div id="modal-anonymiser" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-lg w-full">
                    <div class="p-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 bg-danger-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Anonymiser ce membre (RGPD)</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Cette action est <strong>irréversible</strong>.
                                </p>
                            </div>
                        </div>

                        <div class="bg-danger-50 border border-danger-200 rounded-lg p-3 mb-4 text-sm text-danger-800 space-y-1">
                            <p><strong>Conséquences :</strong></p>
                            <ul class="list-disc ml-5 space-y-0.5">
                                <li>Nom, prénom, email, login remplacés par des valeurs aléatoires</li>
                                <li>Mot de passe écrasé (membre ne peut plus se connecter)</li>
                                <li>Avatar supprimé</li>
                                <li>Adresses postales supprimées</li>
                                <li>Commandes <strong>conservées</strong> (obligations comptables) mais anonymes</li>
                                <li>Commentaires et messages anonymisés</li>
                            </ul>
                        </div>

                        <form method="post" action="<?= url('/admin/membre_action.php') ?>">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                            <input type="hidden" name="action" value="anonymiser">
                            <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">

                            <label for="confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Pour confirmer, tapez exactement le login : <strong><?= h($membre['login']) ?></strong>
                            </label>
                            <input type="text" id="confirmation" name="confirmation" required autocomplete="off"
                                   class="w-full px-4 py-2 border border-danger-300 rounded-lg focus:ring-2 focus:ring-danger-500 transition font-mono">

                            <div class="flex justify-end gap-2 mt-5">
                                <button type="button"
                                        onclick="document.getElementById('modal-anonymiser').classList.add('hidden')"
                                        class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="px-5 py-2 bg-danger-600 text-white font-semibold rounded-lg hover:bg-danger-700 transition shadow-sm">
                                    Anonymiser définitivement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($estMoi): ?>
            <p class="mt-4 pt-4 border-t border-gray-100 text-xs text-amber-700 bg-amber-50 p-2 rounded">
                💡 Vous ne pouvez pas effectuer d'actions sur votre propre compte.
            </p>
        <?php endif; ?>
    </div>


    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 24h</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ1 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 7j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ7 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 30j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ30 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Commandes</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($commandes) ?></p>
        </div>
    </div>


    <!-- Commandes -->
    <?php if (!empty($commandes)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Commandes (<?= count($commandes) ?>)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold">Référence</th>
                            <th class="text-left px-3 py-2 font-semibold">Date</th>
                            <th class="text-right px-3 py-2 font-semibold">Total</th>
                            <th class="text-left px-3 py-2 font-semibold">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($commandes as $c): ?>
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="<?= url('/facture.php?id=' . (int)$c['id_facture']) ?>"
                                       class="text-primary-600 hover:underline"><?= h($c['reference']) ?></a>
                                </td>
                                <td class="px-3 py-2 text-gray-500"><?= h(format_date_courte($c['date_achat'])) ?></td>
                                <td class="px-3 py-2 text-right font-medium"><?= format_prix($c['prix_total']) ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background-color: <?= h($c['statut_couleur'] ?? '#9ca3af') ?>20; color: <?= h($c['statut_couleur'] ?? '#374151') ?>;">
                                        <?= h($c['statut_nom']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <!-- Adresses -->
    <?php if (!empty($adresses)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Adresses</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <?php foreach ($adresses as $a): ?>
                    <div class="border border-gray-200 rounded-lg p-3 text-sm">
                        <p class="font-semibold text-gray-900 mb-1">
                            <?= h($a['libelle']) ?>
                            <?php if ((int)$a['est_defaut'] === 1): ?>
                                <span class="text-[10px] bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded uppercase font-bold">Défaut</span>
                            <?php endif; ?>
                        </p>
                        <p class="text-gray-700">
                            <?= h($a['prenom']) ?> <?= h($a['nom']) ?><br>
                            <?= h($a['rue']) ?> <?= h($a['numero']) ?><br>
                            <?= h($a['cp']) ?> <?= h($a['ville']) ?>, <?= h($a['pays']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Commentaires récents -->
    <?php if ($nbTotalCommentaires > 0): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <h2 class="text-lg font-bold text-gray-900">
                    <?php if ($voirTousCommentaires): ?>
                        Tous les commentaires (<?= count($commentaires) ?>)
                    <?php else: ?>
                        5 derniers commentaires
                        <?php if ($nbTotalCommentaires > 5): ?>
                            <span class="text-sm font-normal text-gray-500">sur <?= (int)$nbTotalCommentaires ?> au total</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </h2>

                <?php if ($nbTotalCommentaires > 5): ?>
                    <?php if ($voirTousCommentaires): ?>
                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$idMembre) ?>"
                           class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                            ← Afficher uniquement les 5 derniers
                        </a>
                    <?php else: ?>
                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$idMembre . '&tous_commentaires=1') ?>"
                           class="text-xs px-3 py-1.5 bg-primary-50 text-primary-700 rounded hover:bg-primary-100 transition">
                            Afficher tous les commentaires →
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($commentaires)): ?>
                <div class="space-y-2 text-sm">
                    <?php foreach ($commentaires as $c): ?>
                        <div class="border-l-2 border-gray-200 pl-3 py-1">
                            <p class="text-xs text-gray-500">
                                Sur <a href="<?= url('/billet.php?id=' . (int)$c['id_billet']) ?>" class="text-primary-600 hover:underline"><?= h($c['billet_titre']) ?></a>
                                · <?= h(format_date_relative($c['date_comm'])) ?>
                            </p>
                            <p class="text-gray-700 mt-1 line-clamp-2"><?= h($c['corps']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>


    <!-- Pseudos utilisés dans le mini-chat (modération admin) -->
    <?php if (!empty($pseudosMinichat)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <h2 class="text-lg font-bold text-gray-900">Pseudos utilisés dans le mini-chat</h2>
                <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-semibold uppercase">
                    Modération
                </span>
            </div>
            <p class="text-xs text-gray-500 mb-3">
                Le cahier des charges autorise les UM à choisir un pseudo libre pour chaque session.
                Voici la liste complète des pseudos utilisés par ce membre, pour faciliter votre travail de modération.
            </p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold text-gray-700">Pseudo</th>
                            <th class="text-center px-3 py-2 font-semibold text-gray-700">Messages</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-700">Première utilisation</th>
                            <th class="text-left px-3 py-2 font-semibold text-gray-700">Dernière utilisation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($pseudosMinichat as $p):
                            $estLoginReel = ($p['pseudo'] === $membre['login']);
                        ?>
                            <tr class="<?= $estLoginReel ? 'bg-green-50' : '' ?>">
                                <td class="px-3 py-2">
                                    <span class="font-semibold"><?= h($p['pseudo']) ?></span>
                                    <?php if ($estLoginReel): ?>
                                        <span class="ml-2 text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded">login réel</span>
                                    <?php else: ?>
                                        <span class="ml-2 text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">alias</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center font-semibold"><?= (int)$p['nb_messages'] ?></td>
                                <td class="px-3 py-2 text-xs text-gray-600"><?= h(format_date($p['premiere_utilisation'])) ?></td>
                                <td class="px-3 py-2 text-xs text-gray-600"><?= h(format_date($p['derniere_utilisation'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                💡 Tous les messages sont liés au compte de ce membre via <code class="bg-gray-100 px-1 rounded">id_membre</code>,
                quel que soit le pseudo utilisé. Aucune action ne peut être totalement anonyme.
            </p>
        </div>
    <?php endif; ?>


    <!-- Dernières connexions -->
    <?php if (!empty($dernieresConnexions)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">10 dernières connexions</h2>
            <div class="text-sm space-y-1">
                <?php foreach ($dernieresConnexions as $cnx): ?>
                    <div class="flex justify-between border-b border-gray-100 py-1 last:border-0">
                        <span class="text-gray-700"><?= h(format_date($cnx['date_log'])) ?></span>
                        <span class="text-gray-500 text-xs font-mono"><?= h($cnx['ip'] ?? '-') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Lien audit -->
    <div class="text-center">
        <a href="<?= url('/admin/audit.php?id_membre=' . (int)$membre['id_membre']) ?>"
           class="text-sm text-primary-600 hover:underline">
            Voir l'historique d'audit pour ce membre →
        </a>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
