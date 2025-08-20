<?php
// Configuration SQLite pour Railway
// Base de données intégrée, pas de configuration externe nécessaire

// Configuration de l'application
function app_name() {
    return "Ferme d'Élevage";
}

function app_slogan() {
    return "Gestion intelligente de votre ferme";
}

function app_footer_columns() {
    return [
        [
            'title' => 'À propos',
            'lines' => [
                'Notre histoire',
                'Notre équipe',
                'Nos valeurs'
            ]
        ],
        [
            'title' => 'Services',
            'lines' => [
                'Gestion des stocks',
                'Suivi des animaux',
                'Rapports'
            ]
        ],
        [
            'title' => 'Support',
            'lines' => [
                'FAQ',
                'Contact',
                'Aide'
            ]
        ]
    ];
}

function app_legal_notice() {
    return "© 2024 Ferme d'Élevage. Tous droits réservés.";
}

// Configuration des devises
$DEVISE_CONFIG = [
    'FCFA' => [
        'symbole' => 'FCFA',
        'separateur' => ' ',
        'position' => 'after',
        'decimales' => 0
    ],
    'EUR' => [
        'symbole' => '€',
        'separateur' => ' ',
        'position' => 'before',
        'decimales' => 2
    ],
    'USD' => [
        'symbole' => '$',
        'separateur' => ' ',
        'position' => 'before',
        'decimales' => 2
    ]
];

// Chemin vers la base de données SQLite
function getDBPath() {
    $db_dir = __DIR__ . '/../database';
    
    // Créer le dossier database s'il n'existe pas
    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }
    
    return $db_dir . '/ferme.db';
}

// Connexion à la base de données SQLite
function connectDB() {
    try {
        $db_path = getDBPath();
        $pdo = new PDO("sqlite:$db_path");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Activer les clés étrangères
        $pdo->exec('PRAGMA foreign_keys = ON');
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur connexion SQLite: " . $e->getMessage());
        return false;
    }
}

// Obtenir les statistiques rapides
function getQuickStats() {
    $db = connectDB();
    if (!$db) {
        return [
            'total_animaux' => 0,
            'total_stocks' => 0,
            'total_employes' => 0,
            'activites_aujourdhui' => 0,
            'alertes_actives' => 0
        ];
    }
    
    $stats = [];
    
    try {
        // Total animaux
        $stmt = $db->query("SELECT COUNT(*) as total FROM animaux");
        $stats['total_animaux'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total stocks
        $stmt = $db->query("SELECT COUNT(*) as total FROM stocks");
        $stats['total_stocks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total employés
        $stmt = $db->query("SELECT COUNT(*) as total FROM employes");
        $stats['total_employes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Activités aujourd'hui
        $stmt = $db->query("SELECT COUNT(*) as total FROM activites WHERE date = date('now')");
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
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
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
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
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

// Initialisation de la session (seulement si pas déjà démarrée et pas d'output)
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
?>
