<?php
/**
 * Configuration d'hébergement - Ferme d'Élevage
 * 
 * Ce fichier contient toutes les configurations nécessaires
 * pour déployer l'application sur différents hébergeurs.
 */

// Configuration de base de données pour différents environnements
$configs = [
    // Configuration locale (développement)
    'local' => [
        'host' => 'localhost',
        'database' => 'ferme_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    
    // Configuration InfinityFree
    'infinityfree' => [
        'host' => 'sql204.infinityfree.com',
        'database' => 'if0_39665291_ferme_ya',
        'username' => 'if0_39665291',
        'password' => 'JPrsDcoxt6DWQ0X',
        'charset' => 'utf8mb4'
    ],
    
    // Configuration 000webhost
    '000webhost' => [
        'host' => 'localhost',
        'database' => 'votre_db_name',
        'username' => 'votre_username',
        'password' => 'votre_password',
        'charset' => 'utf8mb4'
    ],
    
    // Configuration Heroku (PostgreSQL)
    'heroku' => [
        'host' => 'votre_host_heroku',
        'database' => 'votre_db_name',
        'username' => 'votre_username',
        'password' => 'votre_password',
        'charset' => 'utf8'
    ]
];

// Détecter l'environnement automatiquement
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    if (strpos($host, 'infinityfree') !== false) {
        return 'infinityfree';
    } elseif (strpos($host, '000webhost') !== false) {
        return '000webhost';
    } elseif (strpos($host, 'heroku') !== false) {
        return 'heroku';
    } else {
        return 'local';
    }
}

// Obtenir la configuration actuelle
function getCurrentConfig() {
    global $configs;
    $env = detectEnvironment();
    return $configs[$env] ?? $configs['local'];
}

// Configuration de sécurité
$security_config = [
    'session_timeout' => 3600, // 1 heure
    'max_login_attempts' => 5,
    'password_min_length' => 8,
    'csrf_token_expiry' => 1800, // 30 minutes
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    'max_file_size' => 5 * 1024 * 1024, // 5 MB
];

// Configuration des emails
$email_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'votre_email@gmail.com',
    'smtp_password' => 'votre_mot_de_passe_app',
    'from_email' => 'noreply@votreferme.com',
    'from_name' => 'Ferme d\'Élevage'
];

// Configuration des sauvegardes
$backup_config = [
    'auto_backup' => true,
    'backup_frequency' => 'daily', // daily, weekly, monthly
    'backup_retention' => 30, // jours
    'backup_path' => '../backups/',
    'notify_on_backup' => true
];

// Configuration des notifications
$notification_config = [
    'email_alerts' => true,
    'stock_alerts' => true,
    'activity_reminders' => true,
    'weekly_reports' => true,
    'admin_notifications' => true
];

// Fonction pour valider la configuration
function validateConfig($config) {
    $required_fields = ['host', 'database', 'username', 'password', 'charset'];
    
    foreach ($required_fields as $field) {
        if (!isset($config[$field]) || empty($config[$field])) {
            return false;
        }
    }
    
    return true;
}

// Fonction pour tester la connexion
function testConnection($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true, 'message' => 'Connexion réussie'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur de connexion: ' . $e->getMessage()];
    }
}

// Fonction pour générer un fichier de configuration
function generateConfigFile($environment, $config) {
    $content = "<?php\n";
    $content .= "// Configuration générée automatiquement pour $environment\n";
    $content .= "// Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    $content .= "\$config = [\n";
    foreach ($config as $key => $value) {
        $content .= "    '$key' => '$value',\n";
    }
    $content .= "];\n";
    
    return $content;
}

// Fonction pour créer un fichier .htaccess sécurisé
function generateHtaccess() {
    return "
# Protection de sécurité
Options -Indexes
ServerSignature Off

# Protection contre les injections
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Bloquer l'accès aux fichiers sensibles
    RewriteRule ^(config|backup|logs)/ - [F,L]
    
    # Rediriger vers HTTPS si disponible
    RewriteCond %{HTTPS} off
    RewriteCond %{HTTP_HOST} !^localhost
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Headers de sécurité
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"
</IfModule>

# Protection contre les attaques
<IfModule mod_security.c>
    SecRuleEngine On
    SecRequestBodyAccess On
    SecRule REQUEST_HEADERS:Content-Type \"text/xml\" \
        \"id:'200000',phase:1,t:none,t:lowercase,pass,nolog,ctl:requestBodyProcessor=XML\"
</IfModule>
";
}

// Fonction pour créer un script de sauvegarde
function generateBackupScript() {
    return '#!/bin/bash
# Script de sauvegarde automatique - Ferme d\'Élevage

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="../backups"
DB_NAME="ferme_db"
DB_USER="root"
DB_PASS=""

# Créer le dossier de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde de la base de données
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Sauvegarde des fichiers
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz public/

# Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Sauvegarde terminée: $DATE"
';
}

// Fonction pour afficher les informations de configuration
function displayConfigInfo() {
    $config = getCurrentConfig();
    $env = detectEnvironment();
    
    echo "<div class='alert alert-info'>";
    echo "<h5>Informations de configuration</h5>";
    echo "<p><strong>Environnement détecté :</strong> $env</p>";
    echo "<p><strong>Host :</strong> {$config['host']}</p>";
    echo "<p><strong>Base de données :</strong> {$config['database']}</p>";
    echo "<p><strong>Utilisateur :</strong> {$config['username']}</p>";
    echo "</div>";
}

// Fonction pour afficher les étapes de déploiement
function displayDeploymentSteps() {
    echo "<div class='alert alert-warning'>";
    echo "<h5>Étapes de déploiement</h5>";
    echo "<ol>";
    echo "<li>Modifier les paramètres de base de données dans ce fichier</li>";
    echo "<li>Uploader tous les fichiers sur l'hébergeur</li>";
    echo "<li>Créer la base de données et importer les données</li>";
    echo "<li>Tester toutes les fonctionnalités</li>";
    echo "<li>Configurer les sauvegardes automatiques</li>";
    echo "<li>Ajouter les utilisateurs administrateurs</li>";
    echo "</ol>";
    echo "</div>";
}

// Afficher les informations si ce fichier est appelé directement
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    echo "<h1>Configuration d'hébergement - Ferme d'Élevage</h1>";
    displayConfigInfo();
    displayDeploymentSteps();
    
    echo "<h3>Test de connexion</h3>";
    $config = getCurrentConfig();
    $test = testConnection($config);
    
    if ($test['success']) {
        echo "<div class='alert alert-success'>{$test['message']}</div>";
    } else {
        echo "<div class='alert alert-danger'>{$test['message']}</div>";
    }
}
?> 