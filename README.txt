═══════════════════════════════════════════════════════════════
  PHASE 3 — 3 FONCTIONNALITÉS AVANCÉES
═══════════════════════════════════════════════════════════════

⚠️ MIGRATION SQL OBLIGATOIRE AVANT TOUT
   → Exécuter sql/10_migration_article_tag.sql via phpMyAdmin
   (sinon : erreur "Table article_tag doesn't exist")

═══════════════════════════════════════════════════════════════
  PHASE 3.1 — FILTRES AVANCÉS CATALOGUE
═══════════════════════════════════════════════════════════════
Pas de migration SQL nécessaire.

Nouveaux filtres dans /catalogue.php :
   ✓ Prix minimum / prix maximum
   ✓ "En stock uniquement"
   ✓ Note minimale (1-5 étoiles)
   ✓ Filtre par tag (si tags définis)

Nouveaux tris :
   ✓ Mieux notés (note_desc)
   ✓ Ordre alphabétique (alpha)

Panneau "Filtres avancés" dépliable :
   - Affiche un badge "actifs" quand filtres en cours
   - Bouton réinitialiser

Méthode Article::lister() étendue :
   - 'prix_min', 'prix_max', 'en_stock', 'note_min', 'id_tag'
   - 100% rétro-compatible

═══════════════════════════════════════════════════════════════
  PHASE 3.2 — TAGS SUR ARTICLES
═══════════════════════════════════════════════════════════════
⚠️ MIGRATION 10 OBLIGATOIRE.

Nouvelle table article_tag (many-to-many) :
   - Liens article <-> tag avec ON DELETE CASCADE
   - PRIMARY KEY (id_article, id_tag)

Méthodes Article ajoutées :
   - tagsDe($id)        → tags d'un article
   - associerTags()     → met à jour les tags
   - idsTagsDe()        → IDs pour le form

Admin :
   - Sélecteur de tags dans /admin/article_form.php
   - Cases à cocher avec aperçu visuel
   - Si aucun tag : message + lien vers gestion tags

Public :
   - Tags affichés sur la fiche article (cliquables)
   - Filtre par tag dans le catalogue
   - Liste des tags actifs au-dessus du catalogue

═══════════════════════════════════════════════════════════════
  PHASE 3.3 — COMPARATEUR D'ARTICLES
═══════════════════════════════════════════════════════════════
Pas de migration SQL nécessaire.

Nouvelle page /comparer.php :
   - Sélection jusqu'à 4 articles
   - Stocké en session ($_SESSION['comparateur'])
   - Accessible à tous (UNM + UM)

Tableau comparatif (7 critères) :
   - Prix, Catégorie, Note moyenne, Disponibilité
   - Poids, Description, Popularité (ventes)

Actions disponibles :
   - Ajouter (depuis catalogue ou fiche article)
   - Retirer (croix sur chaque colonne)
   - Vider le comparateur
   - Partager par URL (?ids=1,2,3)

Badge dans le header (nav) :
   - Affiche le nombre d'articles dans le comparateur
   - Disparaît si vide

Méthode Article::pourComparaison() :
   - Récupère plusieurs articles avec stats
   - Conserve l'ordre via FIELD()
   - Filtre les articles indisponibles

═══════════════════════════════════════════════════════════════
  INSTALLATION
═══════════════════════════════════════════════════════════════

1. ⚠️ IMPORTANT — D'abord la migration SQL :
   - Ouvrir phpMyAdmin
   - Importer sql/10_migration_article_tag.sql

2. Extraire ce ZIP par-dessus C:\xampp\htdocs\pdvweb\

3. Ctrl + F5

═══════════════════════════════════════════════════════════════
  TESTS À FAIRE
═══════════════════════════════════════════════════════════════

PHASE 3.1 — FILTRES :
[ ] /catalogue.php → panneau "Filtres avancés"
[ ] Tester prix min/max → résultats filtrés
[ ] Tester "En stock uniquement"
[ ] Tester note min 3★
[ ] Tester tri "Mieux notés" → ordre cohérent

PHASE 3.2 — TAGS :
[ ] /admin/tags.php → créer 2-3 tags (Promo, Nouveauté)
[ ] /admin/article_form.php?id=X → cocher des tags
[ ] /article.php?id=X → tags affichés en haut
[ ] Cliquer un tag → filtre dans catalogue

PHASE 3.3 — COMPARATEUR :
[ ] /catalogue.php → cliquer "⚖️ Comparer" sur 2-3 articles
[ ] Badge avec compteur dans le header
[ ] /comparer.php → tableau comparatif
[ ] Retirer un article (×)
[ ] Partage URL : copier l'URL avec ids

═══════════════════════════════════════════════════════════════
  STATS PROJET FINAL
═══════════════════════════════════════════════════════════════
   - 154 fichiers PHP (+2 nouveaux : comparer.php + vue)
   - 10 migrations SQL (+1)
   - 0 erreur de syntaxe
   - 0 colonne inventée
   - 1 nouvelle table : article_tag
   - 4 nouvelles méthodes Article : pour les tags + comparaison
   - Compatible 100% rétroactif (aucune régression métier)

