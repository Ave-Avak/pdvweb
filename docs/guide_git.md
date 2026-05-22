# Guide Git pour PDVWeb

Ce document vous accompagne pour publier le projet sur GitHub et faire évoluer le repo proprement au fil des étapes.

---

## 1. Création du repo GitHub

1. Allez sur [github.com](https://github.com) → connectez-vous (créez un compte si besoin).
2. Cliquez sur le bouton **"+"** en haut à droite → **"New repository"**.
3. Remplissez :
   - **Repository name** : `pdvweb`
   - **Description** : `Plateforme e-commerce dynamique - TFM PRDW 2025-2026`
   - **Public** ou **Private** : à vous de voir. Public = portfolio public ; Private = vous montrez à qui vous voulez.
   - **NE PAS cocher** "Add a README" / "Add .gitignore" / "Choose a license" — on les a déjà
4. Cliquez sur **Create repository**.

GitHub vous donne ensuite une URL du type `https://github.com/votre-username/pdvweb.git`. **Gardez-la sous le coude.**

---

## 2. Installation de Git en local (si pas déjà fait)

### Windows
Téléchargez Git for Windows : https://git-scm.com/download/win
Installez avec les options par défaut.

### Vérification
Ouvrez un terminal (cmd, PowerShell, ou Git Bash) :
```bash
git --version
```
Vous devriez voir une version (par ex. `git version 2.43.0`).

---

## 3. Configuration initiale Git (une seule fois)

Dans un terminal :
```bash
git config --global user.name "Votre Nom"
git config --global user.email "votre.email@example.com"
```

(Utilisez **le même email que votre compte GitHub** pour que vos commits soient bien attribués.)

---

## 4. Premier push : envoyer l'étape 1 sur GitHub

Placez-vous dans le dossier du projet :
```bash
cd chemin/vers/pdvweb
```

Initialisez le dépôt Git :
```bash
git init
git branch -M main
```

Ajoutez tous les fichiers :
```bash
git add .
```

Vérifiez ce qui sera commité :
```bash
git status
```

Faites votre premier commit :
```bash
git commit -m "feat: étape 1 - architecture et schéma BDD 34 tables"
```

Liez le repo local au repo GitHub :
```bash
git remote add origin https://github.com/votre-username/pdvweb.git
```

Et envoyez :
```bash
git push -u origin main
```

GitHub vous demandera vos identifiants. Sur Windows moderne, c'est généralement via une fenêtre de connexion à votre compte GitHub. Sur Mac/Linux, vous devrez peut-être créer un *Personal Access Token* (à la place du mot de passe) : https://github.com/settings/tokens

Rechargez votre page GitHub : vos fichiers doivent apparaître.

---

## 5. Workflow par étape

À chaque fois qu'on termine une étape, vous ferez :

```bash
# 1. Voir ce qui a changé
git status

# 2. Ajouter les modifications
git add .

# 3. Commit avec un message clair (en français OK)
git commit -m "feat: étape 2 - socle commun (classes Db, Auth, Csrf, Flash)"

# 4. Envoyer sur GitHub
git push
```

## 6. Conventions de messages de commit

Pour avoir un historique propre et lisible, je vous propose la convention "Conventional Commits" simplifiée :

| Préfixe | Pour |
|---|---|
| `feat:` | Nouvelle fonctionnalité |
| `fix:` | Correction de bug |
| `docs:` | Documentation seulement |
| `style:` | Mise en forme, CSS |
| `refactor:` | Refonte de code sans changement fonctionnel |
| `security:` | Amélioration de sécurité |
| `chore:` | Tâches diverses (config, dépendances) |

**Exemples** :
```
feat: étape 3 - inscription, connexion et profil membre
fix: validation du formulaire d'inscription
docs: mise à jour README avec captures d'écran
security: ajout protection CSRF sur tous les formulaires
refactor: extraction de la logique panier dans classes/Panier.php
chore: ajout fichier .htaccess pour le dossier public
```

---

## 7. Plan de commits suggéré pour le rendu

Pour montrer une progression réaliste au prof, voici une suggestion de répartition :

1. `feat: étape 1 - architecture et schéma BDD 34 tables`
2. `chore: structure de dossiers complète`
3. `feat: étape 2 - config et classes utilitaires (Db, Auth, Csrf, Flash, Upload)`
4. `feat: étape 2 - header, footer, helpers et bootstrap`
5. `style: intégration de Tailwind CSS`
6. `feat: étape 3 - inscription et connexion`
7. `feat: étape 3 - modification du profil avec upload avatar`
8. `feat: étape 4 - mini-chat`
9. `feat: étape 5 - blog avec billets et commentaires`
10. `feat: étape 5 - moteur de recherche dans le blog`
11. `feat: étape 5 - tags et likes sur billets`
12. `feat: étape 6 - catalogue d'articles`
13. `feat: étape 6 - panier d'achat persistant`
14. `feat: étape 6 - validation de commande et historique`
15. `feat: étape 6 - codes promo et frais de port`
16. `feat: étape 6 - favoris et notes articles`
17. `feat: étape 7 - tableau de bord administrateur`
18. `feat: étape 7 - gestion des membres (blocage, consultation)`
19. `feat: étape 7 - gestion des articles et stock`
20. `feat: étape 7 - statistiques admin`
21. `security: audit log et anti brute-force`
22. `security: vérification CSRF sur tous les formulaires`
23. `feat: notifications internes`
24. `feat: messages privés`
25. `docs: captures d'écran et manuel utilisateur`
26. `docs: rapport final et schéma ERD`

26 commits étalés sur ~3 semaines = ~1-2 commits par jour. Crédible et professionnel.

---

## 8. Tags / versions

À chaque grande étape terminée, créez un tag :

```bash
git tag -a v0.1 -m "Étape 1 terminée : schéma BDD"
git push origin v0.1

git tag -a v0.2 -m "Étape 2 terminée : socle commun"
git push origin v0.2

# ... et ainsi de suite jusqu'à v1.0 (rendu final)
```

Sur GitHub, les tags apparaissent dans la section "Releases". Ça donne une allure très pro.

---

## 9. Si vous bloquez

Quelques commandes utiles :

```bash
# Annuler un commit pas encore poussé (mais garder les modifs)
git reset --soft HEAD~1

# Voir l'historique
git log --oneline

# Voir les différences depuis le dernier commit
git diff

# Récupérer la dernière version du repo (utile si vous travaillez sur 2 machines)
git pull
```

Et si vous voulez juste recommencer proprement, dites-le moi, je vous guiderai.

---

## 10. Lien dans le rendu final

Le **13 juin**, dans le mail au prof, vous pourrez inclure :
- Le ZIP du projet (consigne officielle)
- **Et** le lien vers le repo GitHub (en bonus, montre un historique propre)

Exemple : *"Vous trouverez ci-joint l'archive du projet. Le repo GitHub est également disponible ici pour consulter l'historique des commits : https://github.com/votre-username/pdvweb"*

Effet pro garanti.
