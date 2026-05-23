<?php
/**
 * views/admin/billet_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire de création/édition d'un billet
 * avec barre d'outils Markdown et aperçu en direct.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <!-- Bandeau admin -->
    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'un billet de blog.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">
            <?= $modeEdition ? 'Modifier le billet' : 'Nouveau billet' ?>
        </h1>
        <p class="text-gray-600">
            Le contenu supporte la mise en forme Markdown (gras, italique, listes, liens...).
        </p>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= $modeEdition ? url('/admin/billet_form.php?id=' . $idBillet) : url('/admin/billet_form.php') ?>"
          enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <!-- Titre -->
        <div>
            <label for="titre" class="block text-sm font-medium text-gray-700 mb-1">
                Titre <span class="text-red-500">*</span>
            </label>
            <input type="text" id="titre" name="titre" required maxlength="200"
                   value="<?= h($donnees['titre']) ?>"
                   class="w-full px-4 py-2 border <?= isset($erreurs['titre']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            <?php if (isset($erreurs['titre'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['titre']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Résumé court (optionnel) -->
        <div>
            <label for="resume" class="block text-sm font-medium text-gray-700 mb-1">
                Résumé <span class="text-gray-400 text-xs font-normal">(optionnel, max 500 caractères)</span>
            </label>
            <textarea id="resume" name="resume" maxlength="500" rows="3"
                      placeholder="Description courte affichée sur la page d'accueil et la liste des billets."
                      class="w-full px-4 py-2 border <?= isset($erreurs['resume']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition resize-none"><?= h($donnees['resume']) ?></textarea>
            <p class="text-xs text-gray-500 mt-1">
                Si laissé vide, un extrait automatique du contenu sera utilisé.
            </p>
            <?php if (isset($erreurs['resume'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['resume']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Image illustrative (optionnelle) -->
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                Image illustrative <span class="text-gray-400 text-xs font-normal">(optionnel)</span>
            </label>

            <?php if (!empty($imageActuelle)): ?>
                <div class="mb-3 flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <img src="<?= h(asset_article($imageActuelle)) ?>"
                         alt="Image actuelle"
                         class="w-24 h-24 object-cover rounded border border-gray-300">
                    <div class="flex-1">
                        <p class="text-sm text-gray-700 font-medium mb-1">Image actuelle</p>
                        <p class="text-xs text-gray-500 mb-2"><?= h($imageActuelle) ?></p>
                        <label class="inline-flex items-center gap-2 text-sm text-red-600 cursor-pointer hover:text-red-700">
                            <input type="checkbox" name="supprimer_image" value="1" class="rounded">
                            <span>Supprimer cette image</span>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <input type="file" id="image" name="image"
                   accept=".gif,.jpg,.jpeg,.png,.webp,image/gif,image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-gray-700
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-md file:border-0
                          file:text-sm file:font-semibold
                          file:bg-primary-50 file:text-primary-700
                          hover:file:bg-primary-100 cursor-pointer">
            <p class="text-xs text-gray-500 mt-1">
                Format .gif ou .jpeg, 2 Mo maximum.
                <?php if (!empty($imageActuelle)): ?>
                    Téléverser une nouvelle image remplacera l'actuelle.
                <?php endif; ?>
            </p>
            <?php if (isset($erreurs['image'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['image']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tousLesTags)): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tousLesTags as $t): ?>
                        <?php $coche = in_array((int)$t['id_tag'], $idsTagsSelectionnes, true); ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="tags[]" value="<?= (int)$t['id_tag'] ?>"
                                   <?= $coche ? 'checked' : '' ?>
                                   class="peer hidden">
                            <span class="px-3 py-1 text-sm rounded-full border bg-white text-gray-700 border-gray-300
                                         peer-checked:bg-primary-600 peer-checked:text-white peer-checked:border-primary-600
                                         hover:bg-gray-50 transition inline-block">
                                #<?= h($t['nom']) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Barre d'outils Markdown -->
        <div>
            <label for="corps" class="block text-sm font-medium text-gray-700 mb-1">
                Contenu (Markdown) <span class="text-red-500">*</span>
            </label>

            <div class="border <?= isset($erreurs['corps']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg overflow-hidden">
                <!-- Barre d'outils -->
                <div class="flex items-center gap-1 bg-gray-50 border-b border-gray-200 px-2 py-1.5 flex-wrap">
                    <button type="button" data-md="gras" title="Gras (Ctrl+B)"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm font-bold">B</button>
                    <button type="button" data-md="italique" title="Italique (Ctrl+I)"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm italic">I</button>
                    <button type="button" data-md="code" title="Code inline"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm font-mono">&lt;/&gt;</button>
                    <span class="w-px h-5 bg-gray-300 mx-1"></span>
                    <button type="button" data-md="lien" title="Insérer un lien"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm">🔗</button>
                    <span class="w-px h-5 bg-gray-300 mx-1"></span>
                    <button type="button" data-md="liste" title="Liste à puces"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm">• Liste</button>
                    <button type="button" data-md="liste-num" title="Liste numérotée"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm">1. Liste</button>
                    <button type="button" data-md="citation" title="Citation"
                            class="md-btn px-2 py-1 hover:bg-gray-200 rounded text-sm">❝</button>
                    <span class="flex-1"></span>
                    <button type="button" id="btn-preview"
                            class="px-3 py-1 hover:bg-gray-200 rounded text-sm font-medium">
                        Aperçu
                    </button>
                </div>

                <!-- Textarea -->
                <textarea id="corps" name="corps" required rows="15"
                          placeholder="Tapez votre billet ici...

Exemples de mise en forme :
**texte en gras**
_texte en italique_
[lien vers Apple](https://apple.com)
- élément de liste
1. élément numéroté
> citation
`code inline`"
                          class="w-full px-4 py-3 border-0 focus:ring-0 transition resize-y font-mono text-sm leading-relaxed"><?= h($donnees['corps']) ?></textarea>
            </div>
            <?php if (isset($erreurs['corps'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['corps']) ?></p>
            <?php endif; ?>

            <!-- Zone d'aperçu (cachée par défaut) -->
            <div id="zone-preview" class="hidden mt-3 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Aperçu</p>
                <div id="contenu-preview" class="prose-billet text-gray-800"></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-3">
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer les modifications' : 'Publier le billet' ?>
            </button>
            <a href="<?= url('/admin/billets.php') ?>"
               class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>

    </form>
</div>

<!-- Styles pour le rendu Markdown dans l'aperçu (mêmes que la vue détail) -->
<style>
    .prose-billet p { margin-bottom: 0.75rem; }
    .prose-billet ul, .prose-billet ol { margin: 0.75rem 0; padding-left: 1.5rem; }
    .prose-billet ul { list-style-type: disc; }
    .prose-billet ol { list-style-type: decimal; }
    .prose-billet li { margin-bottom: 0.25rem; }
    .prose-billet blockquote { border-left: 4px solid #cbd5e1; padding: 0.5rem 1rem; margin: 0.75rem 0; color: #475569; font-style: italic; background: #f8fafc; }
    .prose-billet code { background: #f1f5f9; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.875em; }
    .prose-billet a { color: #2563eb; text-decoration: underline; }
</style>

<!-- JS : barre d'outils Markdown + aperçu en direct -->
<script>
(function () {
    'use strict';

    const textarea = document.getElementById('corps');
    const previewBtn = document.getElementById('btn-preview');
    const zonePreview = document.getElementById('zone-preview');
    const contenuPreview = document.getElementById('contenu-preview');

    /**
     * Insère/entoure la sélection avec des marqueurs Markdown.
     */
    function entourer(avant, apres, placeholder) {
        const debut = textarea.selectionStart;
        const fin   = textarea.selectionEnd;
        const selection = textarea.value.substring(debut, fin);
        const texte = selection || placeholder;

        textarea.value =
            textarea.value.substring(0, debut) +
            avant + texte + apres +
            textarea.value.substring(fin);

        // Repositionner le curseur sur le texte inséré
        textarea.focus();
        if (selection) {
            textarea.setSelectionRange(debut + avant.length, debut + avant.length + texte.length);
        } else {
            textarea.setSelectionRange(debut + avant.length, debut + avant.length + texte.length);
        }
    }

    /**
     * Insère un préfixe au début de chaque ligne sélectionnée.
     */
    function prefixerLignes(prefixe) {
        const debut = textarea.selectionStart;
        const fin   = textarea.selectionEnd;
        const selection = textarea.value.substring(debut, fin) || 'élément';
        const lignes = selection.split('\n');
        const nouveau = lignes.map((l, i) => (typeof prefixe === 'function' ? prefixe(i, l) : prefixe + l)).join('\n');

        textarea.value =
            textarea.value.substring(0, debut) +
            nouveau +
            textarea.value.substring(fin);

        textarea.focus();
        textarea.setSelectionRange(debut, debut + nouveau.length);
    }

    // Gestion des boutons
    document.querySelectorAll('.md-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const type = this.getAttribute('data-md');
            switch (type) {
                case 'gras':       entourer('**', '**', 'texte en gras'); break;
                case 'italique':   entourer('_', '_', 'texte en italique'); break;
                case 'code':       entourer('`', '`', 'code'); break;
                case 'lien':       entourer('[', '](https://)', 'libellé du lien'); break;
                case 'liste':      prefixerLignes('- '); break;
                case 'liste-num':  prefixerLignes((i) => (i + 1) + '. '); break;
                case 'citation':   prefixerLignes('> '); break;
            }
        });
    });

    // Raccourcis clavier
    textarea.addEventListener('keydown', function (e) {
        if (e.ctrlKey || e.metaKey) {
            if (e.key === 'b') { e.preventDefault(); entourer('**', '**', 'gras'); }
            if (e.key === 'i') { e.preventDefault(); entourer('_', '_', 'italique'); }
        }
    });

    // Aperçu en direct (rendu côté serveur via fetch ou rendu basique JS)
    // Pour rester simple : on fait un rendu local approximatif
    function rendreMarkdownSimple(texte) {
        // Échapper HTML d'abord
        const div = document.createElement('div');
        div.textContent = texte;
        let html = div.innerHTML;

        // Code inline
        html = html.replace(/`([^`\n]+)`/g, '<code>$1</code>');
        // Liens
        html = html.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (m, t, u) => {
            if (/^javascript:/i.test(u) || /^data:/i.test(u)) return m;
            return '<a href="' + u + '" target="_blank" rel="noopener">' + t + '</a>';
        });
        // Gras et italique
        html = html.replace(/\*\*([^*\n]+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/(?<!\w)_([^_\n]+?)_(?!\w)/g, '<em>$1</em>');

        // Découpage par blocs
        const blocs = html.split(/\n\s*\n/);
        return blocs.map(bloc => {
            const lignes = bloc.split('\n');
            // Liste à puces
            if (lignes.every(l => /^[-*]\s/.test(l) || l.trim() === '')) {
                const items = lignes.filter(l => l.trim()).map(l => '<li>' + l.replace(/^[-*]\s+/, '') + '</li>');
                return '<ul>' + items.join('') + '</ul>';
            }
            // Liste numérotée
            if (lignes.every(l => /^\d+\.\s/.test(l) || l.trim() === '')) {
                const items = lignes.filter(l => l.trim()).map(l => '<li>' + l.replace(/^\d+\.\s+/, '') + '</li>');
                return '<ol>' + items.join('') + '</ol>';
            }
            // Citation
            if (lignes.every(l => /^&gt;\s/.test(l) || l.trim() === '')) {
                return '<blockquote>' + lignes.filter(l => l.trim()).map(l => l.replace(/^&gt;\s?/, '')).join('<br>') + '</blockquote>';
            }
            // Paragraphe normal
            return '<p>' + lignes.join('<br>') + '</p>';
        }).join('\n');
    }

    previewBtn.addEventListener('click', function () {
        if (zonePreview.classList.contains('hidden')) {
            contenuPreview.innerHTML = rendreMarkdownSimple(textarea.value);
            zonePreview.classList.remove('hidden');
            this.textContent = 'Masquer';
        } else {
            zonePreview.classList.add('hidden');
            this.textContent = 'Aperçu';
        }
    });
})();
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
