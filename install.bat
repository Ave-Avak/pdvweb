@echo off
REM =====================================================================
REM install.bat - Installation automatisée de PDVWeb (Windows)
REM ---------------------------------------------------------------------
REM Script destiné à XAMPP. À exécuter une seule fois.
REM
REM Prérequis :
REM   - XAMPP installé avec Apache + MySQL démarrés
REM   - Le projet copié dans C:\xampp\htdocs\pdvweb\
REM
REM Usage :
REM   cd C:\xampp\htdocs\pdvweb
REM   install.bat
REM =====================================================================

echo.
echo ============================================
echo   Installation PDVWeb
echo ============================================
echo.

REM Détection du chemin MySQL
set MYSQL_BIN="C:\xampp\mysql\bin\mysql.exe"
if not exist %MYSQL_BIN% (
    echo [ERREUR] MySQL introuvable : %MYSQL_BIN%
    echo Vérifiez que XAMPP est installé dans C:\xampp\
    exit /b 1
)

REM Vérifier la connexion MySQL
%MYSQL_BIN% -u root -e "SELECT 1;" > nul 2>&1
if errorlevel 1 (
    echo [ERREUR] Impossible de se connecter à MySQL.
    echo Vérifiez que MySQL est démarré dans XAMPP Control Panel.
    exit /b 1
)
echo [OK] MySQL accessible.

REM Étape 1 : Schéma
echo.
echo [1/5] Création du schéma (34 tables)...
%MYSQL_BIN% -u root < sql\01_schema.sql
if errorlevel 1 (
    echo [ERREUR] Échec création du schéma.
    exit /b 1
)
echo [OK] Schéma créé.

REM Étape 2 : Données de test
echo.
echo [2/5] Insertion des données de test...
%MYSQL_BIN% -u root < sql\02_seed.sql
if errorlevel 1 (
    echo [ATTENTION] Erreur sur les données de test. Le schéma est créé mais sans démo.
) else (
    echo [OK] Données de test insérées.
)

REM Étape 3 : Migration soft delete
echo.
echo [3/5] Migration soft delete (billets/commentaires)...
%MYSQL_BIN% -u root < sql\03_migration_soft_delete.sql
echo [OK] Migration soft delete appliquée.

REM Étape 4 : Fix anonymisation
echo.
echo [4/5] Migration anonymisation RGPD...
%MYSQL_BIN% -u root < sql\04_fix_anonymisation_order.sql
echo [OK] Migration anonymisation appliquée.

REM Étape 5 : Mode livraison
echo.
echo [5/6] Migration mode de livraison...
%MYSQL_BIN% -u root < sql\05_migration_mode_livraison.sql
echo [OK] Migration mode livraison appliquee.

REM Étape 6 : Messagerie privée
echo.
echo [6/7] Migration messagerie privee...
%MYSQL_BIN% -u root < sql\06_migration_mp.sql
echo [OK] Migration messagerie privee appliquee.

REM Étape 7 : Pseudo minichat
echo.
echo [7/9] Migration pseudo minichat...
%MYSQL_BIN% -u root < sql\07_migration_pseudo_minichat.sql
echo [OK] Migration pseudo minichat appliquee.

REM Étape 8 : Résumé + image billets
echo.
echo [8/9] Migration billets enrichis...
%MYSQL_BIN% -u root < sql\08_migration_billet_resume_image.sql
echo [OK] Migration billets resume/image appliquee.

REM Étape 9 : Catégories désactivables
echo.
echo [9/9] Migration categories actives...
%MYSQL_BIN% -u root < sql\09_migration_categorie_actif.sql
echo [OK] Migration categories actives appliquee.

REM Création du dossier uploads/articles s'il n'existe pas
if not exist "public\uploads\articles" (
    mkdir "public\uploads\articles"
    echo [OK] Dossier public\uploads\articles cree.
)

echo.
echo ============================================
echo   Installation terminée avec succès !
echo ============================================
echo.
echo Accédez à l'application :
echo   http://localhost/pdvweb/
echo.
echo Comptes de test :
echo   admin / admin2026
echo   jdupont / test1234
echo   smartin / test1234
echo   mlambert / test1234
echo.
pause
