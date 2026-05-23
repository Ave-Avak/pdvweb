═══════════════════════════════════════════════════════════════
  ADMIN — 4 NOUVELLES ACTIONS SUR LES MEMBRES
═══════════════════════════════════════════════════════════════

📊 Fonctionnalités ajoutées :

✅ 1. MODIFIER LES DONNÉES D'UN MEMBRE
   - Bouton "Modifier" sur la fiche membre
   - Nouvelle page /admin/membre_form.php
   - Champs modifiables : nom, prénom, date de naissance,
     email, login, état de vérification email
   - Validation complète : email valide, unicité login/email,
     âge >= 13 ans, regex login

✅ 2. RÉINITIALISER LE MOT DE PASSE
   - Bouton "Reset mdp" sur la fiche membre
   - Génère un mdp temporaire fort (12 caractères, sans
     caractères ambigus)
   - Affiché à l'admin avec bouton "Copier"
   - L'admin communique au membre par un canal sûr
   - Stocké 5 minutes max en session, jamais en BDD en clair

✅ 3. ANONYMISATION RGPD
   - Bouton "Anonymiser" en bas à droite (en rouge)
   - Modale de confirmation forte : il faut RETAPER le login
     du membre pour confirmer (évite les erreurs)
   - Liste exhaustive des conséquences affichée
   - Action IRRÉVERSIBLE
   - Conforme RGPD (article 17 - droit à l'oubli)

✅ 4. VÉRIFIER L'EMAIL MANUELLEMENT
   - Bouton "Vérifier email" (uniquement si non vérifié)
   - Utile en support utilisateur

═══════════════════════════════════════════════════════════════
  AUDIT LOG
═══════════════════════════════════════════════════════════════

Nouvelles actions tracées :
   - membre.admin_modifier
   - membre.admin_reset_mdp
   - membre.anonymiser
   - membre.email_verifie_admin

(en plus des bloquer/debloquer/promouvoir/degrader existantes)

═══════════════════════════════════════════════════════════════
  SÉCURITÉ
═══════════════════════════════════════════════════════════════

✓ Admin ne peut PAS s'auto-modifier (anti-bricolage)
✓ Compte anonymisé : aucune action possible
✓ Confirmation forte pour l'anonymisation (taper le login)
✓ Mdp temporaire en session (5 min max, jamais persistant)
✓ Toutes les actions en POST + CSRF
✓ Tout est tracé dans audit_log

═══════════════════════════════════════════════════════════════
  INSTALLATION
═══════════════════════════════════════════════════════════════

1. Extraire ce ZIP par-dessus C:\xampp\htdocs\pdvweb\
2. Ctrl + F5

Pas de migration SQL nécessaire (toutes les colonnes existent).

═══════════════════════════════════════════════════════════════
  TESTS À FAIRE
═══════════════════════════════════════════════════════════════

[ ] /admin/membres.php → cliquer sur un membre
[ ] Bouton "Modifier" → page d'édition → modifier le nom
[ ] Bouton "Reset mdp" → mdp temporaire affiché
[ ] Bouton "Vérifier email" → email passé à vérifié
[ ] Bouton "Anonymiser" → modale s'ouvre
[ ] Taper un mauvais login → erreur
[ ] Taper le bon login → anonymisation effectuée
[ ] /admin/audit.php → toutes les actions visibles

═══════════════════════════════════════════════════════════════
  POINTS DE VIGILANCE POUR LA DÉFENSE ORALE
═══════════════════════════════════════════════════════════════

🎯 Le mdp temporaire affiché à l'admin est un compromis :
   En production, on enverrait un lien de reset par email.
   Sans SMTP, on affiche le mdp temporaire à l'admin qui doit
   le communiquer au membre par un canal sûr. C'est la pratique
   courante quand pas d'envoi email automatique.

🎯 L'anonymisation est conforme RGPD :
   - Article 17 (droit à l'oubli) : ✓
   - Données personnelles supprimées (pseudonymisation forte)
   - Données comptables conservées (obligation légale belge)
   - Conséquences listées clairement à l'admin
   - Confirmation forte (anti-erreur)

🎯 La modification admin est tracée dans audit_log
   pour conformité RGPD (traçabilité des modifications).

