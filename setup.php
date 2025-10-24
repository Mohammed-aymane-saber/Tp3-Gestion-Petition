<?php

/**
 * ==========================================================================
 * ==  ATTENTION : SCRIPT D'INITIALISATION DE LA BASE DE DONNÉES            ==
 * ==========================================================================
 *
 * Ce script est conçu pour être exécuté UNE SEULE FOIS pour créer les tables
 * et insérer les données initiales dans votre base de données AWS RDS.
 *
 * --- ÉTAPES ---
 * 1. Placez ce fichier à la racine de votre projet.
 * 2. Déployez votre application sur Elastic Beanstalk.
 * 3. Visitez l'URL : http://Saber-Gestion-Peition.eu-west-3.elasticbeanstalk.com/setup.php
 * 4. Une fois que le message "Base de données initialisée avec succès !" s'affiche...
 * 5. !!! SUPPRIMEZ IMMÉDIATEMENT CE FICHIER DE VOTRE SERVEUR !!!
 *
 * Laisser ce fichier en ligne représente un risque de sécurité majeur, car
 * n'importe qui pourrait le ré-exécuter et réinitialiser votre base de données.
 *
 */

header('Content-Type: text/plain; charset=utf-8');

// 1. Récupérer les informations de connexion depuis les variables d'environnement AWS
$host = $_SERVER['RDS_HOSTNAME'] ?? 'localhost';
$user = $_SERVER['RDS_USERNAME'] ?? 'root';
$pass = $_SERVER['RDS_PASSWORD'] ?? '';
$db   = $_SERVER['RDS_DB_NAME']  ?? 'tp3';
$port = $_SERVER['RDS_PORT']     ?? '3306';

echo "Tentative de connexion à la base de données...\n";
echo "Hôte: " . $host . "\n";
echo "Base de données: " . $db . "\n\n";

try {
    // Utilisation de PDO, comme dans le reste de votre projet
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connexion réussie !\n\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}

// 2. Contenu de votre fichier SQL `tp3 (4).sql`
$sql_dump = <<<SQL
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 24 oct. 2025 à 13:55
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Temporarily disable foreign key checks to allow dropping tables in any order
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `account_confirmations`;
DROP TABLE IF EXISTS `petition`;
DROP TABLE IF EXISTS `signature`;
DROP TABLE IF EXISTS `user`;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `tp3`
--

-- --------------------------------------------------------

--
-- Structure de la table `account_confirmations`
--

