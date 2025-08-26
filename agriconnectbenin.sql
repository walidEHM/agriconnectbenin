-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 21 juin 2025 à 16:09
-- Version du serveur : 10.4.27-MariaDB
-- Version de PHP : 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `agriconnectbenin`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `nom`, `email`, `date_creation`) VALUES
(1, 'agriconnect', '$2y$10$KEvrGcNE6/3Xk8SN5BwWrOz7RHhV8jampHqfvbedUfuDKsJQUl4Q6', 'walid EHM', 'elwalid2008@gmail.com', '2025-06-14 19:41:26');

-- --------------------------------------------------------

--
-- Structure de la table `agriculteurs`
--

CREATE TABLE `agriculteurs` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(150) NOT NULL,
  `sexe` varchar(10) DEFAULT NULL COMMENT 'Genre de l''agriculteur',
  `prenom` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL COMMENT 'Numéro de téléphone',
  `communes` text DEFAULT NULL COMMENT 'Liste des communes séparées par des virgules',
  `mot_de_passe` varchar(255) NOT NULL,
  `token_verification` varchar(255) DEFAULT NULL,
  `compte_active` tinyint(1) DEFAULT 0,
  `compte_verifie` tinyint(1) DEFAULT 0,
  `date_demande_verification` datetime DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `expiration_token` datetime DEFAULT NULL,
  `token_reinitialisation` varchar(64) DEFAULT NULL,
  `expiration_reinitialisation` datetime DEFAULT NULL,
  `photo_profil` varchar(255) NOT NULL DEFAULT 'user_default_agriculteur.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agriculteurs`
--

