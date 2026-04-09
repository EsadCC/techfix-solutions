-- ============================================================
--  Techfix Solutions — Volledige Database
--  Importeer via phpMyAdmin:
--    Selecteer database 'techfix solutions' → Importeren
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Verwijder tabellen in juiste volgorde vanwege foreign keys
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tickets`;

-- ─── Tickets (origineel van de klant) ────────────────────────
CREATE TABLE `tickets` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100)    NOT NULL,
  `subject`    varchar(255)     NOT NULL,
  `status`     enum('Open','Closed') NOT NULL DEFAULT 'Open',
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tickets` (`id`, `client_name`, `subject`, `status`, `created_at`) VALUES
(1, 'ACME B.V.',   'Cannot login to portal', 'Open',   '2026-02-11 18:43:27'),
(2, 'TechCo',      'Invoice missing',         'Closed', '2026-02-11 18:43:27'),
(3, 'BASIC FIT',   'Invoice missing',         'Open',   '2026-02-12 12:19:28');

-- ─── Gebruikers ──────────────────────────────────────────────
CREATE TABLE `users` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       varchar(100)     NOT NULL,
  `email`      varchar(150)     NOT NULL,
  `password`   varchar(255)     NOT NULL,
  `role`       enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Alle accounts hebben wachtwoord: password
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin Techfix', 'admin@techfix.nl',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Jan de Vries',  'jan@example.nl',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Paul Smit',     'paul@example.nl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Anne Jacobs',   'anne@example.nl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Erik van Dam',  'erik@example.nl',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- ─── Categorieën ─────────────────────────────────────────────
CREATE TABLE `categories` (
  `id`   int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100)     NOT NULL,
  `slug` varchar(100)     NOT NULL,
  `icon` varchar(10)      DEFAULT '📦',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`name`, `slug`, `icon`) VALUES
('Smartphones', 'smartphones', '📱'),
('Laptops',     'laptops',     '💻'),
('Onderdelen',  'onderdelen',  '🔧'),
('Accessoires', 'accessoires', '🎧'),
('Audio',       'audio',       '🔊');

-- ─── Producten ───────────────────────────────────────────────
CREATE TABLE `products` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name`        varchar(255)     NOT NULL,
  `slug`        varchar(255)     NOT NULL,
  `description` text             DEFAULT NULL,
  `price`       decimal(10,2)    NOT NULL,
  `old_price`   decimal(10,2)    DEFAULT NULL,
  `stock`       int(11)          NOT NULL DEFAULT 0,
  `image`       varchar(255)     DEFAULT NULL,
  `brand`       varchar(100)     DEFAULT NULL,
  `sku`         varchar(100)     DEFAULT NULL,
  `is_featured` tinyint(1)       NOT NULL DEFAULT 0,
  `badge`       varchar(50)      DEFAULT NULL,
  `created_at`  timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_product_categorie` (`category_id`),
  CONSTRAINT `fk_product_categorie`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `products`
  (`category_id`,`name`,`slug`,`description`,`price`,`old_price`,`stock`,`brand`,`sku`,`is_featured`,`badge`)
VALUES
(1,'Samsung Galaxy S24 128GB - Zwart','samsung-galaxy-s24-128gb-zwart',
 'De Samsung Galaxy S24 combineert krachtige prestaties met een stijlvol design. Met een 6.2" Dynamic AMOLED 2X scherm, 50MP camera en de nieuwste Snapdragon 8 Gen 3 processor is deze smartphone klaar voor alles.',
 699.00,799.00,50,'Samsung','S24-128-BLK',1,'NIEUW'),

(1,'Samsung Galaxy S24 256GB - Paars','samsung-galaxy-s24-256gb-paars',
 'De Samsung Galaxy S24 in 256GB met stijlvol paars design en alle premium features.',
 799.00,899.00,35,'Samsung','S24-256-PUR',1,'BESTSELLER'),

(1,'iPhone 15 128GB - Blauw','iphone-15-128gb-blauw',
 'iPhone 15 met A16 Bionic chip, 48MP camera en Dynamic Island. Snel, krachtig en mooi.',
 899.00,NULL,28,'Apple','IP15-128-BLU',1,NULL),

(1,'iPhone 15 Pro 256GB - Titanium','iphone-15-pro-256gb-titanium',
 'De iPhone 15 Pro met titanium design, A17 Pro chip en Pro camera systeem.',
 1199.00,NULL,18,'Apple','IP15PRO-256-TIT',0,NULL),

