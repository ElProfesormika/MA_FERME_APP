-- Script de configuration de la base de données pour la ferme d'élevage
-- À exécuter dans phpMyAdmin

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS ferme_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE ferme_db;

-- Table des utilisateurs (pour l'authentification)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
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
    email VARCHAR(255) NULL,
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

-- Table des logs d'activité
CREATE TABLE IF NOT EXISTS logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    model_type VARCHAR(100) NULL,
    model_id BIGINT UNSIGNED NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insérer des données de test
INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('Admin', 'admin@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

INSERT INTO employes (nom, prenom, poste, date_embauche, salaire, telephone, email, statut, created_at, updated_at) VALUES
('Dupont', 'Jean', 'Vétérinaire', '2023-01-15', 3500.00, '0123456789', 'jean.dupont@ferme.com', 'actif', NOW(), NOW()),
('Martin', 'Marie', 'Soigneur', '2023-02-01', 2200.00, '0987654321', 'marie.martin@ferme.com', 'actif', NOW(), NOW()),
('Bernard', 'Pierre', 'Éleveur', '2022-11-10', 2800.00, '0555666777', 'pierre.bernard@ferme.com', 'actif', NOW(), NOW());

INSERT INTO animaux (nom, espece, race, date_naissance, poids, sexe, employe_id, created_at, updated_at) VALUES
('Belle', 'Vache', 'Holstein', '2020-03-15', 650.50, 'femelle', 1, NOW(), NOW()),
('Max', 'Taureau', 'Charolais', '2019-07-22', 850.00, 'male', 1, NOW(), NOW()),
('Luna', 'Vache', 'Jersey', '2021-01-10', 450.25, 'femelle', 2, NOW(), NOW());

INSERT INTO stocks (produit, quantite, unite, date_entree, date_peremption, prix_unitaire, fournisseur, categorie, created_at, updated_at) VALUES
('Foin de prairie', 1000.00, 'kg', '2024-01-15', '2025-01-15', 0.50, 'Fournisseur Agricole', 'Alimentation', NOW(), NOW()),
('Vaccin bovin', 50.00, 'doses', '2024-02-01', '2024-12-01', 15.00, 'Laboratoire Vétérinaire', 'Médicament', NOW(), NOW()),
('Minéraux', 200.00, 'kg', '2024-01-20', '2025-01-20', 2.50, 'Nutrition Animale', 'Complément', NOW(), NOW());

INSERT INTO activites (titre, description, date, heure_debut, heure_fin, type, statut, employe_id, animal_id, created_at, updated_at) VALUES
('Vaccination annuelle', 'Vaccination contre les maladies courantes', '2024-08-08', '09:00:00', '11:00:00', 'soins', 'planifie', 1, 1, NOW(), NOW()),
('Distribution alimentation', 'Distribution du foin et des compléments', '2024-08-08', '07:00:00', '08:00:00', 'alimentation', 'planifie', 2, NULL, NOW(), NOW()),
('Contrôle sanitaire', 'Vérification de l\'état de santé général', '2024-08-07', '14:00:00', '16:00:00', 'soins', 'termine', 1, 2, NOW(), NOW());

INSERT INTO alertes (type, message, critique, statut, animal_id, created_at, updated_at) VALUES
('sante', 'Animal Belle présente des signes de fatigue', FALSE, 'active', 1, NOW(), NOW()),
('stock', 'Stock de foin faible (moins de 200kg)', TRUE, 'active', NULL, NOW(), NOW());

-- Créer les index pour optimiser les performances
CREATE INDEX idx_animaux_employe ON animaux(employe_id);
CREATE INDEX idx_activites_date ON activites(date);
CREATE INDEX idx_activites_employe ON activites(employe_id);
CREATE INDEX idx_alertes_statut ON alertes(statut);
CREATE INDEX idx_stocks_peremption ON stocks(date_peremption);
CREATE INDEX idx_logs_user ON logs(user_id);
CREATE INDEX idx_logs_action ON logs(action); 