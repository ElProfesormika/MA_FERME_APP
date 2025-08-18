-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 09 août 2025 à 00:06
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ferme_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `activites`
--

DROP TABLE IF EXISTS `activites`;
CREATE TABLE IF NOT EXISTS `activites` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `date` date NOT NULL,
  `heure_debut` time DEFAULT NULL,
  `heure_fin` time DEFAULT NULL,
  `type` enum('soins','alimentation','reproduction','maintenance','autre') COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('planifie','en_cours','termine','annule') COLLATE utf8mb4_unicode_ci DEFAULT 'planifie',
  `employe_id` bigint UNSIGNED DEFAULT NULL,
  `animal_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employe_id` (`employe_id`),
  KEY `animal_id` (`animal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `activites`
--

INSERT INTO `activites` (`id`, `titre`, `description`, `date`, `heure_debut`, `heure_fin`, `type`, `statut`, `employe_id`, `animal_id`, `created_at`, `updated_at`) VALUES
(1, 'Soins vétérinaires', 'Vaccination et contrôle sanitaire', '2024-08-08', '09:00:00', '11:00:00', 'soins', 'planifie', 2, 1, '2025-08-08 18:11:23', NULL),
(2, 'Alimentation du bétail', 'Distribution de nourriture', '2024-08-08', '07:00:00', '08:00:00', 'alimentation', 'termine', 1, NULL, '2025-08-08 18:11:23', NULL),
(3, 'Maintenance des équipements', 'Réparation du système d\'abreuvement', '2024-08-09', '14:00:00', '16:00:00', 'maintenance', 'planifie', 3, NULL, '2025-08-08 18:11:23', NULL),
(4, 'Vaccination annuelle', 'Vaccination contre les maladies courantes', '2024-08-08', '09:00:00', '11:00:00', 'soins', 'planifie', 1, 1, '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(5, 'Distribution alimentation', 'Distribution du foin et des compléments', '2024-08-08', '07:00:00', '08:00:00', 'alimentation', 'planifie', 2, NULL, '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(6, 'Contrôle sanitaire', 'Vérification de l\'état de santé général', '2024-08-07', '14:00:00', '16:00:00', 'soins', 'termine', 1, 2, '2025-08-08 21:23:33', '2025-08-08 21:23:33');

-- --------------------------------------------------------

--
-- Structure de la table `alertes`
--

DROP TABLE IF EXISTS `alertes`;
CREATE TABLE IF NOT EXISTS `alertes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('sante','stock','maintenance','urgence','stock_rupture','stock_peremption','activite_retard') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_echeance` date DEFAULT NULL,
  `priorite` enum('basse','moyenne','haute','critique') COLLATE utf8mb4_unicode_ci DEFAULT 'moyenne',
  `critique` tinyint(1) DEFAULT '0',
  `statut` enum('active','resolue','ignoree') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `animal_id` bigint UNSIGNED DEFAULT NULL,
  `stock_id` bigint UNSIGNED DEFAULT NULL,
  `employe_id` bigint UNSIGNED DEFAULT NULL,
  `assigne_a` bigint UNSIGNED DEFAULT NULL,
  `date_resolution` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `animal_id` (`animal_id`),
  KEY `stock_id` (`stock_id`),
  KEY `employe_id` (`employe_id`),
  KEY `idx_alertes_statut` (`statut`),
  KEY `idx_alertes_priorite` (`priorite`),
  KEY `idx_alertes_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `alertes`
--

INSERT INTO `alertes` (`id`, `type`, `message`, `titre`, `description`, `reference_id`, `date_creation`, `date_echeance`, `priorite`, `critique`, `statut`, `animal_id`, `stock_id`, `employe_id`, `assigne_a`, `date_resolution`, `created_at`, `updated_at`) VALUES
(1, 'stock', NULL, 'Stock faible', 'La nourriture pour bovins est en quantité faible', NULL, '2025-08-08 18:11:23', NULL, 'haute', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 18:11:23', NULL),
(2, 'sante', NULL, 'Animal malade', 'La vache Belle présente des symptômes', NULL, '2025-08-08 18:11:23', NULL, 'critique', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 18:11:23', NULL),
(3, 'activite_retard', NULL, 'Activité en retard : Soins vétérinaires', 'L\'activité Soins vétérinaires était prévue le 08/08/2024', 1, '2025-08-08 21:05:03', NULL, 'haute', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 21:05:03', '2025-08-08 21:05:03'),
(4, 'activite_retard', NULL, 'Activité en retard : Maintenance des équipements', 'L\'activité Maintenance des équipements était prévue le 09/08/2024', 3, '2025-08-08 21:05:03', NULL, 'haute', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 21:05:03', '2025-08-08 21:05:03'),
(5, 'sante', 'Animal Belle présente des signes de fatigue', '', '', NULL, '2025-08-08 21:23:33', NULL, 'moyenne', 0, 'active', 1, NULL, NULL, NULL, NULL, '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(7, 'activite_retard', NULL, 'Activité en retard : Vaccination annuelle', 'L\'activité Vaccination annuelle était prévue le 08/08/2024', 4, '2025-08-08 22:30:08', NULL, 'haute', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 22:30:08', '2025-08-08 22:30:08'),
(8, 'activite_retard', NULL, 'Activité en retard : Distribution alimentation', 'L\'activité Distribution alimentation était prévue le 08/08/2024', 5, '2025-08-08 22:30:08', NULL, 'haute', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-08-08 22:30:08', '2025-08-08 22:30:08');

-- --------------------------------------------------------

--
-- Structure de la table `animaux`
--

DROP TABLE IF EXISTS `animaux`;
CREATE TABLE IF NOT EXISTS `animaux` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `espece` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `race` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date NOT NULL,
  `historique_sante` text COLLATE utf8mb4_unicode_ci,
  `poids` decimal(8,2) DEFAULT NULL,
  `sexe` enum('male','femelle') COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('actif','vendu','mort','malade') COLLATE utf8mb4_unicode_ci DEFAULT 'actif',
  `employe_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employe_id` (`employe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `animaux`
--

INSERT INTO `animaux` (`id`, `nom`, `espece`, `race`, `date_naissance`, `historique_sante`, `poids`, `sexe`, `statut`, `employe_id`, `created_at`, `updated_at`) VALUES
(1, 'Belle', 'Vache', 'Holstein', '2020-05-15', NULL, 650.50, 'femelle', 'actif', 1, '2025-08-08 18:11:23', NULL),
(2, 'Max', 'Taureau', 'Charolais', '2019-08-22', NULL, 850.00, 'male', 'actif', 1, '2025-08-08 18:11:23', NULL),
(3, 'Luna', 'Vache', 'Jersey', '2021-03-10', NULL, 450.20, 'femelle', 'actif', 2, '2025-08-08 18:11:23', NULL),
(4, 'Belle', 'Vache', 'Holstein', '2020-03-15', NULL, 650.50, 'femelle', 'actif', 1, '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(5, 'Max', 'Taureau', 'Charolais', '2019-07-22', NULL, 850.00, 'male', 'actif', 1, '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(6, 'Luna', 'Vache', 'Jersey', '2021-01-10', NULL, 450.25, 'femelle', 'actif', 2, '2025-08-08 21:23:33', '2025-08-08 21:23:33');

-- --------------------------------------------------------

--
-- Structure de la table `employes`
--

DROP TABLE IF EXISTS `employes`;
CREATE TABLE IF NOT EXISTS `employes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poste` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_embauche` date NOT NULL,
  `salaire` decimal(10,2) NOT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci,
  `statut` enum('actif','inactif','vacances') COLLATE utf8mb4_unicode_ci DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `employes`
--

INSERT INTO `employes` (`id`, `nom`, `prenom`, `poste`, `date_embauche`, `salaire`, `telephone`, `email`, `adresse`, `statut`, `created_at`, `updated_at`) VALUES
(1, 'Dupont', 'Jean', 'Éleveur', '2023-01-15', 150000.00, '+226 70123456', 'jean.dupont@ferme.com', NULL, 'actif', '2025-08-08 18:11:23', NULL),
(2, 'Martin', 'Marie', 'Vétérinaire', '2023-03-20', 200000.00, '+226 70234567', 'marie.martin@ferme.com', NULL, 'actif', '2025-08-08 18:11:23', NULL),
(3, 'Bernard', 'Pierre', 'Ouvrier agricole', '2023-06-10', 120000.00, '+226 70345678', 'pierre.bernard@ferme.com', NULL, 'actif', '2025-08-08 18:11:23', NULL),
(4, 'Dupont', 'Jean', 'Vétérinaire', '2023-01-15', 3500.00, '0123456789', 'jean.dupont@ferme.com', NULL, 'actif', '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(5, 'Martin', 'Marie', 'Soigneur', '2023-02-01', 2200.00, '0987654321', 'marie.martin@ferme.com', NULL, 'actif', '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(6, 'Bernard', 'Pierre', 'Éleveur', '2022-11-10', 2800.00, '0555666777', 'pierre.bernard@ferme.com', NULL, 'actif', '2025-08-08 21:23:33', '2025-08-08 21:23:33');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('alerte','activite','stock','systeme') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lu` tinyint(1) DEFAULT '0',
  `lien` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_lu` (`user_id`,`lu`),
  KEY `idx_notifications_type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `titre`, `message`, `lu`, `lien`, `created_at`) VALUES
(1, 1, 'alerte', 'Stock faible', 'Le stock de nourriture pour animaux est faible', 0, 'stocks_improved.php', '2025-08-08 18:11:55'),
(2, 1, 'activite', 'Activité planifiée', 'Soins des animaux prévus pour aujourd\'hui', 0, 'activites_improved.php', '2025-08-08 18:11:55'),
(3, 2, 'stock', 'Nouveau stock arrivé', 'Livraison de médicaments reçue', 0, 'stocks_improved.php', '2025-08-08 18:11:55'),
(4, 1, 'alerte', 'Stock faible', 'Le stock de nourriture pour animaux est faible', 0, 'stocks_improved.php', '2025-08-08 20:25:22'),
(5, 1, 'activite', 'Activité planifiée', 'Soins des animaux prévus pour aujourd\'hui', 0, 'activites_improved.php', '2025-08-08 20:25:22'),
(6, 2, 'stock', 'Nouveau stock arrivé', 'Livraison de médicaments reçue', 0, 'stocks_improved.php', '2025-08-08 20:25:22');

-- --------------------------------------------------------

--
-- Structure de la table `stocks`
--

DROP TABLE IF EXISTS `stocks`;
CREATE TABLE IF NOT EXISTS `stocks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `produit` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantite` decimal(10,2) NOT NULL,
  `unite` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_entree` date NOT NULL,
  `date_peremption` date DEFAULT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `fournisseur` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categorie` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stocks`
--

INSERT INTO `stocks` (`id`, `produit`, `quantite`, `unite`, `date_entree`, `date_peremption`, `prix_unitaire`, `fournisseur`, `categorie`, `created_at`, `updated_at`) VALUES
(1, 'Nourriture pour bovins', 1000.00, 'kg', '2024-01-15', '2024-12-31', 500.00, 'AgroFournitures', 'Alimentation', '2025-08-08 18:11:23', NULL),
(2, 'Médicaments vétérinaires', 50.00, 'boîtes', '2024-02-01', '2025-06-30', 2500.00, 'PharmaVet', 'Santé', '2025-08-08 18:11:23', NULL),
(3, 'Matériel d\'élevage', 25.00, 'pièces', '2024-01-20', NULL, 15000.00, 'EquipElevage', 'Équipement', '2025-08-08 18:11:23', NULL),
(4, 'Foin de prairie', 1000.00, 'kg', '2024-01-15', '2025-01-15', 0.50, 'Fournisseur Agricole', 'Alimentation', '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(5, 'Vaccin bovin', 50.00, 'doses', '2024-02-01', '2024-12-01', 15.00, 'Laboratoire Vétérinaire', 'Médicament', '2025-08-08 21:23:33', '2025-08-08 21:23:33'),
(6, 'Minéraux', 200.00, 'kg', '2024-01-20', '2025-01-20', 2.50, 'Nutrition Animale', 'Complément', '2025-08-08 21:23:33', '2025-08-08 21:23:33');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `permissions` json DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `permissions`, `last_login`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin Farm', 'admin@ferme.com', 'admin', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\", \"equipe\", \"systeme\"]', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-08-08 18:11:22', NULL),
(2, 'Manager Farm', 'manager@ferme.com', 'manager', '[\"animaux\", \"stocks\", \"activites\", \"employes\", \"alertes\", \"rapports\"]', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-08-08 18:11:22', NULL),
(3, 'Employé Farm', 'employe@ferme.com', 'user', '[\"animaux\", \"stocks\", \"activites\"]', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2025-08-08 18:11:22', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_sessions_token` (`token`),
  KEY `idx_user_sessions_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activites`
--
ALTER TABLE `activites`
  ADD CONSTRAINT `activites_ibfk_1` FOREIGN KEY (`employe_id`) REFERENCES `employes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activites_ibfk_2` FOREIGN KEY (`animal_id`) REFERENCES `animaux` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `alertes`
--
ALTER TABLE `alertes`
  ADD CONSTRAINT `alertes_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animaux` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alertes_ibfk_2` FOREIGN KEY (`stock_id`) REFERENCES `stocks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alertes_ibfk_3` FOREIGN KEY (`employe_id`) REFERENCES `employes` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `animaux`
--
ALTER TABLE `animaux`
  ADD CONSTRAINT `animaux_ibfk_1` FOREIGN KEY (`employe_id`) REFERENCES `employes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
