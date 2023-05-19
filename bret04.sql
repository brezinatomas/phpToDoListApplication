-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Počítač: localhost:3306
-- Vytvořeno: Pát 19. kvě 2023, 20:41
-- Verze serveru: 10.5.19-MariaDB-0+deb11u2
-- Verze PHP: 8.1.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `bret04`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `category_id` smallint(6) NOT NULL,
  `name` varchar(30) NOT NULL,
  `family_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci COMMENT='Tabulka s pÅ™ehledem kategoriÃ­';

--
-- Vypisuji data pro tabulku `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `family_id`) VALUES
(26, 'Mazlíčci', 12),
(27, 'Úklid', 12),
(30, 'Nákupy', 13),
(34, 'Luxování', 13),
(35, 'ZKOUSKA', 12),
(36, 'Opravy', 13),
(39, 'Úkoly pro Tomáše', 16),
(40, 'Hi', 16);

-- --------------------------------------------------------

--
-- Struktura tabulky `families`
--

CREATE TABLE `families` (
  `family_id` int(11) NOT NULL,
  `family_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `families`
--

INSERT INTO `families` (`family_id`, `family_name`) VALUES
(12, 'Březinovi'),
(13, 'Švecovi'),
(14, 'testovací rodina'),
(16, 'Březinovi');

-- --------------------------------------------------------

--
-- Struktura tabulky `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `category_id` smallint(6) NOT NULL,
  `text` text NOT NULL,
  `poznamka` text NOT NULL,
  `updated` text NOT NULL,
  `splneno` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci COMMENT='Tabulka s pÅ™Ã­spÄ›vky';

--
-- Vypisuji data pro tabulku `posts`
--

INSERT INTO `posts` (`post_id`, `category_id`, `text`, `poznamka`, `updated`, `splneno`) VALUES
(48, 34, 'test', 'ok', '2023-06-01', 1),
(67, 34, 'xd', 'xd', '2023-05-01', 0),
(68, 36, 'Yes', 'pes', '2023-05-24', 0),
(70, 39, 'Vyčistit bazen', 'Koukej makat', '2023-05-21', 1),
(71, 30, 'sses', 'seess', '2023-05-10', 0),
(72, 34, 'wut', 'ehy', '2023-05-11', 0),
(73, 27, 'bla pdkfdk', 'nšco', '2023-05-23', 0),
(75, 27, 'ssss', 'sss', '2023-06-08', 0),
(76, 26, 'ses', 'sese', '2023-05-17', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` set('user','admin') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'user',
  `family_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci COMMENT='Tabulka s daty uÅ¾ivatelÅ¯';

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `family_id`, `active`) VALUES
(14, 'Tomáš', 'bret04@vse.cz', '$2y$10$Dcsrry89xLei5LcQRf45peh5h2bkGdgYNnut6OVRrlZynlCyyExle', 'admin', 12, 1),
(15, 'Tomáš 2', 'bret04@vsee.cz', '$2y$10$HXbiXOxAs.Bxdk3E2eqnUe7VbS2o35l5Y2h2jfADxc281UOHO6FDG', 'user', 12, 1),
(16, 'Niky', 'nikca@vse.cz', '$2y$10$qFY4HQG0Id/dizwK/MfJF.UPs1pT4GkTvpwHme0P6LSbTN9atI5R.', 'admin', 13, 1),
(17, 'test', 'test@vse.cz', '$2y$10$gyFmZKUYPjE3v7YE0Aat9eiFkvbutmKgywdEPPs7XFlIQlkdFQUMu', 'user', 13, 1),
(18, 'test1', 'test@bis011.vse.cz', '$2y$10$pBm7tw2OlrtNCnecIGv9sOGO7hK7d1Dfmwb5NX2sj8VFf4/RIFHSm', 'admin', 14, 1),
(19, '123', '123@seznam.cz', '$2y$10$Oj0bGEVCF9vmoc8.mq4g4u47BxsI5eZtvPgKpLngxcvbU5iuGdsMa', 'user', NULL, 1),
(20, 'Evicka', 'evicka@vse.cz', '$2y$10$T9rtUb2hOXVk.o6ZVLh2UeSXYFIoaQ3ftTZ5rfPFyKKpPfOtuFN8i', 'user', 13, 1),
(22, 'Wifey', 'natyh1610@gmail.com', '$2y$10$2iwybtXHHwd.b0yxM7pRJehqKHqqLhJ756vLwslG4gbKzybSVB5vy', 'admin', 16, 1),
(23, 'test', 'fji@seznam.cz', '$2y$10$N0hzdjsgvmMvfaspb/tjQum9pF7J9WTjHt0osMZm6VvV/w8/ixyhW', 'user', 12, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `users_posts`
--

CREATE TABLE `users_posts` (
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `users_posts`
--

INSERT INTO `users_posts` (`user_id`, `post_id`) VALUES
(14, 75),
(17, 71),
(20, 67),
(20, 68),
(20, 72),
(22, 70),
(23, 73),
(23, 76);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `family_id` (`family_id`);

--
-- Indexy pro tabulku `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`family_id`);

--
-- Indexy pro tabulku `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `family_id` (`family_id`);

--
-- Indexy pro tabulku `users_posts`
--
ALTER TABLE `users_posts`
  ADD UNIQUE KEY `user_id_2` (`user_id`,`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pro tabulku `families`
--
ALTER TABLE `families`
  MODIFY `family_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pro tabulku `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Omezení pro tabulku `users_posts`
--
ALTER TABLE `users_posts`
  ADD CONSTRAINT `users_posts_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_posts_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
