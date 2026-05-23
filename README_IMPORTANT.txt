═══════════════════════════════════════════════════════════════
  PHASE 1 — PARTIE 1/2 : Ajouts de fonctionnalités
═══════════════════════════════════════════════════════════════

⚠️  IMPORTANT : EXÉCUTEZ D'ABORD LES MIGRATIONS SQL !
   Sinon vous aurez des erreurs "Unknown column..."

═══════════════════════════════════════════════════════════════
  1. MIGRATIONS À APPLIQUER (dans cet ordre)
═══════════════════════════════════════════════════════════════

Méthode phpMyAdmin (recommandée) :
  → http://localhost/phpmyadmin/
  → base 'pdvweb' → onglet "Importer"
  → exécuter dans l'ordre :
    1) sql/08_migration_billet_resume_image.sql
    2) sql/09_migration_categorie_actif.sql

Méthode ligne de commande :
  cd C:\xampp\htdocs\pdvweb
  C:\xampp\mysql\bin\mysql.exe -u root pdvweb < sql\08_migration_billet_resume_image.sql
  C:\xampp\mysql\bin\mysql.exe -u root pdvweb < sql\09_migration_categorie_actif.sql

═══════════════════════════════════════════════════════════════
  2. CONTENU DE CETTE PARTIE
═══════════════════════════════════════════════════════════════

✅ PHASE 1.1 — Billets avec résumé + image
   - Migration 08 : ajout colonnes resume + image à billet
   - Admin peut maintenant :
     * Saisir un résumé court (max 500 caractères)
     * Uploader une image illustrative (.gif/.jpeg, 2 Mo max)
     * Remplacer ou supprimer l'image
   - Affichage public :
     * Accueil → image + résumé dans les coups de cœur
     * Liste blog → image en bandeau + résumé
     * Détail billet → image en hero
   - Compatibilité ascendante : aucun billet existant cassé

✅ PHASE 1.2 — Catégories désactivables
   - Migration 09 : ajout colonne actif à categorie
   - Méthode Categorie::listerActives() ajoutée
   - Méthode Categorie::basculerActif() pour toggle rapide
   - À utiliser depuis la page admin/categories.php

✅ PHASE 1.3 — Mentions légales / RGPD
   - NOUVEAU /public/mentions_legales.php
   - Page complète conforme RGPD belge
   - Lien dans le footer du site
   - 8 sections : éditeur, hébergement, données, droits,
     cookies, sécurité, propriété, contact

═══════════════════════════════════════════════════════════════
  3. TESTS À FAIRE
═══════════════════════════════════════════════════════════════

[ ] Connexion admin → /admin/billet_form.php
    → Champs "Résumé" et "Image illustrative" visibles
    → Créer un billet avec image (jpg/gif < 2 Mo)
[ ] Voir le billet sur l'accueil → image visible
[ ] Voir sur /blog.php → image en bandeau
[ ] Voir le détail → image hero en haut
[ ] /mentions_legales.php → page lisible avec sommaire
[ ] Footer → lien "Mentions légales & RGPD" présent

═══════════════════════════════════════════════════════════════
  4. PROCHAINES PARTIES
═══════════════════════════════════════════════════════════════

PARTIE 2/2 (à venir) :
  - Phase 1.4 : Newsletter (branchement table existante)
  - Phase 2.1-2.4 : Polish UI/UX
  - Phase 3.1-3.3 : Filtres avancés, comparateur, tags

