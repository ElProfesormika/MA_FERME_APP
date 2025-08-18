<?php
// Script de test complet du système
require_once 'config_devises.php';

// Configuration de la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'database' => 'if0_39665291_ferme_ya',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tests = [];
    
    // Test 1: Connexion à la base de données
    $tests[] = [
        'name' => 'Connexion à la base de données',
        'status' => '✅ Réussi',
        'details' => 'Connexion établie avec succès'
    ];
    
    // Test 2: Vérification des tables
    $tables = ['users', 'employes', 'animaux', 'stocks', 'activites', 'alertes'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $tests[] = [
                'name' => "Table '$table'",
                'status' => '✅ Existe',
                'details' => 'Table trouvée'
            ];
        } else {
            $tests[] = [
                'name' => "Table '$table'",
                'status' => '❌ Manquante',
                'details' => 'Table non trouvée'
            ];
        }
    }
    
    // Test 3: Vérification des colonnes de la table users
    $userColumns = ['id', 'name', 'email', 'password', 'role', 'permissions', 'last_login'];
    foreach ($userColumns as $column) {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($stmt->rowCount() > 0) {
            $tests[] = [
                'name' => "Colonne users.$column",
                'status' => '✅ Existe',
                'details' => 'Colonne trouvée'
            ];
        } else {
            $tests[] = [
                'name' => "Colonne users.$column",
                'status' => '❌ Manquante',
                'details' => 'Colonne non trouvée'
            ];
        }
    }
    
    // Test 4: Vérification des données
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Utilisateurs dans la base',
        'status' => $userCount > 0 ? '✅ Données présentes' : '⚠️ Aucun utilisateur',
        'details' => "$userCount utilisateur(s) trouvé(s)"
    ];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM employes");
    $employeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Employés dans la base',
        'status' => $employeCount > 0 ? '✅ Données présentes' : '⚠️ Aucun employé',
        'details' => "$employeCount employé(s) trouvé(s)"
    ];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM animaux");
    $animalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Animaux dans la base',
        'status' => $animalCount > 0 ? '✅ Données présentes' : '⚠️ Aucun animal',
        'details' => "$animalCount animal(aux) trouvé(s)"
    ];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM stocks");
    $stockCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Stocks dans la base',
        'status' => $stockCount > 0 ? '✅ Données présentes' : '⚠️ Aucun stock',
        'details' => "$stockCount stock(s) trouvé(s)"
    ];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM activites");
    $activiteCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Activités dans la base',
        'status' => $activiteCount > 0 ? '✅ Données présentes' : '⚠️ Aucune activité',
        'details' => "$activiteCount activité(s) trouvée(s)"
    ];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM alertes");
    $alerteCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $tests[] = [
        'name' => 'Alertes dans la base',
        'status' => $alerteCount > 0 ? '✅ Données présentes' : '⚠️ Aucune alerte',
        'details' => "$alerteCount alerte(s) trouvée(s)"
    ];
    
    // Test 5: Test des fonctions de devise
    $devise_actuelle = getDeviseActuelle();
    $tests[] = [
        'name' => 'Configuration des devises',
        'status' => '✅ Fonctionne',
        'details' => "Devise actuelle : $devise_actuelle"
    ];
    
    $montant_test = formaterMontantAutomatique(5000);
    $tests[] = [
        'name' => 'Formatage automatique des montants',
        'status' => '✅ Fonctionne',
        'details' => "5000 FCFA = $montant_test"
    ];
    
    // Test 6: Vérification des permissions
    $stmt = $db->query("SELECT role, permissions FROM users WHERE role IS NOT NULL LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $tests[] = [
            'name' => 'Système de permissions',
            'status' => '✅ Fonctionne',
            'details' => "Rôle : {$user['role']}, Permissions : {$user['permissions']}"
        ];
    } else {
        $tests[] = [
            'name' => 'Système de permissions',
            'status' => '⚠️ À configurer',
            'details' => 'Aucun utilisateur avec rôle défini'
        ];
    }
    
} catch (PDOException $e) {
    $tests = [
        [
            'name' => 'Connexion à la base de données',
            'status' => '❌ Échec',
            'details' => 'Erreur : ' . $e->getMessage()
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test du Système - Ferme d'Élevage</title>
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
        .test-item {
            border-left: 4px solid #dee2e6;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .test-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .test-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .test-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .status-icon {
            font-size: 1.2em;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h1><i class="fas fa-cogs"></i> Test du Système</h1>
            <p class="lead">Vérification complète de l'application Ferme d'Élevage</p>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-clipboard-check"></i> Résultats des tests</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($tests as $test): ?>
                            <div class="test-item <?= strpos($test['status'], '✅') !== false ? 'success' : (strpos($test['status'], '⚠️') !== false ? 'warning' : 'error') ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <?php if (strpos($test['status'], '✅') !== false): ?>
                                                <i class="fas fa-check-circle text-success status-icon"></i>
                                            <?php elseif (strpos($test['status'], '⚠️') !== false): ?>
                                                <i class="fas fa-exclamation-triangle text-warning status-icon"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger status-icon"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($test['name']) ?>
                                        </h5>
                                        <p class="mb-0 text-muted"><?= htmlspecialchars($test['details']) ?></p>
                                    </div>
                                    <span class="badge <?= strpos($test['status'], '✅') !== false ? 'bg-success' : (strpos($test['status'], '⚠️') !== false ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= htmlspecialchars($test['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="update_database.php" class="btn btn-warning">
                    <i class="fas fa-database"></i> Mettre à jour la base de données
                </a>
                <a href="gestion_equipe.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Gestion d'équipe
                </a>
                <a href="alertes_improved.php" class="btn btn-danger">
                    <i class="fas fa-bell"></i> Alertes
                </a>
                <a href="index_final.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Menu principal
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 