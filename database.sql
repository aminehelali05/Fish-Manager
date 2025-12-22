-- Database dump for Fish Manager
-- Use: import into MySQL (Render MySQL service)

-- NOTE: Railway provides a database named `railway` by default. Use it so the import matches your
-- runtime DB_NAME (no code change required).
CREATE DATABASE IF NOT EXISTS `railway` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `railway`;

-- --------------------------------------------------------
-- Table `users` (for login)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`,`password`,`role`) VALUES
('admin','admin','admin'),
('user1','password','user');

-- --------------------------------------------------------
-- Table `clients`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `telephone` VARCHAR(50) DEFAULT NULL,
  `total_achat` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_paye` DECIMAL(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `clients` (`nom`,`prenom`,`telephone`,`total_achat`,`total_paye`) VALUES
('Doe','John','21234567',0,0),
('Smith','Alice','21123456',0,0);

-- --------------------------------------------------------
-- Table `fish`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `fish`;
CREATE TABLE `fish` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom_fish` VARCHAR(150) NOT NULL,
  `quantite_kg` DECIMAL(10,3) NOT NULL DEFAULT 0.000,
  `prix_achat` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `prix_vente` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `capital` DECIMAL(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fish` (`nom_fish`,`quantite_kg`,`prix_achat`,`prix_vente`,`capital`) VALUES
('Thon', 50.000, 8.00, 12.00, 400.00),
('Sardine', 100.000, 1.20, 2.00, 120.00),
('Dorade', 30.000, 6.00, 9.00, 180.00);

-- --------------------------------------------------------
-- Table `commandes` (aggregated orders)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `commandes`;
CREATE TABLE `commandes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_client` INT NOT NULL,
  `type_vente` VARCHAR(50) DEFAULT 'Vente',
  `type_paiement` VARCHAR(50) DEFAULT 'Complet',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(12,2) DEFAULT 0.00,
  `montant_paye` DECIMAL(12,2) DEFAULT 0.00,
  `reste` DECIMAL(12,2) DEFAULT 0.00,
  FOREIGN KEY (`id_client`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `commande_items` (items in a commande)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `commande_items`;
CREATE TABLE `commande_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_commande` INT NOT NULL,
  `id_fish` INT NOT NULL,
  `quantite` DECIMAL(10,3) NOT NULL,
  `prix_vente` DECIMAL(12,2) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`id_commande`) REFERENCES `commandes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fish`) REFERENCES `fish`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `orders` (legacy / per-item table used by PDF/stats)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_client` INT NOT NULL,
  `id_fish` INT NOT NULL,
  `quantite_kg` DECIMAL(10,3) NOT NULL,
  `prix_total` DECIMAL(12,2) NOT NULL,
  `montant_paye` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_client`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_fish`) REFERENCES `fish`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample data: create one commande + items and replicate into `orders` for reporting
-- --------------------------------------------------------
INSERT INTO `commandes` (`id_client`,`type_vente`,`type_paiement`,`created_at`) VALUES (1,'Vente','Complet',NOW());
SET @id_comm = LAST_INSERT_ID();

INSERT INTO `commande_items` (`id_commande`,`id_fish`,`quantite`,`prix_vente`,`total`) VALUES
(@id_comm, 1, 2.000, 12.00, 24.00),
(@id_comm, 2, 3.000, 2.00, 6.00);

UPDATE `commandes` SET `total` = 30.00, `montant_paye` = 30.00, `reste` = 0.00 WHERE id=@id_comm;

-- Add matching rows to `orders` (used by facture.php and stats)
INSERT INTO `orders` (`id_client`,`id_fish`,`quantite_kg`,`prix_total`,`montant_paye`,`created_at`) VALUES
(1,1,2.000,24.00,24.00,NOW()),
(1,2,3.000,6.00,6.00,NOW());

-- Update client's totals
UPDATE `clients` SET total_achat = total_achat + 30.00, total_paye = total_paye + 30.00 WHERE id = 1;

-- --------------------------------------------------------
-- Useful views / helpers (optional)
-- --------------------------------------------------------
DROP VIEW IF EXISTS `client_balances`;
CREATE VIEW `client_balances` AS
SELECT c.id, c.nom, c.prenom, COALESCE(SUM(o.prix_total),0) AS total_vente, COALESCE(SUM(o.montant_paye),0) AS total_paye, COALESCE(SUM(o.prix_total - o.montant_paye),0) AS reste
FROM clients c
LEFT JOIN orders o ON o.id_client = c.id
GROUP BY c.id;

-- EOF
