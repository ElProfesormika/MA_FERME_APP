@echo off
echo ========================================
echo Installation de Composer pour Windows
echo ========================================
echo.

echo 1. Téléchargement de Composer...
powershell -Command "& {Invoke-WebRequest -Uri 'https://getcomposer.org/installer' -OutFile 'composer-setup.php'}"

echo 2. Vérification de l'installateur...
powershell -Command "& {php composer-setup.php --check}"

echo 3. Installation de Composer...
powershell -Command "& {php composer-setup.php --install-dir=C:\wamp64\bin\php\php8.2.18 --filename=composer}"

echo 4. Nettoyage...
del composer-setup.php

echo 5. Test de l'installation...
C:\wamp64\bin\php\php8.2.18\composer --version

echo.
echo ========================================
echo Installation terminée !
echo ========================================
echo.
echo Composer est maintenant installé dans :
echo C:\wamp64\bin\php\php8.2.18\composer
echo.
echo Pour l'utiliser, tapez :
echo C:\wamp64\bin\php\php8.2.18\composer install
echo.
pause 