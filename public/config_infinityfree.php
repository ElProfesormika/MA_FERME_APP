<?php
// Configuration sécurisée pour InfinityFree
// Les informations de base de données sont dans un fichier séparé

// Configuration de l'application
$APP_NAME = "Ma FERME D'ÉLEVAGE";
$APP_SHORT_NAME = "BuildNovaG";
$APP_SLOGAN = "Système de gestion complet pour votre exploitation agricole";

$APP_FOOTER_COLUMNS = [
    [
        'title' => '© ' . date('Y') . " Ferme d'Élevage",
        'lines' => ['Tous droits réservés']
    ],
    [
        'title' => 'Système de Gestion',
        'lines' => ["Solution complète pour l'élevage"]
    ],
    [
        'title' => 'Support Technique',
        'lines' => ['Assistance et maintenance']
    ]
];

$APP_LEGAL_NOTICE = "Ce logiciel est protégé par les lois sur la propriété intellectuelle. Toute reproduction ou distribution non autorisée est strictement interdite.#Ismaila.YABRE";

function app_name(): string { global $APP_NAME; return $APP_NAME; }
function app_short_name(): string { global $APP_SHORT_NAME; return $APP_SHORT_NAME; }
function app_slogan(): string { global $APP_SLOGAN; return $APP_SLOGAN; }
function app_footer_columns(): array { global $APP_FOOTER_COLUMNS; return $APP_FOOTER_COLUMNS; }
function app_legal_notice(): string { global $APP_LEGAL_NOTICE; return $APP_LEGAL_NOTICE; }

// Configuration des devises
$DEVISE_CONFIG = [
    'FCFA' => [
        'nom' => 'Franc CFA',
        'symbole' => 'FCFA',
        'position' => 'after',
        'separateur' => ' ',
        'decimales' => 0,
        'taux_euro' => 655.957,
        'taux_usd' => 588.95,
        'couleur' => 'success'
    ],
    'EUR' => [
        'nom' => 'Euro',
        'symbole' => '€',
        'position' => 'before',
        'separateur' => ' ',
        'decimales' => 2,
        'taux_fcfa' => 0.0015,
        'taux_usd' => 1.09,
        'couleur' => 'primary'
    ],
    'USD' => [
        'nom' => 'Dollar US',
        'symbole' => '$',
        'position' => 'before',
        'separateur' => ' ',
        'decimales' => 2,
        'taux_fcfa' => 0.0017,
        'taux_eur' => 0.92,
        'couleur' => 'info'
    ]
];

// Fonction pour obtenir la configuration de la base de données
function getDBConfig() {
    // Priorité à Railway si les variables d'environnement sont présentes
    if (isset($_ENV['MYSQLHOST']) || isset($_ENV['MYSQL_HOST'])) {
        return [
            'host' => $_ENV['MYSQLHOST'] ?? $_ENV['MYSQL_HOST'] ?? 'localhost',
            'database' => $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? 'railway',
            'username' => $_ENV['MYSQL_USERNAME'] ?? $_ENV['MYSQLUSER'] ?? 'root',
            'password' => $_ENV['MYSQL_ROOT_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '',
            'charset' => 'utf8mb4'
        ];
    }
    
    // Fallback vers le fichier local
    $config_file = __DIR__ . '/db_config.php';
    if (file_exists($config_file)) {
        return include $config_file;
    }
    return [
        'host' => 'localhost',
        'database' => 'ferme_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
}

// Fonction de connexion à la base de données
function connectDB() {
    $config = getDBConfig();
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion DB: " . $e->getMessage());
        return false;
    }
}

// Fonction pour obtenir les statistiques rapides
function getQuickStats() {
    $db = connectDB();
    if (!$db) return [];
    
    $stats = [];
    
    try {
        // Animaux
        $stmt = $db->query("SELECT COUNT(*) as total FROM animaux");
        $stats['animaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Stocks en rupture
        $stmt = $db->query("SELECT COUNT(*) as total FROM stocks WHERE quantite <= 10");
        $stats['rupture'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Activités aujourd'hui
        $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE date = CURDATE()");
        $stats['activites_aujourdhui'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Alertes actives
        $stmt = $db->query("SELECT COUNT(*) as total FROM alertes WHERE statut = 'active'");
        $stats['alertes_actives'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        error_log("Erreur stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Fonction pour formater un montant selon la devise
function formaterMontant($montant, $devise = 'FCFA') {
    global $DEVISE_CONFIG;
    
    if (!isset($DEVISE_CONFIG[$devise])) {
        $devise = 'FCFA';
    }
    
    $config = $DEVISE_CONFIG[$devise];
    $montant_formate = number_format($montant, $config['decimales'], ',', ' ');
    
    if ($config['position'] === 'before') {
        return $config['symbole'] . $config['separateur'] . $montant_formate;
    } else {
        return $montant_formate . $config['separateur'] . $config['symbole'];
    }
}

// Fonction pour obtenir la devise actuelle
function getDeviseActuelle() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['devise'])) {
        return $_SESSION['devise'];
    } elseif (isset($_COOKIE['devise'])) {
        return $_COOKIE['devise'];
    }
    
    return 'FCFA'; // Devise par défaut
}

// Fonction pour changer la devise
function setDeviseActuelle($devise) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['devise'] = $devise;
    setcookie('devise', $devise, time() + (86400 * 30), '/'); // 30 jours
}

// Fonction pour convertir un montant d'une devise à une autre
function convertirDevise($montant, $devise_source = 'FCFA', $devise_cible = 'FCFA') {
    global $DEVISE_CONFIG;
    
    if ($devise_source === $devise_cible) {
        return $montant;
    }
    
    if (!isset($DEVISE_CONFIG[$devise_source]) || !isset($DEVISE_CONFIG[$devise_cible])) {
        return $montant; // Retourne le montant original si devise non reconnue
    }
    
    // Conversion via FCFA comme devise de référence
    if ($devise_source === 'FCFA') {
        if ($devise_cible === 'EUR') {
            return $montant * 0.0015; // 1 EUR = 655.957 FCFA, donc 1 FCFA = 0.0015 EUR
        } elseif ($devise_cible === 'USD') {
            return $montant * 0.0017; // 1 USD = 588.95 FCFA, donc 1 FCFA = 0.0017 USD
        }
    } elseif ($devise_source === 'EUR') {
        if ($devise_cible === 'FCFA') {
            return $montant * 655.957; // 1 EUR = 655.957 FCFA
        } elseif ($devise_cible === 'USD') {
            return $montant * 1.09; // 1 EUR = 1.09 USD
        }
    } elseif ($devise_source === 'USD') {
        if ($devise_cible === 'FCFA') {
            return $montant * 588.95; // 1 USD = 588.95 FCFA
        } elseif ($devise_cible === 'EUR') {
            return $montant * 0.92; // 1 USD = 0.92 EUR
        }
    }
    
    return $montant; // Retourne le montant original si conversion non possible
}

// Initialisation de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
