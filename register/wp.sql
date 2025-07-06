-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2025. Jún 27. 10:33
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `wp`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `check_ins`
--

CREATE TABLE `check_ins` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT current_timestamp(),
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 14, 'register', 'Új regisztráció: Alex Benacsek (ID: 14)', '2025-06-27 10:32:39');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`) VALUES
(1, 'Előételek', 'Könnyű fogások az étkezés elejére'),
(2, 'Főételek', 'Laktató ételek a főétkezéshez'),
(3, 'Desszertek', 'Édes finomságok az étkezés végére'),
(4, 'Italok', 'Üdítők, borok és más italok'),
(5, 'Vegetáriánus', 'Húsmentes, egészséges ételek');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `category_id`, `is_available`, `image_url`) VALUES
(1, 'Paradicsomleves', 'Házias paradicsomleves friss bazsalikommal', 1390.00, 1, 1, NULL),
(2, 'Sajttál', 'Válogatott sajtok mézzel és dióval', 2590.00, 1, 1, NULL),
(3, 'Bruschetta', 'Fokhagymás pirítós paradicsomos feltéttel', 1290.00, 1, 1, NULL),
(4, 'Tatár beefsteak', 'Marhahús krémes fűszerekkel, pirítóssal', 3490.00, 1, 1, NULL),
(5, 'Rántott sajt rudak', 'Mozzarella rudak tartármártással', 1790.00, 1, 1, NULL),
(6, 'Rántott csirkemell', 'Köret: hasábburgonya, választható saláta', 3490.00, 2, 1, NULL),
(7, 'Marhapörkölt galuskával', 'Hagyományos magyar étel, friss galuskával', 3890.00, 2, 1, NULL),
(8, 'Grillezett lazac', 'Citromos vajmártással és párolt zöldségekkel', 4890.00, 2, 1, NULL),
(9, 'Csirke tikka masala', 'Indiai fűszeres csirkemell szósszal és rizzsel', 4190.00, 2, 1, NULL),
(10, 'Bolognai spagetti', 'Házi bolognai ragu reszelt parmezánnal', 3190.00, 2, 1, NULL),
(11, 'Sertésszűz érmék', 'Sajtmártás, steak burgonya', 4390.00, 2, 1, NULL),
(12, 'Somlói galuska', 'Házi készítésű, csokiszósszal', 1590.00, 3, 1, NULL),
(13, 'Csokoládé mousse', 'Lágy csokoládéhab friss gyümölcsökkel', 1690.00, 3, 1, NULL),
(14, 'Tiramisu', 'Mascarpone, kávé és babapiskóta', 1890.00, 3, 1, NULL),
(15, 'Panna cotta', 'Vaníliás tejszínes desszert eperöntettel', 1690.00, 3, 1, NULL),
(16, 'Almás rétes', 'Vaníliaöntettel tálalva', 1490.00, 3, 1, NULL),
(17, 'Házi limonádé', '0.5L, citrommal és mentával', 890.00, 4, 1, NULL),
(18, 'Kávé (espresso)', 'Frissen őrölt kávéból', 590.00, 4, 1, NULL),
(19, 'Vörösbor (1 dl)', 'Cabernet Sauvignon, száraz', 790.00, 4, 1, NULL),
(20, 'Ásványvíz (szénsavas)', '0.5L palackozott', 490.00, 4, 1, NULL),
(21, 'Narancslé (100%)', '0.3L frissen facsart', 990.00, 4, 1, NULL),
(22, 'Grillezett zöldségtál', 'Padlizsán, cukkini, paprika, humusz', 3190.00, 5, 1, NULL),
(23, 'Tofus Buddha-tál', 'Barna rizs, tofu, avokádó, zöldségek', 3390.00, 5, 1, NULL),
(24, 'Spenótos lasagne', 'Húsmentes lasagne ricottával és spenóttal', 3590.00, 5, 1, NULL),
(25, 'Cézár saláta tofuval', 'Ropogós saláta, tofu, pirított magvak', 2990.00, 5, 1, NULL);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `reservation_code` varchar(20) NOT NULL,
  `res_date` date NOT NULL,
  `start_time` time NOT NULL,
  `duration_hours` int(11) DEFAULT NULL CHECK (`duration_hours` between 1 and 6),
  `guest_number` int(11) NOT NULL,
  `status` enum('active','cancelled','completed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ;

--
-- Eseményindítók `reservations`
--
DELIMITER $$
CREATE TRIGGER `log_reservation` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
  INSERT INTO logs (user_id, action, description)
  VALUES (
    NEW.user_id,
    'reservation',
    CONCAT('Asztalfoglalás történt (Foglalás ID: ', NEW.id, ', Dátum: ', NEW.res_date, ', Időpont: ', NEW.start_time, ')')
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `location_description` text DEFAULT NULL,
  `seats` int(11) NOT NULL,
  `is_smoking` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- A tábla adatainak kiíratása `tables`
--

INSERT INTO `tables` (`id`, `name`, `location_description`, `seats`, `is_smoking`) VALUES
(1, 'V1', 'VIP room in the top-left corner, located in the smoking area, behind table T1, left of the entrance', 6, 1),
(2, 'V2', 'VIP room left of the entrance, located in the smoking area, behind table T4', 6, 1),
(3, 'V3', 'VIP room right of the entrance, located in the non-smoking area, behind table T7', 6, 0),
(4, 'V4', 'VIP room in the top-right corner, located in the non-smoking area, behind table T10, right of the entrance', 6, 0),
(5, 'T1', 'First table on the left side after entering, located in the smoking area, front row', 6, 1),
(6, 'T2', 'Second table on the left side in the smoking area, middle row', 6, 1),
(7, 'T3', 'Third table on the left side in the smoking area, back row', 6, 1),
(8, 'T4', 'First round table in the middle-left column of the smoking area, front row', 4, 1),
(9, 'T5', 'Second round table in the middle-left column of the smoking area, middle row', 4, 1),
(10, 'T6', 'Third round table in the middle-left column of the smoking area, back row', 4, 1),
(11, 'T7', 'First round table in the middle-right column of the non-smoking area, front row', 4, 0),
(12, 'T8', 'Second round table in the middle-right column of the non-smoking area, middle row', 4, 0),
(13, 'T9', 'Third round table in the middle-right column of the non-smoking area, back row', 4, 0),
(14, 'T10', 'First oval table on the right side after entering, located in the non-smoking area, front row', 6, 0),
(15, 'T11', 'Second oval table on the right side in the non-smoking area, middle row', 6, 0),
(16, 'T12', 'Third oval table on the right side in the non-smoking area, back row', 6, 0);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `registration_token` char(40) NOT NULL,
  `registration_expires` datetime DEFAULT NULL,
  `active` smallint(1) NOT NULL DEFAULT 0,
  `role` enum('user','staff','admin') NOT NULL DEFAULT 'user',
  `forgotten_password_token` char(40) DEFAULT NULL,
  `forgotten_password_expires` datetime DEFAULT NULL,
  `is_banned` smallint(1) NOT NULL DEFAULT 0,
  `date_time` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id_user`, `email`, `password`, `firstname`, `lastname`, `registration_token`, `registration_expires`, `active`, `role`, `forgotten_password_token`, `forgotten_password_expires`, `is_banned`, `date_time`) VALUES
