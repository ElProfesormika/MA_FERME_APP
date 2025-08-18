-- Script SQL pour ajouter les colonnes manquantes
-- À exécuter dans phpMyAdmin

USE ferme_db;

-- 1. Ajouter les colonnes manquantes à la table users
ALTER TABLE users 
ADD COLUMN role ENUM('admin', 'manager', 'user') DEFAULT 'user' AFTER email,
ADD COLUMN permissions JSON NULL AFTER role,
ADD COLUMN last_login TIMESTAMP NULL AFTER permissions;

-- 2. Ajouter les colonnes manquantes à la table alertes
ALTER TABLE alertes 
ADD COLUMN titre VARCHAR(255) NOT NULL DEFAULT '' AFTER type,
ADD COLUMN description TEXT NOT NULL AFTER titre,
ADD COLUMN reference_id BIGINT UNSIGNED NULL AFTER description,
ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reference_id,
ADD COLUMN date_echeance DATE NULL AFTER date_creation,
ADD COLUMN priorite ENUM('basse', 'moyenne', 'haute', 'critique') DEFAULT 'moyenne' AFTER type,
ADD COLUMN assigne_a BIGINT UNSIGNED NULL AFTER employe_id;

-- 3. Mettre à jour l'utilisateur existant avec le rôle admin
UPDATE users SET 
role = 'admin', 
permissions = '["animaux", "stocks", "activites", "employes", "alertes", "rapports", "equipe", "systeme"]' 
WHERE id = 1;

-- 4. Ajouter des utilisateurs de test
INSERT INTO users (name, email, password, role, permissions, created_at) VALUES
('Manager Farm', 'manager@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', '["animaux", "stocks", "activites", "employes", "alertes", "rapports"]', NOW()),
('Employé Farm', 'employe@ferme.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '["animaux", "stocks", "activites"]', NOW())
ON DUPLICATE KEY UPDATE role = VALUES(role), permissions = VALUES(permissions);

-- 5. Créer la table notifications si elle n'existe pas
CREATE TABLE IF NOT EXISTS notifications (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Créer la table user_sessions si elle n'existe pas
CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(191) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_sessions_token (token),
    INDEX idx_user_sessions_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ajouter des index pour améliorer les performances
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_alertes_statut ON alertes(statut);
CREATE INDEX IF NOT EXISTS idx_alertes_priorite ON alertes(priorite);
CREATE INDEX IF NOT EXISTS idx_alertes_type ON alertes(type);

-- 8. Insérer des notifications de test
INSERT INTO notifications (user_id, type, titre, message, lien) VALUES
(1, 'alerte', 'Stock faible', 'Le stock de nourriture pour animaux est faible', 'stocks_improved.php'),
(1, 'activite', 'Activité planifiée', 'Soins des animaux prévus pour aujourd''hui', 'activites_improved.php'),
(2, 'stock', 'Nouveau stock arrivé', 'Livraison de médicaments reçue', 'stocks_improved.php')
ON DUPLICATE KEY UPDATE titre = VALUES(titre), message = VALUES(message);

-- 9. Vérification finale
SELECT 'Colonnes ajoutées avec succès !' as message;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_admins FROM users WHERE role = 'admin';
SELECT COUNT(*) as total_managers FROM users WHERE role = 'manager';
SELECT COUNT(*) as total_alertes FROM alertes; 