-- Script d'initialisation de la base de données pour Railway
-- Ce script sera exécuté automatiquement lors du déploiement

-- Création de la base de données si elle n'existe pas
CREATE DATABASE IF NOT EXISTS ferme_db;
USE ferme_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employe', 'observateur') DEFAULT 'observateur',
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
);

-- Table des animaux
CREATE TABLE IF NOT EXISTS animaux (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    espece VARCHAR(50) NOT NULL,
    race VARCHAR(50),
    date_naissance DATE,
    poids DECIMAL(5,2),
    statut ENUM('actif', 'vendu', 'mort', 'malade') DEFAULT 'actif',
    prix_achat DECIMAL(10,2),
    prix_vente DECIMAL(10,2),
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des stocks
CREATE TABLE IF NOT EXISTS stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_produit VARCHAR(100) NOT NULL,
    categorie VARCHAR(50),
    quantite INT NOT NULL DEFAULT 0,
    unite VARCHAR(20) DEFAULT 'kg',
    prix_unitaire DECIMAL(10,2),
    date_expiration DATE,
    fournisseur VARCHAR(100),
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des employés
CREATE TABLE IF NOT EXISTS employes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    poste VARCHAR(50),
    date_embauche DATE,
    salaire DECIMAL(10,2),
    telephone VARCHAR(20),
    email VARCHAR(100),
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    responsabilites TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des activités
CREATE TABLE IF NOT EXISTS activites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('soins', 'nourriture', 'maintenance', 'vente', 'achat', 'autre') NOT NULL,
    date_debut DATETIME,
    date_fin DATETIME,
    statut ENUM('planifie', 'en_cours', 'termine', 'annule') DEFAULT 'planifie',
    priorite ENUM('basse', 'normale', 'haute', 'urgente') DEFAULT 'normale',
    responsable VARCHAR(100),
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des alertes
CREATE TABLE IF NOT EXISTS alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    statut ENUM('active', 'resolue', 'ignoree') DEFAULT 'active',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_resolution TIMESTAMP NULL
);

-- Insertion des données de test

-- Utilisateur admin par défaut
INSERT INTO utilisateurs (nom, email, mot_de_passe, role, statut) VALUES
('Admin Principal', 'admin@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif'),
('Manager Test', 'manager@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'actif'),
('Employé Test', 'employe@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employe', 'actif'),
('Observateur Test', 'observateur@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'observateur', 'actif');

-- Animaux de test
INSERT INTO animaux (nom, espece, race, date_naissance, poids, statut, prix_achat, prix_vente) VALUES
('Bessie', 'Vache', 'Holstein', '2020-03-15', 650.00, 'actif', 2500.00, 3500.00),
('Max', 'Cochon', 'Large White', '2021-06-20', 120.00, 'actif', 800.00, 1200.00),
('Poulette', 'Poule', 'Rhode Island', '2022-01-10', 2.50, 'actif', 15.00, 25.00),
('Bélier', 'Mouton', 'Mérinos', '2019-11-05', 85.00, 'actif', 600.00, 900.00);

-- Stocks de test
INSERT INTO stocks (nom_produit, categorie, quantite, unite, prix_unitaire, date_expiration, fournisseur) VALUES
('Aliment pour vaches', 'Nourriture', 1000, 'kg', 0.80, '2024-12-31', 'AgroPlus'),
('Vaccin bovin', 'Médicament', 50, 'doses', 15.00, '2024-06-30', 'VetPharma'),
('Foin de prairie', 'Fourrage', 500, 'kg', 0.30, '2024-08-31', 'Fermier Local'),
('Minéraux', 'Complément', 100, 'kg', 2.50, '2025-12-31', 'NutriAnimal');

-- Employés de test
INSERT INTO employes (nom_complet, poste, date_embauche, salaire, telephone, email, statut, responsabilites) VALUES
('Jean Dupont', 'Ouvrier agricole', '2023-01-15', 1800.00, '0123456789', 'jean@ferme.com', 'actif', 'Soins des animaux, maintenance'),
('Marie Martin', 'Vétérinaire', '2022-06-01', 3500.00, '0987654321', 'marie@ferme.com', 'actif', 'Soins vétérinaires, vaccinations'),
('Pierre Durand', 'Chef d\'équipe', '2021-03-10', 2200.00, '0555666777', 'pierre@ferme.com', 'actif', 'Supervision, planning'),
('Sophie Bernard', 'Comptable', '2023-09-01', 2000.00, '0444555666', 'sophie@ferme.com', 'actif', 'Gestion financière, rapports');

-- Activités de test
INSERT INTO activites (titre, description, type, date_debut, date_fin, statut, priorite, responsable) VALUES
('Vaccination des vaches', 'Vaccination annuelle contre les maladies bovines', 'soins', '2024-02-15 09:00:00', '2024-02-15 17:00:00', 'planifie', 'haute', 'Marie Martin'),
('Récolte du foin', 'Récolte du foin de prairie pour l\'hiver', 'maintenance', '2024-07-20 08:00:00', '2024-07-25 18:00:00', 'planifie', 'normale', 'Jean Dupont'),
('Vente de cochons', 'Vente de 5 cochons au marché', 'vente', '2024-03-10 06:00:00', '2024-03-10 12:00:00', 'planifie', 'normale', 'Pierre Durand'),
('Achat d\'aliments', 'Commande d\'aliments pour le mois', 'achat', '2024-02-01 10:00:00', '2024-02-01 11:00:00', 'planifie', 'normale', 'Sophie Bernard');

-- Alertes de test
INSERT INTO alertes (titre, message, type, statut) VALUES
('Stock faible', 'Le stock d\'aliments pour vaches est faible (100kg restant)', 'warning', 'active'),
('Vaccination due', 'Vaccination des poules due dans 3 jours', 'info', 'active'),
('Maintenance préventive', 'Vérification des clôtures prévue cette semaine', 'info', 'active'),
('Vente réussie', 'Vente de 2 vaches réalisée avec succès', 'success', 'resolue');
