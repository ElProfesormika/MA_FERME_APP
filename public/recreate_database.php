<?php
// Script pour recréer complètement la base de données
require_once 'config_devises.php';

// Configuration de la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'database' => 'if0_39665291_ferme_ya',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

$results = [];

try {
    // Connexion sans spécifier la base de données
    $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
    $db = new PDO($dsn, $config['username'], $config['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $results[] = "<h2>🔄 Recréation complète de la base de données</h2>";
    
    // 1. Supprimer la base de données existante si elle existe
    $results[] = "<h3>📋 Étape 1: Nettoyage</h3>";
    
    try {
        $db->exec("DROP DATABASE IF EXISTS ferme_db");
        $results[] = "✅ Ancienne base de données supprimée";
    } catch (PDOException $e) {
        $results[] = "ℹ️ Pas de base de données à supprimer";
    }
    
    // 2. Créer la nouvelle base de données
    $results[] = "<h3>📋 Étape 2: Création de la base</h3>";
    
    $db->exec("CREATE DATABASE ferme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $results[] = "✅ Nouvelle base de données créée";
    
    // 3. Utiliser la nouvelle base de données
    $db->exec("USE ferme_db");
    $results[] = "✅ Base de données sélectionnée";
    
    // 4. Créer toutes les tables avec la structure complète
    $results[] = "<h3>📋 Étape 3: Création des tables</h3>";
    
    // Table users avec toutes les colonnes
    $db->exec("CREATE TABLE users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        email VARCHAR(191) UNIQUE NOT NULL,
        role ENUM('admin', 'manager', 'user') DEFAULT 'user',
        permissions JSON NULL,
        last_login TIMESTAMP NULL,
        email_verified_at TIMESTAMP NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_users_role (role),
        INDEX idx_users_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table users créée avec toutes les colonnes";
    
    // Table employes
    $db->exec("CREATE TABLE employes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        poste VARCHAR(100) NOT NULL,
        date_embauche DATE NOT NULL,
        salaire DECIMAL(10,2) NOT NULL,
        telephone VARCHAR(20) NULL,
        email VARCHAR(191) NULL,
        adresse TEXT NULL,
        statut ENUM('actif', 'inactif', 'vacances') DEFAULT 'actif',
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table employes créée";
    
    // Table animaux
    $db->exec("CREATE TABLE animaux (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        espece VARCHAR(50) NOT NULL,
        race VARCHAR(50) NULL,
        date_naissance DATE NOT NULL,
        historique_sante TEXT NULL,
        poids DECIMAL(8,2) NULL,
        sexe ENUM('male', 'femelle') NOT NULL,
        statut ENUM('actif', 'vendu', 'mort', 'malade') DEFAULT 'actif',
        employe_id BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table animaux créée";
    
    // Table stocks
    $db->exec("CREATE TABLE stocks (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        produit VARCHAR(100) NOT NULL,
        quantite DECIMAL(10,2) NOT NULL,
        unite VARCHAR(20) NOT NULL,
        date_entree DATE NOT NULL,
        date_peremption DATE NULL,
        prix_unitaire DECIMAL(10,2) NOT NULL,
        fournisseur VARCHAR(100) NULL,
        categorie VARCHAR(50) NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table stocks créée";
    
    // Table activites
    $db->exec("CREATE TABLE activites (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(200) NOT NULL,
        description TEXT NULL,
        date DATE NOT NULL,
        heure_debut TIME NULL,
        heure_fin TIME NULL,
        type ENUM('soins', 'alimentation', 'reproduction', 'maintenance', 'autre') NOT NULL,
        statut ENUM('planifie', 'en_cours', 'termine', 'annule') DEFAULT 'planifie',
        employe_id BIGINT UNSIGNED NULL,
        animal_id BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE SET NULL,
        FOREIGN KEY (animal_id) REFERENCES animaux(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table activites créée";
    
    // Table alertes avec toutes les colonnes
    $db->exec("CREATE TABLE alertes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('sante', 'stock', 'maintenance', 'urgence', 'stock_rupture', 'stock_peremption', 'activite_retard') NOT NULL,
        titre VARCHAR(255) NOT NULL DEFAULT '',
        description TEXT NOT NULL,
        reference_id BIGINT UNSIGNED NULL,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_echeance DATE NULL,
        priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne',
        critique BOOLEAN DEFAULT FALSE,
        statut ENUM('active', 'resolue', 'ignoree') DEFAULT 'active',
        animal_id BIGINT UNSIGNED NULL,
        stock_id BIGINT UNSIGNED NULL,
        employe_id BIGINT UNSIGNED NULL,
        assigne_a BIGINT UNSIGNED NULL,
        date_resolution TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (animal_id) REFERENCES animaux(id) ON DELETE SET NULL,
        FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE SET NULL,
        FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE SET NULL,
        INDEX idx_alertes_statut (statut),
        INDEX idx_alertes_priorite (priorite),
        INDEX idx_alertes_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table alertes créée avec toutes les colonnes";
    
    // Table notifications
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
        INDEX idx_notifications_type (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table notifications créée";
    
    // Table user_sessions
    $db->exec("CREATE TABLE user_sessions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        token VARCHAR(191) NOT NULL UNIQUE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_sessions_token (token),
        INDEX idx_user_sessions_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $results[] = "✅ Table user_sessions créée";
    
    // 5. Insérer les données de base
    $results[] = "<h3>📋 Étape 4: Insertion des données</h3>";
    
    // Utilisateur admin
    $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
    ('Admin Farm', 'admin@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]', NOW())");
    $results[] = "✅ Utilisateur admin créé";
    
    // Utilisateur manager
    $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
    ('Manager Farm', 'manager@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NOW())");
    $results[] = "✅ Utilisateur manager créé";
    
    // Utilisateur employé
    $db->exec("INSERT INTO users (name, email, password, role, permissions, created_at) VALUES 
    ('Employé Farm', 'employe@ferme.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NOW())");
    $results[] = "✅ Utilisateur employé créé";
    
    // Employés de test
    $db->exec("INSERT INTO employes (nom, prenom, poste, date_embauche, salaire, telephone, email, created_at) VALUES 
    ('Dupont', 'Jean', 'Éleveur', '2023-01-15', 150000, '+226 70123456', 'jean.dupont@ferme.com', NOW()),
    ('Martin', 'Marie', 'Vétérinaire', '2023-03-20', 200000, '+226 70234567', 'marie.martin@ferme.com', NOW()),
    ('Bernard', 'Pierre', 'Ouvrier agricole', '2023-06-10', 120000, '+226 70345678', 'pierre.bernard@ferme.com', NOW())");
    $results[] = "✅ Employés de test créés";
    
    // Animaux de test
    $db->exec("INSERT INTO animaux (nom, espece, race, date_naissance, poids, sexe, employe_id, created_at) VALUES 
    ('Belle', 'Vache', 'Holstein', '2020-05-15', 650.5, 'femelle', 1, NOW()),
    ('Max', 'Taureau', 'Charolais', '2019-08-22', 850.0, 'male', 1, NOW()),
    ('Luna', 'Vache', 'Jersey', '2021-03-10', 450.2, 'femelle', 2, NOW())");
    $results[] = "✅ Animaux de test créés";
    
    // Stocks de test
    $db->exec("INSERT INTO stocks (produit, quantite, unite, date_entree, date_peremption, prix_unitaire, fournisseur, categorie, created_at) VALUES 
    ('Nourriture pour bovins', 1000.0, 'kg', '2024-01-15', '2024-12-31', 500, 'AgroFournitures', 'Alimentation', NOW()),
    ('Médicaments vétérinaires', 50.0, 'boîtes', '2024-02-01', '2025-06-30', 2500, 'PharmaVet', 'Santé', NOW()),
    ('Matériel d''élevage', 25.0, 'pièces', '2024-01-20', NULL, 15000, 'EquipElevage', 'Équipement', NOW())");
    $results[] = "✅ Stocks de test créés";
    
    // Activités de test
    $db->exec("INSERT INTO activites (titre, description, date, heure_debut, heure_fin, type, statut, employe_id, animal_id, created_at) VALUES 
    ('Soins vétérinaires', 'Vaccination et contrôle sanitaire', '2024-08-08', '09:00:00', '11:00:00', 'soins', 'planifie', 2, 1, NOW()),
    ('Alimentation du bétail', 'Distribution de nourriture', '2024-08-08', '07:00:00', '08:00:00', 'alimentation', 'termine', 1, NULL, NOW()),
    ('Maintenance des équipements', 'Réparation du système d''abreuvement', '2024-08-09', '14:00:00', '16:00:00', 'maintenance', 'planifie', 3, NULL, NOW())");
    $results[] = "✅ Activités de test créées";
    
    // Alertes de test
    $db->exec("INSERT INTO alertes (type, titre, description, priorite, statut, created_at) VALUES 
    ('stock', 'Stock faible', 'La nourriture pour bovins est en quantité faible', 'haute', 'active', NOW()),
    ('sante', 'Animal malade', 'La vache Belle présente des symptômes', 'critique', 'active', NOW())");
    $results[] = "✅ Alertes de test créées";
    
    // 6. Vérification finale
    $results[] = "<h3>📋 Étape 5: Vérification finale</h3>";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total utilisateurs : $totalUsers";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM employes");
    $totalEmployes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total employés : $totalEmployes";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM animaux");
    $totalAnimaux = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total animaux : $totalAnimaux";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM stocks");
    $totalStocks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total stocks : $totalStocks";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM activites");
    $totalActivites = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total activités : $totalActivites";
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM alertes");
    $totalAlertes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $results[] = "📊 Total alertes : $totalAlertes";
    
    $results[] = "<h3>✅ Base de données recréée avec succès !</h3>";
    $results[] = "Tous les modules devraient maintenant fonctionner correctement.";
    
} catch (PDOException $e) {
    $results = ["❌ Erreur : " . $e->getMessage()];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recréation Base de Données - Ferme d'Élevage</title>
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
            <h2><i class="fas fa-database"></i> Recréation Complète de la Base de Données</h2>
            <p class="lead">Nouvelle base avec structure complète et données de test</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> Résultats de la recréation</h3>
            </div>
            <div class="card-body">
                <?php foreach ($results as $result): ?>
                    <?php if (strpos($result, '<h3>') !== false): ?>
                        <?= $result ?>
                    <?php else: ?>
                        <div class="result-item <?= strpos($result, '✅') !== false ? 'success' : 'error' ?>">
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