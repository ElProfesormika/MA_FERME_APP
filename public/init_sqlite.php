<?php
// Script d'initialisation de la base de données SQLite
require_once 'config_sqlite.php';

echo "<h1>🚀 Initialisation de la base de données SQLite</h1>";

try {
    $db = connectDB();
    
    if (!$db) {
        echo "<p style='color: red;'>❌ Impossible de créer la connexion SQLite</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Connexion SQLite établie</p>";
    
    // Création des tables
    echo "<h2>📋 Création des tables...</h2>";
    
    // Table utilisateurs
    $db->exec("
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom_complet TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            mot_de_passe TEXT NOT NULL,
            role TEXT DEFAULT 'observateur',
            statut TEXT DEFAULT 'actif',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Table utilisateurs créée</p>";
    
    // Table animaux
    $db->exec("
        CREATE TABLE IF NOT EXISTS animaux (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            espece TEXT NOT NULL,
            race TEXT,
            date_naissance DATE,
            poids REAL,
            sexe TEXT,
            employe_id INTEGER,
            statut TEXT DEFAULT 'actif',
            notes TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employe_id) REFERENCES employes(id)
        )
    ");
    echo "<p>✅ Table animaux créée</p>";
    
    // Table stocks
    $db->exec("
        CREATE TABLE IF NOT EXISTS stocks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            produit TEXT NOT NULL,
            quantite REAL NOT NULL,
            unite TEXT NOT NULL,
            prix_unitaire REAL NOT NULL,
            categorie TEXT,
            fournisseur TEXT,
            date_entree DATE,
            date_peremption DATE,
            statut TEXT DEFAULT 'disponible',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Table stocks créée</p>";
    
    // Table employés
    $db->exec("
        CREATE TABLE IF NOT EXISTS employes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom_complet TEXT NOT NULL,
            poste TEXT NOT NULL,
            date_embauche DATE,
            salaire REAL,
            telephone TEXT,
            email TEXT,
            statut TEXT DEFAULT 'actif',
            responsabilites TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✅ Table employés créée</p>";
    
    // Table activités
    $db->exec("
        CREATE TABLE IF NOT EXISTS activites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titre TEXT NOT NULL,
            description TEXT,
            date DATE NOT NULL,
            heure_debut TIME,
            heure_fin TIME,
            type TEXT,
            statut TEXT DEFAULT 'planifie',
            employe_id INTEGER,
            animal_id INTEGER,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employe_id) REFERENCES employes(id),
            FOREIGN KEY (animal_id) REFERENCES animaux(id)
        )
    ");
    echo "<p>✅ Table activités créée</p>";
    
    // Table alertes
    $db->exec("
        CREATE TABLE IF NOT EXISTS alertes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titre TEXT NOT NULL,
            message TEXT NOT NULL,
            type TEXT NOT NULL,
            priorite TEXT DEFAULT 'normale',
            statut TEXT DEFAULT 'active',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_resolution DATETIME
        )
    ");
    echo "<p>✅ Table alertes créée</p>";
    
    // Insertion des données de test
    echo "<h2>📝 Insertion des données de test...</h2>";
    
    // Utilisateurs de test
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $manager_password = password_hash('manager123', PASSWORD_DEFAULT);
    $employe_password = password_hash('employe123', PASSWORD_DEFAULT);
    $observateur_password = password_hash('observateur123', PASSWORD_DEFAULT);
    
    $db->exec("
        INSERT OR IGNORE INTO utilisateurs (nom_complet, email, mot_de_passe, role) VALUES
        ('Administrateur', 'admin@ferme.com', '$admin_password', 'admin'),
        ('Manager', 'manager@ferme.com', '$manager_password', 'manager'),
        ('Employé', 'employe@ferme.com', '$employe_password', 'employe'),
        ('Observateur', 'observateur@ferme.com', '$observateur_password', 'observateur')
    ");
    echo "<p>✅ Utilisateurs de test créés</p>";
    
    // Animaux de test
    $db->exec("
        INSERT OR IGNORE INTO animaux (nom, espece, race, date_naissance, poids, statut) VALUES
        ('Bella', 'Vache', 'Holstein', '2020-03-15', 650.5, 'actif'),
        ('Max', 'Cochon', 'Large White', '2021-06-20', 120.0, 'actif'),
        ('Luna', 'Poule', 'Pondeuse', '2022-01-10', 2.5, 'actif'),
        ('Rocky', 'Mouton', 'Mérinos', '2021-09-05', 45.0, 'actif')
    ");
    echo "<p>✅ Animaux de test créés</p>";
    
    // Stocks de test
    $db->exec("
        INSERT OR IGNORE INTO stocks (produit, quantite, unite, prix_unitaire, categorie, fournisseur, date_entree, date_peremption, statut) VALUES
        ('Foin de prairie', 1000.0, 'kg', 0.5, 'Alimentation', 'Fournisseur Agricole', '2024-01-15', '2025-01-15', 'disponible'),
        ('Grains de maïs', 500.0, 'kg', 0.8, 'Alimentation', 'Coopérative Locale', '2024-02-01', '2024-12-01', 'disponible'),
        ('Vaccins bovins', 50.0, 'doses', 15.0, 'Médicaments', 'Laboratoire Vétérinaire', '2024-01-20', '2024-12-20', 'disponible'),
        ('Outils de maintenance', 10.0, 'unités', 25.0, 'Équipement', 'Magasin Agricole', '2024-01-10', '2026-01-10', 'disponible')
    ");
    echo "<p>✅ Stocks de test créés</p>";
    
    // Employés de test
    $db->exec("
        INSERT OR IGNORE INTO employes (nom_complet, poste, date_embauche, salaire, telephone, email, statut) VALUES
        ('Jean Dupont', 'Chef d''équipe', '2023-01-15', 250000, '+226 70123456', 'jean@ferme.com', 'actif'),
        ('Marie Martin', 'Soigneur', '2023-03-20', 180000, '+226 70234567', 'marie@ferme.com', 'actif'),
        ('Pierre Durand', 'Maintenancier', '2023-06-10', 200000, '+226 70345678', 'pierre@ferme.com', 'actif')
    ");
    echo "<p>✅ Employés de test créés</p>";
    
    // Activités de test
    $db->exec("
        INSERT OR IGNORE INTO activites (titre, description, date, heure_debut, heure_fin, type, statut, responsable) VALUES
        ('Nourrissage matinal', 'Distribution du foin et des grains', date('now'), '06:00', '07:00', 'Soins', 'planifie', 'Marie Martin'),
        ('Vaccination', 'Vaccination des bovins', date('now', '+1 day'), '09:00', '11:00', 'Santé', 'planifie', 'Jean Dupont'),
        ('Maintenance', 'Réparation des clôtures', date('now', '+2 days'), '08:00', '12:00', 'Maintenance', 'planifie', 'Pierre Durand')
    ");
    echo "<p>✅ Activités de test créées</p>";
    
    // Alertes de test
    $db->exec("
        INSERT OR IGNORE INTO alertes (titre, message, type, priorite, statut) VALUES
        ('Stock faible', 'Le stock de foin est faible (moins de 200kg)', 'stock', 'haute', 'active'),
        ('Vaccination à faire', 'Vaccination des poules prévue cette semaine', 'sante', 'normale', 'active'),
        ('Maintenance préventive', 'Vérification des systèmes d''irrigation', 'maintenance', 'basse', 'active')
    ");
    echo "<p>✅ Alertes de test créées</p>";
    
    echo "<h2>🎉 Initialisation terminée avec succès !</h2>";
    echo "<p><strong>Comptes de test créés :</strong></p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@ferme.com / admin123</li>";
    echo "<li><strong>Manager:</strong> manager@ferme.com / manager123</li>";
    echo "<li><strong>Employé:</strong> employe@ferme.com / employe123</li>";
    echo "<li><strong>Observateur:</strong> observateur@ferme.com / observateur123</li>";
    echo "</ul>";
    
    echo "<p><a href='index_final.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Accéder à l'application</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors de l'initialisation : " . $e->getMessage() . "</p>";
}
?>