(1,'Google Pixel 8 128GB','google-pixel-8-128gb',
 'Google Pixel 8 met Tensor G3 chip, Magic Eraser en 7 jaar software updates.',
 699.00,749.00,22,'Google','PIX8-128-BLK',0,'-7%'),

(2,'Dell XPS 15 i7 512GB','dell-xps-15-i7-512gb',
 'Krachtige laptop met Intel Core i7, 16GB RAM, 512GB SSD en 15.6" OLED scherm.',
 1299.00,NULL,12,'Dell','XPS15-I7-512',1,NULL),

(2,'Apple MacBook Air M2 256GB','apple-macbook-air-m2',
 'Ultraslim MacBook Air met Apple M2 chip, 8GB RAM en 256GB SSD.',
 1299.00,1499.00,9,'Apple','MBA-M2-256-SIL',1,'-13%'),

(2,'Lenovo ThinkPad X1 Carbon','lenovo-thinkpad-x1-carbon',
 'Zakelijke laptop met 14" IPS display, Intel i7, 16GB RAM en 512GB SSD.',
 1499.00,NULL,7,'Lenovo','TP-X1C-I7',0,NULL),

(3,'Samsung Batterij Galaxy S24','samsung-batterij-s24',
 'Originele vervangingsbatterij voor de Samsung Galaxy S24. 4000mAh capaciteit.',
 49.99,NULL,80,'Samsung','BAT-S24-ORI',0,NULL),

(3,'iPhone 15 Scherm Vervangen Kit','iphone-15-scherm-kit',
 'Complete kit voor schermvervanging iPhone 15 inclusief gereedschap en instructies.',
 89.99,119.99,45,'Apple','SCR-IP15-KIT',0,'-25%'),

(4,'Samsung Galaxy Buds2 Pro','samsung-galaxy-buds2-pro',
 'Premium draadloze earbuds met actieve ruisonderdrukking en 360 Audio.',
 199.00,229.00,40,'Samsung','BUDS2PRO-BLK',1,'-10%'),

(4,'25W USB-C Snellader','usb-c-snellader-25w',
 'Universele 25W USB-C snellader, compatibel met Samsung, Apple en alle USB-C apparaten.',
 19.99,NULL,200,'Samsung','CHR-25W-USBC',0,NULL),

(4,'Samsung Galaxy Watch 6 44mm','samsung-galaxy-watch-6-44mm',
 'Smartwatch met geavanceerde gezondheidsmonitoring en stijlvol design.',
 269.00,299.00,25,'Samsung','WATCH6-44-BLK',0,NULL),

(4,'Galaxy S24 Siliconen Case Zwart','galaxy-s24-siliconen-case-zwart',
 'Officiële Samsung siliconen case voor Galaxy S24.',
 24.99,NULL,120,'Samsung','CASE-S24-SIL-BLK',0,NULL),

(5,'Sony WH-1000XM5 Koptelefoon','sony-wh-1000xm5',
 'Beste noise cancelling koptelefoon ter wereld. 30 uur batterijduur.',
 349.00,399.00,18,'Sony','WH1000XM5-BLK',1,'-13%'),

(5,'JBL Charge 5 Bluetooth Speaker','jbl-charge-5',
 'Waterdichte Bluetooth speaker met 20 uur batterijduur en powerbank functie.',
 169.00,199.00,30,'JBL','CHG5-BLK',0,NULL),

(5,'Bose QuietComfort 45','bose-quietcomfort-45',
 'Premium noise cancelling koptelefoon van Bose. 24 uur batterij.',
 279.00,329.00,14,'Bose','QC45-WHT',0,'-15%');

