-- AR Spatial Furniture Catalog & Visualization System
-- MySQL / MariaDB Database Schema & Seed Data

CREATE DATABASE IF NOT EXISTS `ar_furniture` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ar_furniture`;

-- Drop existing tables to ensure clean setup
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admins`;

-- --------------------------------------------------------
-- Table structure for `admins`
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `categories`
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `slug` VARCHAR(150) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'RM',
  `glb_path` VARCHAR(255) NULL,
  `usdz_path` VARCHAR(255) NULL,
  `thumb_path` VARCHAR(255) NULL,
  `width_cm` DECIMAL(6, 1) NULL DEFAULT 0.0,
  `height_cm` DECIMAL(6, 1) NULL DEFAULT 0.0,
  `depth_cm` DECIMAL(6, 1) NULL DEFAULT 0.0,
  `glb_x_m` DECIMAL(6, 3) NULL DEFAULT 0.000,
  `glb_y_m` DECIMAL(6, 3) NULL DEFAULT 0.000,
  `glb_z_m` DECIMAL(6, 3) NULL DEFAULT 0.000,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Default Admin Account
-- Username: admin
-- Password: adminpassword123
-- --------------------------------------------------------
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$12$Wwdn.lGHlLgahigJcdGqyOaDr0izhj7KqsqNOI52qUXynRwCDe/Qe');

-- --------------------------------------------------------
-- Seed Data for `categories`
-- --------------------------------------------------------
INSERT INTO `categories` (`id`, `slug`, `name`, `sort_order`, `is_active`) VALUES
(1, 'chairs', 'Chairs & Lounge', 1, 1),
(2, 'sofas', 'Sofas & Couches', 2, 1),
(3, 'tables', 'Tables & Desks', 3, 1),
(4, 'lighting', 'Lighting & Ambience', 4, 1);

-- --------------------------------------------------------
-- Seed Data for `products`
-- --------------------------------------------------------
INSERT INTO `products` (`id`, `category_id`, `slug`, `name`, `description`, `price`, `currency`, `glb_path`, `usdz_path`, `thumb_path`, `width_cm`, `height_cm`, `depth_cm`, `glb_x_m`, `glb_y_m`, `glb_z_m`, `is_active`, `sort_order`) VALUES
(1, 1, 'nordic-accent-lounge-chair', 'Nordic Accent Lounge Chair', 'Ergonomically contoured lounge chair featuring solid oak legs and premium woven upholstery. Perfect for living rooms, master bedrooms, and quiet reading nooks.', 580.00, 'RM', 'models/sample_chair.glb', '', 'uploads/thumbs/sample_chair.jpg', 68.0, 82.0, 75.0, 0.680, 0.820, 0.750, 1, 1),
(2, 4, 'industrial-brass-lantern-lamp', 'Industrial Brass Lantern Lamp', 'Vintage industrial ambient floor lamp with polished brass hardware and Edison-style warm glowing illumination.', 220.00, 'RM', 'models/sample_lamp.glb', '', 'uploads/thumbs/sample_lamp.jpg', 32.0, 65.0, 32.0, 0.320, 0.650, 0.320, 1, 2),
(3, 3, 'minimalist-oak-coffee-table', 'Minimalist Oak Coffee Table', 'Sleek oval oak coffee table with matte black powder-coated steel legs. High durability water-resistant protective finish.', 340.00, 'RM', '', '', 'uploads/thumbs/sample_table.jpg', 110.0, 45.0, 60.0, 1.100, 0.450, 0.600, 1, 3),
(4, 2, 'velvet-3-seater-modern-sofa', 'Velvet 3-Seater Modern Sofa', 'Luxurious velvet sofa with high-density ergonomic foam cushions and tapered brushed gold metal feet.', 1450.00, 'RM', '', '', 'uploads/thumbs/sample_sofa.jpg', 210.0, 85.0, 92.0, 2.100, 0.850, 0.920, 1, 4);