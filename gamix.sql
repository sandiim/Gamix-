-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 02 juin 2025 à 16:37
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gamix`
--

-- --------------------------------------------------------

--
-- Structure de la table `game_history`
--

CREATE TABLE `game_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_name` varchar(50) NOT NULL,
  `played_at` datetime DEFAULT current_timestamp(),
  `duration` int(11) NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `game_history`
--

INSERT INTO `game_history` (`id`, `user_id`, `game_name`, `played_at`, `duration`, `score`) VALUES
(1, 6, 'snake', '2025-06-02 07:47:13', 20, 10),
(7, 4, 'snake', '2025-06-02 15:00:31', 30, 10),
(90, 6, 'Snake', '2025-06-02 11:37:38', 21, 30),
(91, 6, 'Tic Tac Toe', '2025-06-02 12:10:15', 11, 100),
(100, 6, 'Tic Tac Toe', '2025-06-02 13:21:17', 16, 100),
(101, 6, 'Tic Tac Toe', '2025-06-02 13:21:34', 15, 10),
(102, 6, 'Memory Game', '2025-06-02 14:52:55', 85, 100),
(103, 6, 'Pierre-Feuille-Ciseaux', '2025-06-02 15:14:55', 25, 0),
(104, 5, 'Pierre-Feuille-Ciseaux', '2025-06-02 15:15:55', 0, 2),
(105, 5, 'Pierre-Feuille-Ciseaux', '2025-06-02 15:16:27', 0, 3);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `created_at`, `role`) VALUES
(4, 'sandi@gmail.com', 'sandi', '$2y$10$GdN4rTLr4t9VJ164LOMdGOz4E4IAfmBQ5mT/Lq1hALPi8T9aJDZRi', '2025-05-31 10:50:17', 'user'),
(5, 'admin@gmail.com', 'Admin', '$2y$10$HNilw4FOOoZl8AekWqKsyeJqGu.M.araXGU9QeLYvkl6NMTI2lzMC', '2025-06-01 16:43:45', 'admin'),
(6, 'chaima.mbarki07@gmail.com', 'chaima', '$2y$10$BwBhPqiSg0cSiFB4PqGlyu7NJRq5pWmcaQ3IZbUjZYsdyNjZv//fa', '2025-06-01 17:11:08', 'user');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `game_history`
--
ALTER TABLE `game_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `game_history`
--
ALTER TABLE `game_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `game_history`
--
ALTER TABLE `game_history`
  ADD CONSTRAINT `game_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
