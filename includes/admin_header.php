<?php
/**
 * includes/admin_header.php
 * ---------------------------------------------------------------------
 * Fragment commun aux pages d'administration.
 * À inclure APRÈS le header.php standard et AVANT le contenu de la page.
 *
 * Variables utilisables (optionnelles) :
 *   - $admin_titre        string  Titre de la page admin
 *   - $admin_sous_titre   string  Sous-titre / description
 *   - $admin_breadcrumb   array   Items du fil d'ariane : [['url' => '/x', 'label' => 'X'], ...]
 *   - $admin_actions      string  HTML libre (ex: bouton "Nouveau...")
 *
 * Toutes ces variables sont optionnelles.
 * ---------------------------------------------------------------------
 */
?>

<!-- Bannière administration : identité visuelle distincte -->
<div class="mb-6 -mx-4 px-4 py-4 bg-gradient-to-r from-primary-900 via-primary-800 to-primary-900 text-white shadow-soft">
    <div class="container mx-auto">
        <!-- Breadcrumb compact -->
        <nav class="flex items-center gap-2 text-xs text-primary-200 mb-2" aria-label="Fil d'Ariane">
            <a href="<?= url('/admin/') ?>" class="hover:text-white transition">
                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </a>
            <span class="text-primary-400">/</span>
            <a href="<?= url('/admin/') ?>" class="hover:text-white transition">Administration</a>
            <?php if (!empty($admin_breadcrumb) && is_array($admin_breadcrumb)): ?>
                <?php foreach ($admin_breadcrumb as $item): ?>
                    <span class="text-primary-400">/</span>
                    <?php if (!empty($item['url'])): ?>
                        <a href="<?= h($item['url']) ?>" class="hover:text-white transition">
                            <?= h($item['label']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-white"><?= h($item['label']) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

        <div class="flex items-start justify-between gap-3 flex-wrap">
            <!-- Titre + sous-titre -->
            <div class="min-w-0">
                <h1 class="text-2xl md:text-3xl font-bold flex items-center gap-2">
                    <span class="inline-flex w-8 h-8 bg-warning-400 text-warning-900 rounded-lg items-center justify-center text-sm font-black">
                        ⚙
                    </span>
                    <?= h($admin_titre ?? 'Administration') ?>
                </h1>
                <?php if (!empty($admin_sous_titre)): ?>
                    <p class="text-sm text-primary-200 mt-1"><?= h($admin_sous_titre) ?></p>
                <?php endif; ?>
            </div>

            <!-- Actions custom -->
            <?php if (!empty($admin_actions)): ?>
                <div class="flex-shrink-0">
                    <?= $admin_actions ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