INSERT INTO `agriculteurs` (`id`, `nom_complet`, `sexe`, `prenom`, `nom`, `email`, `telephone`, `communes`, `mot_de_passe`, `token_verification`, `compte_active`, `compte_verifie`, `date_demande_verification`, `date_inscription`, `expiration_token`, `token_reinitialisation`, `expiration_reinitialisation`, `photo_profil`) VALUES
(16, 'Jinwoo SUNG', NULL, 'Jinwoo', 'SUNG', 'sungjinwooohunter@gmail.com', '0166493008', 'abomey, cotonou, abomey-calavi, tchaourou, parakou', '$2y$10$Wjy1dF3Y4yuzibsqWG3GROlv2njAqJX2MxvZoVzC8YPmYrBzhYjyW', '834429', 1, 1, NULL, '2025-06-05 20:23:24', '2025-06-19 12:56:16', NULL, NULL, 'user_default_agriculteur.jpg'),
(17, 'Mon nom', NULL, 'Mon', 'nom', 'maurilleboko19@gmail.com', '0166493008', 'abomey-calavi, lalo, adjarra, save', '$2y$10$bbj2OBGE5e8pKNvZYO0gv.yC6auRQFsokh/0Yr3InEsdHCl7kxS9i', NULL, 1, 1, NULL, '2025-06-08 17:46:42', NULL, NULL, NULL, 'user_default_agriculteur.jpg'),
(18, 'Maurille BOKO', NULL, 'Maurille', 'BOKO', 'maurilleboko8@gmail.com', NULL, NULL, '$2y$10$IS6zGZ2g/68ZBim5C1c17eD84kYDnqcmm8ZABfHoIVh2lR719irIi', '8ba1278372644e7b8bc2f1f904c1b6a745d71c03c4b375fe6e06f6780c6487ba', 0, 0, NULL, '2025-06-20 14:00:40', NULL, NULL, NULL, 'user_default_agriculteur.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `auteur_type` enum('agriculteur','marche') DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_commentaire` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `participant1_id` int(11) NOT NULL,
  `participant1_type` enum('agriculteur','marche') NOT NULL,
  `participant2_id` int(11) NOT NULL,
  `participant2_type` enum('agriculteur','marche') NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents_agriculteur`
--

CREATE TABLE `documents_agriculteur` (
  `id` int(11) NOT NULL,
  `agriculteur_id` int(11) NOT NULL,
  `type_doc` varchar(50) DEFAULT NULL,
  `chemin` varchar(255) DEFAULT NULL,
  `date_televersement` datetime DEFAULT current_timestamp(),
  `statut` varchar(50) DEFAULT 'en_attente',
  `commentaire_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents_agriculteur`
--

INSERT INTO `documents_agriculteur` (`id`, `agriculteur_id`, `type_doc`, `chemin`, `date_televersement`, `statut`, `commentaire_admin`) VALUES
(1, 16, 'piece_identite', 'identity_16_dd6c0218.png', '2025-06-14 15:56:10', 'approuve', NULL),
(2, 16, 'certificat_culture', 'certificate_16_e5580247.png', '2025-06-14 15:56:10', 'approuve', NULL),
(3, 16, 'photo_champ', 'field_photo_16_11f0c604.png', '2025-06-14 15:56:10', 'approuve', NULL),
(13, 17, 'piece_identite', 'identity_17_92738478.jpg', '2025-06-17 16:32:43', 'approuve', NULL),
(14, 17, 'certificat_culture', 'certificate_17_f0ad277d.jpg', '2025-06-17 16:32:43', 'approuve', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `utilisateur_type` varchar(20) NOT NULL,
  `date_like` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `likes`
--

INSERT INTO `likes` (`id`, `publication_id`, `utilisateur_id`, `utilisateur_type`, `date_like`) VALUES
(2, 3, 16, 'agriculteur', '2025-06-19 11:20:50'),
(3, 2, 16, 'agriculteur', '2025-06-19 11:21:27'),
(6, 2, 4, 'marche', '2025-06-20 04:33:03'),
(7, 1, 4, 'marche', '2025-06-20 04:33:09'),
(8, 3, 4, 'marche', '2025-06-20 05:21:58'),
(11, 4, 17, 'agriculteur', '2025-06-20 07:02:56'),
(12, 3, 17, 'agriculteur', '2025-06-20 07:03:29'),
(13, 1, 17, 'agriculteur', '2025-06-20 08:14:32'),
(14, 2, 17, 'agriculteur', '2025-06-20 08:14:34');

-- --------------------------------------------------------

--
-- Structure de la table `marches`
--

CREATE TABLE `marches` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `token_verification` varchar(255) DEFAULT NULL,
  `compte_active` tinyint(1) DEFAULT 0,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `expiration_token` datetime DEFAULT NULL,
  `token_reinitialisation` varchar(255) DEFAULT NULL,
  `expiration_reinitialisation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `marches`
--

INSERT INTO `marches` (`id`, `nom`, `email`, `mot_de_passe`, `token_verification`, `compte_active`, `date_inscription`, `expiration_token`, `token_reinitialisation`, `expiration_reinitialisation`) VALUES
(4, 'Momo walid', 'momowalid2407@gmail.com', '$2y$10$/fAUp5fzkO3IfXjSWTubIOpIOj9kJRwOUh38V8ArKdWfxKV3u9JEe', NULL, 1, '2025-06-16 16:02:28', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `expediteur_type` enum('agriculteur','marche') DEFAULT NULL,
  `destinataire_id` int(11) NOT NULL,
  `destinataire_type` enum('agriculteur','marche') DEFAULT NULL,
  `contenu` text DEFAULT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0,
  `conversation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `publications`
--

CREATE TABLE `publications` (
  `id` int(11) NOT NULL,
  `agriculteur_id` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `media_chemin` varchar(255) DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  `nombre_likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `publications`
--

INSERT INTO `publications` (`id`, `agriculteur_id`, `contenu`, `media_chemin`, `date_publication`, `nombre_likes`) VALUES
(1, 16, 'Je possède plusieurs  produit frais, veuillez me contactez au 2290166493008 pour plus d\'information.', 'pub_16_6853a9b23eb3e_0.jpg,pub_16_6853a9b244356_1.png,pub_16_6853a9b245186_2.jpg', '2025-06-19 07:09:54', 0),
(2, 16, 'Salut, je suis nouveau sur cette plate form', NULL, '2025-06-19 07:19:24', 0),
(3, 16, 'Je suis un agriculteur', 'pub_16_6853ac57e518c_0.jpg', '2025-06-19 07:21:11', 0),
(4, 17, 'Je suis un agriculteur, je me nomme Maurille boko, suis  specialisé dans la culture des fruits. Inbox-me', 'pub_17_6854f665e44bb_0.png,pub_17_6854f665e879a_1.png', '2025-06-20 06:49:26', 0),
(5, 17, 'Je suis Mr erhel', 'pub_17_68555ce61e036_0.jpg', '2025-06-20 14:06:46', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `agriculteurs`
--
ALTER TABLE `agriculteurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `publication_id` (`publication_id`);

--
-- Index pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `documents_agriculteur`
--
ALTER TABLE `documents_agriculteur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_doc_per_type` (`agriculteur_id`,`type_doc`);

--
-- Index pour la table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`publication_id`,`utilisateur_id`,`utilisateur_type`);

--
-- Index pour la table `marches`
--
ALTER TABLE `marches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conversation` (`conversation_id`);

--
-- Index pour la table `publications`
--
ALTER TABLE `publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agriculteur_id` (`agriculteur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `agriculteurs`
--
ALTER TABLE `agriculteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents_agriculteur`
--
ALTER TABLE `documents_agriculteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `marches`
--
ALTER TABLE `marches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `publications`
--
ALTER TABLE `publications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`);

--
-- Contraintes pour la table `documents_agriculteur`
--
ALTER TABLE `documents_agriculteur`
  ADD CONSTRAINT `documents_agriculteur_ibfk_1` FOREIGN KEY (`agriculteur_id`) REFERENCES `agriculteurs` (`id`);

--
-- Contraintes pour la table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`);

--
-- Contraintes pour la table `publications`
--
ALTER TABLE `publications`
  ADD CONSTRAINT `publications_ibfk_1` FOREIGN KEY (`agriculteur_id`) REFERENCES `agriculteurs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
