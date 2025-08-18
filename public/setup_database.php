<?php
// Script automatique pour créer la base de données
$config = [
    'host' => 'sql204.infinityfree.com',
    'username' => 'if0_39665291',
    'password' => 'JPrsDcoxt6DWQ0X',
    'charset' => 'utf8mb4'
];

echo "<h1>Configuration automatique de la base de données</h1>";

try {
    // Connexion à MySQL
    $pdo = new PDO("mysql:host={$config['host']}", $config['username'], $config['password']);
    echo "<p style='color: green;'>✅ Connexion MySQL réussie</p>";
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS if0_39665291_ferme_ya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Base de données 'if0_39665291_ferme_ya' créée</p>";
    
    // Se connecter à la base de données
    $pdo = new PDO("mysql:host={$config['host']};dbname=if0_39665291_ferme_ya;charset={$config['charset']}", $config['username'], $config['password']);
    
    // Créer les tables
    $sql = "
    -- Table des utilisateurs
    CREATE TABLE IF NOT EXISTS users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        email VARCHAR(191) UNIQUE NOT NULL,
        email_verified_at TIMESTAMP NULL,
        password VARCHAR(255) NOT NULL,
        remember_token VARCHAR(100) NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    );

    -- Table des employés
    CREATE TABLE IF NOT EXISTS employes (
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
    );

    -- Table des animaux
    CREATE TABLE IF NOT EXISTS animaux (
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
    );

    -- Table des stocks
    CREATE TABLE IF NOT EXISTS stocks (
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
    );

    -- Table des activités
    CREATE TABLE IF NOT EXISTS activites (
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
    );

    -- Table des alertes
    CREATE TABLE IF NOT EXISTS alertes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type ENUM('sante', 'stock', 'maintenance', 'urgence') NOT NULL,
        message TEXT NOT NULL,
        critique BOOLEAN DEFAULT FALSE,
        statut ENUM('active', 'resolue', 'ignoree') DEFAULT 'active',
        animal_id BIGINT UNSIGNED NULL,
        stock_id BIGINT UNSIGNED NULL,
        employe_id BIGINT UNSIGNED NULL,
        date_resolution TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (animal_id) REFERENCES animaux(id) ON DELETE CASCADE,
        FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
        FOREIGN KEY (employe_id) REFERENCES employes(id) ON DELETE SET NULL
    );
    ";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tables créées avec succès</p>";
    
    // Insérer des données de test
    $testData = "
    -- Utilisateur admin
    INSERT IGNORE INTO users (name, email, password, created_at, updated_at) VALUES
    ('Admin', 'admin@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

    -- Employés de test
    INSERT IGNORE INTO employes (nom, prenom, poste, date_embauche, salaire, telephone, email, statut, created_at, updated_at) VALUES
    ('Dupont', 'Jean', 'Vétérinaire', '2023-01-15', 3500.00, '0123456789', 'jean.dupont@ferme.com', 'actif', NOW(), NOW()),
    ('Martin', 'Marie', 'Soigneur', '2023-02-01', 2200.00, '0987654321', 'marie.martin@ferme.com', 'actif', NOW(), NOW()),
    ('Bernard', 'Pierre', 'Éleveur', '2022-11-10', 2800.00, '0555666777', 'pierre.bernard@ferme.com', 'actif', NOW(), NOW());

    -- Animaux de test
    INSERT IGNORE INTO animaux (nom, espece, race, date_naissance, poids, sexe, employe_id, created_at, updated_at) VALUES
    ('Belle', 'Vache', 'Holstein', '2020-03-15', 650.50, 'femelle', 1, NOW(), NOW()),
    ('Max', 'Taureau', 'Charolais', '2019-07-22', 850.00, 'male', 1, NOW(), NOW()),
    ('Luna', 'Vache', 'Jersey', '2021-01-10', 450.25, 'femelle', 2, NOW(), NOW());

    -- Stocks de test
    INSERT IGNORE INTO stocks (produit, quantite, unite, date_entree, date_peremption, prix_unitaire, fournisseur, categorie, created_at, updated_at) VALUES
    ('Foin de prairie', 1000.00, 'kg', '2024-01-15', '2025-01-15', 0.50, 'Fournisseur Agricole', 'Alimentation', NOW(), NOW()),
    ('Vaccin bovin', 50.00, 'doses', '2024-02-01', '2024-12-01', 15.00, 'Laboratoire Vétérinaire', 'Médicament', NOW(), NOW()),
    ('Minéraux', 200.00, 'kg', '2024-01-20', '2025-01-20', 2.50, 'Nutrition Animale', 'Complément', NOW(), NOW());

    -- Activités de test
    INSERT IGNORE INTO activites (titre, description, date, heure_debut, heure_fin, type, statut, employe_id, animal_id, created_at, updated_at) VALUES
    ('Vaccination annuelle', 'Vaccination contre les maladies courantes', '2024-08-08', '09:00:00', '11:00:00', 'soins', 'planifie', 1, 1, NOW(), NOW()),
    ('Distribution alimentation', 'Distribution du foin et des compléments', '2024-08-08', '07:00:00', '08:00:00', 'alimentation', 'planifie', 2, NULL, NOW(), NOW()),
    ('Contrôle sanitaire', 'Vérification de l''état de santé général', '2024-08-07', '14:00:00', '16:00:00', 'soins', 'termine', 1, 2, NOW(), NOW());

    -- Alertes de test
    INSERT IGNORE INTO alertes (type, message, critique, statut, animal_id, created_at, updated_at) VALUES
    ('sante', 'Animal Belle présente des signes de fatigue', FALSE, 'active', 1, NOW(), NOW()),
    ('stock', 'Stock de foin faible (moins de 200kg)', TRUE, 'active', NULL, NOW(), NOW());
    ";
    
    $pdo->exec($testData);
    echo "<p style='color: green;'>✅ Données de test insérées</p>";
    
    echo "<hr>";
    echo "<h2>✅ Configuration terminée avec succès !</h2>";
    echo "<p>Votre base de données est maintenant prête avec des données de test.</p>";
    echo "<div class='text-center mt-4'>";
    echo "<a href='index_final.php' class='btn btn-primary'>";
    echo "<i class='fas fa-home'></i> Retour au menu principal";
    echo "</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?> 