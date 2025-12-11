-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 11, 2025 at 07:55 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `billing`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `table_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing_periods`
--

DROP TABLE IF EXISTS `billing_periods`;
CREATE TABLE IF NOT EXISTS `billing_periods` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `billing_month` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `received_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `carried_forward` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_invoices` int NOT NULL DEFAULT '0',
  `affected_invoices` int NOT NULL DEFAULT '0',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_periods_billing_month_unique` (`billing_month`),
  KEY `billing_periods_closed_by_foreign` (`closed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `c_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `customer_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `connection_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_type` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `profile_picture` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_card_front` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_card_back` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`c_id`),
  UNIQUE KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_customers_email` (`email`),
  KEY `idx_customers_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`c_id`, `user_id`, `customer_id`, `name`, `email`, `phone`, `address`, `connection_address`, `id_type`, `id_number`, `is_active`, `profile_picture`, `id_card_front`, `id_card_back`, `created_at`, `updated_at`) VALUES
(2, 82, 'C-25-1', 'Imteaz', 'imteaz@gmail.com', '01236547899', 'kazipara', 'kazipara', NULL, NULL, 1, 'customers/profiles/tD7r7Ne2cBnwGFQEvVvOspzAMntQjlq7wLviti2D.jpg', 'customers/id_cards/vACkd7AlFIluiz3yoGSaIphiKGTTrkeBcY0r31qh.jpg', 'customers/id_cards/5ldsyJZ0SkRjrHJGQoQ20fNpbXNfry8WffHk5dO0.jpg', '2025-12-07 10:12:02', '2025-12-07 10:12:02'),
(3, 83, 'C-25-2', 'Araf Khan', 'arafkhan@gmail.com', '01234456789', 'Mirpur', 'Mirpur', NULL, NULL, 1, 'customers/profiles/TGtZ0WUT7SRTvpNHtJubtOc7CPXzBth27Pln6LPz.jpg', 'customers/id_cards/lZKK8AFPtewNZAx0qUZzc9fuCUnI3AzquYqOAdQT.jpg', 'customers/id_cards/KsrmmN19iifGgiD3mt9MkoAuzvs9YB4pU0Wyg7j6.jpg', '2025-12-08 04:34:24', '2025-12-08 04:34:24'),
(4, 84, 'C-25-3', 'Zia', 'zia@gmail.com', '01123456789', 'Mirpue 1', NULL, NULL, NULL, 1, 'customers/profiles/JNKqA9pwmcsa9CPud7PIEgUR8XG2oWH0v2dqoxrM.jpg', 'customers/id_cards/ESonbCB3qI4ef22i7mIQ5s1RLGB9wYMucjwsnlmi.jpg', 'customers/id_cards/DxWLjXZkUrG1h7nf7LXCaFfXPTcLsgDUANWdHgOB.jpg', '2025-12-08 04:39:40', '2025-12-08 04:39:40'),
(5, 85, 'C-25-4', 'Ratul', 'ratul@gmail.com', '01223456789', 'Kazipara', NULL, NULL, NULL, 1, 'customers/profiles/XBhD9ETWCYMjArnAwyNvEUVPbo9wl8ElmMt7J1Wz.jpg', 'customers/id_cards/2yhOyjnP0gkyNfAMqjOS02oFloFWSYM8g6P054fc.jpg', 'customers/id_cards/XvbSGOLHmcqc2M3eQvz04J6DKz5OBlCK6BHCwl6i.jpg', '2025-12-08 04:40:49', '2025-12-08 04:40:49'),
(6, 86, 'C-25-5', 'Ashraful', 'ash@gmail.com', '01233456789', 'Savar', NULL, NULL, NULL, 1, 'customers/profiles/vHQZNrpZ8V9Lr3gTyrH7nJiHEPRYGBr0nN7a14in.jpg', 'customers/id_cards/CzZWt5HWg9Mvo8SajjTfNda5FU25MCC1xG7SbPk1.jpg', 'customers/id_cards/laLP2E4jJMKPpYkRYuDYD2qnifWaoTinRaaXAfol.jpg', '2025-12-08 04:45:15', '2025-12-08 04:45:15'),
(7, 87, 'C-25-6', 'Tumpa', 'tumpa@gmail.com', '01123445678', 'Savar', NULL, NULL, NULL, 1, 'customers/profiles/8linn667hdct0ZvE6wJ7FSPDYcpIW4WsEIbxrKS4.jpg', 'customers/id_cards/aeopBaFDN64wnHTyvg0Cttn0SwZ8Nd5WRgFKfMpA.jpg', 'customers/id_cards/ReDo56IGu9x4dGrpMa9gig4a1oc03bg05yIOg2Mi.jpg', '2025-12-08 04:49:44', '2025-12-08 04:49:44'),
(8, 88, 'C-25-7', 'Arif Khan', 'arif@gmail.com', '01234456789', 'Mirpur 10', NULL, NULL, NULL, 1, 'customers/profiles/I7ZVXFz2mo5M6EGiidxLNP92pNOaIWPNXuhLXckJ.jpg', 'customers/id_cards/Q464iFL9nQ6Xh5rzDQggib8oOFlAWktYDrjanCDV.jpg', 'customers/id_cards/KnJ8TzLQWxhzRsCcarNIW6g5euku4k3yFP4JAH6n.jpg', '2025-12-08 04:50:52', '2025-12-08 04:50:52'),
(9, 89, 'C-25-8', 'Suman', 'suman@gmail.com', '01123456579', 'Mirpue 11', NULL, NULL, NULL, 1, 'customers/profiles/tAoCwG8QQAiSNmdFuSr80jf1zREGF1UW7OU9tIlc.png', 'customers/id_cards/YrnDh0P8LYDtH0Wo1R9f9My9XNisfi7Ueksgxd2T.jpg', 'customers/id_cards/yNfNVkEd8dO4VnO46DWkh16Jq3Lee3syP6F1FhJs.jpg', '2025-12-08 04:51:55', '2025-12-08 04:51:55'),
(10, NULL, 'C-25-undefined', 'Sohel', NULL, '01987654321', 'esfwgfrdgrg', NULL, NULL, NULL, 1, NULL, NULL, NULL, '2025-12-11 05:44:56', '2025-12-11 05:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `customer_to_products`
--

DROP TABLE IF EXISTS `customer_to_products`;
CREATE TABLE IF NOT EXISTS `customer_to_products` (
  `cp_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `c_id` int UNSIGNED NOT NULL,
  `p_id` int UNSIGNED NOT NULL,
  `custom_price` decimal(12,2) DEFAULT NULL,
  `customer_product_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assign_date` date NOT NULL,
  `billing_cycle_months` int NOT NULL DEFAULT '1',
  `due_date` date DEFAULT NULL,
  `custom_due_date` date DEFAULT NULL COMMENT 'Custom due date set by user, overrides calculated due_date',
  `status` enum('active','pending','expired','paused') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`cp_id`),
  UNIQUE KEY `customer_to_products_customer_product_id_unique` (`customer_product_id`),
  KEY `fk_customer_packages_customer` (`c_id`),
  KEY `fk_customer_packages_package` (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_to_products`
--

INSERT INTO `customer_to_products` (`cp_id`, `c_id`, `p_id`, `custom_price`, `customer_product_id`, `assign_date`, `billing_cycle_months`, `due_date`, `custom_due_date`, `status`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `invoice_id`) VALUES
(55, 2, 1, 2000.00, NULL, '2025-02-11', 3, '2025-05-11', '2025-05-11', 'active', 1, '2025-12-11 07:47:34', '2025-12-11 07:47:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cp_id` bigint UNSIGNED DEFAULT NULL,
  `issue_date` date NOT NULL,
  `previous_due` decimal(12,2) DEFAULT '0.00',
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `received_amount` decimal(12,2) DEFAULT '0.00',
  `next_due` decimal(12,2) DEFAULT '0.00',
  `is_active_rolling` tinyint(1) DEFAULT '1',
  `billing_cycle_number` int DEFAULT '1',
  `cycle_position` int DEFAULT '0',
  `cycle_start_date` date DEFAULT NULL,
  `status` enum('unpaid','paid','partial','cancelled','confirmed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_invoices_status` (`status`),
  KEY `created_by` (`created_by`),
  KEY `idx_invoices_customer_product` (`cp_id`),
  KEY `invoices_closed_by_foreign` (`closed_by`),
  KEY `idx_invoices_active_rolling` (`is_active_rolling`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `cp_id`, `issue_date`, `previous_due`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `is_active_rolling`, `billing_cycle_number`, `cycle_position`, `cycle_start_date`, `status`, `is_closed`, `closed_at`, `closed_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(203, 'INV-25-02-0001', 55, '2025-02-11', 0.00, 2000.00, 2000.00, 1000.00, 1000.00, 1, 1, 0, NULL, 'partial', 1, '2025-12-11 07:48:11', 1, '\n[User Confirmed: 2025-12-11 13:48:11 by Admin ] Due amount of ৳1,000 carried forward to next billing cycle.', NULL, '2025-12-11 07:47:34', '2025-12-11 07:48:11'),
(204, 'INV-25-05-0001', 55, '2025-05-11', 1000.00, 2000.00, 3000.00, 0.00, 3000.00, 1, 1, 0, NULL, 'unpaid', 0, NULL, NULL, 'Carried forward amount from invoice INV-25-02-0001', 1, '2025-12-11 07:48:11', '2025-12-11 07:48:11'),
(205, 'INV-25-03-0001', 55, '2025-03-11', 1000.00, 0.00, 1000.00, 0.00, 1000.00, 1, 1, 0, NULL, 'unpaid', 0, NULL, NULL, 'Carry-forward invoice created from confirmed payment in February 2025', 1, '2025-12-11 07:48:11', '2025-12-11 07:48:11'),
(206, 'INV-25-04-0001', 55, '2025-04-11', 1000.00, 0.00, 1000.00, 0.00, 1000.00, 1, 1, 0, NULL, 'unpaid', 0, NULL, NULL, 'Carry-forward invoice created from confirmed payment in February 2025', 1, '2025-12-11 07:48:11', '2025-12-11 07:48:11');

-- --------------------------------------------------------

--
-- Table structure for table `invoices_backup`
--

DROP TABLE IF EXISTS `invoices_backup`;
CREATE TABLE IF NOT EXISTS `invoices_backup` (
  `invoice_id` int UNSIGNED NOT NULL DEFAULT '0',
  `invoice_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cp_id` bigint UNSIGNED NOT NULL,
  `issue_date` date NOT NULL,
  `previous_due` decimal(12,2) DEFAULT '0.00',
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `received_amount` decimal(12,2) DEFAULT '0.00',
  `next_due` decimal(12,2) DEFAULT '0.00',
  `status` enum('unpaid','paid','partial','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_11_06_100942_sync_with_current_database', 1),
(2, '2025_12_07_170217_add_foreign_key_constraint_to_invoices_table', 2),
(3, '2025_12_08_141141_add_custom_due_date_to_customer_to_products_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_billing_summaries`
--

DROP TABLE IF EXISTS `monthly_billing_summaries`;
CREATE TABLE IF NOT EXISTS `monthly_billing_summaries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `billing_month` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_month` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_customers` int NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `received_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monthly_billing_summaries_billing_month_index` (`billing_month`),
  KEY `monthly_billing_summaries_is_locked_index` (`is_locked`),
  KEY `monthly_billing_summaries_created_by_foreign` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_revenue_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `monthly_revenue_summary`;
CREATE TABLE IF NOT EXISTS `monthly_revenue_summary` (
`collected_revenue` decimal(34,2)
,`invoice_count` bigint
,`month_year` varchar(7)
,`pending_revenue` decimal(35,2)
,`total_revenue` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_date` datetime NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `collected_by` int UNSIGNED DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`payment_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `payments_collected_by_foreign` (`collected_by`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `amount`, `payment_method`, `payment_date`, `note`, `created_at`, `updated_at`, `collected_by`, `status`, `notes`) VALUES
(21, 203, 1000.00, 'cash', '2025-12-11 00:00:00', NULL, '2025-12-11 07:48:04', '2025-12-11 07:48:04', 1, 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `tokenable_id` (`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `p_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_type_id` bigint UNSIGNED DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `monthly_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`p_id`),
  KEY `products_product_type_id_foreign` (`product_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`p_id`, `name`, `product_type_id`, `description`, `monthly_price`, `created_at`, `updated_at`) VALUES
(1, 'Business', 2, 'fdfdfdf', 1000.00, '2025-12-07 10:36:23', '2025-12-07 10:36:23'),
(2, 'Basic Plan', 2, 'wsed', 1500.00, '2025-12-07 10:52:32', '2025-12-07 10:52:32'),
(3, 'Super 1', 3, 'sdfgh', 600.00, '2025-12-07 11:07:26', '2025-12-07 11:07:26'),
(6, 'Business 1', 1, 'qazwsx', 1200.00, '2025-12-07 11:21:06', '2025-12-07 11:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_type`
--

DROP TABLE IF EXISTS `product_type`;
CREATE TABLE IF NOT EXISTS `product_type` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descriptions` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'Basic Plan', 'Basic internet', '2025-12-07 10:35:38', '2025-12-07 10:35:38'),
(2, 'Business', 'Business users', '2025-12-07 10:35:56', '2025-12-07 10:35:56'),
(3, 'Super', 'sdfgh', '2025-12-07 11:07:03', '2025-12-07 11:07:03');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Gvw1cY6DOOI3xCx3axGR5YaCN1iDqcx4E76YYA8M', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRmQ3Q042VVdNb0hLUjRzTmtCaTZyOXZCYXNRSWlNeGdjUEo3WnNCVyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9iaWxsaW5nL2JpbGxpbmctaW52b2ljZXMiO3M6NToicm91dGUiO3M6MzA6ImFkbWluLmJpbGxpbmcuYmlsbGluZy1pbnZvaWNlcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1765439619);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` bigint UNSIGNED NOT NULL,
  `package_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_customer_id_foreign` (`customer_id`),
  KEY `subscriptions_package_id_foreign` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin ', 'admin@netbill.com', NULL, '$2y$12$QLo9cr29855m64bGJ7aQyeha4XXzLGjUk7UKdArCUiih0k3k/0N8.', 'admin', NULL, '2025-01-01 00:40:02', '2024-12-31 18:49:19'),
(82, 'Imteaz', 'imteaz@gmail.com', NULL, '$2y$12$d9h0FSvaXOAXDRrXmNRfi.cyMZvYkCPqaCGNPZMLz21IS1Ob.xw0y', 'customer', NULL, '2025-12-07 09:34:02', '2025-12-07 09:34:02'),
(83, 'Araf Khan', 'arafkhan@gmail.com', NULL, '$2y$12$ShE4zaM0SrS95rygo0kJHO3wk7bezcxUMvrzSactapb4WHQDXqaS.', 'customer', NULL, '2025-12-08 04:34:24', '2025-12-08 04:34:24'),
(84, 'Zia', 'zia@gmail.com', NULL, '$2y$12$iGHPQYL/6GSq5CQxqGUHBuX2ZLOn3hlwtHdM.5mT2z7SAVfnKcZ86', 'customer', NULL, '2025-12-08 04:39:40', '2025-12-08 04:39:40'),
(85, 'Ratul', 'ratul@gmail.com', NULL, '$2y$12$tPXvnjeQY2SG6eeBDpE1B.gXIE8yFOZ1X9jjyi3BPcEV1MNux0GmG', 'customer', NULL, '2025-12-08 04:40:49', '2025-12-08 04:40:49'),
(86, 'Ashraful', 'ash@gmail.com', NULL, '$2y$12$ihn2O9HdRFsM/LB9V7/5jeGj6i5urkd6kKkCJd687Pnuycsvfrr3y', 'customer', NULL, '2025-12-08 04:45:15', '2025-12-08 04:45:15'),
(87, 'Tumpa', 'tumpa@gmail.com', NULL, '$2y$12$HQVFFWuRGe1Ixj4rLtObmOl8qwMEPyYHesCWgRXwJqA8XkDGMA8Oe', 'customer', NULL, '2025-12-08 04:49:44', '2025-12-08 04:49:44'),
(88, 'Arif Khan', 'arif@gmail.com', NULL, '$2y$12$RKl.wx43M706ag4STn4jH./Twupzq1G8IKptIbP.jzuZosyxE8yB.', 'customer', NULL, '2025-12-08 04:50:53', '2025-12-08 04:50:53'),
(89, 'Suman', 'suman@gmail.com', NULL, '$2y$12$jtbj/.p.qXl3yyn/th9Fsu9i2DVPxEf01LBs04PpQYBpNFeuyoDjm', 'customer', NULL, '2025-12-08 04:51:56', '2025-12-08 04:51:56');

-- --------------------------------------------------------

--
-- Structure for view `monthly_revenue_summary`
--
DROP TABLE IF EXISTS `monthly_revenue_summary`;

DROP VIEW IF EXISTS `monthly_revenue_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_revenue_summary`  AS SELECT date_format(`i`.`issue_date`,'%Y-%m') AS `month_year`, count(`i`.`invoice_id`) AS `invoice_count`, sum(`i`.`total_amount`) AS `total_revenue`, sum(`i`.`received_amount`) AS `collected_revenue`, sum((`i`.`total_amount` - `i`.`received_amount`)) AS `pending_revenue` FROM `invoices` AS `i` GROUP BY date_format(`i`.`issue_date`,'%Y-%m') ORDER BY `month_year` DESC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customer_to_products`
--
ALTER TABLE `customer_to_products`
  ADD CONSTRAINT `fk_customer_to_products_c_id` FOREIGN KEY (`c_id`) REFERENCES `customers` (`c_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_customer_to_products_p_id` FOREIGN KEY (`p_id`) REFERENCES `products` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_cp_id` FOREIGN KEY (`cp_id`) REFERENCES `customer_to_products` (`cp_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `invoices_cp_id_foreign` FOREIGN KEY (`cp_id`) REFERENCES `customer_to_products` (`cp_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_collected_by` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_invoice_id` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_product_type` FOREIGN KEY (`product_type_id`) REFERENCES `product_type` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