-- ─── Bestellingen ────────────────────────────────────────────
CREATE TABLE `orders` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     int(10) UNSIGNED DEFAULT NULL,
  `first_name`  varchar(100)     NOT NULL,
  `last_name`   varchar(100)     NOT NULL,
  `email`       varchar(150)     NOT NULL,
  `phone`       varchar(30)      DEFAULT NULL,
  `street`      varchar(200)     NOT NULL,
  `postal_code` varchar(20)      NOT NULL,
  `city`        varchar(100)     NOT NULL,
  `country`     varchar(100)     NOT NULL DEFAULT 'Nederland',
  `total`       decimal(10,2)    NOT NULL,
  `status`      enum('Nieuw','Verwerking','Verzonden','Geleverd','Geannuleerd') NOT NULL DEFAULT 'Nieuw',
  `created_at`  timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_bestelling_gebruiker` (`user_id`),
  CONSTRAINT `fk_bestelling_gebruiker`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `orders` (`user_id`,`first_name`,`last_name`,`email`,`phone`,`street`,`postal_code`,`city`,`country`,`total`,`status`) VALUES
(2,'Jan','de Vries','jan@example.nl','+31 6 12345678','Hoofdstraat 12','1234 AB','Amsterdam','Nederland',718.99,'Verzonden'),
(3,'Paul','Smit','paul@example.nl','+31 6 87654321','Kerkstraat 5','5678 CD','Rotterdam','Nederland',199.00,'Nieuw'),
(4,'Anne','Jacobs','anne@example.nl','+31 6 11223344','Dorpsplein 3','9012 EF','Utrecht','Nederland',1299.00,'Verwerking'),
(5,'Erik','van Dam','erik@example.nl',NULL,'Stationsweg 88','3456 GH','Den Haag','Nederland',349.00,'Geleverd'),
(2,'Jan','de Vries','jan@example.nl','+31 6 12345678','Hoofdstraat 12','1234 AB','Amsterdam','Nederland',24.99,'Geleverd');

-- ─── Bestelregels ────────────────────────────────────────────
CREATE TABLE `order_items` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`   int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity`   int(11)          NOT NULL DEFAULT 1,
  `price`      decimal(10,2)    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_regel_bestelling` (`order_id`),
  KEY `fk_regel_product`    (`product_id`),
  CONSTRAINT `fk_regel_bestelling`
    FOREIGN KEY (`order_id`)   REFERENCES `orders`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_regel_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `order_items` (`order_id`,`product_id`,`quantity`,`price`) VALUES
(1,1,1,699.00),(1,12,1,19.99),
(2,11,1,199.00),
(3,7,1,1299.00),
(4,15,1,349.00),
(5,14,1,24.99);

-- ─── Productafbeeldingen ─────────────────────────────────────
-- Elk product kan maximaal 4 foto's hebben.
-- volgorde 1 = hoofdfoto (getoond in overzicht en bovenaan detail)
CREATE TABLE `product_images` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `bestandsnaam` varchar(255)   NOT NULL,
  `volgorde`   tinyint(1)       NOT NULL DEFAULT 1,
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_foto_product` (`product_id`),
  CONSTRAINT `fk_foto_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── Reviews ────────────────────────────────────────────────
CREATE TABLE `reviews` (
  `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id`    int(10) UNSIGNED NOT NULL,
  `rating`     tinyint(1)       NOT NULL,
  `title`      varchar(255)     NOT NULL,
  `body`       text             NOT NULL,
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp        NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_review_product`    (`product_id`),
  KEY `fk_review_gebruiker`  (`user_id`),
  CONSTRAINT `fk_review_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_review_gebruiker`
    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_beoordeling` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `reviews` (`product_id`,`user_id`,`rating`,`title`,`body`) VALUES
(1,2,5,'Tevreden over review','Geweldige telefoon! De camera is fantastisch en de batterij gaat heel lang mee. Snelle levering ook, heel blij met mijn aankoop.'),
(1,3,4,'Goede review','Mooie telefoon, werkt snel en de camera is prima. Alleen de prijs is wat aan de hoge kant, maar verder zeer tevreden.'),
(1,4,5,'Lorem review','Absoluut de beste smartphone die ik ooit gehad heb. Aanrader voor iedereen die op zoek is naar een premium toestel.'),
(1,5,3,'Groot sentemr review','Scherm is mooi maar ik merkte dat de batterij na een paar weken minder lang meegaat. Verder prima telefoon.'),
(11,2,5,'Geweldige earbuds','Noise cancelling werkt perfect, geluidskwaliteit is top. Draag ze elke dag!'),
(7,4,4,'Fijne laptop','Snel en stil, ideaal voor werken onderweg. Batterij gaat de hele dag mee.'),
(15,5,5,'Beste koptelefoon ooit','Sony heeft het echt geweldig gedaan. Noise cancelling is indrukwekkend.'),
(12,3,4,'Snelle lader','Laadt mijn telefoon erg snel op. Handige compacte maat ook.');

COMMIT;
