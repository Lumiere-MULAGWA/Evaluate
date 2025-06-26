-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for evaluation_personnel2
DROP DATABASE IF EXISTS `evaluation_personnel2`;
CREATE DATABASE IF NOT EXISTS `evaluation_personnel2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `evaluation_personnel2`;

-- Dumping structure for table evaluation_personnel2.departements
DROP TABLE IF EXISTS `departements`;
CREATE TABLE IF NOT EXISTS `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `chef_departement_id` int DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `idx_chef` (`chef_departement_id`),
  CONSTRAINT `fk_dept_chef` FOREIGN KEY (`chef_departement_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table evaluation_personnel2.departements: ~5 rows (approximately)
DELETE FROM `departements`;
INSERT INTO `departements` (`id`, `nom`, `description`, `chef_departement_id`, `budget`, `date_creation`, `actif`) VALUES
	(1, 'Ressources Humaines', 'Gestion du personnel et des compétences', NULL, NULL, '2025-06-25 22:28:32', 1),
	(2, 'Informatique', 'Développement et maintenance des systèmes', NULL, NULL, '2025-06-25 22:28:32', 1),
	(3, 'Commercial', 'Ventes et relation client', NULL, NULL, '2025-06-25 22:28:32', 1),
	(4, 'Marketing', 'Communication et promotion', NULL, NULL, '2025-06-25 22:28:32', 1),
	(5, 'Finance', 'Comptabilité et gestion financière', NULL, NULL, '2025-06-25 22:28:32', 1);

-- Dumping structure for table evaluation_personnel2.evaluations
DROP TABLE IF EXISTS `evaluations`;
CREATE TABLE IF NOT EXISTS `evaluations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_employe` int NOT NULL,
  `id_evaluateur` int NOT NULL,
  `critere` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` int NOT NULL,
  `commentaire` text COLLATE utf8mb4_unicode_ci,
  `periode_debut` date NOT NULL,
  `periode_fin` date NOT NULL,
  `statut` enum('brouillon','finalise','valide') COLLATE utf8mb4_unicode_ci DEFAULT 'brouillon',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evaluation` (`id_employe`,`id_evaluateur`,`critere`,`periode_debut`,`periode_fin`),
  KEY `idx_employe` (`id_employe`),
  KEY `idx_evaluateur` (`id_evaluateur`),
  KEY `idx_periode` (`periode_debut`,`periode_fin`),
  KEY `idx_critere` (`critere`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`id_employe`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`id_evaluateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluations_chk_1` CHECK (((`note` >= 0) and (`note` <= 100)))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table evaluation_personnel2.evaluations: ~6 rows (approximately)
DELETE FROM `evaluations`;
INSERT INTO `evaluations` (`id`, `id_employe`, `id_evaluateur`, `critere`, `note`, `commentaire`, `periode_debut`, `periode_fin`, `statut`, `date_creation`, `date_modification`) VALUES
	(1, 41, 38, 'ponctualite', 14, 'dvdfvdfbfgbfgbfgbfgbfgfg', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49'),
	(2, 41, 38, 'competence', 14, '', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49'),
	(3, 41, 38, 'travail_equipe', 14, '', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49'),
	(4, 41, 38, 'initiative', 16, '', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49'),
	(5, 41, 38, 'qualite_travail', 16, '', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49'),
	(6, 41, 38, 'communication', 16, '', '2025-06-26', '2025-06-26', 'finalise', '2025-06-26 12:19:49', '2025-06-26 12:19:49');

-- Dumping structure for table evaluation_personnel2.objectifs
DROP TABLE IF EXISTS `objectifs`;
CREATE TABLE IF NOT EXISTS `objectifs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_employe` int NOT NULL,
  `id_createur` int NOT NULL,
  `titre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_echeance` date NOT NULL,
  `priorite` enum('basse','moyenne','haute','critique') COLLATE utf8mb4_unicode_ci DEFAULT 'moyenne',
  `statut` enum('nouveau','en_cours','termine','reporte','annule') COLLATE utf8mb4_unicode_ci DEFAULT 'nouveau',
  `progres` int DEFAULT '0',
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_employe` (`id_employe`),
  KEY `idx_createur` (`id_createur`),
  KEY `idx_echeance` (`date_echeance`),
  KEY `idx_statut` (`statut`),
  CONSTRAINT `objectifs_ibfk_1` FOREIGN KEY (`id_employe`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `objectifs_ibfk_2` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `objectifs_chk_1` CHECK (((`progres` >= 0) and (`progres` <= 100)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table evaluation_personnel2.objectifs: ~0 rows (approximately)
DELETE FROM `objectifs`;

-- Dumping structure for table evaluation_personnel2.services
DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `id_departement` int NOT NULL,
  `chef_service_id` int DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actif` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_departement` (`id_departement`),
  KEY `idx_chef` (`chef_service_id`),
  CONSTRAINT `fk_service_chef` FOREIGN KEY (`chef_service_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table evaluation_personnel2.services: ~70 rows (approximately)
DELETE FROM `services`;
INSERT INTO `services` (`id`, `nom`, `description`, `id_departement`, `chef_service_id`, `date_creation`, `actif`) VALUES
	(1, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-25 22:28:32', 1),
	(2, 'Formation', 'Formation et développement', 1, NULL, '2025-06-25 22:28:32', 1),
	(3, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-25 22:28:32', 1),
	(4, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-25 22:28:32', 1),
	(5, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-25 22:28:32', 1),
	(6, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-25 22:28:32', 1),
	(7, 'Communication', 'Communication externe', 4, NULL, '2025-06-25 22:28:32', 1),
	(8, 'Digital', 'Marketing digital', 4, NULL, '2025-06-25 22:28:32', 1),
	(9, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-25 22:28:32', 1),
	(10, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-25 22:28:32', 1),
	(11, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:29:27', 1),
	(12, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:29:27', 1),
	(13, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:29:27', 1),
	(14, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:29:27', 1),
	(15, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:29:28', 1),
	(16, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:29:28', 1),
	(17, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:29:28', 1),
	(18, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:29:28', 1),
	(19, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:29:28', 1),
	(20, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:29:28', 1),
	(21, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:31:50', 1),
	(22, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:31:50', 1),
	(23, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:31:50', 1),
	(24, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:31:50', 1),
	(25, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:31:50', 1),
	(26, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:31:50', 1),
	(27, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:31:51', 1),
	(28, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:31:51', 1),
	(29, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:31:51', 1),
	(30, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:31:51', 1),
	(31, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:31:56', 1),
	(32, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:31:56', 1),
	(33, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:31:56', 1),
	(34, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:31:56', 1),
	(35, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:31:57', 1),
	(36, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:31:57', 1),
	(37, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:31:57', 1),
	(38, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:31:57', 1),
	(39, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:31:57', 1),
	(40, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:31:57', 1),
	(41, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:33:12', 1),
	(42, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:33:13', 1),
	(43, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:33:13', 1),
	(44, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:33:13', 1),
	(45, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:33:13', 1),
	(46, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:33:13', 1),
	(47, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:33:13', 1),
	(48, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:33:13', 1),
	(49, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:33:13', 1),
	(50, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:33:13', 1),
	(51, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:34:01', 1),
	(52, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:34:01', 1),
	(53, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:34:01', 1),
	(54, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:34:01', 1),
	(55, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:34:01', 1),
	(56, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:34:01', 1),
	(57, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:34:01', 1),
	(58, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:34:01', 1),
	(59, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:34:01', 1),
	(60, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:34:01', 1),
	(61, 'Recrutement', 'Recrutement et intégration', 1, NULL, '2025-06-26 10:50:55', 1),
	(62, 'Formation', 'Formation et développement', 1, NULL, '2025-06-26 10:50:55', 1),
	(63, 'Développement Web', 'Applications web et sites', 2, NULL, '2025-06-26 10:50:55', 1),
	(64, 'Infrastructure', 'Serveurs et réseaux', 2, NULL, '2025-06-26 10:50:55', 1),
	(65, 'Vente B2B', 'Vente aux entreprises', 3, NULL, '2025-06-26 10:50:55', 1),
	(66, 'Vente B2C', 'Vente aux particuliers', 3, NULL, '2025-06-26 10:50:55', 1),
	(67, 'Communication', 'Communication externe', 4, NULL, '2025-06-26 10:50:56', 1),
	(68, 'Digital', 'Marketing digital', 4, NULL, '2025-06-26 10:50:56', 1),
	(69, 'Comptabilité', 'Comptabilité générale', 5, NULL, '2025-06-26 10:50:56', 1),
	(70, 'Contrôle de gestion', 'Analyse financière', 5, NULL, '2025-06-26 10:50:56', 1);

-- Dumping structure for table evaluation_personnel2.utilisateurs
DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('drh','chef_departement','chef_service','employe') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employe',
  `id_service` int DEFAULT NULL,
  `id_departement` int DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_embauche` date DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `actif` tinyint(1) DEFAULT '1',
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_service` (`id_service`),
  KEY `idx_departement` (`id_departement`),
  KEY `idx_email` (`email`),
  CONSTRAINT `fk_user_departement` FOREIGN KEY (`id_departement`) REFERENCES `departements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_user_service` FOREIGN KEY (`id_service`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table evaluation_personnel2.utilisateurs: ~8 rows (approximately)
DELETE FROM `utilisateurs`;
INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `id_service`, `id_departement`, `telephone`, `date_embauche`, `date_creation`, `date_modification`, `actif`, `derniere_connexion`) VALUES
	(1, 'Administrateur', 'Système', 'admin@evaluate.local', '$2y$10$yaRetA6CVtvKW6FZd4YMa.lYswepz6eleJbYuxFP.BMIGW1KJB.te', 'drh', NULL, 1, NULL, NULL, '2025-06-25 22:28:33', '2025-06-25 22:28:33', 1, NULL),
	(38, 'Martin', 'Pierre', 'chef.dev@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'chef_service', 3, 2, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(39, 'Dubois', 'Marie', 'chef.commercial@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'chef_service', 5, 3, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(40, 'Lefebvre', 'Jean', 'chef.marketing@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'chef_departement', 8, 4, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(41, 'Moreau', 'Sophie', 'employe1@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'employe', 3, 2, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(42, 'Garcia', 'Luis', 'employe2@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'employe', 5, 3, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(43, 'Bernard', 'Alice', 'employe3@test.local', '$2y$10$xgwRLs7AziCqs5cxAe7yPOy4xQkoLcKrFYgtqZbCoyFgARs.K55mS', 'employe', 7, 4, NULL, NULL, '2025-06-26 10:50:56', '2025-06-26 10:50:56', 1, NULL),
	(44, 'employe', '', 'employe@evaluate.local', '$2y$10$cqFBVHtN2MVxjD3U0DTIU.el6bhvG9kK4N95gZI2DrODPxCkF/1Ai', 'employe', 39, NULL, NULL, NULL, '2025-06-26 11:14:28', '2025-06-26 11:14:28', 1, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
