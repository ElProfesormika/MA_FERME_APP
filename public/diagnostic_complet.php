<?php
// Diagnostic complet de la base de données
require_once 'config_devises.php';

// Configuration de la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'database' => 'if0_39665291_ferme_ya',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

$diagnostic = [];
$corrections = [];

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $diagnostic[] = "<h2>🔍 Diagnostic complet de la base de données</h2>";
    
    // 1. Vérifier l'existence des tables
    $diagnostic[] = "<h3>📋 Étape 1: Vérification des tables</h3>";
    
    $tables_requises = ['users', 'employes', 'animaux', 'stocks', 'activites', 'alertes'];
    $tables_existantes = [];
    
    foreach ($tables_requises as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $tables_existantes[] = $table;
            $diagnostic[] = "✅ Table '$table' existe";
        } else {
            $diagnostic[] = "❌ Table '$table' MANQUANTE";
        }
    }
    
    // 2. Vérifier la structure de la table users
    $diagnostic[] = "<h3>📋 Étape 2: Structure de la table users</h3>";
    
    if (in_array('users', $tables_existantes)) {
        $stmt = $db->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $colonnes_users_requises = ['id', 'name', 'email', 'password', 'role', 'permissions', 'last_login'];
        
        foreach ($colonnes_users_requises as $colonne) {
            if (in_array($colonne, $columnNames)) {
                $diagnostic[] = "✅ Colonne users.$colonne existe";
            } else {
                $diagnostic[] = "❌ Colonne users.$colonne MANQUANTE";
                $corrections[] = "ALTER TABLE users ADD COLUMN $colonne " . 
                    ($colonne === 'role' ? "ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email" :
                    ($colonne === 'permissions' ? "JSON NULL AFTER role" :
                    ($colonne === 'last_login' ? "TIMESTAMP NULL AFTER permissions" : "VARCHAR(255) NULL")));
            }
        }
    }
    
    // 3. Vérifier la structure de la table alertes
    $diagnostic[] = "<h3>📋 Étape 3: Structure de la table alertes</h3>";
    
    if (in_array('alertes', $tables_existantes)) {
        $stmt = $db->query("DESCRIBE alertes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        $colonnes_alertes_requises = ['id', 'type', 'message', 'critique', 'statut', 'titre', 'description', 'reference_id', 'date_creation', 'date_echeance', 'priorite', 'assigne_a'];
        
        foreach ($colonnes_alertes_requises as $colonne) {
            if (in_array($colonne, $columnNames)) {
                $diagnostic[] = "✅ Colonne alertes.$colonne existe";
            } else {
                $diagnostic[] = "❌ Colonne alertes.$colonne MANQUANTE";
                $corrections[] = "ALTER TABLE alertes ADD COLUMN $colonne " . 
                    ($colonne === 'titre' ? "VARCHAR(255) NOT NULL DEFAULT '' AFTER type" :
                    ($colonne === 'description' ? "TEXT NOT NULL AFTER titre" :
                    ($colonne === 'reference_id' ? "BIGINT UNSIGNED NULL AFTER description" :
                    ($colonne === 'date_creation' ? "TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reference_id" :
                    ($colonne === 'date_echeance' ? "DATE NULL AFTER date_creation" :
                    ($colonne === 'priorite' ? "ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne' AFTER type" :
                    ($colonne === 'assigne_a' ? "BIGINT UNSIGNED NULL AFTER employe_id" : ""))))))
            }
        }
    }
    
    // 4. Vérifier les données
    $diagnostic[] = "<h3>📋 Étape 4: Vérification des données</h3>";
    
    foreach ($tables_existantes as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $diagnostic[] = "📊 Table $table : $count enregistrement(s)";
        } catch (PDOException $e) {
            $diagnostic[] = "❌ Erreur lecture $table : " . $e->getMessage();
        }
    }
    
    // 5. Appliquer les corrections si nécessaire
    if (!empty($corrections)) {
        $diagnostic[] = "<h3>🔧 Étape 5: Application des corrections</h3>";
        
        foreach ($corrections as $correction) {
            try {
                $db->exec($correction);
                $diagnostic[] = "✅ Correction appliquée : " . substr($correction, 0, 50) . "...";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    $diagnostic[] = "ℹ️ Colonne existe déjà : " . substr($correction, 0, 50) . "...";
                } else {
                    $diagnostic[] = "❌ Erreur correction : " . $e->getMessage();
                }
            }
        }
        
        // Mettre à jour l'utilisateur admin après les corrections
        try {
            $db->exec("UPDATE users SET role = 'admin', permissions = '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]' WHERE id = 1");
            $diagnostic[] = "✅ Utilisateur admin mis à jour";
        } catch (PDOException $e) {
            $diagnostic[] = "❌ Erreur mise à jour admin : " . $e->getMessage();
        }
    }
    
    // 6. Test final des modules
    $diagnostic[] = "<h3>🧪 Étape 6: Test des modules</h3>";
    
    // Test de la fonction getUsers
    try {
        $stmt = $db->query("SELECT id, name, email, role, permissions, created_at, last_login FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $diagnostic[] = "✅ Test getUsers : " . count($users) . " utilisateur(s) récupéré(s)";
    } catch (PDOException $e) {
        $diagnostic[] = "❌ Test getUsers échoué : " . $e->getMessage();
    }
    
    // Test de la fonction getAlertes
    try {
        $stmt = $db->query("SELECT * FROM alertes ORDER BY created_at DESC");
        $alertes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $diagnostic[] = "✅ Test getAlertes : " . count($alertes) . " alerte(s) récupérée(s)";
    } catch (PDOException $e) {
        $diagnostic[] = "❌ Test getAlertes échoué : " . $e->getMessage();
    }
    
    // Test de la conversion des devises
    try {
        $devise_actuelle = getDeviseActuelle();
        $montant_test = formaterMontantAutomatique(5000);
        $diagnostic[] = "✅ Test conversion devises : 5000 FCFA = $montant_test (devise: $devise_actuelle)";
    } catch (Exception $e) {
        $diagnostic[] = "❌ Test conversion devises échoué : " . $e->getMessage();
    }
    
    $diagnostic[] = "<h3>✅ Diagnostic terminé !</h3>";
    
} catch (PDOException $e) {
    $diagnostic[] = "<h3>❌ Erreur de connexion</h3>";
    $diagnostic[] = "Erreur : " . $e->getMessage();
    $diagnostic[] = "Vérifiez que :";
    $diagnostic[] = "- La base de données 'ferme_db' existe";
    $diagnostic[] = "- Les identifiants de connexion sont corrects";
    $diagnostic[] = "- L'utilisateur a les droits d'administration";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Complet - Ferme d'Élevage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 20px auto;
            max-width: 900px;
        }
        .diagnostic-item {
            border-left: 4px solid #dee2e6;
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .diagnostic-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .diagnostic-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .diagnostic-item.info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        .diagnostic-item h3 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .summary-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1><i class="fas fa-stethoscope"></i> Diagnostic Complet</h1>
            <p class="lead">Vérification et correction automatique de tous les problèmes</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-list"></i> Résultats du diagnostic</h3>
            </div>
            <div class="card-body">
                <?php foreach ($diagnostic as $item): ?>
                    <?php if (strpos($item, '<h3>') !== false): ?>
                        <?= $item ?>
                    <?php else: ?>
                        <div class="diagnostic-item <?= strpos($item, '✅') !== false ? 'success' : (strpos($item, '❌') !== false ? 'error' : 'info') ?>">
                            <?= htmlspecialchars($item) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (!empty($corrections)): ?>
            <div class="summary-card">
                <h4><i class="fas fa-tools"></i> Corrections appliquées</h4>
                <p><?= count($corrections) ?> correction(s) ont été appliquées automatiquement.</p>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="gestion_equipe.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Tester Gestion d'équipe
                </a>
                <a href="alertes_improved.php" class="btn btn-danger">
                    <i class="fas fa-bell"></i> Tester Alertes
                </a>
                <a href="test_system.php" class="btn btn-info">
                    <i class="fas fa-clipboard-check"></i> Test complet
                </a>
                <a href="index_final.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Accueil
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 