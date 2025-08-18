<?php
// Script de mise à jour automatique de la base de données
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
    
    echo "<h2>🔄 Mise à jour de la base de données</h2>";
    
    // Vérifier et ajouter les colonnes manquantes à la table users
    $updates = [];
    
    // Vérifier la colonne 'role'
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email");
        $updates[] = "✅ Colonne 'role' ajoutée";
    } else {
        $updates[] = "ℹ️ Colonne 'role' existe déjà";
    }
    
    // Vérifier la colonne 'permissions'
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'permissions'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN permissions JSON NULL AFTER role");
        $updates[] = "✅ Colonne 'permissions' ajoutée";
    } else {
        $updates[] = "ℹ️ Colonne 'permissions' existe déjà";
    }
    
    // Vérifier la colonne 'last_login'
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER permissions");
        $updates[] = "✅ Colonne 'last_login' ajoutée";
    } else {
        $updates[] = "ℹ️ Colonne 'last_login' existe déjà";
    }
    
    // Mettre à jour l'utilisateur existant avec le rôle admin
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($adminCount == 0) {
        $db->exec("UPDATE users SET role = 'admin', permissions = '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]' WHERE id = 1");
        $updates[] = "✅ Premier utilisateur défini comme administrateur";
    } else {
        $updates[] = "ℹ️ Administrateur existe déjà";
    }
    
    // Vérifier et ajouter les colonnes manquantes à la table alertes
    $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'priorite'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE alertes ADD COLUMN priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne' AFTER type");
        $updates[] = "✅ Colonne 'priorite' ajoutée à la table alertes";
    } else {
        $updates[] = "ℹ️ Colonne 'priorite' existe déjà dans alertes";
    }
    
    // Vérifier et ajouter les colonnes manquantes à la table alertes
    $stmt = $db->query("SHOW COLUMNS FROM alertes LIKE 'assigne_a'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE alertes ADD COLUMN assigne_a BIGINT UNSIGNED NULL AFTER employe_id");
        $updates[] = "✅ Colonne 'assigne_a' ajoutée à la table alertes";
    } else {
        $updates[] = "ℹ️ Colonne 'assigne_a' existe déjà dans alertes";
    }
    
    // Créer la table notifications si elle n'existe pas
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->rowCount() == 0) {
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
        $updates[] = "✅ Table 'notifications' créée";
    } else {
        $updates[] = "ℹ️ Table 'notifications' existe déjà";
    }
    
    // Créer la table user_sessions si elle n'existe pas
    $stmt = $db->query("SHOW TABLES LIKE 'user_sessions'");
    if ($stmt->rowCount() == 0) {
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
        $updates[] = "✅ Table 'user_sessions' créée";
    } else {
        $updates[] = "ℹ️ Table 'user_sessions' existe déjà";
    }
    
    // Ajouter des utilisateurs de test si nécessaire
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE email = 'manager@ferme.com'");
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
        $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Manager Farm', 'manager@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NOW())");
        $updates[] = "✅ Utilisateur manager ajouté";
    } else {
        $updates[] = "ℹ️ Utilisateur manager existe déjà";
    }
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE email = 'employe@ferme.com'");
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
        $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
        ('Employé Farm', 'employe@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NOW())");
        $updates[] = "✅ Utilisateur employé ajouté";
    } else {
        $updates[] = "ℹ️ Utilisateur employé existe déjà";
    }
    
    // Ajouter des index de manière sécurisée (sans erreur de clé trop longue)
    try {
        // Vérifier si l'index existe avant de le créer
        $stmt = $db->query("SHOW INDEX FROM users WHERE Key_name = 'idx_users_role'");
        if ($stmt->rowCount() == 0) {
            $db->exec("CREATE INDEX idx_users_role ON users(role)");
            $updates[] = "✅ Index idx_users_role créé";
        } else {
            $updates[] = "ℹ️ Index idx_users_role existe déjà";
        }
    } catch (PDOException $e) {
        $updates[] = "⚠️ Index idx_users_role non créé (déjà existant ou erreur)";
    }
    
    try {
        $stmt = $db->query("SHOW INDEX FROM alertes WHERE Key_name = 'idx_alertes_statut'");
        if ($stmt->rowCount() == 0) {
            $db->exec("CREATE INDEX idx_alertes_statut ON alertes(statut)");
            $updates[] = "✅ Index idx_alertes_statut créé";
        } else {
            $updates[] = "ℹ️ Index idx_alertes_statut existe déjà";
        }
    } catch (PDOException $e) {
        $updates[] = "⚠️ Index idx_alertes_statut non créé (déjà existant ou erreur)";
    }
    
    try {
        $stmt = $db->query("SHOW INDEX FROM alertes WHERE Key_name = 'idx_alertes_priorite'");
        if ($stmt->rowCount() == 0) {
            $db->exec("CREATE INDEX idx_alertes_priorite ON alertes(priorite)");
            $updates[] = "✅ Index idx_alertes_priorite créé";
        } else {
            $updates[] = "ℹ️ Index idx_alertes_priorite existe déjà";
        }
    } catch (PDOException $e) {
        $updates[] = "⚠️ Index idx_alertes_priorite non créé (déjà existant ou erreur)";
    }
    
    // Insérer des notifications de test
    $stmt = $db->query("SELECT COUNT(*) as count FROM notifications");
    if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
        $db->exec("INSERT INTO notifications (user_id, type, titre, message, lien) VALUES
        (1, 'alerte', 'Stock faible', 'Le stock de nourriture pour animaux est faible', 'stocks_improved.php'),
        (1, 'activite', 'Activité planifiée', 'Soins des animaux prévus pour aujourd''hui', 'activites_improved.php'),
        (2, 'stock', 'Nouveau stock arrivé', 'Livraison de médicaments reçue', 'stocks_improved.php')");
        $updates[] = "✅ Notifications de test ajoutées";
    } else {
        $updates[] = "ℹ️ Notifications existent déjà";
    }
    
    // Afficher les résultats
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📋 Résultats de la mise à jour :</h3>";
    echo "<ul>";
    foreach ($updates as $update) {
        echo "<li>$update</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Afficher les statistiques
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'manager'");
    $totalManagers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📊 Statistiques de l'équipe :</h3>";
    echo "<p><strong>Total utilisateurs :</strong> $totalUsers</p>";
    echo "<p><strong>Administrateurs :</strong> $totalAdmins</p>";
    echo "<p><strong>Gestionnaires :</strong> $totalManagers</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='gestion_equipe.php' class='btn btn-primary btn-lg'>";
    echo "<i class='fas fa-users'></i> Accéder à la gestion d'équipe";
    echo "</a>";
    echo "<br><br>";
    echo "<a href='index_final.php' class='btn btn-secondary'>";
    echo "<i class='fas fa-home'></i> Retour au menu principal";
    echo "</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Erreur lors de la mise à jour :</h3>";
    echo "<p><strong>Erreur :</strong> " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>La base de données 'ferme_db' existe</li>";
    echo "<li>Les identifiants de connexion sont corrects</li>";
    echo "<li>L'utilisateur a les droits d'administration</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='index_final.php' class='btn btn-secondary'>";
    echo "<i class='fas fa-home'></i> Retour au menu principal";
    echo "</a>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour Base de Données - Ferme d'Élevage</title>
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
        h2 {
            color: #667eea;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Le contenu PHP sera affiché ici -->
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 