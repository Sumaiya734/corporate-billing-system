-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2025 at 06:22 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `netbill_bd`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_id_unique` (`customer_id`),
  UNIQUE KEY `customers_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_id`, `name`, `email`, `phone`, `address`, `password`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'CUST-68F5CBE7C08D4', 'Test Customer', 'customer@netbillbd.com', '+8801712345678', 'Dhaka, Bangladesh', '$2y$12$iiUMoszlNjrAFNqmYMFD8OXB.1BxN1uHVrRIS9OlfHrHhc.2Fwmne', 1, '2025-10-19 23:43:04', '2025-10-19 23:43:04'),
(2, 'CUST-73A2D8E9F15B6', 'Abdul Rahman', 'abdul.rahman@example.com', '+8801812345679', 'Gulshan, Dhaka', '$2y$12$abCDef123456GHIjklMNOPQRstuvWXYZabcdEFGH...', 1, '2025-10-19 04:15:22', '2025-10-19 04:15:22'),
(3, 'CUST-84B3E7F2C19A5', 'Fatima Begum', 'fatima.begum@example.com', '+8801912345680', 'Uttara, Dhaka', '$2y$12$mnOPqr789012STUvwxYZabcdEFGHijklMNOPQR...', 1, '2025-10-18 08:30:45', '2025-10-18 08:30:45'),
(4, 'CUST-95C4F8A3D27B4', 'Mohammad Ali', 'mohammad.ali@example.com', '+8801612345681', 'Mirpur, Dhaka', '$2y$12$vwXYZabc123456DEFghiJKLmnoPQRstuVWXyz...', 0, '2025-10-17 03:20:33', '2025-10-17 03:20:33'),
(5, 'CUST-26D5A9B4E38C5', 'Ayesha Akter', 'ayesha.akter@example.com', '+8801512345682', 'Dhanmondi, Dhaka', '$2y$12$jklMNOpqr567890STUvwxYZabcDEFghiJKLmn...', 1, '2025-10-16 10:45:12', '2025-10-16 10:45:12'),
(6, 'CUST-37E6B0C5F49D6', 'Kamal Hossain', 'kamal.hossain@example.com', '+8801412345683', 'Banani, Dhaka', '$2y$12$pqrSTUvwx890123YZabcDEFghiJKLmnoPQRst...', 1, '2025-10-15 05:10:28', '2025-10-15 05:10:28'),
(7, 'CUST-48F7C1D6G50E7', 'Nusrat Jahan', 'nusrat.jahan@example.com', '+8801312345684', 'Mohakhali, Dhaka', '$2y$12$xyzABCdef456789GHIjklMNOpqrSTUvwxYZab...', 0, '2025-10-14 07:25:17', '2025-10-14 07:25:17'),
(8, 'CUST-59G8D2E7H61F8', 'Rahim Ahmed', 'rahim.ahmed@example.com', '+8801212345685', 'Bashundhara, Dhaka', '$2y$12$defGHIjkl789012MNOpqrSTUvwxYZabcDEFgh...', 1, '2025-10-13 02:35:49', '2025-10-13 02:35:49'),
(9, 'CUST-60H9E3F8I72G9', 'Sultana Parvin', 'sultana.parvin@example.com', '+8801112345686', 'Baridhara, Dhaka', '$2y$12$ghiJKLmno012345PQRstuVWXyzABCDEFghijk...', 1, '2025-10-12 09:50:23', '2025-10-12 09:50:23'),
(10, 'CUST-71I0F4G9J83H0', 'Imran Khan', 'imran.khan@example.com', '+8801012345687', 'Motijheel, Dhaka', '$2y$12$jklMNOpqr123456STUvwxYZabcDEFghiJKLmn...', 1, '2025-10-11 06:05:38', '2025-10-11 06:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `billing_month` date NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `fixed_charge` decimal(10,2) NOT NULL DEFAULT '50.00',
  `subtotal` decimal(10,2) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vat_percentage` decimal(5,2) NOT NULL DEFAULT '7.00',
  `vat_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('draft','pending','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `paid_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_customer_id_foreign` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `billing_month`, `issue_date`, `due_date`, `fixed_charge`, `subtotal`, `discount_percentage`, `discount_amount`, `vat_percentage`, `vat_amount`, `total_amount`, `status`, `paid_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-202510-001', 1, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 1000.00, 5.00, 50.00, 15.00, 142.50, 1092.50, 'paid', '2025-10-10', 'Payment received via bKash', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(2, 'INV-202510-002', 2, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 500.00, 0.00, 0.00, 15.00, 75.00, 575.00, 'paid', '2025-10-12', 'Bank transfer', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(3, 'INV-202510-003', 3, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 1350.00, 10.00, 135.00, 15.00, 182.25, 1397.25, 'paid', '2025-10-08', 'Early payment discount applied', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(4, 'INV-202510-004', 4, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 800.00, 0.00, 0.00, 15.00, 120.00, 920.00, 'pending', NULL, 'Due on 15th October', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(5, 'INV-202510-005', 5, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 1500.00, 5.00, 75.00, 15.00, 213.75, 1638.75, 'paid', '2025-10-14', 'Cash payment', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(6, 'INV-202510-006', 6, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 500.00, 0.00, 0.00, 15.00, 75.00, 575.00, 'pending', NULL, 'Payment reminder sent', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(7, 'INV-202510-007', 7, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 800.00, 0.00, 0.00, 15.00, 120.00, 920.00, 'overdue', NULL, 'Overdue - follow up required', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(8, 'INV-202510-008', 8, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 1400.00, 8.00, 112.00, 15.00, 193.20, 1481.20, 'paid', '2025-10-05', 'Advance payment', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(9, 'INV-202510-009', 9, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 950.00, 0.00, 0.00, 15.00, 142.50, 1092.50, 'paid', '2025-10-11', 'Online payment', '2025-10-21 06:17:33', '2025-10-21 06:17:33'),
