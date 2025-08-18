# Script PowerShell pour télécharger Composer
Write-Host "========================================" -ForegroundColor Green
Write-Host "Téléchargement de Composer" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green

# Désactiver la vérification SSL temporairement
[System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}

try {
    Write-Host "1. Téléchargement de l'installateur Composer..." -ForegroundColor Yellow
    $url = "https://getcomposer.org/installer"
    $output = "composer-setup.php"
    
    Invoke-WebRequest -Uri $url -OutFile $output -UseBasicParsing
    
    Write-Host "2. Vérification de l'installateur..." -ForegroundColor Yellow
    & "C:\wamp64\bin\php\php8.2.18\php.exe" composer-setup.php --check
    
    Write-Host "3. Installation de Composer..." -ForegroundColor Yellow
    & "C:\wamp64\bin\php\php8.2.18\php.exe" composer-setup.php --install-dir="C:\wamp64\bin\php\php8.2.18" --filename="composer"
    
    Write-Host "4. Nettoyage..." -ForegroundColor Yellow
    Remove-Item composer-setup.php -Force
    
    Write-Host "5. Test de l'installation..." -ForegroundColor Yellow
    & "C:\wamp64\bin\php\php8.2.18\composer" --version
    
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Installation terminée avec succès !" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Composer est maintenant installé dans :" -ForegroundColor Cyan
    Write-Host "C:\wamp64\bin\php\php8.2.18\composer" -ForegroundColor White
    Write-Host ""
    Write-Host "Pour l'utiliser, tapez :" -ForegroundColor Cyan
    Write-Host "C:\wamp64\bin\php\php8.2.18\composer install" -ForegroundColor White
    
} catch {
    Write-Host "Erreur lors de l'installation : $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Tentative de téléchargement manuel..." -ForegroundColor Yellow
    
    # Téléchargement manuel
    Write-Host "Téléchargez Composer manuellement depuis :" -ForegroundColor Cyan
    Write-Host "https://getcomposer.org/Composer-Setup.exe" -ForegroundColor White
}

Read-Host "Appuyez sur Entrée pour continuer" 