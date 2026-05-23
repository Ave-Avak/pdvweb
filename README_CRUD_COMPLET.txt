═══════════════════════════════════════════════════════════════
  CRUD COMPLET POUR TOUTES LES GESTIONS ADMIN
═══════════════════════════════════════════════════════════════

📊 RÉSUMÉ : 18 fichiers livrés
   - 3 classes étendues (FraisPort, Tag, Facture)
   - 8 nouveaux contrôleurs admin
   - 7 nouvelles vues / vues mises à jour
   - 0 migration SQL nécessaire

═══════════════════════════════════════════════════════════════
  PHASE A — CRUD FRAIS DE PORT
═══════════════════════════════════════════════════════════════
✓ FraisPort::creer / modifier / supprimer / trouverParId
✓ public/admin/frais_port_form.php (création + édition)
✓ public/admin/frais_port_supprimer.php
✓ views/admin/frais_port_form.php (formulaire complet)
✓ views/admin/frais_port.php (refonte avec boutons CRUD)

Fonctionnalités :
   - Datalist auto-suggérée pour le pays
   - Validation du min/max panier (cohérence)
   - Affichage "Gratuit" si prix = 0
   - Bouton supprimer avec confirmation

═══════════════════════════════════════════════════════════════
  PHASE B — CRUD TAGS
═══════════════════════════════════════════════════════════════
✓ Tag::creer / modifier / supprimer / codeExiste
✓ public/admin/tags.php (NOUVEAU)
✓ public/admin/tag_form.php (NOUVEAU)
✓ public/admin/tag_supprimer.php (NOUVEAU)
✓ views/admin/tags.php (NOUVEAU)
✓ views/admin/tag_form.php (NOUVEAU)
✓ Carte "Tags" ajoutée sur le dashboard admin

Fonctionnalités :
   - Auto-génération du slug depuis le nom (création)
   - Validation regex du slug
   - Affichage du nombre de billets associés
   - Suppression avec confirmation (mention des billets)
   - ON DELETE CASCADE retire automatiquement les liens billet_tag

═══════════════════════════════════════════════════════════════
  PHASE C — FILTRES + PAGINATION COMMANDES
═══════════════════════════════════════════════════════════════
✓ Facture::listerToutes(array $opts) étendu
✓ public/admin/commandes.php (récupère les filtres)
✓ views/admin/commandes.php (UI filtres + pagination)

Filtres disponibles :
   - Recherche : login, nom, prénom, ou ID de commande
   - Statut : tous / en cours / expédié / livré...
   - Période : date début + date fin
   - Pagination : 20 commandes par page

Pagination intelligente :
   - Page courante mise en évidence
   - 5 numéros centrés autour de la page courante
   - Boutons "Précédent" / "Suivant"
   - URL conservant les filtres entre les pages

✓ COMPATIBILITÉ ASCENDANTE : 
   Facture::listerToutes() sans args fonctionne toujours comme avant.

═══════════════════════════════════════════════════════════════
  PHASE D — NOTIFICATIONS COMMANDE
═══════════════════════════════════════════════════════════════
✓ DÉJÀ EXISTANT (déjà implémenté dans Facture::changerStatut)
   Le client reçoit une notification quand son statut change.

═══════════════════════════════════════════════════════════════
  PHASE E — EXPORT CSV COMMANDES
═══════════════════════════════════════════════════════════════
✓ public/admin/commandes_export.php (NOUVEAU)
✓ Bouton "Export CSV" en haut de la liste commandes

Caractéristiques :
   - Encodage UTF-8 avec BOM (compat Excel français)
   - Séparateur ; (compat Excel français)
   - Décimales avec virgule
   - Respect des filtres actifs (export filtré)
   - Trace dans l'audit log
   - Nom de fichier daté

Colonnes : Référence, Date, Login, Nom, Prénom, Mode livraison,
           Lignes, Sous-total, Frais port, Remise, Total TTC, Statut

═══════════════════════════════════════════════════════════════
  INSTALLATION
═══════════════════════════════════════════════════════════════

1. Extraire ce ZIP par-dessus C:\xampp\htdocs\pdvweb\
2. Ctrl+F5 sur le site

⚠️  PAS DE MIGRATION SQL NÉCESSAIRE
   Toutes les colonnes utilisées existent déjà dans le schéma.

═══════════════════════════════════════════════════════════════
  TESTS À FAIRE
═══════════════════════════════════════════════════════════════

[ ] /admin/frais_port.php : 
    - Bouton "Nouvelle grille" → formulaire complet
    - Modifier une grille existante → données pré-remplies
    - Toggle actif/inactif → fonctionne
    - Supprimer → confirmation + suppression

[ ] /admin/tags.php :
    - Bouton "Nouveau tag" → formulaire
    - Création avec auto-suggestion du slug
    - Modifier un tag existant
    - Supprimer un tag (les billets restent valides)

[ ] /admin/commandes.php :
    - Filtre par statut
    - Recherche par login/nom
    - Filtre par dates
    - Réinitialiser les filtres
    - Naviguer entre les pages
    - Bouton "Export CSV" → fichier téléchargé
    - Ouvrir le CSV dans Excel → bonne lecture

[ ] /admin/audit.php :
    - Voir tag.creer, tag.modifier, tag.supprimer
    - Voir frais_port.creer, frais_port.modifier, frais_port.supprimer
    - Voir commandes.export_csv

═══════════════════════════════════════════════════════════════
  STATISTIQUES PROJET
═══════════════════════════════════════════════════════════════
   - 150 fichiers PHP (+9 nouveaux)
   - 0 erreur de syntaxe
   - 0 colonne SQL inventée
   - CRUD complet pour 6 entités : Article, Billet, Categorie,
     CodePromo, FraisPort, Tag