(10, 'INV-202510-010', 10, '2025-10-01', '2025-10-01', '2025-10-15', 100.00, 1200.00, 0.00, 0.00, 15.00, 180.00, 1380.00, 'pending', NULL, 'New customer', '2025-10-21 06:17:33', '2025-10-21 06:17:33');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('fixed','regular','special','discount','vat') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_10_19_084428_create_customers_table', 1),
(2, '2025_10_19_084429_create_packages_table', 1),
(3, '2025_10_19_084429_create_subscriptions_table', 1),
(4, '2025_10_19_084430_create_invoice_items_table', 1),
(5, '2025_10_19_084430_create_invoices_table', 1),
(6, '2025_10_19_093809_create_payments_table', 1),
(7, '2025_10_19_093810_create_system_settings_table', 1),
(8, '2014_10_12_100000_create_password_resets_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
CREATE TABLE IF NOT EXISTS `packages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('regular','special') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `type`, `price`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Basic Speed', 'regular', 500.00, 'Basic internet for everyday browsing', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(2, 'Fast Speed', 'regular', 800.00, 'Fast internet for streaming and downloads', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(3, 'Super Speed', 'regular', 1200.00, 'Super fast internet for gaming and 4K streaming', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(4, 'Gaming Boost', 'special', 200.00, 'Low latency for gaming', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(5, 'Streaming Plus', 'special', 150.00, 'Optimized for HD streaming', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(6, 'Family Pack', 'special', 300.00, 'Multiple device connectivity', 1, '2025-10-19 03:52:06', '2025-10-19 03:52:06');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint UNSIGNED NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'fixed_monthly_charge', '50', 'Fixed monthly charge for all customers', '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(2, 'vat_percentage', '7', 'VAT percentage applied to invoices', '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(3, 'company_name', 'NetBill BD', 'Company name for invoices', '2025-10-19 03:52:06', '2025-10-19 03:52:06'),
(4, 'company_address', 'Dhaka, Bangladesh', 'Company address for invoices', '2025-10-19 03:52:06', '2025-10-19 03:52:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'NetBill Admin', 'admin@netbillbd.com', '2025-10-19 11:25:58', '$2y$12$p5G8304musaf3xFkTfSp5OCFEj7WAwTNZrXHZ.dSsLsxLeJz5js4m', NULL, '2025-10-19 11:25:58', '2025-10-19 23:07:04');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