(11, 'adambickei12@gmail.com', '$2y$10$qzOkhmtGKvJdui0uzPXK2uOdD9afoSq29aTdWNybpQiATdB.lZk6q', 'Adam', 'Bickei', '717ad84dff1e6120f2e20b9275d08e4a4be22692', '2025-06-18 18:33:11', 1, 'user', '', NULL, 0, '2025-06-17 19:01:31'),
(13, 'akosbalazs75@gmail.com', '$2y$10$hXzmpRWKzE8DSWLB1KYqGOZZWZLldmZ.GvGUWhAko72c47.2Zh2xe', 'Akos', 'Balazs', '', NULL, 1, 'user', '', NULL, 0, '2025-06-18 11:44:26'),
(14, 'balex@gmail.com', '$2y$10$6T6ZbcTKnhJuoG.8hX.uAunHzaKnhNsb8/qMk89Gn32P2Y4NQwwT2', 'Alex', 'Benacsek', '', NULL, 1, 'user', NULL, NULL, 0, '2025-06-27 10:33:03');

--
-- Eseményindítók `users`
--
DELIMITER $$
CREATE TRIGGER `log_password_change` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
  IF OLD.password <> NEW.password THEN
    INSERT INTO logs (user_id, action, description)
    VALUES (
      NEW.id_user,
      'password_change',
      CONCAT('Felhasználó jelszót változtatott (ID: ', NEW.id_user, ')')
    );
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `log_user_registration` AFTER INSERT ON `users` FOR EACH ROW BEGIN
  INSERT INTO logs (user_id, action, description)
  VALUES (
    NEW.id_user,
    'register',
    CONCAT('Új regisztráció: ', NEW.firstname, ' ', NEW.lastname, ' (ID: ', NEW.id_user, ')')
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `user_email_failures`
--

CREATE TABLE `user_email_failures` (
  `id_user_email_failure` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `date_time_added` datetime NOT NULL,
  `date_time_tried` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `check_ins`
--
ALTER TABLE `check_ins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- A tábla indexei `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- A tábla indexei `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- A tábla indexei `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservation_code` (`reservation_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `table_id` (`table_id`);

--
-- A tábla indexei `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `user_email_failures`
--
ALTER TABLE `user_email_failures`
  ADD PRIMARY KEY (`id_user_email_failure`),
  ADD KEY `id_user` (`id_user`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `check_ins`
--
ALTER TABLE `check_ins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT a táblához `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT a táblához `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT a táblához `user_email_failures`
--
ALTER TABLE `user_email_failures`
  MODIFY `id_user_email_failure` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `check_ins`
--
ALTER TABLE `check_ins`
  ADD CONSTRAINT `check_ins_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `check_ins_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Megkötések a táblához `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Megkötések a táblához `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

--
-- Megkötések a táblához `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE;

--
-- Megkötések a táblához `user_email_failures`
--
ALTER TABLE `user_email_failures`
  ADD CONSTRAINT `user_email_failures_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
