<?php
// Script simple pour ajouter les colonnes manquantes
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
    
    echo "<h2>🔧 Correction simple de la base de données</h2>";
    
    $results = [];
    
    // 1. Vérifier et ajouter les colonnes à la table users
    $results[] = "<h3>📋 Étape 1: Table users</h3>";
    
    $queries_users = [
        "ALTER TABLE users ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email",
        "ALTER TABLE users ADD COLUMN permissions JSON NULL AFTER role",
        "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER permissions"
    ];
    
    foreach ($queries_users as $query) {
        try {
            $db->exec($query);
            $results[] = "✅ Colonne ajoutée à users";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $results[] = "ℹ️ Colonne existe déjà dans users";
            } else {
                $results[] = "❌ Erreur users : " . $e->getMessage();
            }
        }
    }
    
    // 2. Vérifier et ajouter les colonnes à la table alertes
    $results[] = "<h3>📋 Étape 2: Table alertes</h3>";
    
    $queries_alertes = [
        "ALTER TABLE alertes ADD COLUMN titre VARCHAR(255) NOT NULL DEFAULT '' AFTER type",
        "ALTER TABLE alertes ADD COLUMN description TEXT NOT NULL AFTER titre",
        "ALTER TABLE alertes ADD COLUMN reference_id BIGINT UNSIGNED NULL AFTER description",
        "ALTER TABLE alertes ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reference_id",
        "ALTER TABLE alertes ADD COLUMN date_echeance DATE NULL AFTER date_creation",
        "ALTER TABLE alertes ADD COLUMN priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne' AFTER type",
        "ALTER TABLE alertes ADD COLUMN assigne_a BIGINT UNSIGNED NULL AFTER employe_id"
    ];
    
    foreach ($queries_alertes as $query) {
        try {
            $db->exec($query);
            $results[] = "✅ Colonne ajoutée à alertes";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $results[] = "ℹ️ Colonne existe déjà dans alertes";
            } else {
                $results[] = "❌ Erreur alertes : " . $e->getMessage();
            }
        }
    }
    
    // 3. Mettre à jour l'utilisateur existant
    $results[] = "<h3>📋 Étape 3: Mise à jour des données</h3>";
    
    try {
        $db->exec("UPDATE users SET role = 'admin', permissions = '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]' WHERE id = 1");
        $results[] = "✅ Utilisateur admin mis à jour";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur mise à jour admin : " . $e->getMessage();
    }
    
    // 4. Ajouter des utilisateurs de test (sans contraintes)
    try {
        $db->exec("INSERT IGNORE INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Manager Farm', 'manager@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NOW())");
        $results[] = "✅ Utilisateur manager ajouté";
    } catch (PDOException $e) {
        $results[] = "ℹ️ Utilisateur manager existe déjà ou erreur : " . $e->getMessage();
    }
    
    try {
        $db->exec("INSERT IGNORE INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Employé Farm', 'employe@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NOW())");
        $results[] = "✅ Utilisateur employé ajouté";
    } catch (PDOException $e) {
        $results[] = "ℹ️ Utilisateur employé existe déjà ou erreur : " . $e->getMessage();
    }
    
    // 5. Créer les tables simples (sans contraintes de clés étrangères)
    $results[] = "<h3>📋 Étape 4: Nouvelles tables</h3>";
    
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            type ENUM('alerte', 'activite', 'stock', 'systeme') NOT NULL,
            titre VARCHAR(191) NOT NULL,
            message TEXT NOT NULL,
            lu BOOLEAN DEFAULT FALSE,
            lien VARCHAR(191) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_user_lu (user_id, lu),
            INDEX idx_notifications_type (type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $results[] = "✅ Table notifications créée";
    } catch (PDOException $e) {
        $results[] = "ℹ️ Table notifications existe déjà ou erreur : " . $e->getMessage();
    }
    
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS user_sessions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(191) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_sessions_token (token),
            INDEX idx_user_sessions_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $results[] = "✅ Table user_sessions créée";
    } catch (PDOException $e) {
        $results[] = "ℹ️ Table user_sessions existe déjà ou erreur : " . $e->getMessage();
    }
    
    // 6. Vérification finale
    $results[] = "<h3>📋 Étape 5: Vérification</h3>";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM users");
        $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $results[] = "📊 Total utilisateurs : $totalUsers";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur comptage utilisateurs : " . $e->getMessage();
    }
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
        $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $results[] = "📊 Administrateurs : $totalAdmins";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur comptage admins : " . $e->getMessage();
    }
    
    try {
        $stmt = $db->query("DESCRIBE alertes");
        $alerteColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results[] = "📊 Colonnes dans alertes : " . count($alerteColumns);
    } catch (PDOException $e) {
        $results[] = "❌ Erreur vérification alertes : " . $e->getMessage();
    }
    
    $results[] = "<h3>✅ Correction terminée !</h3>";
    $results[] = "Vous pouvez maintenant tester les modules.";
    
} catch (PDOException $e) {
    $results = ["❌ Erreur de connexion : " . $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correction Simple - Ferme d'Élevage</title>
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
            max-width: 800px;
        }
        .result-item {
            border-left: 4px solid #dee2e6;
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .result-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .result-item.info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        .result-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .result-item h3 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h2><i class="fas fa-tools"></i> Correction Simple de la Base de Données</h2>
            <p class="lead">Ajout des colonnes manquantes sans contraintes problématiques</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Résultats des opérations</h3>
            </div>
            <div class="card-body">
                <?php foreach ($results as $result): ?>
                    <?php if (strpos($result, '<h3>') !== false): ?>
                        <?= $result ?>
                    <?php else: ?>
                        <div class="result-item <?= strpos($result, '✅') !== false ? 'success' : (strpos($result, 'ℹ️') !== false ? 'info' : 'error') ?>">
                            <?= htmlspecialchars($result) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
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