CREATE TABLE `account_confirmations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `petition`
--

CREATE TABLE `petition` (
  `idP` int(11) NOT NULL,
  `titreP` varchar(255) DEFAULT NULL,
  `descriptionP` text DEFAULT NULL,
  `dateAjoutP` date DEFAULT NULL,
  `dateFinP` date DEFAULT NULL,
  `dateApproved` datetime DEFAULT NULL,
  `CreatorUser` int(11) DEFAULT NULL,
  `Statut_petition` enum('Refuse','en_attente','Approve','Termine','expire') NOT NULL,
  `nombre_signature` int(11) NOT NULL DEFAULT 0,
  `Objectif_signature` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `petition`
--

INSERT INTO `petition` (`idP`, `titreP`, `descriptionP`, `dateAjoutP`, `dateFinP`, `dateApproved`, `CreatorUser`, `Statut_petition`, `nombre_signature`, `Objectif_signature`) VALUES
(1, 'Pour plus de pistes cyclables en ville', 'Nous demandons à la mairie de créer un réseau de pistes cyclables sécurisées pour encourager les déplacements écologiques.', '2025-01-10', '2025-06-30', NULL, 1, 'Approve', 0, 500),
(2, 'Sauvons la forêt locale', 'Un projet immobilier menace notre forêt. Signez pour demander sa classification en zone protégée.', '2025-02-15', '2025-08-15', NULL, 2, 'Approve', 0, 1500),
(3, 'Cantine scolaire 100% bio', 'Pétition pour que les repas servis dans les écoles de notre commune soient issus de l\'agriculture biologique locale.', '2025-03-01', '2025-09-01', NULL, 1, 'Approve', 0, 1000),
(4, 'Baisse des tarifs de transport', 'Les tarifs des transports en commun ne cessent d\'augmenter. Demandons un gel des prix et des tarifs réduits pour les étudiants.', '2025-03-20', '2025-07-20', NULL, 4, 'Refuse', 0, 2000),
(5, 'Objectif atteint : Parc Ouvert', 'Grâce à vos signatures, le parc restera ouvert au public ! La pétition est maintenant terminée.', '2024-11-01', '2025-02-01', NULL, 5, 'Termine', 0, 250),
(6, 'petition de changer le directeur', 'changement', '2025-10-23', '2025-10-31', NULL, 3, 'Termine', 0, 2);

-- --------------------------------------------------------

--
-- Structure de la table `signature`
--

CREATE TABLE `signature` (
  `idS` int(11) NOT NULL,
  `idP` int(11) DEFAULT NULL,
  `idUser` int(11) DEFAULT NULL,
  `dateS` date DEFAULT NULL,
  `heureS` time DEFAULT NULL,
  `paysS` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `signature`
--

INSERT INTO `signature` (`idS`, `idP`, `idUser`, `dateS`, `heureS`, `paysS`) VALUES
(1, 1, 2, '2025-01-12', '10:30:00', 'France'),
(2, 1, 4, '2025-01-13', '15:00:00', 'France'),
(3, 1, 5, '2025-01-14', '09:15:00', 'France'),
(4, 2, 1, '2025-02-18', '18:45:00', 'Maroc'),
(5, 2, 5, '2025-02-20', '11:00:00', 'Maroc'),
(6, 5, 1, '2024-11-15', '14:00:00', 'France'),
(7, 5, 2, '2024-11-16', '16:25:00', 'France');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `Date_naissance` date DEFAULT NULL,
  `Image_Signature` text DEFAULT NULL,
  `statut_compte` enum('pending','active','banned') NOT NULL DEFAULT 'pending',
  `firebase_uid` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `Date_naissance`, `Image_Signature`, `statut_compte`, `firebase_uid`, `created_at`) VALUES
(1, 'Dupont', 'Jean', 'jean.dupont@email.com', '$2y$10$LJUcxoMm2K3bu59nfz/72.WiCmSVTV8tKJl1IpMACRRnLPKMLS68y', 'user', '1990-05-12', NULL, 'active', NULL, '2025-10-23 12:53:37'),
(2, 'Durand', 'Marie', 'marie.durand@email.com', '$2y$10$1YMRIxrc5gO4/4Qad/EiR.Gdp6ivvmssp3vD0BbPtSjSHFANvjEoq', 'user', '1988-11-25', NULL, 'active', NULL, '2025-10-23 12:53:37'),
(3, 'Admin', 'Principal', 'admin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '1980-01-01', NULL, 'active', 'L4O28ySX81UVZrDiGhMwQQlCJEG3', '2025-10-23 12:53:37'),
(4, 'Leroy', 'Paul', 'paul.leroy@email.com', '$2y$10$hashedpassword1', 'user', '1995-03-18', NULL, 'pending', NULL, '2025-10-23 12:53:37'),
(5, 'Moreau', 'Lucie', 'lucie.moreau@email.com', '$2y$10$hashedpassword2', 'user', '2001-07-30', NULL, 'active', NULL, '2025-10-23 12:53:37'),
(6, 'Saber', 'aymane', 'aymanesaber13@gmail.com', '$2y$10$UL7gdfWI84eV9sCEXo5.TOiTiKFj6oklUJOerQWZ2KfsRiss5D6Kq', 'user', '2025-10-01', NULL, 'active', '971MoCMRqDg3viycb1DM4ogKv6P2', '2025-10-23 12:55:21'),
(7, 'Saber', 'Aymane', 'aymanesaber15@gmail.com', NULL, 'user', NULL, NULL, 'active', 'lVMlRGmtPCOT2AVKN9vMfLzpFBM2', '2025-10-23 13:05:02');

--
-- Index pour les tables déchargées
--

ALTER TABLE `account_confirmations` ADD PRIMARY KEY (`id`), ADD KEY `user_id_idx` (`user_id`);
ALTER TABLE `petition` ADD PRIMARY KEY (`idP`), ADD KEY `fk_petition_creator` (`CreatorUser`);
ALTER TABLE `signature` ADD PRIMARY KEY (`idS`), ADD KEY `fk_signature_petition` (`idP`), ADD KEY `fk_signature_user` (`idUser`);
ALTER TABLE `user` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email_unique` (`email`), ADD UNIQUE KEY `firebase_uid_unique` (`firebase_uid`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

ALTER TABLE `account_confirmations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `petition` MODIFY `idP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
ALTER TABLE `signature` MODIFY `idS` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
ALTER TABLE `user` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

ALTER TABLE `account_confirmations` ADD CONSTRAINT `fk_confirmation_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `petition` ADD CONSTRAINT `fk_petition_creator` FOREIGN KEY (`CreatorUser`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `signature` ADD CONSTRAINT `fk_signature_petition` FOREIGN KEY (`idP`) REFERENCES `petition` (`idP`) ON DELETE CASCADE ON UPDATE CASCADE, ADD CONSTRAINT `fk_signature_user` FOREIGN KEY (`idUser`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;
SQL;

try {
    echo "Exécution du script SQL...\n";
    // Exécuter toutes les requêtes d'un coup
    $conn->exec($sql_dump);
    echo "✅ Base de données initialisée avec succès !\n\n";
    echo "==================================================================\n";
    echo "== N'OUBLIEZ PAS DE SUPPRIMER LE FICHIER setup.php MAINTENANT ! ==\n";
    echo "==================================================================\n";
} catch (PDOException $e) {
    die("❌ Erreur lors de l'exécution du script SQL : " . $e->getMessage());
}

?>