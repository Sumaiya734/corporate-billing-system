-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 24, 2025 at 04:09 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billing_periods`
--

INSERT INTO `billing_periods` (`id`, `billing_month`, `is_closed`, `total_amount`, `received_amount`, `carried_forward`, `total_invoices`, `affected_invoices`, `closed_at`, `closed_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, '2025-02', 1, 32272.50, 3807.50, 29072.50, 10, 7, '2025-11-20 05:33:19', 1, 'Month closed with 7 invoices having outstanding dues totaling ৳29,072.50', '2025-11-20 05:33:19', '2025-11-20 05:33:19'),
(2, '2025-03', 1, 48927.50, 6114.50, 43318.00, 12, 10, '2025-11-20 05:35:21', 1, 'Month closed with 10 invoices having outstanding dues totaling ৳43,318.00', '2025-11-20 05:35:21', '2025-11-20 05:35:21'),
(3, '2025-04', 1, 149245.00, 27612.50, 125950.00, 14, 9, '2025-11-20 05:35:38', 1, 'Month closed with 9 invoices having outstanding dues totaling ৳125,950.00', '2025-11-20 05:35:38', '2025-11-20 05:35:38'),
(4, '2025-05', 1, 7303157.50, 1602.50, 7301957.50, 16, 15, '2025-11-20 05:36:12', 1, 'Month closed with 15 invoices having outstanding dues totaling ৳7,301,957.50', '2025-11-20 05:36:12', '2025-11-20 05:36:12'),
(5, '2025-06', 1, 3884647.50, 1102.50, 3883545.00, 17, 17, '2025-11-20 05:36:26', 1, 'Month closed with 17 invoices having outstanding dues totaling ৳3,883,545.00', '2025-11-20 05:36:26', '2025-11-20 05:36:26'),
(6, '2025-07', 1, 1966420.00, 7717.50, 1961815.00, 18, 15, '2025-11-20 05:36:40', 1, 'Month closed with 15 invoices having outstanding dues totaling ৳1,961,815.00', '2025-11-20 05:36:40', '2025-11-20 05:36:40');

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`c_id`),
  UNIQUE KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_customers_email` (`email`),
  KEY `idx_customers_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`c_id`, `user_id`, `customer_id`, `name`, `email`, `phone`, `address`, `connection_address`, `id_type`, `id_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'C-25-0001', 'Sumaiya Akter', 'sumaiya@example.com', '01730844732', 'Gazipur, Dhaka', 'House 12, Gazipur', 'NID', '123456789', 1, '2025-01-04 23:50:20', '2025-01-05 04:35:13'),
(2, 3, 'C-25-0002', 'Nayeem Hasan', 'nayeem@example.com', '01711112222', 'Mirpur, Dhaka', 'House 44, Mirpur', 'NID', '987654321', 1, '2025-01-09 23:50:20', '2025-01-10 04:35:13'),
(3, 4, 'C-25-0003', 'Afsana Rahman', 'afsana@example.com', '01888883333', 'Uttara, Dhaka', 'Road 10, Uttara', 'NID', '555888333', 1, '2025-01-14 23:50:20', '2025-01-15 04:35:13'),
(4, 5, 'C-25-0004', 'Nadia Zaman', 'nadiyazaman@gmail.com', '01723456789', 'Gazipur', 'Joydebpur', 'nid', '111222333', 1, '2025-01-20 00:10:27', '2025-01-20 00:12:47'),
(5, 6, 'C-25-0005', 'Rahim Khan', 'rahim@example.com', '01712345678', 'Dhanmondi, Dhaka', 'Road 8, Dhanmondi', 'NID', '444555666', 1, '2025-01-25 00:10:27', '2025-01-25 00:12:47'),
(6, 7, 'C-25-0006', 'Fatima Begum', 'fatima@example.com', '01812345678', 'Mohakhali, Dhaka', 'House 25, Mohakhali', 'NID', '777888999', 1, '2025-02-05 00:10:27', '2025-02-05 00:12:47'),
(7, 8, 'C-25-0007', 'Kamal Hossain', 'kamal@example.com', '01912345678', 'Banani, Dhaka', 'Road 11, Banani', 'NID', '111333555', 1, '2025-02-12 00:10:27', '2025-02-12 00:12:47'),
(8, 9, 'C-25-0008', 'Sadia Islam', 'sadia@example.com', '01798765432', 'Gulshan, Dhaka', 'House 45, Gulshan', 'NID', '222444666', 1, '2025-02-20 00:10:27', '2025-02-20 00:12:47'),
(9, 10, 'C-25-0009', 'Arif Mahmud', 'arif@example.com', '01898765432', 'Baridhara, Dhaka', 'Road 6, Baridhara', 'NID', '333555777', 1, '2025-03-08 00:10:27', '2025-03-08 00:12:47'),
(10, 11, 'C-25-0010', 'Tania Ahmed', 'tania@example.com', '01998765432', 'Bashundhara, Dhaka', 'House 33, Bashundhara', 'NID', '444666888', 1, '2025-03-18 00:10:27', '2025-03-18 00:12:47'),
(11, 12, 'C-25-0011', 'Sohel Rana', 'sohel@example.com', '01755556666', 'Mirpur DOHS, Dhaka', 'Road 4, Mirpur DOHS', 'NID', '555777999', 1, '2025-04-05 00:10:27', '2025-04-05 00:12:47'),
(12, 13, 'C-25-0012', 'Nusrat Jahan', 'nusrat@example.com', '01855556666', 'Uttara Sector 4', 'House 78, Uttara', 'NID', '666888000', 1, '2025-04-15 00:10:27', '2025-04-15 00:12:47'),
(13, 14, 'C-25-0013', 'Imran Hossain', 'imran@example.com', '01955556666', 'Mohammadpur, Dhaka', 'Road 12, Mohammadpur', 'NID', '777999111', 1, '2025-05-10 00:10:27', '2025-05-10 00:12:47'),
(14, 15, 'C-25-0014', 'Moumita Rahman', 'moumita@example.com', '01766667777', 'Shyamoli, Dhaka', 'House 56, Shyamoli', 'NID', '888000222', 1, '2025-05-20 00:10:27', '2025-05-20 00:12:47'),
(15, 16, 'C-25-0015', 'Faisal Ahmed', 'faisal@example.com', '01866667777', 'Malibagh, Dhaka', 'Road 9, Malibagh', 'NID', '999111333', 1, '2025-06-12 00:10:27', '2025-06-12 00:12:47'),
(16, 17, 'C-25-0016', 'Sabrina Chowdhury', 'sabrina@example.com', '01966667777', 'Rampura, Dhaka', 'House 67, Rampura', 'NID', '000222444', 1, '2025-07-08 00:10:27', '2025-07-08 00:12:47'),
(17, 18, 'C-25-0017', 'Rashidul Islam', 'rashidul@example.com', '01777778888', 'Badda, Dhaka', 'Road 7, Badda', 'NID', '111333555', 1, '2025-08-05 00:10:27', '2025-08-05 00:12:47'),
(18, 19, 'C-25-0018', 'Anika Tasnim', 'anika@example.com', '01877778888', 'Khilgaon, Dhaka', 'House 89, Khilgaon', 'NID', '222444666', 1, '2025-08-18 00:10:27', '2025-08-18 00:12:47'),
(19, 20, 'C-25-0019', 'Shahriar Manzoor', 'shahriar@example.com', '01977778888', 'Demra, Dhaka', 'Road 5, Demra', 'NID', '333555777', 1, '2025-09-10 00:10:27', '2025-09-10 00:12:47'),
(20, 21, 'C-25-0020', 'Jannatul Ferdous', 'jannatul@example.com', '01788889999', 'Jatrabari, Dhaka', 'House 34, Jatrabari', 'NID', '444666888', 1, '2025-09-22 00:10:27', '2025-09-22 00:12:47'),
(21, 22, 'C-25-0021', 'Rafiqul Islam', 'rafiqul@example.com', '01799990000', 'Mirpur 10', 'House 23, Mirpur', 'NID', '555777111', 1, '2025-10-01 00:10:27', '2025-10-01 00:12:47'),
(22, 23, 'C-25-0022', 'Sharmin Akter', 'sharmin@example.com', '01899990000', 'Uttara 11', 'Road 7, Uttara', 'NID', '666888222', 1, '2025-10-02 00:10:27', '2025-10-02 00:12:47'),
(23, 24, 'C-25-0023', 'Nasir Uddin', 'nasir@example.com', '01999990000', 'Dhanmondi 15', 'House 45, Dhanmondi', 'NID', '777999333', 1, '2025-10-03 00:10:27', '2025-10-03 00:12:47'),
(24, 25, 'C-25-0024', 'Mitu Rahman', 'mitu@example.com', '01788881111', 'Gulshan 2', 'Road 9, Gulshan', 'NID', '888000444', 1, '2025-10-04 00:10:27', '2025-10-04 00:12:47'),
(25, 26, 'C-25-0025', 'Sajal Hossain', 'sajal@example.com', '01888881111', 'Banani 11', 'House 67, Banani', 'NID', '999111555', 1, '2025-10-05 00:10:27', '2025-10-05 00:12:47'),
(26, 27, 'C-25-0026', 'Poly Begum', 'poly@example.com', '01988881111', 'Mohakhali DOHS', 'Road 3, Mohakhali', 'NID', '000222666', 1, '2025-10-06 00:10:27', '2025-10-06 00:12:47'),
(27, 28, 'C-25-0027', 'Bappi Sarker', 'bappi@example.com', '01777772222', 'Baridhara DOHS', 'House 89, Baridhara', 'NID', '111333777', 1, '2025-10-07 00:10:27', '2025-10-07 00:12:47'),
(28, 29, 'C-25-0028', 'Tumpa Akter', 'tumpa@example.com', '01877772222', 'Bashundhara R/A', 'Road 12, Bashundhara', 'NID', '222444888', 1, '2025-10-08 00:10:27', '2025-10-08 00:12:47'),
(29, 30, 'C-25-0029', 'Mamun Or Rashid', 'mamun@example.com', '01977772222', 'Mirpur 13', 'House 34, Mirpur', 'NID', '333555999', 1, '2025-10-09 00:10:27', '2025-10-09 00:12:47'),
(30, 31, 'C-25-0030', 'Shirin Sultana', 'shirin@example.com', '01766663333', 'Uttara 8', 'Road 5, Uttara', 'NID', '444666000', 1, '2025-10-10 00:10:27', '2025-10-10 00:12:47'),
(31, 32, 'C-25-0031', 'Rokonuzzaman', 'rokon@example.com', '01866663333', 'Mohammadpur', 'House 56, Mohammadpur', 'NID', '555777111', 1, '2025-10-11 00:10:27', '2025-10-11 00:12:47'),
(32, 33, 'C-25-0032', 'Farhana Yesmin', 'farhana@example.com', '01966663333', 'Shyamoli', 'Road 8, Shyamoli', 'NID', '666888222', 1, '2025-10-12 00:10:27', '2025-10-12 00:12:47'),
(33, 34, 'C-25-0033', 'Alamgir Hossain', 'alamgir@example.com', '01755554444', 'Malibagh', 'House 67, Malibagh', 'NID', '777999333', 1, '2025-10-13 00:10:27', '2025-10-13 00:12:47'),
(34, 35, 'C-25-0034', 'Nazma Begum', 'nazma@example.com', '01855554444', 'Rampura', 'Road 10, Rampura', 'NID', '888000444', 1, '2025-10-14 00:10:27', '2025-10-14 00:12:47'),
(35, 36, 'C-25-0035', 'Sazzad Hossain', 'sazzad@example.com', '01955554444', 'Badda', 'House 78, Badda', 'NID', '999111555', 1, '2025-10-15 00:10:27', '2025-10-15 00:12:47'),
(36, 37, 'C-25-0036', 'Morshed Alam', 'morshed@example.com', '01744445555', 'Khilgaon', 'Road 12, Khilgaon', 'NID', '000222666', 1, '2025-10-16 00:10:27', '2025-10-16 00:12:47'),
(37, 38, 'C-25-0037', 'Shahinur Rahman', 'shahinur@example.com', '01844445555', 'Demra', 'House 89, Demra', 'NID', '111333777', 1, '2025-10-17 00:10:27', '2025-10-17 00:12:47'),
(38, 39, 'C-25-0038', 'Nargis Akter', 'nargis@example.com', '01944445555', 'Jatrabari', 'Road 15, Jatrabari', 'NID', '222444888', 1, '2025-10-18 00:10:27', '2025-10-18 00:12:47'),
(39, 40, 'C-25-0039', 'Babul Miah', 'babul@example.com', '01733336666', 'Mirpur 1', 'House 34, Mirpur', 'NID', '333555999', 1, '2025-10-19 00:10:27', '2025-10-19 00:12:47'),
(40, 41, 'C-25-0040', 'Rina Akter', 'rina@example.com', '01833336666', 'Uttara 3', 'Road 6, Uttara', 'NID', '444666000', 1, '2025-10-20 00:10:27', '2025-10-20 00:12:47'),
(41, 42, 'C-25-0041', 'Shafiqul Islam', 'shafiqul@example.com', '01933336666', 'Dhanmondi 27', 'House 45, Dhanmondi', 'NID', '555777111', 1, '2025-10-21 00:10:27', '2025-10-21 00:12:47'),
(42, 43, 'C-25-0042', 'Mousumi Rahman', 'mousumi@example.com', '01722227777', 'Gulshan 1', 'Road 8, Gulshan', 'NID', '666888222', 1, '2025-10-22 00:10:27', '2025-10-22 00:12:47'),
(43, 44, 'C-25-0043', 'Jamal Uddin', 'jamal@example.com', '01822227777', 'Banani 12', 'House 56, Banani', 'NID', '777999333', 1, '2025-10-23 00:10:27', '2025-10-23 00:12:47'),
(44, 45, 'C-25-0044', 'Ruma Akter', 'ruma@example.com', '01922227777', 'Mohakhali', 'Road 11, Mohakhali', 'NID', '888000444', 1, '2025-10-24 00:10:27', '2025-10-24 00:12:47'),
(45, 46, 'C-25-0045', 'Salam Miah', 'salam@example.com', '01711118888', 'Baridhara', 'House 67, Baridhara', 'NID', '999111555', 1, '2025-10-25 00:10:27', '2025-10-25 00:12:47'),
(46, 47, 'C-25-0046', 'Nipa Moni', 'nipa@example.com', '01811118888', 'Bashundhara', 'Road 14, Bashundhara', 'NID', '000222666', 1, '2025-10-26 00:10:27', '2025-10-26 00:12:47'),
(47, 48, 'C-25-0047', 'Khalid Hasan', 'khalid@example.com', '01911118888', 'Mirpur 6', 'House 78, Mirpur', 'NID', '111333777', 1, '2025-10-27 00:10:27', '2025-10-27 00:12:47'),
(48, 49, 'C-25-0048', 'Mitu Akter', 'mitu2@example.com', '01700009999', 'Uttara 7', 'Road 9, Uttara', 'NID', '222444888', 1, '2025-10-28 00:10:27', '2025-10-28 00:12:47'),
(49, 50, 'C-25-0049', 'Ratan Sarker', 'ratan@example.com', '01800009999', 'Dhanmondi 32', 'House 89, Dhanmondi', 'NID', '333555999', 1, '2025-10-29 00:10:27', '2025-10-29 00:12:47'),
(50, 51, 'C-25-0050', 'Shamima Nasrin', 'shamima@example.com', '01900009999', 'Gulshan 2', 'Road 16, Gulshan', 'NID', '444666000', 1, '2025-10-30 00:10:27', '2025-10-30 00:12:47'),
(51, 52, 'C-25-0051', 'Bappi Das', 'bappi2@example.com', '01799998888', 'Banani 11', 'House 34, Banani', 'NID', '555777111', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(52, 53, 'C-25-0052', 'Moriom Begum', 'moriom@example.com', '01899998888', 'Mohakhali DOHS', 'Road 5, Mohakhali', 'NID', '666888222', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(53, 54, 'C-25-0053', 'Raju Ahmed', 'raju@example.com', '01999998888', 'Baridhara DOHS', 'House 45, Baridhara', 'NID', '777999333', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(54, 55, 'C-25-0054', 'Shila Akter', 'shila@example.com', '01788887777', 'Bashundhara R/A', 'Road 8, Bashundhara', 'NID', '888000444', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(55, 56, 'C-25-0055', 'Mizanur Rahman', 'mizan@example.com', '01888887777', 'Mirpur 10', 'House 56, Mirpur', 'NID', '999111555', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(56, 57, 'C-25-0056', 'Naznin Sultanaaaa', 'naznin@example.com', '01988887777', 'Uttara 4', 'Road 7, Uttara', 'NID', '000222666', 1, '2025-10-31 00:10:27', '2025-11-11 06:03:54'),
(57, 58, 'C-25-0057', 'Alauddin Hasan', 'alauddin@example.com', '01777776666', 'Dhanmondi 15', 'House 67, Dhanmondi', 'NID', '111333777', 1, '2025-10-31 00:10:27', '2025-11-11 04:30:56'),
(58, 59, 'C-25-0058', 'Rokeya Begum', 'rokeya@example.com', '01877776666', 'Gulshan 1', 'Road 12, Gulshan', 'NID', '222444888', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(59, 60, 'C-25-0059', 'Sohag Miah', 'sohag@example.com', '01977776666', 'Banani 12', 'House 78, Banani', 'NID', '333555999', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(60, 61, 'C-25-0060', 'Laboni Akter', 'laboni@example.com', '01766665555', 'Mohakhali', 'Road 9, Mohakhali', 'NID', '444666000', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(61, 62, 'C-25-0061', 'Shahidul Islam', 'shahidul@example.com', '01866665555', 'Baridhara', 'House 89, Baridhara', 'NID', '555777111', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(62, 63, 'C-25-0062', 'Mita Rahman', 'mita@example.com', '01966665555', 'Bashundhara', 'Road 14, Bashundhara', 'NID', '666888222', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(63, 64, 'C-25-0063', 'Rony Ahmed', 'rony@example.com', '01755554444', 'Mirpur 13', 'House 34, Mirpur', 'NID', '777999333', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(64, 65, 'C-25-0064', 'Tania Islam', 'tania2@example.com', '01855554444', 'Uttara 8', 'Road 6, Uttara', 'NID', '888000444', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(65, 66, 'C-25-0065', 'Sajib Hasan', 'sajib@example.com', '01955554444', 'Dhanmondi 27', 'House 45, Dhanmondi', 'NID', '999111555', 1, '2025-10-31 00:10:27', '2025-10-31 00:12:47'),
(66, 67, 'C-25-0066', 'Imteaz', 'imteaz@gmail.com', '01236547899', 'Mirpur10', 'Mirpur11', NULL, NULL, 1, '2025-11-13 00:53:26', '2025-11-13 00:53:26'),
(73, 74, 'C-25-0073', 'Zia', 'zia@gmail.com', '01477413690', 'Barisal', NULL, NULL, NULL, 1, '2025-11-14 09:55:02', '2025-11-14 09:55:02'),
(74, 68, 'C-25-0074', 'Mim', 'mim@gmail.com', '01452369877', 'Kazipara', NULL, NULL, NULL, 1, '2025-11-14 12:05:09', '2025-11-14 12:05:09'),
(75, 75, 'C-25-0075', 'Joni Khan', 'jonikhan@gmail.com', '01712345678', 'Dhaka, Joydebpur', NULL, NULL, NULL, 1, '2025-11-16 01:39:22', '2025-11-23 04:27:56'),
(76, 76, 'C-25-0076', 'Araf Khan', 'arafkhan@gmail.com', '01987654321', 'Mirpur', 'Mirpur 11', NULL, NULL, 1, '2025-11-16 02:15:33', '2025-11-16 02:15:33'),
(77, 77, 'C-25-0077', 'Shafi Islam', 'shafi@gmail.com', '01987654334', 'Badda', 'Badda', NULL, NULL, 1, '2025-11-16 03:56:13', '2025-11-16 03:56:13'),
(78, 78, 'C-25-0078', 'Arif Khan', 'arif@gmail.com', '01213456789', 'Pollobi', 'Pollobi', NULL, NULL, 1, '2025-11-20 00:44:55', '2025-11-20 00:44:55');

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
  `status` enum('active','pending','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `invoice_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`cp_id`),
  UNIQUE KEY `customer_to_products_customer_product_id_unique` (`customer_product_id`),
  KEY `fk_customer_packages_customer` (`c_id`),
  KEY `fk_customer_packages_package` (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_to_products`
--

INSERT INTO `customer_to_products` (`cp_id`, `c_id`, `p_id`, `custom_price`, `customer_product_id`, `assign_date`, `billing_cycle_months`, `due_date`, `status`, `is_active`, `created_at`, `updated_at`, `invoice_id`) VALUES
(1, 1, 1, NULL, 'C-25-0001-P1', '2025-01-05', 1, '2025-02-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(2, 1, 5, NULL, 'C-25-0001-P5', '2025-01-05', 6, '2025-07-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(3, 2, 2, NULL, 'C-25-0002-P2', '2025-01-10', 1, '2025-02-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(4, 2, 4, NULL, 'C-25-0002-P4', '2025-01-10', 3, '2025-04-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(5, 2, 6, NULL, 'C-25-0002-P6', '2025-01-10', 6, '2025-07-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(6, 3, 3, NULL, 'C-25-0003-P3', '2025-01-15', 1, '2025-02-28', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(7, 4, 1, NULL, 'C-25-0004-P1', '2025-01-20', 1, '2025-02-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(8, 4, 5, NULL, 'C-25-0004-P5', '2025-01-20', 1, '2025-02-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(9, 5, 2, NULL, 'C-25-0005-P2', '2025-01-25', 1, '2025-02-25', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(10, 5, 4, NULL, 'C-25-0005-P4', '2025-01-25', 3, '2025-04-25', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(11, 5, 5, NULL, 'C-25-0005-P5', '2025-01-25', 6, '2025-07-25', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(12, 6, 1, NULL, 'C-25-0006-P1', '2025-02-05', 6, '2025-08-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(13, 7, 3, NULL, 'C-25-0007-P3', '2025-02-12', 1, '2025-03-12', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(14, 7, 5, NULL, 'C-25-0007-P5', '2025-02-12', 12, '2026-02-12', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(15, 8, 2, NULL, 'C-25-0008-P2', '2025-02-20', 1, '2025-03-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(16, 8, 4, NULL, 'C-25-0008-P4', '2025-02-20', 3, '2025-05-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(17, 8, 6, NULL, 'C-25-0008-P6', '2025-02-20', 6, '2025-08-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(18, 9, 1, NULL, 'C-25-0009-P1', '2025-03-08', 1, '2025-04-08', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(19, 10, 3, NULL, 'C-25-0010-P3', '2025-03-18', 6, '2025-09-18', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(20, 10, 6, NULL, 'C-25-0010-P6', '2025-03-18', 6, '2025-09-18', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(21, 11, 1, NULL, 'C-25-0011-P1', '2025-04-05', 1, '2025-05-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(22, 11, 4, NULL, 'C-25-0011-P4', '2025-04-05', 3, '2025-07-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(23, 11, 5, NULL, 'C-25-0011-P5', '2025-04-05', 6, '2025-10-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(24, 12, 2, NULL, 'C-25-0012-P2', '2025-04-15', 1, '2025-05-15', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(25, 13, 3, NULL, 'C-25-0013-P3', '2025-05-10', 1, '2025-06-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(26, 13, 4, NULL, 'C-25-0013-P4', '2025-05-10', 3, '2025-08-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(27, 14, 1, NULL, 'C-25-0014-P1', '2025-05-20', 1, '2025-06-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(28, 14, 5, NULL, 'C-25-0014-P5', '2025-05-20', 6, '2025-11-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(29, 14, 6, NULL, 'C-25-0014-P6', '2025-05-20', 12, '2026-05-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(30, 15, 2, NULL, 'C-25-0015-P2', '2025-06-12', 1, '2025-07-12', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(31, 16, 3, NULL, 'C-25-0016-P3', '2025-07-08', 1, '2025-08-08', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(32, 16, 5, NULL, 'C-25-0016-P5', '2025-07-08', 12, '2026-07-08', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(33, 17, 1, NULL, 'C-25-0017-P1', '2025-08-05', 1, '2025-09-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(34, 17, 4, NULL, 'C-25-0017-P4', '2025-08-05', 3, '2025-11-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(35, 17, 6, NULL, 'C-25-0017-P6', '2025-08-05', 6, '2026-02-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(36, 18, 2, NULL, 'C-25-0018-P2', '2025-08-18', 1, '2025-09-18', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(37, 19, 3, NULL, 'C-25-0019-P3', '2025-09-10', 1, '2025-10-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(38, 19, 4, NULL, 'C-25-0019-P4', '2025-09-10', 3, '2025-12-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(39, 20, 1, NULL, 'C-25-0020-P1', '2025-09-22', 1, '2025-10-22', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(40, 20, 5, NULL, 'C-25-0020-P5', '2025-09-22', 6, '2026-03-22', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(41, 20, 6, NULL, 'C-25-0020-P6', '2025-09-22', 12, '2026-09-22', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(42, 21, 2, NULL, 'C-25-0021-P2', '2025-10-01', 1, '2025-11-01', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(43, 22, 1, NULL, 'C-25-0022-P1', '2025-10-02', 1, '2025-11-02', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(44, 22, 4, NULL, 'C-25-0022-P4', '2025-10-02', 3, '2026-01-02', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(45, 23, 3, NULL, 'C-25-0023-P3', '2025-10-03', 1, '2025-11-03', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(46, 23, 5, NULL, 'C-25-0023-P5', '2025-10-03', 6, '2026-04-03', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(47, 24, 1, NULL, 'C-25-0024-P1', '2025-10-04', 1, '2025-11-04', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(48, 24, 4, NULL, 'C-25-0024-P4', '2025-10-04', 3, '2026-01-04', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(49, 24, 6, NULL, 'C-25-0024-P6', '2025-10-04', 6, '2026-04-04', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(50, 25, 2, NULL, 'C-25-0025-P2', '2025-10-05', 1, '2025-11-05', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(51, 26, 3, NULL, 'C-25-0026-P3', '2025-10-06', 1, '2025-11-06', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(52, 26, 5, NULL, 'C-25-0026-P5', '2025-10-06', 12, '2026-10-06', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(53, 27, 1, NULL, 'C-25-0027-P1', '2025-10-07', 1, '2025-11-07', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(54, 27, 4, NULL, 'C-25-0027-P4', '2025-10-07', 3, '2026-01-07', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(55, 28, 2, NULL, 'C-25-0028-P2', '2025-10-08', 1, '2025-11-08', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(56, 28, 6, NULL, 'C-25-0028-P6', '2025-10-08', 6, '2026-04-08', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(57, 29, 3, NULL, 'C-25-0029-P3', '2025-10-09', 1, '2025-11-09', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(58, 30, 1, NULL, 'C-25-0030-P1', '2025-10-10', 1, '2025-11-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(59, 30, 5, NULL, 'C-25-0030-P5', '2025-10-10', 6, '2026-04-10', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(60, 31, 2, NULL, 'C-25-0031-P2', '2025-10-11', 1, '2025-11-11', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(61, 32, 3, NULL, 'C-25-0032-P3', '2025-10-12', 1, '2025-11-12', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(62, 32, 4, NULL, 'C-25-0032-P4', '2025-10-12', 3, '2026-01-12', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(64, 33, 5, NULL, 'C-25-0033-P5', '2025-10-13', 6, '2026-04-13', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(65, 33, 6, NULL, 'C-25-0033-P6', '2025-10-13', 12, '2026-10-01', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(66, 34, 2, NULL, 'C-25-0034-P2', '2025-10-14', 1, '2025-11-14', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(67, 35, 3, NULL, 'C-25-0035-P3', '2025-10-15', 1, '2025-11-15', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(68, 35, 4, NULL, 'C-25-0035-P4', '2025-10-15', 3, '2026-01-15', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(69, 36, 1, NULL, 'C-25-0036-P1', '2025-10-16', 1, '2025-11-16', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(70, 37, 2, NULL, 'C-25-0037-P2', '2025-10-17', 1, '2025-11-17', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(71, 37, 6, NULL, 'C-25-0037-P6', '2025-10-17', 6, '2026-04-17', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(72, 38, 3, NULL, 'C-25-0038-P3', '2025-10-18', 1, '2025-11-18', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(73, 39, 1, NULL, 'C-25-0039-P1', '2025-10-19', 1, '2025-11-19', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(74, 39, 5, NULL, 'C-25-0039-P5', '2025-10-19', 6, '2026-04-19', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(75, 40, 2, NULL, 'C-25-0040-P2', '2025-10-20', 1, '2025-11-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(76, 40, 4, NULL, 'C-25-0040-P4', '2025-10-20', 3, '2026-01-20', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(77, 41, 3, NULL, 'C-25-0041-P3', '2025-10-21', 1, '2025-11-21', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(80, 43, 2, NULL, 'C-25-0043-P2', '2025-10-23', 1, '2025-11-23', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(82, 44, 3, NULL, 'C-25-0044-P3', '2025-10-24', 1, '2025-11-24', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(83, 45, 1, NULL, 'C-25-0045-P1', '2025-10-25', 1, '2025-11-25', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(85, 46, 2, NULL, 'C-25-0046-P2', '2025-10-26', 1, '2025-11-26', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(86, 47, 3, NULL, 'C-25-0047-P3', '2025-10-27', 1, '2025-11-27', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(87, 47, 4, NULL, 'C-25-0047-P4', '2025-10-27', 3, '2026-01-27', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(88, 48, 1, NULL, 'C-25-0048-P1', '2025-10-28', 1, '2025-11-28', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(89, 48, 6, NULL, 'C-25-0048-P6', '2025-10-28', 6, '2026-04-28', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(90, 49, 2, NULL, 'C-25-0049-P2', '2025-10-29', 1, '2025-11-29', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(91, 50, 3, NULL, 'C-25-0050-P3', '2025-10-30', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(92, 50, 5, NULL, 'C-25-0050-P5', '2025-10-30', 12, '2026-10-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(93, 51, 1, NULL, 'C-25-0051-P1', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(94, 51, 4, NULL, 'C-25-0051-P4', '2025-10-31', 3, '2026-01-31', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(95, 52, 2, NULL, 'C-25-0052-P2', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(96, 53, 3, NULL, 'C-25-0053-P3', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(97, 53, 6, NULL, 'C-25-0053-P6', '2025-10-31', 6, '2026-04-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(98, 54, 1, NULL, 'C-25-0054-P1', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(99, 55, 2, NULL, 'C-25-0055-P2', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(101, 56, 3, NULL, 'C-25-0056-P3', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(102, 57, 1, NULL, 'C-25-0057-P1', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(103, 57, 5, NULL, 'C-25-0057-P5', '2025-10-31', 6, '2026-04-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(104, 58, 2, NULL, 'C-25-0058-P2', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(105, 59, 3, NULL, 'C-25-0059-P3', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(106, 59, 4, NULL, 'C-25-0059-P4', '2025-10-31', 3, '2026-01-31', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(107, 60, 1, NULL, 'C-25-0060-P1', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(108, 61, 2, NULL, 'C-25-0061-P2', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(109, 61, 6, NULL, 'C-25-0061-P6', '2025-10-31', 12, '2026-10-31', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(110, 62, 3, NULL, 'C-25-0062-P3', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(111, 63, 1, NULL, 'C-25-0063-P1', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(112, 63, 5, NULL, 'C-25-0063-P5', '2025-10-31', 6, '2026-04-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(113, 64, 2, NULL, 'C-25-0064-P2', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(114, 64, 4, NULL, 'C-25-0064-P4', '2025-10-31', 3, '2026-01-31', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(115, 65, 3, NULL, 'C-25-0065-P3', '2025-10-31', 1, '2025-11-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(116, 65, 6, NULL, 'C-25-0065-P6', '2025-10-31', 6, '2026-04-30', 'active', 1, '2025-11-03 11:52:05', '2025-11-20 07:06:45', NULL),
(117, 3, 4, NULL, 'C-25-0003-P4', '2025-11-06', 3, '2026-02-28', 'active', 1, '2025-11-05 22:35:01', '2025-11-20 07:06:45', NULL),
(118, 3, 9, NULL, 'C-25-0003-P9', '2025-11-11', 2, '2026-01-26', 'active', 1, '2025-11-11 05:38:13', '2025-11-20 07:06:45', NULL),
(119, 57, 11, NULL, 'C-25-0057-P11', '2025-09-01', 6, '2026-03-01', 'active', 1, '2025-11-11 06:14:21', '2025-11-20 07:06:45', NULL),
(120, 66, 8, NULL, 'C-25-0066-P8', '2025-11-13', 3, '2026-02-13', 'active', 1, '2025-11-13 00:59:42', '2025-11-20 07:06:45', NULL),
(121, 57, 12, NULL, 'C-25-0057-P12', '2025-11-13', 3, '2026-02-13', 'active', 1, '2025-11-13 01:17:45', '2025-11-20 07:06:45', NULL),
(122, 66, 1, NULL, 'C-25-0066-P1', '2025-11-13', 6, '2026-05-13', 'active', 1, '2025-11-13 01:19:25', '2025-11-20 07:06:45', NULL),
(124, 66, 2, NULL, 'C-25-0066-P2', '2025-11-13', 12, '2026-11-13', 'active', 1, '2025-11-13 02:06:51', '2025-11-20 07:06:45', NULL),
(125, 73, 6, NULL, 'C-25-0073-P6', '2025-11-14', 6, '2026-05-14', 'active', 1, '2025-11-14 09:55:38', '2025-11-20 07:06:45', NULL),
(126, 74, 10, NULL, 'C-25-0074-P10', '2025-11-14', 3, '2026-02-14', 'active', 1, '2025-11-14 12:06:20', '2025-11-20 07:06:46', NULL),
(127, 74, 7, NULL, 'C-25-0074-P7', '2025-11-14', 2, '2026-01-14', 'active', 1, '2025-11-14 12:07:46', '2025-11-20 07:06:46', NULL),
(128, 73, 4, NULL, 'C-25-0073-P4', '2025-11-16', 6, '2026-05-16', 'active', 1, '2025-11-15 22:34:03', '2025-11-20 07:06:46', NULL),
(129, 75, 4, NULL, 'C-25-0075-P4', '2024-11-16', 3, '2025-02-15', 'active', 1, '2025-11-16 01:40:52', '2025-11-20 07:06:46', NULL),
(130, 76, 4, NULL, 'C-25-0076-P4', '2024-12-16', 3, '2025-03-01', 'active', 1, '2025-11-16 02:16:18', '2025-11-20 07:06:46', NULL),
(131, 76, 7, NULL, 'C-25-0076-P7', '2025-11-16', 3, '2026-02-04', 'active', 1, '2025-11-16 02:26:24', '2025-11-20 07:06:46', NULL),
(132, 77, 9, NULL, 'C-25-0077-P9', '2025-11-16', 6, '2026-05-12', 'active', 1, '2025-11-16 03:59:19', '2025-11-20 07:06:46', NULL),
(133, 66, 5, NULL, 'C-25-0066-P5', '2025-11-17', 2, '2026-01-08', 'active', 1, '2025-11-17 04:42:31', '2025-11-20 07:06:46', NULL),
(134, 73, 1, NULL, 'C-25-0073-P1', '2025-11-19', 6, '2026-05-19', 'active', 1, '2025-11-19 03:02:38', '2025-11-20 07:06:46', NULL),
(135, 76, 8, NULL, 'C-25-0076-P8', '2025-02-19', 2, '2025-04-12', 'active', 1, '2025-11-19 03:17:46', '2025-11-20 07:06:46', NULL),
(137, 76, 10, NULL, 'C-25-0076-P10', '2025-11-19', 2, '2026-01-06', 'active', 1, '2025-11-19 04:41:31', '2025-11-20 07:06:46', NULL),
(138, 78, 13, NULL, 'C-25-0078-P13', '2025-11-20', 6, '2026-05-20', 'active', 1, '2025-11-20 00:51:01', '2025-11-20 07:06:46', NULL),
(139, 78, 10, NULL, NULL, '2025-11-20', 2, '2026-01-10', 'active', 1, '2025-11-20 02:58:04', '2025-11-20 02:58:04', NULL),
(140, 66, 13, NULL, 'C-25-0066-P13', '2025-11-23', 2, '2026-01-12', 'active', 1, '2025-11-23 05:02:22', '2025-11-23 05:02:22', NULL),
(141, 25, 3, 5000.00, 'C-25-0025-P3', '2025-11-23', 6, '2026-05-27', 'active', 1, '2025-11-23 11:26:58', '2025-11-23 11:26:58', NULL);

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
  `status` enum('unpaid','paid','partial','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
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
  KEY `invoices_closed_by_foreign` (`closed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `cp_id`, `issue_date`, `previous_due`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `status`, `is_closed`, `closed_at`, `closed_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-2025-0001', 1, '2025-02-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 5\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-04 22:00:00', '2025-11-20 05:33:19'),
(2, 'INV-2025-0002', 3, '2025-02-10', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 10\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-09 22:00:00', '2025-11-20 05:33:19'),
(3, 'INV-2025-0003', 118, '2025-02-15', 0.00, 4000.00, 4000.00, 500.00, 3500.00, 'partial', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 15\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳3,500.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳3,500.00 carried forward to next billing cycle.', 1, '2025-02-14 22:00:00', '2025-11-20 05:33:19'),
(4, 'INV-2025-0004', 7, '2025-02-20', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 20\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳700.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-02-19 22:00:00', '2025-11-20 05:33:19'),
(5, 'INV-2025-0005', 1, '2025-03-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '2nd monthly cycle\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Invoice fully paid.', 1, '2025-03-04 21:00:00', '2025-11-20 05:35:21'),
(6, 'INV-2025-0006', 3, '2025-03-10', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '2nd monthly cycle\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Invoice fully paid.', 1, '2025-03-09 21:00:00', '2025-11-20 05:35:21'),
(7, 'INV-2025-0007', 118, '2025-03-15', 602.50, 4000.00, 4602.50, 1704.50, 2898.00, 'paid', 0, NULL, NULL, '2nd monthly cycle - cleared previous due\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳2,898.00 carried forward to next billing cycle.', 1, '2025-03-14 21:00:00', '2025-11-20 05:35:21'),
(8, 'INV-2025-0008', 7, '2025-03-20', 1102.50, 700.00, 1802.50, 0.00, 1802.50, 'unpaid', 0, NULL, NULL, '2nd monthly cycle - overdue from Feb\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,802.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,802.50 carried forward to next billing cycle.', 1, '2025-03-19 21:00:00', '2025-11-20 05:35:21'),
(9, 'INV-2025-0009', 1, '2025-04-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '3rd monthly cycle\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-04 21:00:00', '2025-11-20 05:35:38'),
(10, 'INV-2025-0010', 3, '2025-04-10', 0.00, 1000.00, 1000.00, 1897.50, 0.00, 'paid', 0, NULL, NULL, '3-month billing cycle completed\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-09 21:00:00', '2025-11-20 05:35:38'),
(11, 'INV-2025-0011', 9, '2025-02-25', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-24 21:00:00', '2025-11-20 05:33:19'),
(12, 'INV-2025-0012', 1, '2025-07-05', 0.00, 700.00, 700.00, 3307.50, 0.00, 'paid', 0, NULL, NULL, '6-month billing cycle completed\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-04 21:00:00', '2025-11-20 05:36:40'),
(13, 'INV-2025-0013', 3, '2025-07-10', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, '6-month billing cycle - pending payment\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-07-09 21:00:00', '2025-11-20 05:36:40'),
(14, 'INV-2025-0014', 12, '2025-03-05', 0.00, 4200.00, 4200.00, 1102.50, 3097.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳3,097.50 carried forward to next billing cycle.', 1, '2025-03-04 21:00:00', '2025-11-20 05:35:21'),
(15, 'INV-2025-0015', 13, '2025-03-12', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳397.50 carried forward to next billing cycle.', 1, '2025-03-11 21:00:00', '2025-11-20 05:35:21'),
(16, 'INV-2025-0016', 15, '2025-03-20', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'First monthly bill - overdue\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,000.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-03-19 21:00:00', '2025-11-20 05:35:21'),
(17, 'INV-2025-0017', 18, '2025-04-08', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-07 21:00:00', '2025-11-20 05:35:38'),
(18, 'INV-2025-0018', 19, '2025-09-18', 0.00, 9000.00, 9000.00, 9000.00, 0.00, 'paid', 0, NULL, NULL, '6-month cycle bill - pending', 1, '2025-09-17 21:00:00', '2025-11-19 06:06:58'),
(19, 'INV-2025-0019', 21, '2025-05-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Invoice fully paid.', 1, '2025-05-04 21:00:00', '2025-11-20 05:36:12'),
(20, 'INV-2025-0020', 24, '2025-05-15', 0.00, 1000.00, 1000.00, 500.00, 500.00, 'partial', 0, NULL, NULL, 'First monthly bill - partial payment\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳500.00 carried forward to next billing cycle.', 1, '2025-05-14 21:00:00', '2025-11-20 05:36:12'),
(21, 'INV-2025-0021', 25, '2025-06-10', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳397.50 carried forward to next billing cycle.', 1, '2025-06-09 21:00:00', '2025-11-20 05:36:26'),
(22, 'INV-2025-0022', 27, '2025-11-20', 0.00, 700.00, 700.00, 100.00, 600.00, 'partial', 0, NULL, NULL, '6-month cycle bill - future\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳600.00 carried forward to next billing cycle.', 1, '2025-11-19 21:00:00', '2025-11-20 05:18:43'),
(23, 'INV-2025-0023', 30, '2025-07-12', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-11 21:00:00', '2025-11-20 05:36:40'),
(24, 'INV-2025-0024', 31, '2026-07-08', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, '12-month cycle bill - future', 1, '2026-07-07 21:00:00', '2025-11-18 08:34:32'),
(25, 'INV-2025-0025', 33, '2025-09-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill', 1, '2025-09-04 21:00:00', '2025-11-18 08:34:32'),
(26, 'INV-2025-0026', 7, '2025-07-20', 2205.00, 700.00, 2905.00, 3307.50, 0.00, 'paid', 0, NULL, NULL, 'Cleared all overdue amounts\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-19 21:00:00', '2025-11-20 05:36:40'),
(27, 'INV-2025-0027', 36, '2025-09-18', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill', 1, '2025-09-17 21:00:00', '2025-11-18 08:34:32'),
(28, 'INV-2025-0028', 37, '2025-10-10', 0.00, 1500.00, 1500.00, 1016.07, 483.93, 'partial', 0, NULL, NULL, 'First monthly bill - new customer', 1, '2025-10-09 21:00:00', '2025-11-18 08:34:32'),
(29, 'INV-2025-0029', 39, '2026-03-22', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, '6-month cycle bill - future', 1, '2026-03-21 21:00:00', '2025-11-18 08:34:32'),
(30, 'INV-2025-0030', 15, '2025-08-20', 1102.50, 1000.00, 2102.50, 2205.00, 0.00, 'paid', 0, NULL, NULL, 'Cleared overdue amount', 1, '2025-08-19 21:00:00', '2025-11-18 08:34:32'),
(31, 'INV-2025-0031', 42, '2025-11-01', 0.00, 1000.00, 1000.00, 500.00, 500.00, 'partial', 0, NULL, NULL, 'First monthly bill - new Oct customer\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳500.00 carried forward to next billing cycle.', 1, '2025-10-31 21:00:00', '2025-11-20 05:18:43'),
(32, 'INV-2025-0032', 43, '2025-11-02', 0.00, 700.00, 700.00, 457.85, 242.15, 'partial', 0, NULL, NULL, 'First monthly bill - new Oct customer\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳242.15 carried forward to next billing cycle.', 1, '2025-11-01 21:00:00', '2025-11-20 05:18:43'),
(33, 'INV-2025-0033', 45, '2025-11-03', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill - new Oct customer', 1, '2025-11-02 21:00:00', '2025-11-18 08:34:32'),
(34, 'INV-2025-0034', 3, '2025-09-10', 3307.50, 1000.00, 4307.50, 4410.00, 0.00, 'paid', 0, NULL, NULL, 'Cleared 6-month cycle overdue', 1, '2025-09-09 21:00:00', '2025-11-18 08:34:32'),
(35, 'INV-TEST-001', 129, '2025-11-16', 0.00, 600.00, 600.00, 0.00, 600.00, 'unpaid', 0, NULL, NULL, 'Test invoice for Jeni Khan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳600.00 carried forward to next billing cycle.', 1, '2025-11-16 02:12:02', '2025-11-20 05:18:43'),
(36, 'INV-2025-0035', 1, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(37, 'INV-2025-0036', 3, '2025-11-30', 3307.50, 1000.00, 4307.50, 0.00, 4307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(38, 'INV-2025-0037', 118, '2025-11-30', 602.50, 4000.00, 4602.50, 0.00, 4602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost, Turbo MAX (Includes ৳602.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,602.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(39, 'INV-2025-0038', 7, '2025-11-30', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(40, 'INV-2025-0039', 9, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(41, 'INV-2025-0040', 12, '2025-11-30', 0.00, 4200.00, 4200.00, 0.00, 4200.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,200.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(42, 'INV-2025-0041', 13, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(43, 'INV-2025-0042', 15, '2025-11-30', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Family Pack (Includes ৳1,102.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳2,102.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(44, 'INV-2025-0043', 18, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(45, 'INV-2025-0044', 19, '2025-11-30', 3307.50, 9000.00, 12307.50, 0.00, 12307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳12,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(46, 'INV-2025-0045', 21, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(47, 'INV-2025-0046', 24, '2025-11-30', 602.50, 1000.00, 1602.50, 0.00, 1602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan (Includes ৳602.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,602.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(48, 'INV-2025-0047', 25, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(49, 'INV-2025-0048', 30, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(50, 'INV-2025-0049', 31, '2025-11-30', 3307.50, 1500.00, 4807.50, 0.00, 4807.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,807.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(51, 'INV-2025-0050', 33, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(52, 'INV-2025-0051', 36, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(53, 'INV-2025-0052', 37, '2025-11-30', 86.43, 1500.00, 1586.43, 0.00, 1586.43, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost (Includes ৳86.43 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,586.43 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(54, 'INV-2025-0053', 39, '2025-11-30', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(55, 'INV-2025-0054', 47, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(56, 'INV-2025-0055', 50, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(57, 'INV-2025-0056', 51, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(58, 'INV-2025-0057', 53, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(59, 'INV-2025-0058', 55, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(60, 'INV-2025-0059', 57, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(61, 'INV-2025-0060', 58, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(62, 'INV-2025-0061', 60, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(63, 'INV-2025-0062', 61, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(64, 'INV-2025-0063', 64, '2025-11-30', 0.00, 900.00, 900.00, 0.00, 900.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Streaming Plus, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳900.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(65, 'INV-2025-0064', 66, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(66, 'INV-2025-0065', 67, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(67, 'INV-2025-0066', 69, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(68, 'INV-2025-0067', 70, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(69, 'INV-2025-0068', 72, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(70, 'INV-2025-0069', 73, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(71, 'INV-2025-0070', 75, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(72, 'INV-2025-0071', 77, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(73, 'INV-2025-0072', 80, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(74, 'INV-2025-0073', 82, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(75, 'INV-2025-0074', 83, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(76, 'INV-2025-0075', 85, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(77, 'INV-2025-0076', 86, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(78, 'INV-2025-0077', 88, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(79, 'INV-2025-0078', 90, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(80, 'INV-2025-0079', 91, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(81, 'INV-2025-0080', 93, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(82, 'INV-2025-0081', 95, '2025-11-30', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(83, 'INV-2025-0082', 96, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(84, 'INV-2025-0083', 98, '2025-11-30', 0.00, 700.00, 700.00, 787.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(85, 'INV-2025-0084', 99, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(86, 'INV-2025-0085', 101, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(87, 'INV-2025-0086', 121, '2025-11-30', 0.00, 15000.00, 15000.00, 0.00, 15000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus, WS, WS\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳15,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(88, 'INV-2025-0087', 104, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(89, 'INV-2025-0088', 105, '2025-11-30', 0.00, 1500.00, 1500.00, 1500.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost', 1, '2025-11-16 03:35:31', '2025-11-20 02:04:26'),
(90, 'INV-2025-0089', 107, '2025-11-30', 0.00, 700.00, 700.00, 187.50, 512.50, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳512.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(91, 'INV-2025-0090', 108, '2025-11-30', 0.00, 1000.00, 1000.00, 600.00, 400.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳400.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(92, 'INV-2025-0091', 110, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(93, 'INV-2025-0092', 111, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(94, 'INV-2025-0093', 113, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(95, 'INV-2025-0094', 115, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(96, 'INV-2025-0095', 133, '2025-11-30', 0.00, 300.00, 300.00, 13952.50, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Turbo, Basic Plan, Standard Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(97, 'INV-2025-0096', 126, '2025-11-30', 0.00, 900.00, 900.00, 1207.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: star, Test Package', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(98, 'INV-2025-0097', 128, '2025-11-30', 0.00, 1200.00, 1200.00, 2912.90, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Family Pack, Gaming Boost', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(99, 'INV-2025-0098', 131, '2025-11-30', 0.00, 300.00, 300.00, 899.63, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Gaming Boost, Test Package', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(100, 'INV-2025-0099', 131, '2025-03-31', 997.50, 300.00, 1297.50, 0.00, 1297.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for March 2025 (Includes ৳997.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,297.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,297.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(101, 'INV-2025-0100', 18, '2025-03-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for March 2025 (Includes ৳787.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,487.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,487.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(102, 'INV-2025-0101', 129, '2025-03-31', 52.50, 600.00, 652.50, 0.00, 652.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for March 2025 (Includes ৳52.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳652.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳652.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(103, 'INV-2025-0102', 9, '2025-03-31', 2677.50, 1000.00, 3677.50, 0.00, 3677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for March 2025 (Includes ৳2,677.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳3,677.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳3,677.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(104, 'INV-2025-0103', 19, '2025-03-31', 18007.50, 9000.00, 27007.50, 0.00, 27007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for March 2025 (Includes ৳18,007.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳27,007.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳27,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(105, 'INV-2025-0104', 131, '2025-02-28', 2677.50, 300.00, 2977.50, 0.00, 2977.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for February 2025 (Includes ৳2,677.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳2,977.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳2,977.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:03', '2025-11-20 05:33:19'),
(106, 'INV-2025-0105', 12, '2025-02-28', 4462.50, 4200.00, 8662.50, 0.00, 8662.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for February 2025 (Includes ৳4,462.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳8,662.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳8,662.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:03', '2025-11-20 05:33:19'),
(107, 'INV-2025-0106', 129, '2025-02-28', 787.50, 600.00, 1387.50, 0.00, 1387.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for February 2025 (Includes ৳787.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳1,387.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳1,387.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:33:19'),
(108, 'INV-2025-0107', 13, '2025-02-28', 3517.50, 1500.00, 5017.50, 5017.01, 0.49, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for February 2025 (Includes ৳3,517.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳5,017.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳5,017.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:35:05'),
(109, 'INV-2025-0108', 15, '2025-02-28', 5827.50, 1000.00, 6827.50, 0.00, 6827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for February 2025 (Includes ৳5,827.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳6,827.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳6,827.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:33:19'),
(110, 'INV-2025-0109', 118, '2025-04-30', 7662.50, 4000.00, 11662.50, 0.00, 11662.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳7,662.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳11,662.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(111, 'INV-2025-0110', 131, '2025-04-30', 6037.50, 300.00, 6337.50, 0.00, 6337.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for April 2025 (Includes ৳6,037.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳6,337.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(112, 'INV-2025-0111', 12, '2025-04-30', 13387.50, 4200.00, 17587.50, 0.00, 17587.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳13,387.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳17,587.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(113, 'INV-2025-0112', 129, '2025-04-30', 2257.50, 600.00, 2857.50, 0.00, 2857.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for April 2025 (Includes ৳2,257.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳2,857.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(114, 'INV-2025-0113', 13, '2025-04-30', 10552.50, 1500.00, 12052.50, 0.00, 12052.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for April 2025 (Includes ৳10,552.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳12,052.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(115, 'INV-2025-0114', 7, '2025-04-30', 7560.00, 700.00, 8260.00, 0.00, 8260.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳7,560.00 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳8,260.00 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(116, 'INV-2025-0115', 24, '2025-04-30', 2307.50, 1000.00, 3307.50, 0.00, 3307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳2,307.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳3,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(117, 'INV-2025-0116', 9, '2025-04-30', 8032.50, 1000.00, 9032.50, 10710.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳8,032.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(118, 'INV-2025-0117', 15, '2025-04-30', 15277.50, 1000.00, 16277.50, 0.00, 16277.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳15,277.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳16,277.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(119, 'INV-2025-0118', 21, '2025-04-30', 2362.50, 700.00, 3062.50, 4000.00, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳2,362.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(120, 'INV-2025-0119', 19, '2025-04-30', 47407.50, 9000.00, 56407.50, 8800.00, 47607.50, 'partial', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳47,407.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳47,607.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(121, 'INV-2025-0120', 118, '2025-10-31', 16952.50, 4000.00, 20952.50, 0.00, 20952.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳16,952.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(122, 'INV-2025-0121', 64, '2025-10-31', 4777.50, 900.00, 5677.50, 0.00, 5677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳4,777.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(123, 'INV-2025-0122', 121, '2025-10-31', 48982.50, 15000.00, 63982.50, 0.00, 63982.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳48,982.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(124, 'INV-2025-0123', 36, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(125, 'INV-2025-0124', 131, '2025-10-31', 12757.50, 300.00, 13057.50, 0.00, 13057.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳12,757.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(126, 'INV-2025-0125', 18, '2025-10-31', 2362.50, 700.00, 3062.50, 0.00, 3062.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳2,362.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(127, 'INV-2025-0126', 73, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(128, 'INV-2025-0127', 93, '2025-10-31', 1417.50, 700.00, 2117.50, 0.00, 2117.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,417.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(129, 'INV-2025-0128', 53, '2025-10-31', 1417.50, 700.00, 2117.50, 0.00, 2117.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,417.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(130, 'INV-2025-0129', 30, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(131, 'INV-2025-0130', 61, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(132, 'INV-2025-0131', 12, '2025-10-31', 31237.50, 4200.00, 35437.50, 0.00, 35437.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳31,237.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(133, 'INV-2025-0132', 25, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(134, 'INV-2025-0133', 80, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(135, 'INV-2025-0134', 39, '2025-10-31', 12127.50, 700.00, 12827.50, 0.00, 12827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳12,127.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(136, 'INV-2025-0135', 129, '2025-10-31', 5197.50, 600.00, 5797.50, 0.00, 5797.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳5,197.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(137, 'INV-2025-0136', 13, '2025-10-31', 24622.50, 1500.00, 26122.50, 0.00, 26122.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳24,622.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(138, 'INV-2025-0137', 86, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(139, 'INV-2025-0138', 107, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(140, 'INV-2025-0139', 57, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(141, 'INV-2025-0140', 110, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(142, 'INV-2025-0141', 88, '2025-10-31', 2677.50, 700.00, 3377.50, 0.00, 3377.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,677.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(143, 'INV-2025-0142', 47, '2025-10-31', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(144, 'INV-2025-0143', 99, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(145, 'INV-2025-0144', 95, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(146, 'INV-2025-0145', 69, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(147, 'INV-2025-0146', 27, '2025-10-31', 3207.50, 700.00, 3907.50, 0.00, 3907.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,207.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(148, 'INV-2025-0147', 7, '2025-10-31', 16065.00, 700.00, 16765.00, 0.00, 16765.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳16,065.00 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(149, 'INV-2025-0148', 72, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(150, 'INV-2025-0149', 45, '2025-10-31', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(151, 'INV-2025-0150', 3, '2025-10-31', 10237.50, 1000.00, 11237.50, 0.00, 11237.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳10,237.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(152, 'INV-2025-0151', 66, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(153, 'INV-2025-0152', 101, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(154, 'INV-2025-0153', 85, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(155, 'INV-2025-0154', 24, '2025-10-31', 5717.50, 1000.00, 6717.50, 0.00, 6717.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳5,717.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(156, 'INV-2025-0155', 51, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(157, 'INV-2025-0156', 42, '2025-10-31', 602.50, 1000.00, 1602.50, 0.00, 1602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳602.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34');
INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `cp_id`, `issue_date`, `previous_due`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `status`, `is_closed`, `closed_at`, `closed_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(158, 'INV-2025-0157', 9, '2025-10-31', 18742.50, 1000.00, 19742.50, 0.00, 19742.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳18,742.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(159, 'INV-2025-0158', 96, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(160, 'INV-2025-0159', 33, '2025-10-31', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(161, 'INV-2025-0160', 90, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(162, 'INV-2025-0161', 75, '2025-10-31', 1732.50, 1000.00, 2732.50, 0.00, 2732.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(163, 'INV-2025-0162', 104, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(164, 'INV-2025-0163', 60, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(165, 'INV-2025-0164', 111, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(166, 'INV-2025-0165', 82, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(167, 'INV-2025-0166', 31, '2025-10-31', 10132.50, 1500.00, 11632.50, 0.00, 11632.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳10,132.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(168, 'INV-2025-0167', 15, '2025-10-31', 34177.50, 1000.00, 35177.50, 0.00, 35177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳34,177.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(169, 'INV-2025-0168', 50, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(170, 'INV-2025-0169', 115, '2025-10-31', 3517.50, 1500.00, 5017.50, 5017.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-20 02:13:38'),
(171, 'INV-2025-0170', 83, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(172, 'INV-2025-0171', 67, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(173, 'INV-2025-0172', 77, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(174, 'INV-2025-0173', 108, '2025-10-31', 4882.50, 1000.00, 5882.50, 0.00, 5882.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳4,882.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(175, 'INV-2025-0174', 70, '2025-10-31', 2992.50, 1000.00, 3992.50, 0.00, 3992.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,992.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(176, 'INV-2025-0175', 91, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(177, 'INV-2025-0176', 43, '2025-10-31', 644.65, 700.00, 1344.65, 0.00, 1344.65, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳644.65 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(178, 'INV-2025-0177', 98, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(179, 'INV-2025-0178', 58, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(180, 'INV-2025-0179', 105, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(181, 'INV-2025-0180', 21, '2025-10-31', 7087.50, 700.00, 7787.50, 0.00, 7787.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳7,087.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(182, 'INV-2025-0181', 1, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(183, 'INV-2025-0182', 19, '2025-10-31', 106207.50, 9000.00, 115207.50, 0.00, 115207.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳106,207.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(184, 'INV-2025-0183', 113, '2025-10-31', 1732.50, 1000.00, 2732.50, 0.00, 2732.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(185, 'INV-2025-0184', 55, '2025-10-31', 2992.50, 1000.00, 3992.50, 3155.80, 836.70, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,992.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(186, 'INV-2025-0185', 118, '2025-09-30', 35532.50, 4000.00, 39532.50, 39532.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳35,532.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 06:10:35'),
(187, 'INV-2025-0186', 121, '2025-09-30', 131197.50, 15000.00, 146197.50, 0.00, 146197.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳131,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(188, 'INV-2025-0187', 131, '2025-09-30', 26197.50, 300.00, 26497.50, 0.00, 26497.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳26,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(189, 'INV-2025-0188', 18, '2025-09-30', 5512.50, 700.00, 6212.50, 0.00, 6212.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳5,512.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(190, 'INV-2025-0189', 30, '2025-09-30', 3307.50, 1000.00, 4307.50, 0.00, 4307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(191, 'INV-2025-0190', 12, '2025-09-30', 66937.50, 4200.00, 71137.50, 0.00, 71137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳66,937.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(192, 'INV-2025-0191', 25, '2025-09-30', 6772.50, 1500.00, 8272.50, 8272.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳6,772.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 06:04:34'),
(193, 'INV-2025-0192', 39, '2025-09-30', 29767.50, 700.00, 30467.50, 0.00, 30467.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳29,767.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(194, 'INV-2025-0193', 129, '2025-09-30', 11077.50, 600.00, 11677.50, 0.00, 11677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳11,077.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(195, 'INV-2025-0194', 13, '2025-09-30', 52762.50, 1500.00, 54262.50, 0.00, 54262.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳52,762.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(196, 'INV-2025-0195', 27, '2025-09-30', 11927.50, 700.00, 12627.50, 0.00, 12627.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳11,927.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(197, 'INV-2025-0196', 7, '2025-09-30', 33075.00, 700.00, 33775.00, 0.00, 33775.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳33,075.00 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(198, 'INV-2025-0197', 24, '2025-09-30', 12537.50, 1000.00, 13537.50, 0.00, 13537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳12,537.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(199, 'INV-2025-0198', 9, '2025-09-30', 40162.50, 1000.00, 41162.50, 0.00, 41162.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳40,162.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(200, 'INV-2025-0199', 31, '2025-09-30', 23782.50, 1500.00, 25282.50, 0.00, 25282.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳23,782.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(201, 'INV-2025-0200', 15, '2025-09-30', 71977.50, 1000.00, 72977.50, 72977.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳71,977.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 05:51:06'),
(202, 'INV-2025-0201', 37, '2025-09-30', 2430.36, 1500.00, 3930.36, 3030.36, 900.00, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳2,430.36 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 05:43:07'),
(203, 'INV-2025-0202', 21, '2025-09-30', 16537.50, 700.00, 17237.50, 961.93, 16275.57, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳16,537.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(204, 'INV-2025-0203', 1, '2025-09-30', 5197.50, 700.00, 5897.50, 1437.18, 4460.32, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳5,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(205, 'INV-2025-0204', 118, '2025-08-31', 72692.50, 4000.00, 76692.50, 0.00, 76692.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳72,692.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(206, 'INV-2025-0205', 36, '2025-08-31', 3307.50, 1000.00, 4307.50, 4410.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(207, 'INV-2025-0206', 131, '2025-08-31', 53077.50, 300.00, 53377.50, 0.00, 53377.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳53,077.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(208, 'INV-2025-0207', 18, '2025-08-31', 11812.50, 700.00, 12512.50, 0.00, 12512.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳11,812.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(209, 'INV-2025-0208', 30, '2025-08-31', 7717.50, 1000.00, 8717.50, 0.00, 8717.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳7,717.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(210, 'INV-2025-0209', 12, '2025-08-31', 138337.50, 4200.00, 142537.50, 0.00, 142537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳138,337.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(211, 'INV-2025-0210', 25, '2025-08-31', 15802.50, 1500.00, 17302.50, 8060.00, 9242.50, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳15,802.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(212, 'INV-2025-0211', 129, '2025-08-31', 22837.50, 600.00, 23437.50, 0.00, 23437.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳22,837.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(213, 'INV-2025-0212', 13, '2025-08-31', 109042.50, 1500.00, 110542.50, 0.00, 110542.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳109,042.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(214, 'INV-2025-0213', 27, '2025-08-31', 29367.50, 700.00, 30067.50, 0.00, 30067.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳29,367.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(215, 'INV-2025-0214', 7, '2025-08-31', 67095.00, 700.00, 67795.00, 0.00, 67795.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳67,095.00 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(216, 'INV-2025-0215', 3, '2025-08-31', 24097.50, 1000.00, 25097.50, 0.00, 25097.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳24,097.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(217, 'INV-2025-0216', 24, '2025-08-31', 26177.50, 1000.00, 27177.50, 0.00, 27177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳26,177.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(218, 'INV-2025-0217', 9, '2025-08-31', 83002.50, 1000.00, 84002.50, 0.00, 84002.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳83,002.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(219, 'INV-2025-0218', 33, '2025-08-31', 9922.50, 700.00, 10622.50, 0.00, 10622.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳9,922.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(220, 'INV-2025-0219', 31, '2025-08-31', 51082.50, 1500.00, 52582.50, 54600.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳51,082.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(221, 'INV-2025-0220', 21, '2025-08-31', 35437.50, 700.00, 36137.50, 0.00, 36137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳35,437.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(222, 'INV-2025-0221', 1, '2025-08-31', 12127.50, 700.00, 12827.50, 0.00, 12827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳12,127.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(223, 'INV-2025-0222', 19, '2025-08-31', 223807.50, 9000.00, 232807.50, 0.00, 232807.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳223,807.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(224, 'INV-2025-0223', 118, '2025-07-31', 147012.50, 4000.00, 151012.50, 0.00, 151012.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳147,012.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳151,012.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(225, 'INV-2025-0224', 131, '2025-07-31', 106837.50, 300.00, 107137.50, 0.00, 107137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳106,837.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳107,137.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(226, 'INV-2025-0225', 18, '2025-07-31', 24412.50, 700.00, 25112.50, 0.00, 25112.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳24,412.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳25,112.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(227, 'INV-2025-0226', 12, '2025-07-31', 281137.50, 4200.00, 285337.50, 0.00, 285337.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳281,137.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳285,337.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(228, 'INV-2025-0227', 25, '2025-07-31', 33862.50, 1500.00, 35362.50, 0.00, 35362.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳33,862.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳35,362.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(229, 'INV-2025-0228', 129, '2025-07-31', 46357.50, 600.00, 46957.50, 0.00, 46957.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳46,357.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳46,957.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(230, 'INV-2025-0229', 13, '2025-07-31', 221602.50, 1500.00, 223102.50, 0.00, 223102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳221,602.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳223,102.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(231, 'INV-2025-0230', 27, '2025-07-31', 64247.50, 700.00, 64947.50, 0.00, 64947.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳64,247.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳64,947.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(232, 'INV-2025-0231', 24, '2025-07-31', 53457.50, 1000.00, 54457.50, 0.00, 54457.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳53,457.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳54,457.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(233, 'INV-2025-0232', 9, '2025-07-31', 168682.50, 1000.00, 169682.50, 0.00, 169682.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳168,682.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳169,682.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(234, 'INV-2025-0233', 31, '2025-07-31', 105682.50, 1500.00, 107182.50, 0.00, 107182.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳105,682.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳107,182.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(235, 'INV-2025-0234', 15, '2025-07-31', 147577.50, 1000.00, 148577.50, 0.00, 148577.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳147,577.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳148,577.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(236, 'INV-2025-0235', 21, '2025-07-31', 73237.50, 700.00, 73937.50, 0.00, 73937.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳73,237.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳73,937.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(237, 'INV-2025-0236', 19, '2025-07-31', 459007.50, 9000.00, 468007.50, 0.00, 468007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳459,007.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳468,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(238, 'INV-2025-0237', 118, '2025-06-30', 295652.50, 4000.00, 299652.50, 0.00, 299652.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳295,652.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳299,652.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(239, 'INV-2025-0238', 131, '2025-06-30', 214357.50, 300.00, 214657.50, 0.00, 214657.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for June 2025 (Includes ৳214,357.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳214,657.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(240, 'INV-2025-0239', 18, '2025-06-30', 49612.50, 700.00, 50312.50, 0.00, 50312.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳49,612.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳50,312.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(241, 'INV-2025-0240', 30, '2025-06-30', 16537.50, 1000.00, 17537.50, 0.00, 17537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳16,537.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳17,537.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(242, 'INV-2025-0241', 12, '2025-06-30', 566737.50, 4200.00, 570937.50, 0.00, 570937.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳566,737.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳570,937.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(243, 'INV-2025-0242', 129, '2025-06-30', 93397.50, 600.00, 93997.50, 0.00, 93997.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for June 2025 (Includes ৳93,397.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳93,997.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(244, 'INV-2025-0243', 13, '2025-06-30', 446722.50, 1500.00, 448222.50, 0.00, 448222.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for June 2025 (Includes ৳446,722.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳448,222.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(245, 'INV-2025-0244', 27, '2025-06-30', 134007.50, 700.00, 134707.50, 0.00, 134707.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for June 2025 (Includes ৳134,007.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳134,707.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(246, 'INV-2025-0245', 7, '2025-06-30', 135135.00, 700.00, 135835.00, 0.00, 135835.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳135,135.00 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳135,835.00 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(247, 'INV-2025-0246', 3, '2025-06-30', 51817.50, 1000.00, 52817.50, 0.00, 52817.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳51,817.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳52,817.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(248, 'INV-2025-0247', 24, '2025-06-30', 108017.50, 1000.00, 109017.50, 0.00, 109017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳108,017.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳109,017.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(249, 'INV-2025-0248', 9, '2025-06-30', 340042.50, 1000.00, 341042.50, 0.00, 341042.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳340,042.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳341,042.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(250, 'INV-2025-0249', 15, '2025-06-30', 298777.50, 1000.00, 299777.50, 0.00, 299777.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳298,777.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳299,777.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(251, 'INV-2025-0250', 21, '2025-06-30', 148837.50, 700.00, 149537.50, 0.00, 149537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳148,837.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳149,537.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(252, 'INV-2025-0251', 1, '2025-06-30', 25987.50, 700.00, 26687.50, 0.00, 26687.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳25,987.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳26,687.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(253, 'INV-2025-0252', 19, '2025-06-30', 929407.50, 9000.00, 938407.50, 0.00, 938407.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳929,407.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳938,407.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(254, 'INV-2025-0253', 118, '2025-05-31', 592932.50, 4000.00, 596932.50, 0.00, 596932.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳592,932.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳596,932.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:55', '2025-11-20 05:36:12'),
(255, 'INV-2025-0254', 131, '2025-05-31', 429397.50, 300.00, 429697.50, 0.00, 429697.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳429,397.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳429,697.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:55', '2025-11-20 05:36:12'),
(256, 'INV-2025-0255', 18, '2025-05-31', 100012.50, 700.00, 100712.50, 0.00, 100712.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳100,012.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳100,712.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(257, 'INV-2025-0256', 12, '2025-05-31', 1137937.50, 4200.00, 1142137.50, 0.00, 1142137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳1,137,937.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳1,142,137.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(258, 'INV-2025-0257', 25, '2025-05-31', 69982.50, 1500.00, 71482.50, 0.00, 71482.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳69,982.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳71,482.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(259, 'INV-2025-0258', 129, '2025-05-31', 187477.50, 600.00, 188077.50, 0.00, 188077.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳187,477.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳188,077.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(260, 'INV-2025-0259', 13, '2025-05-31', 896962.50, 1500.00, 898462.50, 0.00, 898462.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for May 2025 (Includes ৳896,962.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳898,462.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(261, 'INV-2025-0260', 27, '2025-05-31', 273527.50, 700.00, 274227.50, 0.00, 274227.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for May 2025 (Includes ৳273,527.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳274,227.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(262, 'INV-2025-0261', 7, '2025-05-31', 271215.00, 700.00, 271915.00, 0.00, 271915.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳271,215.00 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳271,915.00 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(263, 'INV-2025-0262', 3, '2025-05-31', 107257.50, 1000.00, 108257.50, 0.00, 108257.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳107,257.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳108,257.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(264, 'INV-2025-0263', 9, '2025-05-31', 682762.50, 1000.00, 683762.50, 0.00, 683762.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳682,762.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳683,762.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(265, 'INV-2025-0264', 15, '2025-05-31', 601177.50, 1000.00, 602177.50, 0.00, 602177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳601,177.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳602,177.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(266, 'INV-2025-0265', 1, '2025-05-31', 53707.50, 700.00, 54407.50, 0.00, 54407.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳53,707.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳54,407.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(267, 'INV-2025-0266', 19, '2025-05-31', 1870207.50, 9000.00, 1879207.50, 0.00, 1879207.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳1,870,207.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳1,879,207.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(268, 'INV-2025-0267', 132, '2025-11-30', 0.00, 12000.00, 12000.00, 12652.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for November 2025', 1, '2025-11-16 04:27:52', '2025-11-18 08:34:35');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoices_backup`
--

INSERT INTO `invoices_backup` (`invoice_id`, `invoice_number`, `cp_id`, `issue_date`, `previous_due`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `status`, `is_closed`, `closed_at`, `closed_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-2025-0001', 1, '2025-02-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 5\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-04 22:00:00', '2025-11-20 05:33:19'),
(2, 'INV-2025-0002', 3, '2025-02-10', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 10\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-09 22:00:00', '2025-11-20 05:33:19'),
(3, 'INV-2025-0003', 118, '2025-02-15', 0.00, 4000.00, 4000.00, 500.00, 3500.00, 'partial', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 15\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳3,500.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳3,500.00 carried forward to next billing cycle.', 1, '2025-02-14 22:00:00', '2025-11-20 05:33:19'),
(4, 'INV-2025-0004', 7, '2025-02-20', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Monthly billing cycle - Package assigned Jan 20\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳700.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-02-19 22:00:00', '2025-11-20 05:33:19'),
(5, 'INV-2025-0005', 1, '2025-03-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '2nd monthly cycle\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Invoice fully paid.', 1, '2025-03-04 21:00:00', '2025-11-20 05:35:21'),
(6, 'INV-2025-0006', 3, '2025-03-10', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '2nd monthly cycle\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Invoice fully paid.', 1, '2025-03-09 21:00:00', '2025-11-20 05:35:21'),
(7, 'INV-2025-0007', 118, '2025-03-15', 602.50, 4000.00, 4602.50, 1704.50, 2898.00, 'paid', 0, NULL, NULL, '2nd monthly cycle - cleared previous due\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳2,898.00 carried forward to next billing cycle.', 1, '2025-03-14 21:00:00', '2025-11-20 05:35:21'),
(8, 'INV-2025-0008', 7, '2025-03-20', 1102.50, 700.00, 1802.50, 0.00, 1802.50, 'unpaid', 0, NULL, NULL, '2nd monthly cycle - overdue from Feb\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,802.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,802.50 carried forward to next billing cycle.', 1, '2025-03-19 21:00:00', '2025-11-20 05:35:21'),
(9, 'INV-2025-0009', 1, '2025-04-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, '3rd monthly cycle\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-04 21:00:00', '2025-11-20 05:35:38'),
(10, 'INV-2025-0010', 3, '2025-04-10', 0.00, 1000.00, 1000.00, 1897.50, 0.00, 'paid', 0, NULL, NULL, '3-month billing cycle completed\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-09 21:00:00', '2025-11-20 05:35:38'),
(11, 'INV-2025-0011', 9, '2025-02-25', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Invoice fully paid.', 1, '2025-02-24 21:00:00', '2025-11-20 05:33:19'),
(12, 'INV-2025-0012', 1, '2025-07-05', 0.00, 700.00, 700.00, 3307.50, 0.00, 'paid', 0, NULL, NULL, '6-month billing cycle completed\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-04 21:00:00', '2025-11-20 05:36:40'),
(13, 'INV-2025-0013', 3, '2025-07-10', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, '6-month billing cycle - pending payment\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-07-09 21:00:00', '2025-11-20 05:36:40'),
(14, 'INV-2025-0014', 12, '2025-03-05', 0.00, 4200.00, 4200.00, 1102.50, 3097.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳3,097.50 carried forward to next billing cycle.', 1, '2025-03-04 21:00:00', '2025-11-20 05:35:21'),
(15, 'INV-2025-0015', 13, '2025-03-12', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳397.50 carried forward to next billing cycle.', 1, '2025-03-11 21:00:00', '2025-11-20 05:35:21'),
(16, 'INV-2025-0016', 15, '2025-03-20', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'First monthly bill - overdue\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,000.00 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-03-19 21:00:00', '2025-11-20 05:35:21'),
(17, 'INV-2025-0017', 18, '2025-04-08', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-04-07 21:00:00', '2025-11-20 05:35:38'),
(18, 'INV-2025-0018', 19, '2025-09-18', 0.00, 9000.00, 9000.00, 9000.00, 0.00, 'paid', 0, NULL, NULL, '6-month cycle bill - pending', 1, '2025-09-17 21:00:00', '2025-11-19 06:06:58'),
(19, 'INV-2025-0019', 21, '2025-05-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Invoice fully paid.', 1, '2025-05-04 21:00:00', '2025-11-20 05:36:12'),
(20, 'INV-2025-0020', 24, '2025-05-15', 0.00, 1000.00, 1000.00, 500.00, 500.00, 'partial', 0, NULL, NULL, 'First monthly bill - partial payment\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳500.00 carried forward to next billing cycle.', 1, '2025-05-14 21:00:00', '2025-11-20 05:36:12'),
(21, 'INV-2025-0021', 25, '2025-06-10', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳397.50 carried forward to next billing cycle.', 1, '2025-06-09 21:00:00', '2025-11-20 05:36:26'),
(22, 'INV-2025-0022', 27, '2025-11-20', 0.00, 700.00, 700.00, 100.00, 600.00, 'partial', 0, NULL, NULL, '6-month cycle bill - future\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳600.00 carried forward to next billing cycle.', 1, '2025-11-19 21:00:00', '2025-11-20 05:18:43'),
(23, 'INV-2025-0023', 30, '2025-07-12', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-11 21:00:00', '2025-11-20 05:36:40'),
(24, 'INV-2025-0024', 31, '2026-07-08', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, '12-month cycle bill - future', 1, '2026-07-07 21:00:00', '2025-11-18 08:34:32'),
(25, 'INV-2025-0025', 33, '2025-09-05', 0.00, 700.00, 700.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill', 1, '2025-09-04 21:00:00', '2025-11-18 08:34:32'),
(26, 'INV-2025-0026', 7, '2025-07-20', 2205.00, 700.00, 2905.00, 3307.50, 0.00, 'paid', 0, NULL, NULL, 'Cleared all overdue amounts\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Invoice fully paid.', 1, '2025-07-19 21:00:00', '2025-11-20 05:36:40'),
(27, 'INV-2025-0027', 36, '2025-09-18', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'First monthly bill', 1, '2025-09-17 21:00:00', '2025-11-18 08:34:32'),
(28, 'INV-2025-0028', 37, '2025-10-10', 0.00, 1500.00, 1500.00, 1016.07, 483.93, 'partial', 0, NULL, NULL, 'First monthly bill - new customer', 1, '2025-10-09 21:00:00', '2025-11-18 08:34:32'),
(29, 'INV-2025-0029', 39, '2026-03-22', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, '6-month cycle bill - future', 1, '2026-03-21 21:00:00', '2025-11-18 08:34:32'),
(30, 'INV-2025-0030', 15, '2025-08-20', 1102.50, 1000.00, 2102.50, 2205.00, 0.00, 'paid', 0, NULL, NULL, 'Cleared overdue amount', 1, '2025-08-19 21:00:00', '2025-11-18 08:34:32'),
(31, 'INV-2025-0031', 42, '2025-11-01', 0.00, 1000.00, 1000.00, 500.00, 500.00, 'partial', 0, NULL, NULL, 'First monthly bill - new Oct customer\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳500.00 carried forward to next billing cycle.', 1, '2025-10-31 21:00:00', '2025-11-20 05:18:43'),
(32, 'INV-2025-0032', 43, '2025-11-02', 0.00, 700.00, 700.00, 457.85, 242.15, 'partial', 0, NULL, NULL, 'First monthly bill - new Oct customer\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳242.15 carried forward to next billing cycle.', 1, '2025-11-01 21:00:00', '2025-11-20 05:18:43'),
(33, 'INV-2025-0033', 45, '2025-11-03', 0.00, 1500.00, 1500.00, 1102.50, 397.50, 'paid', 0, NULL, NULL, 'First monthly bill - new Oct customer', 1, '2025-11-02 21:00:00', '2025-11-18 08:34:32'),
(34, 'INV-2025-0034', 3, '2025-09-10', 3307.50, 1000.00, 4307.50, 4410.00, 0.00, 'paid', 0, NULL, NULL, 'Cleared 6-month cycle overdue', 1, '2025-09-09 21:00:00', '2025-11-18 08:34:32'),
(35, 'INV-TEST-001', 129, '2025-11-16', 0.00, 600.00, 600.00, 0.00, 600.00, 'unpaid', 0, NULL, NULL, 'Test invoice for Jeni Khan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳600.00 carried forward to next billing cycle.', 1, '2025-11-16 02:12:02', '2025-11-20 05:18:43'),
(36, 'INV-2025-0035', 1, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(37, 'INV-2025-0036', 3, '2025-11-30', 3307.50, 1000.00, 4307.50, 0.00, 4307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(38, 'INV-2025-0037', 118, '2025-11-30', 602.50, 4000.00, 4602.50, 0.00, 4602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost, Turbo MAX (Includes ৳602.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,602.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(39, 'INV-2025-0038', 7, '2025-11-30', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(40, 'INV-2025-0039', 9, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(41, 'INV-2025-0040', 12, '2025-11-30', 0.00, 4200.00, 4200.00, 0.00, 4200.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,200.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(42, 'INV-2025-0041', 13, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(43, 'INV-2025-0042', 15, '2025-11-30', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost, Family Pack (Includes ৳1,102.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳2,102.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(44, 'INV-2025-0043', 18, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(45, 'INV-2025-0044', 19, '2025-11-30', 3307.50, 9000.00, 12307.50, 0.00, 12307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳12,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(46, 'INV-2025-0045', 21, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(47, 'INV-2025-0046', 24, '2025-11-30', 602.50, 1000.00, 1602.50, 0.00, 1602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan (Includes ৳602.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,602.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(48, 'INV-2025-0047', 25, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(49, 'INV-2025-0048', 30, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(50, 'INV-2025-0049', 31, '2025-11-30', 3307.50, 1500.00, 4807.50, 0.00, 4807.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,807.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(51, 'INV-2025-0050', 33, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(52, 'INV-2025-0051', 36, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(53, 'INV-2025-0052', 37, '2025-11-30', 86.43, 1500.00, 1586.43, 0.00, 1586.43, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost (Includes ৳86.43 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,586.43 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(54, 'INV-2025-0053', 39, '2025-11-30', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus, Family Pack (Includes ৳3,307.50 previous due)\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳4,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(55, 'INV-2025-0054', 47, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(56, 'INV-2025-0055', 50, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(57, 'INV-2025-0056', 51, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(58, 'INV-2025-0057', 53, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(59, 'INV-2025-0058', 55, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(60, 'INV-2025-0059', 57, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(61, 'INV-2025-0060', 58, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(62, 'INV-2025-0061', 60, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:30', '2025-11-20 05:18:43'),
(63, 'INV-2025-0062', 61, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(64, 'INV-2025-0063', 64, '2025-11-30', 0.00, 900.00, 900.00, 0.00, 900.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Streaming Plus, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳900.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(65, 'INV-2025-0064', 66, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(66, 'INV-2025-0065', 67, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(67, 'INV-2025-0066', 69, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(68, 'INV-2025-0067', 70, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(69, 'INV-2025-0068', 72, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(70, 'INV-2025-0069', 73, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(71, 'INV-2025-0070', 75, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(72, 'INV-2025-0071', 77, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(73, 'INV-2025-0072', 80, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(74, 'INV-2025-0073', 82, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(75, 'INV-2025-0074', 83, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(76, 'INV-2025-0075', 85, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(77, 'INV-2025-0076', 86, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(78, 'INV-2025-0077', 88, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(79, 'INV-2025-0078', 90, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(80, 'INV-2025-0079', 91, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(81, 'INV-2025-0080', 93, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(82, 'INV-2025-0081', 95, '2025-11-30', 0.00, 1000.00, 1000.00, 1102.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(83, 'INV-2025-0082', 96, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(84, 'INV-2025-0083', 98, '2025-11-30', 0.00, 700.00, 700.00, 787.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(85, 'INV-2025-0084', 99, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(86, 'INV-2025-0085', 101, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(87, 'INV-2025-0086', 121, '2025-11-30', 0.00, 15000.00, 15000.00, 0.00, 15000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus, WS, WS\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳15,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(88, 'INV-2025-0087', 104, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(89, 'INV-2025-0088', 105, '2025-11-30', 0.00, 1500.00, 1500.00, 1500.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Gaming Boost', 1, '2025-11-16 03:35:31', '2025-11-20 02:04:26'),
(90, 'INV-2025-0089', 107, '2025-11-30', 0.00, 700.00, 700.00, 187.50, 512.50, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳512.50 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(91, 'INV-2025-0090', 108, '2025-11-30', 0.00, 1000.00, 1000.00, 600.00, 400.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳400.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(92, 'INV-2025-0091', 110, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(93, 'INV-2025-0092', 111, '2025-11-30', 0.00, 700.00, 700.00, 0.00, 700.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Basic Plan, Streaming Plus\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳700.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(94, 'INV-2025-0093', 113, '2025-11-30', 0.00, 1000.00, 1000.00, 0.00, 1000.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Standard Plan, Gaming Boost\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,000.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(95, 'INV-2025-0094', 115, '2025-11-30', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Premium Plan, Family Pack\n[Month Closed: 2025-11-20 11:18:43] Due amount of ৳1,500.00 carried forward to next billing cycle.', 1, '2025-11-16 03:35:31', '2025-11-20 05:18:43'),
(96, 'INV-2025-0095', 133, '2025-11-30', 0.00, 300.00, 300.00, 13952.50, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Turbo, Basic Plan, Standard Plan', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(97, 'INV-2025-0096', 126, '2025-11-30', 0.00, 900.00, 900.00, 1207.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: star, Test Package', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(98, 'INV-2025-0097', 128, '2025-11-30', 0.00, 1200.00, 1200.00, 2912.90, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Family Pack, Gaming Boost', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(99, 'INV-2025-0098', 131, '2025-11-30', 0.00, 300.00, 300.00, 899.63, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated invoice for November 2025 - Products: Gaming Boost, Test Package', 1, '2025-11-16 03:35:31', '2025-11-18 08:34:33'),
(100, 'INV-2025-0099', 131, '2025-03-31', 997.50, 300.00, 1297.50, 0.00, 1297.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for March 2025 (Includes ৳997.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,297.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,297.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(101, 'INV-2025-0100', 18, '2025-03-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for March 2025 (Includes ৳787.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳1,487.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳1,487.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(102, 'INV-2025-0101', 129, '2025-03-31', 52.50, 600.00, 652.50, 0.00, 652.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for March 2025 (Includes ৳52.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳652.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳652.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(103, 'INV-2025-0102', 9, '2025-03-31', 2677.50, 1000.00, 3677.50, 0.00, 3677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for March 2025 (Includes ৳2,677.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳3,677.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳3,677.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(104, 'INV-2025-0103', 19, '2025-03-31', 18007.50, 9000.00, 27007.50, 0.00, 27007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for March 2025 (Includes ৳18,007.50 previous due)\n[Month Closed: 2025-11-20 11:20:34] Due amount of ৳27,007.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:35:21 by Admin User] Due amount of ৳27,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:49:55', '2025-11-20 05:35:21'),
(105, 'INV-2025-0104', 131, '2025-02-28', 2677.50, 300.00, 2977.50, 0.00, 2977.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for February 2025 (Includes ৳2,677.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳2,977.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳2,977.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:03', '2025-11-20 05:33:19'),
(106, 'INV-2025-0105', 12, '2025-02-28', 4462.50, 4200.00, 8662.50, 0.00, 8662.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for February 2025 (Includes ৳4,462.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳8,662.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳8,662.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:03', '2025-11-20 05:33:19'),
(107, 'INV-2025-0106', 129, '2025-02-28', 787.50, 600.00, 1387.50, 0.00, 1387.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for February 2025 (Includes ৳787.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳1,387.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳1,387.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:33:19'),
(108, 'INV-2025-0107', 13, '2025-02-28', 3517.50, 1500.00, 5017.50, 5017.01, 0.49, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for February 2025 (Includes ৳3,517.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳5,017.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳5,017.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:35:05'),
(109, 'INV-2025-0108', 15, '2025-02-28', 5827.50, 1000.00, 6827.50, 0.00, 6827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for February 2025 (Includes ৳5,827.50 previous due)\n[Month Closed: 2025-11-20 11:19:59] Due amount of ৳6,827.50 carried forward to next billing cycle.\n[Month Closed: 2025-11-20 11:33:19 by Admin User] Due amount of ৳6,827.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:04', '2025-11-20 05:33:19'),
(110, 'INV-2025-0109', 118, '2025-04-30', 7662.50, 4000.00, 11662.50, 0.00, 11662.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳7,662.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳11,662.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(111, 'INV-2025-0110', 131, '2025-04-30', 6037.50, 300.00, 6337.50, 0.00, 6337.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for April 2025 (Includes ৳6,037.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳6,337.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(112, 'INV-2025-0111', 12, '2025-04-30', 13387.50, 4200.00, 17587.50, 0.00, 17587.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳13,387.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳17,587.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(113, 'INV-2025-0112', 129, '2025-04-30', 2257.50, 600.00, 2857.50, 0.00, 2857.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for April 2025 (Includes ৳2,257.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳2,857.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(114, 'INV-2025-0113', 13, '2025-04-30', 10552.50, 1500.00, 12052.50, 0.00, 12052.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for April 2025 (Includes ৳10,552.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳12,052.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(115, 'INV-2025-0114', 7, '2025-04-30', 7560.00, 700.00, 8260.00, 0.00, 8260.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳7,560.00 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳8,260.00 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(116, 'INV-2025-0115', 24, '2025-04-30', 2307.50, 1000.00, 3307.50, 0.00, 3307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for April 2025 (Includes ৳2,307.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳3,307.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(117, 'INV-2025-0116', 9, '2025-04-30', 8032.50, 1000.00, 9032.50, 10710.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳8,032.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(118, 'INV-2025-0117', 15, '2025-04-30', 15277.50, 1000.00, 16277.50, 0.00, 16277.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳15,277.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳16,277.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(119, 'INV-2025-0118', 21, '2025-04-30', 2362.50, 700.00, 3062.50, 4000.00, 0.00, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳2,362.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Invoice fully paid.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(120, 'INV-2025-0119', 19, '2025-04-30', 47407.50, 9000.00, 56407.50, 8800.00, 47607.50, 'partial', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for April 2025 (Includes ৳47,407.50 previous due)\n[Month Closed: 2025-11-20 11:35:38 by Admin User] Due amount of ৳47,607.50 carried forward to next billing cycle.', 1, '2025-11-16 03:51:59', '2025-11-20 05:35:38'),
(121, 'INV-2025-0120', 118, '2025-10-31', 16952.50, 4000.00, 20952.50, 0.00, 20952.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳16,952.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(122, 'INV-2025-0121', 64, '2025-10-31', 4777.50, 900.00, 5677.50, 0.00, 5677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳4,777.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(123, 'INV-2025-0122', 121, '2025-10-31', 48982.50, 15000.00, 63982.50, 0.00, 63982.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳48,982.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(124, 'INV-2025-0123', 36, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(125, 'INV-2025-0124', 131, '2025-10-31', 12757.50, 300.00, 13057.50, 0.00, 13057.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳12,757.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(126, 'INV-2025-0125', 18, '2025-10-31', 2362.50, 700.00, 3062.50, 0.00, 3062.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳2,362.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(127, 'INV-2025-0126', 73, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(128, 'INV-2025-0127', 93, '2025-10-31', 1417.50, 700.00, 2117.50, 0.00, 2117.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,417.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(129, 'INV-2025-0128', 53, '2025-10-31', 1417.50, 700.00, 2117.50, 0.00, 2117.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,417.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(130, 'INV-2025-0129', 30, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(131, 'INV-2025-0130', 61, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(132, 'INV-2025-0131', 12, '2025-10-31', 31237.50, 4200.00, 35437.50, 0.00, 35437.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳31,237.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(133, 'INV-2025-0132', 25, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(134, 'INV-2025-0133', 80, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(135, 'INV-2025-0134', 39, '2025-10-31', 12127.50, 700.00, 12827.50, 0.00, 12827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳12,127.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(136, 'INV-2025-0135', 129, '2025-10-31', 5197.50, 600.00, 5797.50, 0.00, 5797.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳5,197.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(137, 'INV-2025-0136', 13, '2025-10-31', 24622.50, 1500.00, 26122.50, 0.00, 26122.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳24,622.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(138, 'INV-2025-0137', 86, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(139, 'INV-2025-0138', 107, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(140, 'INV-2025-0139', 57, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(141, 'INV-2025-0140', 110, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(142, 'INV-2025-0141', 88, '2025-10-31', 2677.50, 700.00, 3377.50, 0.00, 3377.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,677.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(143, 'INV-2025-0142', 47, '2025-10-31', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(144, 'INV-2025-0143', 99, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(145, 'INV-2025-0144', 95, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(146, 'INV-2025-0145', 69, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:33'),
(147, 'INV-2025-0146', 27, '2025-10-31', 3207.50, 700.00, 3907.50, 0.00, 3907.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,207.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(148, 'INV-2025-0147', 7, '2025-10-31', 16065.00, 700.00, 16765.00, 0.00, 16765.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳16,065.00 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(149, 'INV-2025-0148', 72, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(150, 'INV-2025-0149', 45, '2025-10-31', 0.00, 1500.00, 1500.00, 0.00, 1500.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(151, 'INV-2025-0150', 3, '2025-10-31', 10237.50, 1000.00, 11237.50, 0.00, 11237.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳10,237.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(152, 'INV-2025-0151', 66, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(153, 'INV-2025-0152', 101, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(154, 'INV-2025-0153', 85, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(155, 'INV-2025-0154', 24, '2025-10-31', 5717.50, 1000.00, 6717.50, 0.00, 6717.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳5,717.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(156, 'INV-2025-0155', 51, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(157, 'INV-2025-0156', 42, '2025-10-31', 602.50, 1000.00, 1602.50, 0.00, 1602.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳602.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34');
INSERT INTO `invoices_backup` (`invoice_id`, `invoice_number`, `cp_id`, `issue_date`, `previous_due`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `status`, `is_closed`, `closed_at`, `closed_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(158, 'INV-2025-0157', 9, '2025-10-31', 18742.50, 1000.00, 19742.50, 0.00, 19742.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳18,742.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(159, 'INV-2025-0158', 96, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(160, 'INV-2025-0159', 33, '2025-10-31', 3307.50, 700.00, 4007.50, 0.00, 4007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(161, 'INV-2025-0160', 90, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(162, 'INV-2025-0161', 75, '2025-10-31', 1732.50, 1000.00, 2732.50, 0.00, 2732.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(163, 'INV-2025-0162', 104, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(164, 'INV-2025-0163', 60, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:23', '2025-11-18 08:34:34'),
(165, 'INV-2025-0164', 111, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(166, 'INV-2025-0165', 82, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(167, 'INV-2025-0166', 31, '2025-10-31', 10132.50, 1500.00, 11632.50, 0.00, 11632.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳10,132.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(168, 'INV-2025-0167', 15, '2025-10-31', 34177.50, 1000.00, 35177.50, 0.00, 35177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳34,177.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(169, 'INV-2025-0168', 50, '2025-10-31', 1102.50, 1000.00, 2102.50, 0.00, 2102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,102.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(170, 'INV-2025-0169', 115, '2025-10-31', 3517.50, 1500.00, 5017.50, 5017.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-20 02:13:38'),
(171, 'INV-2025-0170', 83, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(172, 'INV-2025-0171', 67, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(173, 'INV-2025-0172', 77, '2025-10-31', 1627.50, 1500.00, 3127.50, 0.00, 3127.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳1,627.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(174, 'INV-2025-0173', 108, '2025-10-31', 4882.50, 1000.00, 5882.50, 0.00, 5882.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳4,882.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(175, 'INV-2025-0174', 70, '2025-10-31', 2992.50, 1000.00, 3992.50, 0.00, 3992.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,992.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(176, 'INV-2025-0175', 91, '2025-10-31', 3517.50, 1500.00, 5017.50, 0.00, 5017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for October 2025 (Includes ৳3,517.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(177, 'INV-2025-0176', 43, '2025-10-31', 644.65, 700.00, 1344.65, 0.00, 1344.65, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳644.65 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(178, 'INV-2025-0177', 98, '2025-10-31', 787.50, 700.00, 1487.50, 0.00, 1487.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for October 2025 (Includes ৳787.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(179, 'INV-2025-0178', 58, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(180, 'INV-2025-0179', 105, '2025-10-31', 2257.50, 1500.00, 3757.50, 0.00, 3757.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳2,257.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(181, 'INV-2025-0180', 21, '2025-10-31', 7087.50, 700.00, 7787.50, 0.00, 7787.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳7,087.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(182, 'INV-2025-0181', 1, '2025-10-31', 1732.50, 700.00, 2432.50, 0.00, 2432.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(183, 'INV-2025-0182', 19, '2025-10-31', 106207.50, 9000.00, 115207.50, 0.00, 115207.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳106,207.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(184, 'INV-2025-0183', 113, '2025-10-31', 1732.50, 1000.00, 2732.50, 0.00, 2732.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for October 2025 (Includes ৳1,732.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(185, 'INV-2025-0184', 55, '2025-10-31', 2992.50, 1000.00, 3992.50, 3155.80, 836.70, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for October 2025 (Includes ৳2,992.50 previous due)', 1, '2025-11-16 03:52:24', '2025-11-18 08:34:34'),
(186, 'INV-2025-0185', 118, '2025-09-30', 35532.50, 4000.00, 39532.50, 39532.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳35,532.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 06:10:35'),
(187, 'INV-2025-0186', 121, '2025-09-30', 131197.50, 15000.00, 146197.50, 0.00, 146197.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳131,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(188, 'INV-2025-0187', 131, '2025-09-30', 26197.50, 300.00, 26497.50, 0.00, 26497.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳26,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(189, 'INV-2025-0188', 18, '2025-09-30', 5512.50, 700.00, 6212.50, 0.00, 6212.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳5,512.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(190, 'INV-2025-0189', 30, '2025-09-30', 3307.50, 1000.00, 4307.50, 0.00, 4307.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(191, 'INV-2025-0190', 12, '2025-09-30', 66937.50, 4200.00, 71137.50, 0.00, 71137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳66,937.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(192, 'INV-2025-0191', 25, '2025-09-30', 6772.50, 1500.00, 8272.50, 8272.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳6,772.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 06:04:34'),
(193, 'INV-2025-0192', 39, '2025-09-30', 29767.50, 700.00, 30467.50, 0.00, 30467.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳29,767.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(194, 'INV-2025-0193', 129, '2025-09-30', 11077.50, 600.00, 11677.50, 0.00, 11677.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳11,077.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(195, 'INV-2025-0194', 13, '2025-09-30', 52762.50, 1500.00, 54262.50, 0.00, 54262.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳52,762.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(196, 'INV-2025-0195', 27, '2025-09-30', 11927.50, 700.00, 12627.50, 0.00, 12627.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳11,927.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(197, 'INV-2025-0196', 7, '2025-09-30', 33075.00, 700.00, 33775.00, 0.00, 33775.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳33,075.00 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(198, 'INV-2025-0197', 24, '2025-09-30', 12537.50, 1000.00, 13537.50, 0.00, 13537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for September 2025 (Includes ৳12,537.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(199, 'INV-2025-0198', 9, '2025-09-30', 40162.50, 1000.00, 41162.50, 0.00, 41162.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳40,162.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(200, 'INV-2025-0199', 31, '2025-09-30', 23782.50, 1500.00, 25282.50, 0.00, 25282.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for September 2025 (Includes ৳23,782.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(201, 'INV-2025-0200', 15, '2025-09-30', 71977.50, 1000.00, 72977.50, 72977.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳71,977.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 05:51:06'),
(202, 'INV-2025-0201', 37, '2025-09-30', 2430.36, 1500.00, 3930.36, 3030.36, 900.00, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for September 2025 (Includes ৳2,430.36 previous due)', 1, '2025-11-16 03:52:46', '2025-11-19 05:43:07'),
(203, 'INV-2025-0202', 21, '2025-09-30', 16537.50, 700.00, 17237.50, 961.93, 16275.57, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳16,537.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(204, 'INV-2025-0203', 1, '2025-09-30', 5197.50, 700.00, 5897.50, 1437.18, 4460.32, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for September 2025 (Includes ৳5,197.50 previous due)', 1, '2025-11-16 03:52:46', '2025-11-18 08:34:34'),
(205, 'INV-2025-0204', 118, '2025-08-31', 72692.50, 4000.00, 76692.50, 0.00, 76692.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳72,692.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(206, 'INV-2025-0205', 36, '2025-08-31', 3307.50, 1000.00, 4307.50, 4410.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳3,307.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(207, 'INV-2025-0206', 131, '2025-08-31', 53077.50, 300.00, 53377.50, 0.00, 53377.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳53,077.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(208, 'INV-2025-0207', 18, '2025-08-31', 11812.50, 700.00, 12512.50, 0.00, 12512.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳11,812.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(209, 'INV-2025-0208', 30, '2025-08-31', 7717.50, 1000.00, 8717.50, 0.00, 8717.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳7,717.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(210, 'INV-2025-0209', 12, '2025-08-31', 138337.50, 4200.00, 142537.50, 0.00, 142537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳138,337.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(211, 'INV-2025-0210', 25, '2025-08-31', 15802.50, 1500.00, 17302.50, 8060.00, 9242.50, 'partial', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳15,802.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(212, 'INV-2025-0211', 129, '2025-08-31', 22837.50, 600.00, 23437.50, 0.00, 23437.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for August 2025 (Includes ৳22,837.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(213, 'INV-2025-0212', 13, '2025-08-31', 109042.50, 1500.00, 110542.50, 0.00, 110542.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳109,042.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(214, 'INV-2025-0213', 27, '2025-08-31', 29367.50, 700.00, 30067.50, 0.00, 30067.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳29,367.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(215, 'INV-2025-0214', 7, '2025-08-31', 67095.00, 700.00, 67795.00, 0.00, 67795.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳67,095.00 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(216, 'INV-2025-0215', 3, '2025-08-31', 24097.50, 1000.00, 25097.50, 0.00, 25097.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳24,097.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(217, 'INV-2025-0216', 24, '2025-08-31', 26177.50, 1000.00, 27177.50, 0.00, 27177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for August 2025 (Includes ৳26,177.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(218, 'INV-2025-0217', 9, '2025-08-31', 83002.50, 1000.00, 84002.50, 0.00, 84002.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳83,002.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(219, 'INV-2025-0218', 33, '2025-08-31', 9922.50, 700.00, 10622.50, 0.00, 10622.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳9,922.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(220, 'INV-2025-0219', 31, '2025-08-31', 51082.50, 1500.00, 52582.50, 54600.00, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for August 2025 (Includes ৳51,082.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(221, 'INV-2025-0220', 21, '2025-08-31', 35437.50, 700.00, 36137.50, 0.00, 36137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳35,437.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(222, 'INV-2025-0221', 1, '2025-08-31', 12127.50, 700.00, 12827.50, 0.00, 12827.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳12,127.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(223, 'INV-2025-0222', 19, '2025-08-31', 223807.50, 9000.00, 232807.50, 0.00, 232807.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for August 2025 (Includes ৳223,807.50 previous due)', 1, '2025-11-16 03:53:21', '2025-11-18 08:34:34'),
(224, 'INV-2025-0223', 118, '2025-07-31', 147012.50, 4000.00, 151012.50, 0.00, 151012.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳147,012.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳151,012.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(225, 'INV-2025-0224', 131, '2025-07-31', 106837.50, 300.00, 107137.50, 0.00, 107137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳106,837.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳107,137.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(226, 'INV-2025-0225', 18, '2025-07-31', 24412.50, 700.00, 25112.50, 0.00, 25112.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳24,412.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳25,112.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(227, 'INV-2025-0226', 12, '2025-07-31', 281137.50, 4200.00, 285337.50, 0.00, 285337.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳281,137.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳285,337.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(228, 'INV-2025-0227', 25, '2025-07-31', 33862.50, 1500.00, 35362.50, 0.00, 35362.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳33,862.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳35,362.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(229, 'INV-2025-0228', 129, '2025-07-31', 46357.50, 600.00, 46957.50, 0.00, 46957.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for July 2025 (Includes ৳46,357.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳46,957.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(230, 'INV-2025-0229', 13, '2025-07-31', 221602.50, 1500.00, 223102.50, 0.00, 223102.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳221,602.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳223,102.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(231, 'INV-2025-0230', 27, '2025-07-31', 64247.50, 700.00, 64947.50, 0.00, 64947.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳64,247.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳64,947.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(232, 'INV-2025-0231', 24, '2025-07-31', 53457.50, 1000.00, 54457.50, 0.00, 54457.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for July 2025 (Includes ৳53,457.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳54,457.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(233, 'INV-2025-0232', 9, '2025-07-31', 168682.50, 1000.00, 169682.50, 0.00, 169682.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳168,682.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳169,682.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(234, 'INV-2025-0233', 31, '2025-07-31', 105682.50, 1500.00, 107182.50, 0.00, 107182.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for July 2025 (Includes ৳105,682.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳107,182.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(235, 'INV-2025-0234', 15, '2025-07-31', 147577.50, 1000.00, 148577.50, 0.00, 148577.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳147,577.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳148,577.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(236, 'INV-2025-0235', 21, '2025-07-31', 73237.50, 700.00, 73937.50, 0.00, 73937.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳73,237.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳73,937.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(237, 'INV-2025-0236', 19, '2025-07-31', 459007.50, 9000.00, 468007.50, 0.00, 468007.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for July 2025 (Includes ৳459,007.50 previous due)\n[Month Closed: 2025-11-20 11:36:40 by Admin User] Due amount of ৳468,007.50 carried forward to next billing cycle.', 1, '2025-11-16 03:53:39', '2025-11-20 05:36:40'),
(238, 'INV-2025-0237', 118, '2025-06-30', 295652.50, 4000.00, 299652.50, 0.00, 299652.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳295,652.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳299,652.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(239, 'INV-2025-0238', 131, '2025-06-30', 214357.50, 300.00, 214657.50, 0.00, 214657.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for June 2025 (Includes ৳214,357.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳214,657.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(240, 'INV-2025-0239', 18, '2025-06-30', 49612.50, 700.00, 50312.50, 0.00, 50312.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳49,612.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳50,312.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(241, 'INV-2025-0240', 30, '2025-06-30', 16537.50, 1000.00, 17537.50, 0.00, 17537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳16,537.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳17,537.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(242, 'INV-2025-0241', 12, '2025-06-30', 566737.50, 4200.00, 570937.50, 0.00, 570937.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳566,737.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳570,937.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(243, 'INV-2025-0242', 129, '2025-06-30', 93397.50, 600.00, 93997.50, 0.00, 93997.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for June 2025 (Includes ৳93,397.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳93,997.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(244, 'INV-2025-0243', 13, '2025-06-30', 446722.50, 1500.00, 448222.50, 0.00, 448222.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for June 2025 (Includes ৳446,722.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳448,222.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(245, 'INV-2025-0244', 27, '2025-06-30', 134007.50, 700.00, 134707.50, 0.00, 134707.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for June 2025 (Includes ৳134,007.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳134,707.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(246, 'INV-2025-0245', 7, '2025-06-30', 135135.00, 700.00, 135835.00, 0.00, 135835.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳135,135.00 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳135,835.00 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(247, 'INV-2025-0246', 3, '2025-06-30', 51817.50, 1000.00, 52817.50, 0.00, 52817.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳51,817.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳52,817.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(248, 'INV-2025-0247', 24, '2025-06-30', 108017.50, 1000.00, 109017.50, 0.00, 109017.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for June 2025 (Includes ৳108,017.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳109,017.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(249, 'INV-2025-0248', 9, '2025-06-30', 340042.50, 1000.00, 341042.50, 0.00, 341042.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳340,042.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳341,042.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(250, 'INV-2025-0249', 15, '2025-06-30', 298777.50, 1000.00, 299777.50, 0.00, 299777.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳298,777.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳299,777.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(251, 'INV-2025-0250', 21, '2025-06-30', 148837.50, 700.00, 149537.50, 0.00, 149537.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳148,837.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳149,537.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(252, 'INV-2025-0251', 1, '2025-06-30', 25987.50, 700.00, 26687.50, 0.00, 26687.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳25,987.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳26,687.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(253, 'INV-2025-0252', 19, '2025-06-30', 929407.50, 9000.00, 938407.50, 0.00, 938407.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for June 2025 (Includes ৳929,407.50 previous due)\n[Month Closed: 2025-11-20 11:36:26 by Admin User] Due amount of ৳938,407.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:29', '2025-11-20 05:36:26'),
(254, 'INV-2025-0253', 118, '2025-05-31', 592932.50, 4000.00, 596932.50, 0.00, 596932.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳592,932.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳596,932.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:55', '2025-11-20 05:36:12'),
(255, 'INV-2025-0254', 131, '2025-05-31', 429397.50, 300.00, 429697.50, 0.00, 429697.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳429,397.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳429,697.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:55', '2025-11-20 05:36:12'),
(256, 'INV-2025-0255', 18, '2025-05-31', 100012.50, 700.00, 100712.50, 0.00, 100712.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳100,012.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳100,712.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(257, 'INV-2025-0256', 12, '2025-05-31', 1137937.50, 4200.00, 1142137.50, 0.00, 1142137.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳1,137,937.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳1,142,137.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(258, 'INV-2025-0257', 25, '2025-05-31', 69982.50, 1500.00, 71482.50, 0.00, 71482.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳69,982.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳71,482.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(259, 'INV-2025-0258', 129, '2025-05-31', 187477.50, 600.00, 188077.50, 0.00, 188077.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 3-Month billing for 3 month(s) - Due for May 2025 (Includes ৳187,477.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳188,077.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(260, 'INV-2025-0259', 13, '2025-05-31', 896962.50, 1500.00, 898462.50, 0.00, 898462.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 12-Month billing for 12 month(s) - Due for May 2025 (Includes ৳896,962.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳898,462.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(261, 'INV-2025-0260', 27, '2025-05-31', 273527.50, 700.00, 274227.50, 0.00, 274227.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s), 12-Month billing for 12 month(s) - Due for May 2025 (Includes ৳273,527.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳274,227.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(262, 'INV-2025-0261', 7, '2025-05-31', 271215.00, 700.00, 271915.00, 0.00, 271915.00, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 1-Month billing for 1 month(s) - Due for May 2025 (Includes ৳271,215.00 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳271,915.00 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(263, 'INV-2025-0262', 3, '2025-05-31', 107257.50, 1000.00, 108257.50, 0.00, 108257.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳107,257.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳108,257.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(264, 'INV-2025-0263', 9, '2025-05-31', 682762.50, 1000.00, 683762.50, 0.00, 683762.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳682,762.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳683,762.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(265, 'INV-2025-0264', 15, '2025-05-31', 601177.50, 1000.00, 602177.50, 0.00, 602177.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 3-Month billing for 3 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳601,177.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳602,177.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(266, 'INV-2025-0265', 1, '2025-05-31', 53707.50, 700.00, 54407.50, 0.00, 54407.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 1-Month billing for 1 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳53,707.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳54,407.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(267, 'INV-2025-0266', 19, '2025-05-31', 1870207.50, 9000.00, 1879207.50, 0.00, 1879207.50, 'unpaid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s), 6-Month billing for 6 month(s) - Due for May 2025 (Includes ৳1,870,207.50 previous due)\n[Month Closed: 2025-11-20 11:36:12 by Admin User] Due amount of ৳1,879,207.50 carried forward to next billing cycle.', 1, '2025-11-16 03:54:56', '2025-11-20 05:36:12'),
(268, 'INV-2025-0267', 132, '2025-11-30', 0.00, 12000.00, 12000.00, 12652.50, 0.00, 'paid', 0, NULL, NULL, 'Auto-generated: 6-Month billing for 6 month(s) - Due for November 2025', 1, '2025-11-16 04:27:52', '2025-11-18 08:34:35');

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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_11_06_100942_sync_with_current_database', 1),
(2, '2025_11_06_101329_create_audit_logs_table', 1),
(3, '2025_11_06_101329_create_customer_to_packages_table', 1),
(4, '2025_11_06_101329_create_customers_table', 1),
(5, '2025_11_06_101329_create_failed_jobs_table', 1),
(6, '2025_11_06_101329_create_invoices_table', 1),
(7, '2025_11_06_101329_create_notifications_table', 1),
(8, '2025_11_06_101329_create_packages_table', 1),
(9, '2025_11_06_101329_create_password_resets_table', 1),
(10, '2025_11_06_101329_create_payments_table', 1),
(11, '2025_11_06_101329_create_personal_access_tokens_table', 1),
(12, '2025_11_06_101329_create_settings_table', 1),
(13, '2025_11_06_101329_create_subscriptions_table', 1),
(14, '2025_11_06_101329_create_system_settings_table', 1),
(15, '2025_11_06_101329_create_users_table', 1),
(16, '2025_11_06_101330_create_monthly_revenue_summary_view', 1),
(17, '2025_11_06_101332_add_foreign_keys_to_customer_to_packages_table', 1),
(18, '2025_11_06_101332_add_foreign_keys_to_customers_table', 1),
(19, '2025_11_10_060151_create_sessions_table', 1),
(20, '2025_11_10_082250_add_missing_columns_to_payments_table', 1),
(21, '2025_11_10_082359_add_transaction_id_to_payments_table', 1),
(22, '2025_11_10_082423_add_transaction_id_to_payments_table', 1),
(23, '2025_11_11_080509_rename_packages_to_products', 1),
(24, '2025_11_11_080538_rename_customer_to_packages_to_customer_to_products', 1),
(25, '2025_11_11_080621_update_foreign_keys_in_customer_to_products', 1),
(26, '2025_11_11_090000_create_product_types_table', 2),
(27, '2025_11_11_090001_fix_product_type_id_column_in_products_table', 3),
(28, '2025_11_11_090002_change_product_type_id_to_varchar_in_products_table', 4),
(29, '2025_11_11_090003_rename_product_types_to_product_type_and_add_descriptions', 4),
(30, '2025_11_11_090004_change_product_type_to_product_type_id_and_add_foreign_key', 5),
(31, '2025_11_11_115949_fix_payments_foreign_keys', 6),
(32, '2025_11_13_110116_add_due_day_to_customer_to_products_table', 7),
(33, '2025_11_13_110413_set_default_due_day_for_existing_records', 8),
(34, '2025_11_15_060245_add_invoice_id_to_customer_to_products_table', 9),
(35, '2025_11_16_150000_update_customer_to_products_due_date', 10),
(36, '2025_11_17_042413_add_product_id_to_payments_table', 11),
(37, '2025_11_17_043312_remove_vat_and_service_charge_from_invoices_table', 12),
(38, '2025_11_17_061352_add_product_id_to_invoices_table', 13),
(39, '2025_11_17_update_payments_table', 14),
(40, '2025_11_18_061259_add_cp_id_to_payments_table', 14),
(41, '2025_11_18_062655_backfill_cp_id_in_payments_table', 15),
(42, '2025_11_18_070000_refactor_invoices_to_use_cp_id', 16),
(43, '2025_11_19_095417_add_subtotal_to_customer_to_products_table', 17),
(44, '2025_11_20_064900_add_customer_product_id_to_customer_to_products_table', 18),
(45, '2025_11_20_100000_remove_subtotal_from_customer_to_products_table', 19),
(46, '2025_11_20_110000_create_monthly_billing_summaries_table', 20),
(47, '2024_01_20_000000_add_closed_months_tracking', 21),
(48, '2025_11_23_105828_fix_cp_id_auto_increment_in_customer_to_products_table', 22),
(49, '2025_11_23_100000_add_custom_price_to_customer_to_products_table', 23);

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
  `cp_id` bigint UNSIGNED DEFAULT NULL,
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
  KEY `payments_collected_by_foreign` (`collected_by`),
  KEY `payments_cp_id_foreign` (`cp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `invoice_id`, `cp_id`, `amount`, `payment_method`, `payment_date`, `note`, `created_at`, `updated_at`, `collected_by`, `status`, `notes`) VALUES
(1, 28, 37, 1002.50, 'cash', '2025-11-10 00:00:00', NULL, '2025-11-09 21:14:20', '2025-11-18 06:27:49', NULL, 'completed', NULL),
(2, 33, 45, 1002.50, 'card', '2025-11-13 00:00:00', NULL, '2025-11-13 00:50:17', '2025-11-18 06:27:49', 1, 'completed', 'qqq'),
(3, 33, 45, 1002.50, 'card', '2025-11-13 00:00:00', NULL, '2025-11-13 00:50:17', '2025-11-18 06:27:49', 1, 'completed', 'qqq'),
(4, 33, 45, 100.00, 'cash', '2025-11-13 00:00:00', NULL, '2025-11-13 03:49:27', '2025-11-18 06:27:49', 1, 'completed', 'okok'),
(5, 33, 45, 100.00, 'cash', '2025-11-13 00:00:00', NULL, '2025-11-13 03:49:27', '2025-11-18 06:27:49', 1, 'completed', 'okok'),
(6, 28, 37, 13.57, 'cash', '2025-11-15 00:00:00', NULL, '2025-11-14 23:47:47', '2025-11-18 06:27:49', 1, 'completed', 'pore'),
(7, 28, 37, 13.57, 'cash', '2025-11-15 00:00:00', NULL, '2025-11-14 23:47:47', '2025-11-18 06:27:49', 1, 'completed', 'pore'),
(8, 31, 42, 500.00, 'mobile_banking', '2025-11-15 00:00:00', NULL, '2025-11-14 23:56:53', '2025-11-18 06:27:49', 1, 'completed', 'next week'),
(9, 31, 42, 500.00, 'mobile_banking', '2025-11-15 00:00:00', NULL, '2025-11-14 23:56:53', '2025-11-18 06:27:49', 1, 'completed', 'next week'),
(10, 32, 43, 200.00, 'cash', '2025-11-15 00:00:00', NULL, '2025-11-14 23:57:29', '2025-11-18 06:27:49', 1, 'completed', 'next week'),
(11, 32, 43, 200.00, 'cash', '2025-11-15 00:00:00', NULL, '2025-11-14 23:57:29', '2025-11-18 06:27:49', 1, 'completed', 'next week'),
(12, 32, 43, 107.69, 'mobile_banking', '2025-11-15 00:00:00', NULL, '2025-11-15 00:09:17', '2025-11-18 06:27:49', 1, 'completed', 'ouuouo'),
(13, 32, 43, 107.69, 'mobile_banking', '2025-11-15 00:00:00', NULL, '2025-11-15 00:09:17', '2025-11-18 06:27:49', 1, 'completed', 'ouuouo'),
(14, 32, 43, 34.32, 'online', '2025-11-15 00:00:00', NULL, '2025-11-15 00:20:09', '2025-11-18 06:27:49', 1, 'completed', 'sdsdsds'),
(15, 32, 43, 34.32, 'online', '2025-11-15 00:00:00', NULL, '2025-11-15 00:20:09', '2025-11-18 06:27:49', 1, 'completed', 'sdsdsds'),
(16, 32, 43, 115.84, 'mobile_banking', '2025-11-16 00:00:00', NULL, '2025-11-15 22:38:33', '2025-11-18 06:27:49', 1, 'completed', 'cvcv'),
(17, 32, 43, 115.84, 'mobile_banking', '2025-11-16 00:00:00', NULL, '2025-11-15 22:38:33', '2025-11-18 06:27:49', 1, 'completed', 'cvcv'),
(18, 22, 27, 100.00, 'online', '2025-11-16 00:00:00', NULL, '2025-11-15 23:38:07', '2025-11-18 06:27:49', 1, 'completed', 'ww'),
(19, 22, 27, 100.00, 'online', '2025-11-16 00:00:00', NULL, '2025-11-15 23:38:07', '2025-11-18 06:27:49', 1, 'completed', 'ww'),
(20, 268, 132, 12000.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:33:08', '2025-11-18 06:27:49', 1, 'completed', 'qqq'),
(21, 268, 132, 12000.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:33:08', '2025-11-18 06:27:49', 1, 'completed', 'qqq'),
(22, 96, 120, 1500.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:58:48', '2025-11-18 06:27:49', 1, 'completed', NULL),
(23, 96, 120, 1500.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:58:48', '2025-11-18 06:27:49', 1, 'completed', NULL),
(24, 99, 130, 500.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:59:41', '2025-11-18 06:27:49', 1, 'completed', NULL),
(25, 99, 130, 500.00, 'cash', '2025-11-16 00:00:00', NULL, '2025-11-16 04:59:41', '2025-11-18 06:27:49', 1, 'completed', NULL),
(26, 99, 130, 200.00, 'bank_transfer', '2025-11-16 00:00:00', NULL, '2025-11-16 05:04:19', '2025-11-18 06:27:49', 1, 'completed', NULL),
(27, 99, 130, 200.00, 'bank_transfer', '2025-11-16 00:00:00', NULL, '2025-11-16 05:04:19', '2025-11-18 06:27:49', 1, 'completed', NULL),
(28, 99, 130, 66.60, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 22:16:21', '2025-11-18 06:27:49', 1, 'completed', NULL),
(29, 98, 125, 1000.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 22:17:54', '2025-11-18 06:27:49', 1, 'completed', NULL),
(30, 97, 126, 300.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 22:28:50', '2025-11-18 06:27:49', 1, 'completed', 'er4'),
(31, 96, 120, 1050.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 22:58:12', '2025-11-18 06:27:49', 1, 'completed', '1q1'),
(32, 96, 120, 1300.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 23:32:11', '2025-11-18 06:27:49', 1, 'completed', NULL),
(33, 91, 108, 600.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 23:48:40', '2025-11-18 06:27:49', 1, 'completed', '2wsx'),
(34, 185, 55, 1068.75, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-16 23:52:57', '2025-11-18 06:27:49', 1, 'completed', NULL),
(35, 185, 55, 1087.05, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 00:05:46', '2025-11-18 06:27:49', 1, 'completed', 'as'),
(36, 98, 125, 200.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 00:07:38', '2025-11-18 06:27:49', 1, 'completed', NULL),
(37, 98, 125, 220.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 00:43:31', '2025-11-18 06:27:49', 1, 'completed', NULL),
(38, 90, 107, 187.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 01:11:20', '2025-11-18 06:27:49', 1, 'completed', 'qw'),
(39, 98, 125, 782.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 02:37:49', '2025-11-18 06:27:49', 1, 'completed', NULL),
(40, 99, 130, 88.93, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:02:08', '2025-11-18 06:27:49', 1, 'completed', NULL),
(41, 99, 130, 88.93, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:02:08', '2025-11-18 06:27:49', 1, 'completed', NULL),
(42, 99, 130, 29.65, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:07:23', '2025-11-18 06:27:49', 1, 'completed', NULL),
(43, 99, 130, 29.65, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:07:23', '2025-11-18 06:27:49', 1, 'completed', NULL),
(44, 204, 1, 823.59, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:13:04', '2025-11-18 06:27:49', 1, 'completed', NULL),
(45, 204, 1, 823.59, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:13:04', '2025-11-18 06:27:49', 1, 'completed', NULL),
(46, 203, 21, 961.93, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:13:28', '2025-11-18 06:27:49', 1, 'completed', NULL),
(47, 203, 21, 961.93, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:13:28', '2025-11-18 06:27:49', 1, 'completed', NULL),
(48, 204, 1, 613.59, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:26:16', '2025-11-18 06:27:49', 1, 'completed', NULL),
(49, 99, 130, 9.88, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:32:13', '2025-11-18 06:27:49', 1, 'completed', NULL),
(50, 99, 130, 3.29, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:32:33', '2025-11-18 06:27:49', 1, 'completed', NULL),
(51, 99, 130, 0.55, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:39:26', '2025-11-18 06:27:49', 1, 'completed', NULL),
(52, 98, 125, 319.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:40:11', '2025-11-18 06:27:49', 1, 'completed', NULL),
(53, 99, 130, 0.73, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:47:08', '2025-11-18 06:27:49', 1, 'completed', NULL),
(54, 98, 125, 191.40, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:47:28', '2025-11-18 06:27:49', 1, 'completed', NULL),
(55, 268, 132, 652.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:53:48', '2025-11-18 06:27:49', 1, 'completed', NULL),
(56, 82, 95, 1102.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 03:54:15', '2025-11-18 06:27:49', 1, 'completed', NULL),
(57, 96, 120, 10102.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 04:50:40', '2025-11-18 06:27:49', 1, 'completed', NULL),
(58, 84, 98, 787.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 04:59:27', '2025-11-18 06:27:49', 1, 'completed', NULL),
(59, 98, 125, 200.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 05:01:53', '2025-11-18 06:27:49', 1, 'completed', NULL),
(60, 97, 126, 907.50, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 05:42:19', '2025-11-18 06:27:49', 1, 'completed', NULL),
(61, 220, 31, 54600.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 05:53:49', '2025-11-18 06:27:49', 1, 'completed', NULL),
(62, 206, 36, 4410.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 05:54:05', '2025-11-18 06:27:49', 1, 'completed', NULL),
(63, 211, 25, 8060.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 05:55:10', '2025-11-18 06:27:49', 1, 'completed', NULL),
(64, 117, 9, 10710.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 06:02:21', '2025-11-18 06:27:49', 1, 'completed', NULL),
(65, 120, 19, 8800.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 06:05:19', '2025-11-18 06:27:49', 1, 'completed', NULL),
(66, 119, 21, 4000.00, 'cash', '2025-11-17 00:00:00', NULL, '2025-11-17 06:06:15', '2025-11-18 06:27:49', 1, 'completed', NULL),
(67, 185, 55, 1000.00, 'cash', '2025-11-18 00:00:00', NULL, '2025-11-17 23:46:25', '2025-11-18 06:27:49', 1, 'completed', NULL),
(68, 202, NULL, 3030.36, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 05:43:07', '2025-11-19 05:43:07', 1, 'completed', NULL),
(69, 201, NULL, 70977.50, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 05:43:29', '2025-11-19 05:43:29', 1, 'completed', NULL),
(70, 201, NULL, 2000.00, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 05:51:06', '2025-11-19 05:51:06', 1, 'completed', NULL),
(71, 192, 26, 8072.50, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:04:05', '2025-11-19 06:04:05', 1, 'completed', NULL),
(72, 192, 26, 200.00, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:04:34', '2025-11-19 06:04:34', 1, 'completed', NULL),
(73, 18, 20, 3000.00, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:05:48', '2025-11-19 06:05:48', 1, 'completed', NULL),
(74, 18, 19, 6000.00, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:06:58', '2025-11-19 06:06:58', 1, 'completed', NULL),
(75, 186, 118, 30532.50, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:10:10', '2025-11-19 06:10:10', 1, 'completed', NULL),
(76, 186, 117, 9000.00, 'cash', '2025-11-19 00:00:00', NULL, '2025-11-19 06:10:35', '2025-11-19 06:10:35', 1, 'completed', NULL),
(77, 89, 105, 1500.00, 'cash', '2025-11-20 00:00:00', NULL, '2025-11-20 02:04:26', '2025-11-20 02:04:26', 1, 'completed', NULL),
(78, 170, 115, 5017.50, 'cash', '2025-11-20 00:00:00', NULL, '2025-11-20 02:13:38', '2025-11-20 02:13:38', 1, 'completed', NULL),
(79, 108, 13, 5017.01, 'cash', '2025-11-20 00:00:00', NULL, '2025-11-20 05:35:05', '2025-11-20 05:35:05', 1, 'completed', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`p_id`, `name`, `product_type_id`, `description`, `monthly_price`, `created_at`, `updated_at`) VALUES
(1, 'Basic Plan', 1, 'Home broadband 10 Mbps connection', 700.00, '2024-12-31 17:50:20', NULL),
(2, 'Standard Plan', 1, 'Home broadband 25 Mbps connection', 1000.00, '2024-12-31 17:50:20', NULL),
(3, 'Premium Plan', 1, 'Home broadband 50 Mbps connection', 1500.00, '2024-12-31 17:50:20', NULL),
(4, 'Gaming Boost', 2, 'Enhanced gaming experience with optimized connectivity', 200.00, '2024-12-31 19:01:22', '2024-12-31 19:01:22'),
(5, 'Streaming Plus', 2, 'Improved streaming quality and bandwidth for video content', 150.00, '2024-12-31 19:01:22', '2024-12-31 19:01:22'),
(6, 'Family Pack', 2, 'Additional connections and shared benefits for family members', 300.00, '2024-12-31 19:01:22', '2024-12-31 19:01:22'),
(7, 'Test Package', 2, 'Test Description', 100.00, '2025-11-09 23:32:34', '2025-11-09 23:32:34'),
(8, 'Turbo', 2, 'zzzz', 600.00, '2025-11-09 23:44:15', '2025-11-09 23:44:15'),
(9, 'Turbo MAX', 4, 'max', 2000.00, '2025-11-10 00:01:08', '2025-11-11 05:01:53'),
(10, 'star', 7, 'demo', 300.00, '2025-11-11 05:19:20', '2025-11-11 05:19:20'),
(11, 'WS', 10, 'yifyfhv', 5000.00, '2025-11-11 06:06:16', '2025-11-11 06:06:16'),
(12, 'WS', 10, 'yifyfhv', 5000.00, '2025-11-11 06:06:30', '2025-11-11 06:06:30'),
(13, 'Test02', 6, 'Testing', 300.00, '2025-11-19 23:14:16', '2025-11-19 23:43:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_type`
--

INSERT INTO `product_type` (`id`, `name`, `descriptions`, `created_at`, `updated_at`) VALUES
(1, 'regular', NULL, '2025-11-09 18:35:49', '2025-11-09 18:35:49'),
(2, 'special', NULL, '2025-11-09 18:35:49', '2025-11-09 18:35:49'),
(4, 'Silver', NULL, '2025-11-10 00:06:48', '2025-11-10 00:06:48'),
(5, 'test01', NULL, '2025-11-10 04:13:14', '2025-11-10 04:13:14'),
(6, 'type02', NULL, '2025-11-10 05:44:09', '2025-11-10 05:44:09'),
(7, 'Dimond', 'demo', '2025-11-11 05:02:47', '2025-11-11 05:02:47'),
(8, 'Platinum', 'demo', '2025-11-11 05:07:21', '2025-11-11 05:07:21'),
(9, 'Turbo MAX', 'demo', '2025-11-11 05:18:48', '2025-11-11 05:18:48'),
(10, 'business', 'test', '2025-11-11 06:04:50', '2025-11-11 06:04:50');

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
('DOpqSUDn72DAaftTZXmQVkb4HBsGnozBgMa4xds8', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiR2J1QXJPQUU0NXJOVWRWaG10TElWVkpQem9yb05KNW9FaUtFRnE4NCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2MToiaHR0cDovL2xvY2FsaG9zdC9uYW5vc29mdC1iaWxsaW5nLTEvcHVibGljL2FkbWluL2N1c3RvbWVycy82NiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjc4OiJodHRwOi8vbG9jYWxob3N0L25hbm9zb2Z0LWJpbGxpbmctMS9wdWJsaWMvYWRtaW4vYmlsbGluZy9tb250aGx5LWJpbGxzLzIwMjUtMTEiO3M6NToicm91dGUiO3M6Mjc6ImFkbWluLmJpbGxpbmcubW9udGhseS1iaWxscyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1763922523);

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
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin ', 'admin@netbill.com', NULL, '$2y$12$QLo9cr29855m64bGJ7aQyeha4XXzLGjUk7UKdArCUiih0k3k/0N8.', 'admin', NULL, '2025-01-01 00:40:02', '2024-12-31 18:49:19'),
(2, 'Sumaiya Akter', 'sumaiya@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-05 00:40:02', NULL),
(3, 'Nayeem Hasan', 'nayeem@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-10 00:40:02', NULL),
(4, 'Afsana Rahman', 'afsana@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-15 00:40:02', NULL),
(5, 'Nadia Zaman', 'nadiyazaman@gmail.com', NULL, '$2y$12$UtWg4WGBYipkf95ev43UFOoOjjfwxFEsJosw6vmveZXKZyCiX7wSa', 'customer', NULL, '2025-01-20 00:10:27', '2025-01-20 00:10:27'),
(6, 'Rahim Khan', 'rahim@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-25 00:40:02', NULL),
(7, 'Fatima Begum', 'fatima@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-05 00:40:02', NULL),
(8, 'Kamal Hossain', 'kamal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-12 00:40:02', NULL),
(9, 'Sadia Islam', 'sadia@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-20 00:40:02', NULL),
(10, 'Arif Mahmud', 'arif@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-03-08 00:40:02', NULL),
(11, 'Tania Ahmed', 'tania@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-03-18 00:40:02', NULL),
(12, 'Sohel Rana', 'sohel@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-04-05 00:40:02', NULL),
(13, 'Nusrat Jahan', 'nusrat@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-04-15 00:40:02', NULL),
(14, 'Imran Hossain', 'imran@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-05-10 00:40:02', NULL),
(15, 'Moumita Rahman', 'moumita@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-05-20 00:40:02', NULL),
(16, 'Faisal Ahmed', 'faisal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-06-12 00:40:02', NULL),
(17, 'Sabrina Chowdhury', 'sabrina@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-07-08 00:40:02', NULL),
(18, 'Rashidul Islam', 'rashidul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-08-05 00:40:02', NULL),
(19, 'Anika Tasnim', 'anika@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-08-18 00:40:02', NULL),
(20, 'Shahriar Manzoor', 'shahriar@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-09-10 00:40:02', NULL),
(21, 'Jannatul Ferdous', 'jannatul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-09-22 00:40:02', NULL),
(22, 'Rafiqul Islam', 'rafiqul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-01 00:40:02', NULL),
(23, 'Sharmin Akter', 'sharmin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-02 00:40:02', NULL),
(24, 'Nasir Uddin', 'nasir@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-03 00:40:02', NULL),
(25, 'Mitu Rahman', 'mitu@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-04 00:40:02', NULL),
(26, 'Sajal Hossain', 'sajal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-05 00:40:02', NULL),
(27, 'Poly Begum', 'poly@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-06 00:40:02', NULL),
(28, 'Bappi Sarker', 'bappi@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-07 00:40:02', NULL),
(29, 'Tumpa Akter', 'tumpa@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-08 00:40:02', NULL),
(30, 'Mamun Or Rashid', 'mamun@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-09 00:40:02', NULL),
(31, 'Shirin Sultana', 'shirin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-10 00:40:02', NULL),
(32, 'Rokonuzzaman', 'rokon@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-11 00:40:02', NULL),
(33, 'Farhana Yesmin', 'farhana@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-12 00:40:02', NULL),
(34, 'Alamgir Hossain', 'alamgir@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-13 00:40:02', NULL),
(35, 'Nazma Begum', 'nazma@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-14 00:40:02', NULL),
(36, 'Sazzad Hossain', 'sazzad@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-15 00:40:02', NULL),
(37, 'Morshed Alam', 'morshed@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-16 00:40:02', NULL),
(38, 'Shahinur Rahman', 'shahinur@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-17 00:40:02', NULL),
(39, 'Nargis Akter', 'nargis@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-18 00:40:02', NULL),
(40, 'Babul Miah', 'babul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-19 00:40:02', NULL),
(41, 'Rina Akter', 'rina@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-20 00:40:02', NULL),
(42, 'Shafiqul Islam', 'shafiqul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-21 00:40:02', NULL),
(43, 'Mousumi Rahman', 'mousumi@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-22 00:40:02', NULL),
(44, 'Jamal Uddin', 'jamal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-23 00:40:02', NULL),
(45, 'Ruma Akter', 'ruma@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-24 00:40:02', NULL),
(46, 'Salam Miah', 'salam@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-25 00:40:02', NULL),
(47, 'Nipa Moni', 'nipa@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-26 00:40:02', NULL),
(48, 'Khalid Hasan', 'khalid@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-27 00:40:02', NULL),
(49, 'Mitu Akter', 'mitu2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-28 00:40:02', NULL),
(50, 'Ratan Sarker', 'ratan@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-29 00:40:02', NULL),
(51, 'Shamima Nasrin', 'shamima@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-30 00:40:02', NULL),
(52, 'Bappi Das', 'bappi2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(53, 'Moriom Begum', 'moriom@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(54, 'Raju Ahmed', 'raju@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(55, 'Shila Akter', 'shila@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(56, 'Mizanur Rahman', 'mizan@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(57, 'Naznin Sultanaaaa', 'naznin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', '2025-11-11 06:03:54'),
(58, 'Alauddin Hasan', 'alauddin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', '2025-11-11 04:30:56'),
(59, 'Rokeya Begum', 'rokeya@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(60, 'Sohag Miah', 'sohag@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(61, 'Laboni Akter', 'laboni@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(62, 'Shahidul Islam', 'shahidul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(63, 'Mita Rahman', 'mita@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(64, 'Rony Ahmed', 'rony@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(65, 'Tania Islam', 'tania2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(66, 'Sajib Hasan', 'sajib@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 00:40:02', NULL),
(67, 'Imteaz', 'imteaz@gmail.com', NULL, '$2y$12$MkFG9f3l2UmDvnGDleelweiSYEKHjiOk/REV53NSrjZ13pHynWClC', 'customer', NULL, '2025-11-13 00:53:26', '2025-11-13 00:53:26'),
(68, 'Mim', 'mim@gmail.com', NULL, '$2y$12$yzCfhLTgLcmQuWO0EXTXk.eFBieO0KGbJXeHv5tm4Rz6bWygQZF6.', 'customer', NULL, '2025-11-14 08:49:21', '2025-11-14 08:49:21'),
(69, 'Emon', 'emon@gmail.com', NULL, '$2y$12$OB4fHw7vfVBCBirQDQcuOejK0krTvZpMKPEE1xtqQDM13r/fZI6hy', 'customer', NULL, '2025-11-14 09:20:46', '2025-11-14 09:20:46'),
(70, 'Emon', 'emon1@gmail.com', NULL, '$2y$12$QUjexNJXJvR1fCirNYu6z.8cqYdYasDUHmMVkPvvwvjjXbsVut11m', 'customer', NULL, '2025-11-14 09:21:34', '2025-11-14 09:21:34'),
(71, 'ishaq', 'ishaq@gmail.com', NULL, '$2y$12$/DTS4hCOhE/val.J7.u/Pe/BbnUvU1gKLSwN9cNgVJxPF31UzNRbu', 'customer', NULL, '2025-11-14 09:34:04', '2025-11-14 09:34:04'),
(72, 'Ratul', 'ratul@gmail.com', NULL, '$2y$12$vNXrx4bJoq.gF/PtgORzDOQMYAmzqmNgl.QTULRZPUARbnzj14O46', 'customer', NULL, '2025-11-14 09:35:45', '2025-11-14 09:35:45'),
(73, 'Ashraful', 'ashraful@gmail.com', NULL, '$2y$12$qx30GRv4KGK.g6wbxUB0feNGULm.sZvcGnC.7/Q7aJx2mSO214Jpi', 'customer', NULL, '2025-11-14 09:47:42', '2025-11-14 09:47:42'),
(74, 'Zia', 'zia@gmail.com', NULL, '$2y$12$fGCENnUugDUBPhW9dLDMFunsPKwlNNAOpCncpLfMiK/ka8TDwj1uq', 'customer', NULL, '2025-11-14 09:55:02', '2025-11-14 09:55:02'),
(75, 'Joni Khan', 'jonikhan@gmail.com', NULL, '$2y$12$NyDm9Fe/5iXr0ueLpBfZk.Dwx8fVZJ2ECKyaUdNP.J1fzg65pxlBK', 'customer', NULL, '2025-11-16 01:39:23', '2025-11-23 04:27:56'),
(76, 'Araf Khan', 'arafkhan@gmail.com', NULL, '$2y$12$FcT2GzWifMRzSia3O8hX3Ox6GVN52LgjMUsPeePykHI.zIo4IJFrG', 'customer', NULL, '2025-11-16 02:15:33', '2025-11-16 02:15:33'),
(77, 'Shafi Islam', 'shafi@gmail.com', NULL, '$2y$12$ajlZdgjg5qbRMqbCqFIY8.b/2QdxW74lDget5o1OTwlszJGid49Uq', 'customer', NULL, '2025-11-16 03:56:13', '2025-11-16 03:56:13'),
(78, 'Arif Khan', 'arif@gmail.com', NULL, '$2y$12$.20CahxZlzElM94noaMggu/UjQxqZBeaN9wEpn8xbXSKIvFigkuzm', 'customer', NULL, '2025-11-20 00:44:55', '2025-11-20 00:44:55');

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
  ADD CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
