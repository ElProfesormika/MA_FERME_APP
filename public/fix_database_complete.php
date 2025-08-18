<?php
// Script de réparation complète de la base de données
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
    
    echo "<h2>🔧 Réparation complète de la base de données</h2>";
    
    $repairs = [];
    
    // Étape 1: Réparer la table users
    $repairs[] = "<h3>📋 Étape 1: Table users</h3>";
    
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    $userColumns = [
        'role' => "ALTER TABLE users ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email",
        'permissions' => "ALTER TABLE users ADD COLUMN permissions JSON NULL AFTER role",
        'last_login' => "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER permissions"
    ];
    
    foreach ($userColumns as $column => $sql) {
        if (!in_array($column, $columnNames)) {
            try {
                $db->exec($sql);
                $repairs[] = "✅ Colonne '$column' ajoutée à la table users";
            } catch (PDOException $e) {
                $repairs[] = "❌ Erreur lors de l'ajout de '$column': " . $e->getMessage();
            }
        } else {
            $repairs[] = "ℹ️ Colonne '$column' existe déjà dans users";
        }
    }
    
    // Étape 2: Réparer la table alertes
    $repairs[] = "<h3>📋 Étape 2: Table alertes</h3>";
    
    $stmt = $db->query("DESCRIBE alertes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');
    
    $alerteColumns = [
        'titre' => "ALTER TABLE alertes ADD COLUMN titre VARCHAR(255) NOT NULL AFTER type",
        'description' => "ALTER TABLE alertes ADD COLUMN description TEXT NOT NULL AFTER titre",
        'reference_id' => "ALTER TABLE alertes ADD COLUMN reference_id BIGINT UNSIGNED NULL AFTER description",
        'date_creation' => "ALTER TABLE alertes ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reference_id",
        'date_echeance' => "ALTER TABLE alertes ADD COLUMN date_echeance DATE NULL AFTER date_creation",
        'priorite' => "ALTER TABLE alertes ADD COLUMN priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne' AFTER type",
        'assigne_a' => "ALTER TABLE alertes ADD COLUMN assigne_a BIGINT UNSIGNED NULL AFTER employe_id"
    ];
    
    foreach ($alerteColumns as $column => $sql) {
        if (!in_array($column, $columnNames)) {
            try {
                $db->exec($sql);
                $repairs[] = "✅ Colonne '$column' ajoutée à la table alertes";
            } catch (PDOException $e) {
                $repairs[] = "❌ Erreur lors de l'ajout de '$column': " . $e->getMessage();
            }
        } else {
            $repairs[] = "ℹ️ Colonne '$column' existe déjà dans alertes";
        }
    }
    
    // Étape 3: Créer les nouvelles tables
    $repairs[] = "<h3>📋 Étape 3: Nouvelles tables</h3>";
    
    // Table notifications
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->rowCount() == 0) {
        try {
            $db->exec("CREATE TABLE notifications (
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
            $repairs[] = "✅ Table 'notifications' créée";
        } catch (PDOException $e) {
            $repairs[] = "❌ Erreur lors de la création de 'notifications': " . $e->getMessage();
        }
    } else {
        $repairs[] = "ℹ️ Table 'notifications' existe déjà";
    }
    
    // Table user_sessions
    $stmt = $db->query("SHOW TABLES LIKE 'user_sessions'");
    if ($stmt->rowCount() == 0) {
        try {
            $db->exec("CREATE TABLE user_sessions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                token VARCHAR(191) NOT NULL UNIQUE,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_sessions_token (token),
                INDEX idx_user_sessions_user (user_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $repairs[] = "✅ Table 'user_sessions' créée";
        } catch (PDOException $e) {
            $repairs[] = "❌ Erreur lors de la création de 'user_sessions': " . $e->getMessage();
        }
    } else {
        $repairs[] = "ℹ️ Table 'user_sessions' existe déjà";
    }
    
    // Étape 4: Mettre à jour les données
    $repairs[] = "<h3>📋 Étape 4: Mise à jour des données</h3>";
    
    // Définir le premier utilisateur comme admin
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($adminCount == 0) {
            $db->exec("UPDATE users SET role = 'admin', permissions = '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]' WHERE id = 1");
            $repairs[] = "✅ Premier utilisateur défini comme administrateur";
        } else {
            $repairs[] = "ℹ️ Administrateur existe déjà";
        }
    } catch (PDOException $e) {
        $repairs[] = "❌ Erreur lors de la mise à jour de l'administrateur: " . $e->getMessage();
    }
    
    // Ajouter des utilisateurs de test
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE email = 'manager@ferme.com'");
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
            $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
            ('Manager Farm', 'manager@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NOW())");
            $repairs[] = "✅ Utilisateur manager ajouté";
        } else {
            $repairs[] = "ℹ️ Utilisateur manager existe déjà";
        }
    } catch (PDOException $e) {
        $repairs[] = "❌ Erreur lors de l'ajout du manager: " . $e->getMessage();
    }
    
    try {
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE email = 'employe@ferme.com'");
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
            $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
            ('Employé Farm', 'employe@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NOW())");
            $repairs[] = "✅ Utilisateur employé ajouté";
        } else {
            $repairs[] = "ℹ️ Utilisateur employé existe déjà";
        }
    } catch (PDOException $e) {
        $repairs[] = "❌ Erreur lors de l'ajout de l'employé: " . $e->getMessage();
    }
    
    // Étape 5: Vérification finale
    $repairs[] = "<h3>📋 Étape 5: Vérification finale</h3>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $repairs[] = "📊 Total utilisateurs : $totalUsers";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $repairs[] = "📊 Administrateurs : $totalAdmins";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'manager'");
    $totalManagers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $repairs[] = "📊 Gestionnaires : $totalManagers";
    
    // Vérifier les colonnes de la table alertes
    $stmt = $db->query("DESCRIBE alertes");
    $alerteColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $repairs[] = "📊 Colonnes dans alertes : " . count($alerteColumns);
    
    $repairs[] = "✅ Réparation complète terminée avec succès !";
    
} catch (PDOException $e) {
    $repairs[] = "<h3>❌ Erreur de connexion</h3>";
    $repairs[] = "Erreur : " . $e->getMessage();
    $repairs[] = "Vérifiez que :";
    $repairs[] = "- La base de données 'ferme_db' existe";
    $repairs[] = "- Les identifiants de connexion sont corrects";
    $repairs[] = "- L'utilisateur a les droits d'administration";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réparation Complète - Ferme d'Élevage</title>
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
        .repair-log {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            max-height: 600px;
            overflow-y: auto;
        }
        .repair-log h3 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .repair-log p {
            margin: 5px 0;
            padding: 5px 0;
        }
        .btn {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h2><i class="fas fa-tools"></i> Réparation Complète de la Base de Données</h2>
            <p class="lead">Correction de toutes les colonnes manquantes</p>
        </div>
        
        <div class="repair-log">
            <?php foreach ($repairs as $repair): ?>
                <?php if (strpos($repair, '<h3>') !== false): ?>
                    <?= $repair ?>
                <?php else: ?>
                    <p><?= htmlspecialchars($repair) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="test_system.php" class="btn btn-info">
                    <i class="fas fa-clipboard-check"></i> Tester le système
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