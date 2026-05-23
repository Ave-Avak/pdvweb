<?php
/**
 * views/admin/securite.php
 * ---------------------------------------------------------------------
 * Vue admin : tableau de bord sécurité et maintenance.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — sécurité &amp; maintenance.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Sécurité &amp; Maintenance</h1>
        <p class="text-gray-600">État de la sécurité de l'application et purge des données anciennes</p>
    </div>


    <!-- KPI sécurité -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Administrateurs actifs</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbAdmins ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Membres bloqués</p>
            <p class="text-2xl font-bold <?= $nbBloques > 0 ? 'text-amber-600' : 'text-gray-900' ?>"><?= (int)$nbBloques ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Échecs de connexion 24h</p>
            <p class="text-2xl font-bold <?= $nbTentatives24h > 20 ? 'text-red-600' : ($nbTentatives24h > 5 ? 'text-amber-600' : 'text-gray-900') ?>">
                <?= (int)$nbTentatives24h ?>
            </p>
        </div>
    </div>


    <!-- État de sécurité (checklist) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">État de la configuration</h2>
        <ul class="space-y-2">
            <?php foreach ($verifications as $v): ?>
                <li class="flex items-start gap-3 p-3 rounded-lg <?= $v['ok'] ? 'bg-green-50 border border-green-100' : 'bg-amber-50 border border-amber-200' ?>">
                    <span class="text-lg flex-shrink-0">
                        <?= $v['ok'] ? '✓' : '⚠' ?>
                    </span>
                    <div class="flex-1">
                        <p class="font-semibold text-sm <?= $v['ok'] ? 'text-green-900' : 'text-amber-900' ?>">
                            <?= h($v['titre']) ?>
                        </p>
                        <p class="text-xs <?= $v['ok'] ? 'text-green-800' : 'text-amber-800' ?> mt-1">
                            <?= h($v['message']) ?>
                        </p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>


    <!-- Headers HTTP envoyés -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Headers HTTP de sécurité</h2>
        <p class="text-sm text-gray-600 mb-3">
            Ces headers sont envoyés sur chaque page pour protéger le navigateur contre les attaques courantes.
        </p>
        <div class="space-y-2 text-xs font-mono">
            <div class="bg-gray-50 p-2 rounded border border-gray-200">
                <strong class="text-primary-700">Content-Security-Policy</strong>
                <span class="text-gray-500">— anti-XSS, limite les sources autorisées</span>
            </div>
            <div class="bg-gray-50 p-2 rounded border border-gray-200">
                <strong class="text-primary-700">X-Frame-Options: DENY</strong>
                <span class="text-gray-500">— anti-clickjacking, empêche l'intégration en iframe</span>
            </div>
            <div class="bg-gray-50 p-2 rounded border border-gray-200">
                <strong class="text-primary-700">X-Content-Type-Options: nosniff</strong>
                <span class="text-gray-500">— empêche le MIME-sniffing</span>
            </div>
            <div class="bg-gray-50 p-2 rounded border border-gray-200">
                <strong class="text-primary-700">Referrer-Policy: strict-origin-when-cross-origin</strong>
                <span class="text-gray-500">— limite la fuite d'URL vers d'autres sites</span>
            </div>
            <div class="bg-gray-50 p-2 rounded border border-gray-200">
                <strong class="text-primary-700">Permissions-Policy</strong>
                <span class="text-gray-500">— désactive caméra, micro, géolocalisation, paiement</span>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            💡 Voir le détail dans <code>classes/util/Securite.php</code> → <code>envoyerHeadersSecurite()</code>
        </p>
    </div>


    <!-- Purge -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Purge des données anciennes</h2>
        <p class="text-sm text-gray-600 mb-4">
            La purge supprime les données techniques anciennes pour respecter le RGPD
            (minimisation des données) et garder une BDD propre.
        </p>

        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold">Type</th>
                        <th class="text-left px-3 py-2 font-semibold">Critère</th>
                        <th class="text-right px-3 py-2 font-semibold">À purger</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-3 py-2">Tokens expirés</td>
                        <td class="px-3 py-2 text-gray-500">date d'expiration dépassée</td>
                        <td class="px-3 py-2 text-right font-semibold"><?= (int)$compteurs['tokens_expires'] ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Tentatives de connexion</td>
                        <td class="px-3 py-2 text-gray-500">plus de 30 jours</td>
                        <td class="px-3 py-2 text-right font-semibold"><?= (int)$compteurs['tentatives_anciennes'] ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Logs de connexion</td>
                        <td class="px-3 py-2 text-gray-500">plus d'1 an</td>
                        <td class="px-3 py-2 text-right font-semibold"><?= (int)$compteurs['log_connexion_anciens'] ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Vues d'articles</td>
                        <td class="px-3 py-2 text-gray-500">plus de 90 jours</td>
                        <td class="px-3 py-2 text-right font-semibold"><?= (int)$compteurs['vues_articles_anciennes'] ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Logs de recherche</td>
                        <td class="px-3 py-2 text-gray-500">plus de 6 mois</td>
                        <td class="px-3 py-2 text-right font-semibold"><?= (int)$compteurs['recherches_anciennes'] ?></td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="2" class="px-3 py-2 text-right font-bold">Total</td>
                        <td class="px-3 py-2 text-right font-bold"><?= (int)$totalPurgeable ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <form method="post">
            <?= Csrf::champ() ?>
            <input type="hidden" name="action" value="purger">
            <button type="submit"
                    data-confirm="Purger <?= (int)$totalPurgeable ?> élément(s) ? Cette action est irréversible."
                    <?= $totalPurgeable === 0 ? 'disabled' : '' ?>
                    class="px-5 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Lancer la purge (<?= (int)$totalPurgeable ?> éléments)
            </button>
        </form>
    </div>


    <!-- OWASP -->
    <div class="bg-primary-50 border border-primary-200 rounded-xl p-5">
        <h2 class="text-lg font-bold text-primary-900 mb-2">📘 Documentation sécurité</h2>
        <p class="text-sm text-primary-800 mb-3">
            Le projet implémente les protections recommandées par OWASP Top 10.
            Consultez la documentation pour le détail :
        </p>
        <ul class="text-sm text-primary-800 space-y-1 list-disc pl-5">
            <li><code>docs/securite.md</code> — Synthèse OWASP Top 10 du projet</li>
            <li><code>docs/audit_securite.md</code> — Rapport d'audit détaillé</li>
        </ul>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
