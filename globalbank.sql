-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 08 mai 2026 à 12:44
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ubs_users`
--

-- --------------------------------------------------------

--
-- Structure de la table `cartes_virtuelles`
--

DROP TABLE IF EXISTS `cartes_virtuelles`;
CREATE TABLE IF NOT EXISTS `cartes_virtuelles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `numero_carte` varchar(19) NOT NULL,
  `date_expiration` varchar(7) NOT NULL,
  `cvv` varchar(3) NOT NULL,
  `nom_titulaire` varchar(100) NOT NULL,
  `type_carte` varchar(20) DEFAULT 'Visa',
  `plafond` decimal(10,2) DEFAULT '5000.00',
  `statut` varchar(20) DEFAULT 'active',
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_carte` (`numero_carte`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cartes_virtuelles`
--



-- --------------------------------------------------------

--
-- Structure de la table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
CREATE TABLE IF NOT EXISTS `configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cle` varchar(100) NOT NULL,
  `valeur` text,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cle` (`cle`),
  KEY `cle_2` (`cle`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `configuration`
--



-- --------------------------------------------------------

--
-- Structure de la table `historique_acces`
--

DROP TABLE IF EXISTS `historique_acces`;
CREATE TABLE IF NOT EXISTS `historique_acces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `virement_id` int NOT NULL,
  `user_id` int NOT NULL,
  `code_swift` varchar(50) NOT NULL,
  `date_acces` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_adresse` varchar(45) DEFAULT NULL,
  `user_agent` text,
  PRIMARY KEY (`id`),
  KEY `virement_id` (`virement_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `historique_acces`
--

 Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0'),
(2, 9, 1, 'TRX2026000166', '2026-03-19 00:42:59', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0'),
(3, 1, 1, 'TRX202603164501', '2026-03-19 10:34:35', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0'),
(4, 2, 8, 'TRX202603169564', '2026-03-19 23:10:21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0'),
(5, 9, 1, 'TRX2026000166', '2026-04-04 15:18:35', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0'),
(6, 9, 7, 'TRX2026000166', '2026-04-05 01:59:05', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0'),
(7, 9, 1, 'TRX2026000166', '2026-04-14 10:29:06', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0'),
(8, 9, 1, 'TRX2026000166', '2026-04-24 22:32:45', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:149.0) Gecko/20100101 Firefox/149.0');

-- --------------------------------------------------------

--
-- Structure de la table `rib_bancaires`
--

DROP TABLE IF EXISTS `rib_bancaires`;
CREATE TABLE IF NOT EXISTS `rib_bancaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `routing_number` varchar(9) NOT NULL,
  `account_number` varchar(17) NOT NULL,
  `cle_rib` varchar(2) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `swift_code` varchar(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_address` text,
  `beneficiary_name` varchar(100) NOT NULL,
  `beneficiary_address` text,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `rib_bancaires`
--



-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) DEFAULT 'client',
  `pays` varchar(100) DEFAULT 'USA',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--



-- --------------------------------------------------------

--
-- Structure de la table `virements`
--

DROP TABLE IF EXISTS `virements`;
CREATE TABLE IF NOT EXISTS `virements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `code_swift` varchar(50) DEFAULT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `date_valeur` datetime DEFAULT CURRENT_TIMESTAMP,
  `motif_virement` text DEFAULT NULL,
  `expediteur_nom` varchar(100) DEFAULT NULL,
  `expediteur_prenom` varchar(100) DEFAULT NULL,
  `expediteur_pays` varchar(100) DEFAULT NULL,
  `expediteur_numero_aba` varchar(100) DEFAULT NULL,
  `expediteur_numero_compte` varchar(100) DEFAULT NULL,
  `expediteur_nom_banque` varchar(200) DEFAULT NULL,
  `destinataire_nom` varchar(100) DEFAULT NULL,
  `destinataire_prenom` varchar(100) DEFAULT NULL,
  `destinataire_pays` varchar(100) DEFAULT NULL,
  `destinataire_code_banque` varchar(50) DEFAULT NULL,
  `destinataire_code_guichet` varchar(50) DEFAULT NULL,
  `destinataire_numero_compte` varchar(100) DEFAULT NULL,
  `destinataire_nom_banque` varchar(200) DEFAULT NULL,
  `devise` varchar(10) DEFAULT 'USD',
  `montant` decimal(15,2) DEFAULT NULL,
  `motif` text,
  `statut` varchar(50) DEFAULT 'En cours',
  `contact_whatsapp` varchar(50) DEFAULT NULL,
  `message_statut` text,
  `client_email` varchar(100) DEFAULT NULL,
  `pourcentage` int DEFAULT '95',
  `expediteur_bic` varchar(20) DEFAULT 'BKBCGB2L',
  `destinataire_bic` varchar(20) DEFAULT 'UGABGALI',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_swift` (`code_swift`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `virements`
--


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
