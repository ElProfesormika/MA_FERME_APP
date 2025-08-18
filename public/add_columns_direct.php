<?php
// Script direct pour ajouter les colonnes manquantes
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
    
    echo "<h2>🔧 Ajout direct des colonnes manquantes</h2>";
    
    $results = [];
    
    // 1. Ajouter les colonnes à la table users
    $queries_users = [
        "ALTER TABLE users ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email",
        "ALTER TABLE users ADD COLUMN permissions JSON NULL AFTER role",
        "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER permissions"
    ];
    
    foreach ($queries_users as $query) {
        try {
            $db->exec($query);
            $results[] = "✅ Requête exécutée : " . substr($query, 0, 50) . "...";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $results[] = "ℹ️ Colonne existe déjà : " . substr($query, 0, 50) . "...";
            } else {
                $results[] = "❌ Erreur : " . $e->getMessage();
            }
        }
    }
    
    // 2. Ajouter les colonnes à la table alertes
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
            $results[] = "✅ Requête exécutée : " . substr($query, 0, 50) . "...";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                $results[] = "ℹ️ Colonne existe déjà : " . substr($query, 0, 50) . "...";
            } else {
                $results[] = "❌ Erreur : " . $e->getMessage();
            }
        }
    }
    
    // 3. Mettre à jour l'utilisateur existant
    try {
        $db->exec("UPDATE users SET role = 'admin', permissions = '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]' WHERE id = 1");
        $results[] = "✅ Utilisateur admin mis à jour";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur mise à jour admin : " . $e->getMessage();
    }
    
    // 4. Ajouter des utilisateurs de test
    try {
        $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Manager Farm', 'manager@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NOW())");
        $results[] = "✅ Utilisateur manager ajouté";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $results[] = "ℹ️ Utilisateur manager existe déjà";
        } else {
            $results[] = "❌ Erreur ajout manager : " . $e->getMessage();
        }
    }
    
    try {
        $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Employé Farm', 'employe@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NOW())");
        $results[] = "✅ Utilisateur employé ajouté";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $results[] = "ℹ️ Utilisateur employé existe déjà";
        } else {
            $results[] = "❌ Erreur ajout employé : " . $e->getMessage();
        }
    }
    
    // 5. Créer les tables si elles n'existent pas
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
            INDEX idx_notifications_type (type),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $results[] = "✅ Table notifications créée/vérifiée";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur table notifications : " . $e->getMessage();
    }
    
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS user_sessions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(191) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_sessions_token (token),
            INDEX idx_user_sessions_user (user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $results[] = "✅ Table user_sessions créée/vérifiée";
    } catch (PDOException $e) {
        $results[] = "❌ Erreur table user_sessions : " . $e->getMessage();
    }
    
    // 6. Vérification finale
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'manager'");
    $totalManagers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $results[] = "<h3>📊 Statistiques finales :</h3>";
    $results[] = "📊 Total utilisateurs : $totalUsers";
    $results[] = "📊 Administrateurs : $totalAdmins";
    $results[] = "📊 Gestionnaires : $totalManagers";
    
    $results[] = "<h3>✅ Opération terminée !</h3>";
    
} catch (PDOException $e) {
    $results = ["❌ Erreur de connexion : " . $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout Direct des Colonnes - Ferme d'Élevage</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h2><i class="fas fa-database"></i> Ajout Direct des Colonnes</h2>
            <p class="lead">Correction immédiate de la structure de la base de données</p>
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