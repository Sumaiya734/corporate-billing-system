-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2025 at 05:50 PM
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
  `action` varchar(100) DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `c_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `customer_id` varchar(60) DEFAULT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text,
  `connection_address` text,
  `id_type` varchar(60) DEFAULT NULL,
  `id_number` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`c_id`),
  UNIQUE KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_customers_phone` (`phone`),
  KEY `idx_customers_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`c_id`, `user_id`, `customer_id`, `name`, `email`, `phone`, `address`, `connection_address`, `id_type`, `id_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'CUST1001', 'Sumaiya Akter', 'sumaiya@example.com', '01730844732', 'Gazipur, Dhaka', 'House 12, Gazipur', 'NID', '123456789', 1, '2025-01-05 05:50:20', '2025-01-05 10:35:13'),
(2, 3, 'CUST1002', 'Nayeem Hasan', 'nayeem@example.com', '01711112222', 'Mirpur, Dhaka', 'House 44, Mirpur', 'NID', '987654321', 1, '2025-01-10 05:50:20', '2025-01-10 10:35:13'),
(3, 4, 'CUST1003', 'Afsana Rahman', 'afsana@example.com', '01888883333', 'Uttara, Dhaka', 'Road 10, Uttara', 'NID', '555888333', 1, '2025-01-15 05:50:20', '2025-01-15 10:35:13'),
(4, 5, 'CUST1004', 'Nadia Zaman', 'nadiyazaman@gmail.com', '01723456789', 'Gazipur', 'Joydebpur', 'nid', '111222333', 1, '2025-01-20 06:10:27', '2025-01-20 06:12:47'),
(5, 6, 'CUST1005', 'Rahim Khan', 'rahim@example.com', '01712345678', 'Dhanmondi, Dhaka', 'Road 8, Dhanmondi', 'NID', '444555666', 1, '2025-01-25 06:10:27', '2025-01-25 06:12:47'),
(6, 7, 'CUST1006', 'Fatima Begum', 'fatima@example.com', '01812345678', 'Mohakhali, Dhaka', 'House 25, Mohakhali', 'NID', '777888999', 1, '2025-02-05 06:10:27', '2025-02-05 06:12:47'),
(7, 8, 'CUST1007', 'Kamal Hossain', 'kamal@example.com', '01912345678', 'Banani, Dhaka', 'Road 11, Banani', 'NID', '111333555', 1, '2025-02-12 06:10:27', '2025-02-12 06:12:47'),
(8, 9, 'CUST1008', 'Sadia Islam', 'sadia@example.com', '01798765432', 'Gulshan, Dhaka', 'House 45, Gulshan', 'NID', '222444666', 1, '2025-02-20 06:10:27', '2025-02-20 06:12:47'),
(9, 10, 'CUST1009', 'Arif Mahmud', 'arif@example.com', '01898765432', 'Baridhara, Dhaka', 'Road 6, Baridhara', 'NID', '333555777', 1, '2025-03-08 06:10:27', '2025-03-08 06:12:47'),
(10, 11, 'CUST1010', 'Tania Ahmed', 'tania@example.com', '01998765432', 'Bashundhara, Dhaka', 'House 33, Bashundhara', 'NID', '444666888', 1, '2025-03-18 06:10:27', '2025-03-18 06:12:47'),
(11, 12, 'CUST1011', 'Sohel Rana', 'sohel@example.com', '01755556666', 'Mirpur DOHS, Dhaka', 'Road 4, Mirpur DOHS', 'NID', '555777999', 1, '2025-04-05 06:10:27', '2025-04-05 06:12:47'),
(12, 13, 'CUST1012', 'Nusrat Jahan', 'nusrat@example.com', '01855556666', 'Uttara Sector 4', 'House 78, Uttara', 'NID', '666888000', 1, '2025-04-15 06:10:27', '2025-04-15 06:12:47'),
(13, 14, 'CUST1013', 'Imran Hossain', 'imran@example.com', '01955556666', 'Mohammadpur, Dhaka', 'Road 12, Mohammadpur', 'NID', '777999111', 1, '2025-05-10 06:10:27', '2025-05-10 06:12:47'),
(14, 15, 'CUST1014', 'Moumita Rahman', 'moumita@example.com', '01766667777', 'Shyamoli, Dhaka', 'House 56, Shyamoli', 'NID', '888000222', 1, '2025-05-20 06:10:27', '2025-05-20 06:12:47'),
(15, 16, 'CUST1015', 'Faisal Ahmed', 'faisal@example.com', '01866667777', 'Malibagh, Dhaka', 'Road 9, Malibagh', 'NID', '999111333', 1, '2025-06-12 06:10:27', '2025-06-12 06:12:47'),
(16, 17, 'CUST1016', 'Sabrina Chowdhury', 'sabrina@example.com', '01966667777', 'Rampura, Dhaka', 'House 67, Rampura', 'NID', '000222444', 1, '2025-07-08 06:10:27', '2025-07-08 06:12:47'),
(17, 18, 'CUST1017', 'Rashidul Islam', 'rashidul@example.com', '01777778888', 'Badda, Dhaka', 'Road 7, Badda', 'NID', '111333555', 1, '2025-08-05 06:10:27', '2025-08-05 06:12:47'),
(18, 19, 'CUST1018', 'Anika Tasnim', 'anika@example.com', '01877778888', 'Khilgaon, Dhaka', 'House 89, Khilgaon', 'NID', '222444666', 1, '2025-08-18 06:10:27', '2025-08-18 06:12:47'),
(19, 20, 'CUST1019', 'Shahriar Manzoor', 'shahriar@example.com', '01977778888', 'Demra, Dhaka', 'Road 5, Demra', 'NID', '333555777', 1, '2025-09-10 06:10:27', '2025-09-10 06:12:47'),
(20, 21, 'CUST1020', 'Jannatul Ferdous', 'jannatul@example.com', '01788889999', 'Jatrabari, Dhaka', 'House 34, Jatrabari', 'NID', '444666888', 1, '2025-09-22 06:10:27', '2025-09-22 06:12:47'),
(21, 22, 'CUST1021', 'Rafiqul Islam', 'rafiqul@example.com', '01799990000', 'Mirpur 10', 'House 23, Mirpur', 'NID', '555777111', 1, '2025-10-01 06:10:27', '2025-10-01 06:12:47'),
(22, 23, 'CUST1022', 'Sharmin Akter', 'sharmin@example.com', '01899990000', 'Uttara 11', 'Road 7, Uttara', 'NID', '666888222', 1, '2025-10-02 06:10:27', '2025-10-02 06:12:47'),
(23, 24, 'CUST1023', 'Nasir Uddin', 'nasir@example.com', '01999990000', 'Dhanmondi 15', 'House 45, Dhanmondi', 'NID', '777999333', 1, '2025-10-03 06:10:27', '2025-10-03 06:12:47'),
(24, 25, 'CUST1024', 'Mitu Rahman', 'mitu@example.com', '01788881111', 'Gulshan 2', 'Road 9, Gulshan', 'NID', '888000444', 1, '2025-10-04 06:10:27', '2025-10-04 06:12:47'),
(25, 26, 'CUST1025', 'Sajal Hossain', 'sajal@example.com', '01888881111', 'Banani 11', 'House 67, Banani', 'NID', '999111555', 1, '2025-10-05 06:10:27', '2025-10-05 06:12:47'),
(26, 27, 'CUST1026', 'Poly Begum', 'poly@example.com', '01988881111', 'Mohakhali DOHS', 'Road 3, Mohakhali', 'NID', '000222666', 1, '2025-10-06 06:10:27', '2025-10-06 06:12:47'),
(27, 28, 'CUST1027', 'Bappi Sarker', 'bappi@example.com', '01777772222', 'Baridhara DOHS', 'House 89, Baridhara', 'NID', '111333777', 1, '2025-10-07 06:10:27', '2025-10-07 06:12:47'),
(28, 29, 'CUST1028', 'Tumpa Akter', 'tumpa@example.com', '01877772222', 'Bashundhara R/A', 'Road 12, Bashundhara', 'NID', '222444888', 1, '2025-10-08 06:10:27', '2025-10-08 06:12:47'),
(29, 30, 'CUST1029', 'Mamun Or Rashid', 'mamun@example.com', '01977772222', 'Mirpur 13', 'House 34, Mirpur', 'NID', '333555999', 1, '2025-10-09 06:10:27', '2025-10-09 06:12:47'),
(30, 31, 'CUST1030', 'Shirin Sultana', 'shirin@example.com', '01766663333', 'Uttara 8', 'Road 5, Uttara', 'NID', '444666000', 1, '2025-10-10 06:10:27', '2025-10-10 06:12:47'),
(31, 32, 'CUST1031', 'Rokonuzzaman', 'rokon@example.com', '01866663333', 'Mohammadpur', 'House 56, Mohammadpur', 'NID', '555777111', 1, '2025-10-11 06:10:27', '2025-10-11 06:12:47'),
(32, 33, 'CUST1032', 'Farhana Yesmin', 'farhana@example.com', '01966663333', 'Shyamoli', 'Road 8, Shyamoli', 'NID', '666888222', 1, '2025-10-12 06:10:27', '2025-10-12 06:12:47'),
(33, 34, 'CUST1033', 'Alamgir Hossain', 'alamgir@example.com', '01755554444', 'Malibagh', 'House 67, Malibagh', 'NID', '777999333', 1, '2025-10-13 06:10:27', '2025-10-13 06:12:47'),
(34, 35, 'CUST1034', 'Nazma Begum', 'nazma@example.com', '01855554444', 'Rampura', 'Road 10, Rampura', 'NID', '888000444', 1, '2025-10-14 06:10:27', '2025-10-14 06:12:47'),
(35, 36, 'CUST1035', 'Sazzad Hossain', 'sazzad@example.com', '01955554444', 'Badda', 'House 78, Badda', 'NID', '999111555', 1, '2025-10-15 06:10:27', '2025-10-15 06:12:47'),
(36, 37, 'CUST1036', 'Morshed Alam', 'morshed@example.com', '01744445555', 'Khilgaon', 'Road 12, Khilgaon', 'NID', '000222666', 1, '2025-10-16 06:10:27', '2025-10-16 06:12:47'),
(37, 38, 'CUST1037', 'Shahinur Rahman', 'shahinur@example.com', '01844445555', 'Demra', 'House 89, Demra', 'NID', '111333777', 1, '2025-10-17 06:10:27', '2025-10-17 06:12:47'),
(38, 39, 'CUST1038', 'Nargis Akter', 'nargis@example.com', '01944445555', 'Jatrabari', 'Road 15, Jatrabari', 'NID', '222444888', 1, '2025-10-18 06:10:27', '2025-10-18 06:12:47'),
(39, 40, 'CUST1039', 'Babul Miah', 'babul@example.com', '01733336666', 'Mirpur 1', 'House 34, Mirpur', 'NID', '333555999', 1, '2025-10-19 06:10:27', '2025-10-19 06:12:47'),
(40, 41, 'CUST1040', 'Rina Akter', 'rina@example.com', '01833336666', 'Uttara 3', 'Road 6, Uttara', 'NID', '444666000', 1, '2025-10-20 06:10:27', '2025-10-20 06:12:47'),
(41, 42, 'CUST1041', 'Shafiqul Islam', 'shafiqul@example.com', '01933336666', 'Dhanmondi 27', 'House 45, Dhanmondi', 'NID', '555777111', 1, '2025-10-21 06:10:27', '2025-10-21 06:12:47'),
(42, 43, 'CUST1042', 'Mousumi Rahman', 'mousumi@example.com', '01722227777', 'Gulshan 1', 'Road 8, Gulshan', 'NID', '666888222', 1, '2025-10-22 06:10:27', '2025-10-22 06:12:47'),
(43, 44, 'CUST1043', 'Jamal Uddin', 'jamal@example.com', '01822227777', 'Banani 12', 'House 56, Banani', 'NID', '777999333', 1, '2025-10-23 06:10:27', '2025-10-23 06:12:47'),
(44, 45, 'CUST1044', 'Ruma Akter', 'ruma@example.com', '01922227777', 'Mohakhali', 'Road 11, Mohakhali', 'NID', '888000444', 1, '2025-10-24 06:10:27', '2025-10-24 06:12:47'),
(45, 46, 'CUST1045', 'Salam Miah', 'salam@example.com', '01711118888', 'Baridhara', 'House 67, Baridhara', 'NID', '999111555', 1, '2025-10-25 06:10:27', '2025-10-25 06:12:47'),
(46, 47, 'CUST1046', 'Nipa Moni', 'nipa@example.com', '01811118888', 'Bashundhara', 'Road 14, Bashundhara', 'NID', '000222666', 1, '2025-10-26 06:10:27', '2025-10-26 06:12:47'),
(47, 48, 'CUST1047', 'Khalid Hasan', 'khalid@example.com', '01911118888', 'Mirpur 6', 'House 78, Mirpur', 'NID', '111333777', 1, '2025-10-27 06:10:27', '2025-10-27 06:12:47'),
(48, 49, 'CUST1048', 'Mitu Akter', 'mitu2@example.com', '01700009999', 'Uttara 7', 'Road 9, Uttara', 'NID', '222444888', 1, '2025-10-28 06:10:27', '2025-10-28 06:12:47'),
(49, 50, 'CUST1049', 'Ratan Sarker', 'ratan@example.com', '01800009999', 'Dhanmondi 32', 'House 89, Dhanmondi', 'NID', '333555999', 1, '2025-10-29 06:10:27', '2025-10-29 06:12:47'),
(50, 51, 'CUST1050', 'Shamima Nasrin', 'shamima@example.com', '01900009999', 'Gulshan 2', 'Road 16, Gulshan', 'NID', '444666000', 1, '2025-10-30 06:10:27', '2025-10-30 06:12:47'),
(51, 52, 'CUST1051', 'Bappi Das', 'bappi2@example.com', '01799998888', 'Banani 11', 'House 34, Banani', 'NID', '555777111', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(52, 53, 'CUST1052', 'Moriom Begum', 'moriom@example.com', '01899998888', 'Mohakhali DOHS', 'Road 5, Mohakhali', 'NID', '666888222', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(53, 54, 'CUST1053', 'Raju Ahmed', 'raju@example.com', '01999998888', 'Baridhara DOHS', 'House 45, Baridhara', 'NID', '777999333', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(54, 55, 'CUST1054', 'Shila Akter', 'shila@example.com', '01788887777', 'Bashundhara R/A', 'Road 8, Bashundhara', 'NID', '888000444', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(55, 56, 'CUST1055', 'Mizanur Rahman', 'mizan@example.com', '01888887777', 'Mirpur 10', 'House 56, Mirpur', 'NID', '999111555', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(56, 57, 'CUST1056', 'Naznin Sultana', 'naznin@example.com', '01988887777', 'Uttara 4', 'Road 7, Uttara', 'NID', '000222666', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(57, 58, 'CUST1057', 'Alauddin', 'alauddin@example.com', '01777776666', 'Dhanmondi 15', 'House 67, Dhanmondi', 'NID', '111333777', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(58, 59, 'CUST1058', 'Rokeya Begum', 'rokeya@example.com', '01877776666', 'Gulshan 1', 'Road 12, Gulshan', 'NID', '222444888', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(59, 60, 'CUST1059', 'Sohag Miah', 'sohag@example.com', '01977776666', 'Banani 12', 'House 78, Banani', 'NID', '333555999', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(60, 61, 'CUST1060', 'Laboni Akter', 'laboni@example.com', '01766665555', 'Mohakhali', 'Road 9, Mohakhali', 'NID', '444666000', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(61, 62, 'CUST1061', 'Shahidul Islam', 'shahidul@example.com', '01866665555', 'Baridhara', 'House 89, Baridhara', 'NID', '555777111', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(62, 63, 'CUST1062', 'Mita Rahman', 'mita@example.com', '01966665555', 'Bashundhara', 'Road 14, Bashundhara', 'NID', '666888222', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(63, 64, 'CUST1063', 'Rony Ahmed', 'rony@example.com', '01755554444', 'Mirpur 13', 'House 34, Mirpur', 'NID', '777999333', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(64, 65, 'CUST1064', 'Tania Islam', 'tania2@example.com', '01855554444', 'Uttara 8', 'Road 6, Uttara', 'NID', '888000444', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47'),
(65, 66, 'CUST1065', 'Sajib Hasan', 'sajib@example.com', '01955554444', 'Dhanmondi 27', 'House 45, Dhanmondi', 'NID', '999111555', 1, '2025-10-31 06:10:27', '2025-10-31 06:12:47');

--
-- Triggers `customers`
--
DROP TRIGGER IF EXISTS `update_customers_timestamp`;
DELIMITER $$
CREATE TRIGGER `update_customers_timestamp` BEFORE UPDATE ON `customers` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_to_packages`
--

DROP TABLE IF EXISTS `customer_to_packages`;
CREATE TABLE IF NOT EXISTS `customer_to_packages` (
  `cp_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `c_id` int UNSIGNED NOT NULL,
  `p_id` int UNSIGNED NOT NULL,
  `assign_date` date NOT NULL,
  `billing_cycle_months` int NOT NULL DEFAULT '1',
  `due_date` date GENERATED ALWAYS AS ((`assign_date` + interval `billing_cycle_months` month)) STORED,
  `status` enum('active','pending','expired') DEFAULT 'active',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cp_id`),
  KEY `fk_customer_packages_customer` (`c_id`),
  KEY `fk_customer_packages_package` (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customer_to_packages`
--

INSERT INTO `customer_to_packages` (`cp_id`, `c_id`, `p_id`, `assign_date`, `billing_cycle_months`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-01-05', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(2, 1, 5, '2025-01-05', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(3, 2, 2, '2025-01-10', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(4, 2, 4, '2025-01-10', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(5, 2, 6, '2025-01-10', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(6, 3, 3, '2025-01-15', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(7, 4, 1, '2025-01-20', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(8, 4, 5, '2025-01-20', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(9, 5, 2, '2025-01-25', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(10, 5, 4, '2025-01-25', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(11, 5, 5, '2025-01-25', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(12, 6, 1, '2025-02-05', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(13, 7, 3, '2025-02-12', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(14, 7, 5, '2025-02-12', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(15, 8, 2, '2025-02-20', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(16, 8, 4, '2025-02-20', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(17, 8, 6, '2025-02-20', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(18, 9, 1, '2025-03-08', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(19, 10, 3, '2025-03-18', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(20, 10, 6, '2025-03-18', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(21, 11, 1, '2025-04-05', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(22, 11, 4, '2025-04-05', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(23, 11, 5, '2025-04-05', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(24, 12, 2, '2025-04-15', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(25, 13, 3, '2025-05-10', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(26, 13, 4, '2025-05-10', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(27, 14, 1, '2025-05-20', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(28, 14, 5, '2025-05-20', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(29, 14, 6, '2025-05-20', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(30, 15, 2, '2025-06-12', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(31, 16, 3, '2025-07-08', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(32, 16, 5, '2025-07-08', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(33, 17, 1, '2025-08-05', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(34, 17, 4, '2025-08-05', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(35, 17, 6, '2025-08-05', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(36, 18, 2, '2025-08-18', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(37, 19, 3, '2025-09-10', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(38, 19, 4, '2025-09-10', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(39, 20, 1, '2025-09-22', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(40, 20, 5, '2025-09-22', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(41, 20, 6, '2025-09-22', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(42, 21, 2, '2025-10-01', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(43, 22, 1, '2025-10-02', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(44, 22, 4, '2025-10-02', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(45, 23, 3, '2025-10-03', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(46, 23, 5, '2025-10-03', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(47, 24, 1, '2025-10-04', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(48, 24, 4, '2025-10-04', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(49, 24, 6, '2025-10-04', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(50, 25, 2, '2025-10-05', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(51, 26, 3, '2025-10-06', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(52, 26, 5, '2025-10-06', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(53, 27, 1, '2025-10-07', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(54, 27, 4, '2025-10-07', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(55, 28, 2, '2025-10-08', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(56, 28, 6, '2025-10-08', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(57, 29, 3, '2025-10-09', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(58, 30, 1, '2025-10-10', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(59, 30, 5, '2025-10-10', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(60, 31, 2, '2025-10-11', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(61, 32, 3, '2025-10-12', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(62, 32, 4, '2025-10-12', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(64, 33, 5, '2025-10-13', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(65, 33, 6, '2025-10-13', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(66, 34, 2, '2025-10-14', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(67, 35, 3, '2025-10-15', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(68, 35, 4, '2025-10-15', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(69, 36, 1, '2025-10-16', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(70, 37, 2, '2025-10-17', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(71, 37, 6, '2025-10-17', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(72, 38, 3, '2025-10-18', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(73, 39, 1, '2025-10-19', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(74, 39, 5, '2025-10-19', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(75, 40, 2, '2025-10-20', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(76, 40, 4, '2025-10-20', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(77, 41, 3, '2025-10-21', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(80, 43, 2, '2025-10-23', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(82, 44, 3, '2025-10-24', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(83, 45, 1, '2025-10-25', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(85, 46, 2, '2025-10-26', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(86, 47, 3, '2025-10-27', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(87, 47, 4, '2025-10-27', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(88, 48, 1, '2025-10-28', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(89, 48, 6, '2025-10-28', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(90, 49, 2, '2025-10-29', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(91, 50, 3, '2025-10-30', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(92, 50, 5, '2025-10-30', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(93, 51, 1, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(94, 51, 4, '2025-10-31', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(95, 52, 2, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(96, 53, 3, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(97, 53, 6, '2025-10-31', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(98, 54, 1, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(99, 55, 2, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(101, 56, 3, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(102, 57, 1, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(103, 57, 5, '2025-10-31', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(104, 58, 2, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(105, 59, 3, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(106, 59, 4, '2025-10-31', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(107, 60, 1, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(108, 61, 2, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(109, 61, 6, '2025-10-31', 12, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(110, 62, 3, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(111, 63, 1, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(112, 63, 5, '2025-10-31', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(113, 64, 2, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(114, 64, 4, '2025-10-31', 3, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(115, 65, 3, '2025-10-31', 1, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(116, 65, 6, '2025-10-31', 6, 'active', 1, '2025-11-03 17:52:05', '2025-11-03 17:52:05'),
(117, 3, 4, '2025-11-06', 3, 'active', 1, '2025-11-06 04:35:01', '2025-11-06 04:35:01');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `c_id` int UNSIGNED NOT NULL,
  `issue_date` date NOT NULL,
  `previous_due` decimal(12,2) DEFAULT '0.00',
  `service_charge` decimal(12,2) DEFAULT '50.00',
  `vat_percentage` decimal(5,2) DEFAULT '5.00',
  `vat_amount` decimal(12,2) DEFAULT '0.00',
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `received_amount` decimal(12,2) DEFAULT '0.00',
  `next_due` decimal(12,2) DEFAULT '0.00',
  `status` enum('unpaid','paid','partial','cancelled') DEFAULT 'unpaid',
  `notes` text,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `created_by` (`created_by`),
  KEY `idx_invoices_customer` (`c_id`),
  KEY `idx_invoices_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `c_id`, `issue_date`, `previous_due`, `service_charge`, `vat_percentage`, `vat_amount`, `subtotal`, `total_amount`, `received_amount`, `next_due`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-2025-0001', 1, '2025-02-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'Monthly billing cycle - Package assigned Jan 5', 1, '2025-02-05 04:00:00', '2025-02-10 08:30:00'),
(2, 'INV-2025-0002', 2, '2025-02-10', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'Monthly billing cycle - Package assigned Jan 10', 1, '2025-02-10 04:00:00', '2025-02-15 05:20:00'),
(3, 'INV-2025-0003', 3, '2025-02-15', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 500.00, 602.50, 'partial', 'Monthly billing cycle - Package assigned Jan 15', 1, '2025-02-15 04:00:00', '2025-02-25 03:45:00'),
(4, 'INV-2025-0004', 4, '2025-02-20', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'Monthly billing cycle - Package assigned Jan 20', 1, '2025-02-20 04:00:00', '2025-02-20 04:00:00'),
(5, 'INV-2025-0005', 1, '2025-03-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', '2nd monthly cycle', 1, '2025-03-05 03:00:00', '2025-03-10 08:30:00'),
(6, 'INV-2025-0006', 2, '2025-03-10', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', '2nd monthly cycle', 1, '2025-03-10 03:00:00', '2025-03-15 05:20:00'),
(7, 'INV-2025-0007', 3, '2025-03-15', 602.50, 50.00, 5.00, 52.50, 1050.00, 1704.50, 1704.50, 0.00, 'paid', '2nd monthly cycle - cleared previous due', 1, '2025-03-15 03:00:00', '2025-03-25 10:20:00'),
(8, 'INV-2025-0008', 4, '2025-03-20', 1102.50, 50.00, 5.00, 52.50, 1050.00, 2205.00, 0.00, 2205.00, 'unpaid', '2nd monthly cycle - overdue from Feb', 1, '2025-03-20 03:00:00', '2025-03-20 03:00:00'),
(9, 'INV-2025-0009', 1, '2025-04-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', '3rd monthly cycle', 1, '2025-04-05 03:00:00', '2025-04-10 08:30:00'),
(10, 'INV-2025-0010', 2, '2025-04-10', 0.00, 50.00, 5.00, 157.50, 1800.00, 1897.50, 1897.50, 0.00, 'paid', '3-month billing cycle completed', 1, '2025-04-10 03:00:00', '2025-04-15 05:20:00'),
(11, 'INV-2025-0011', 5, '2025-02-25', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-02-25 03:00:00', '2025-03-01 08:30:00'),
(12, 'INV-2025-0012', 1, '2025-07-05', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 3307.50, 0.00, 'paid', '6-month billing cycle completed', 1, '2025-07-05 03:00:00', '2025-07-10 08:30:00'),
(13, 'INV-2025-0013', 2, '2025-07-10', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 0.00, 3307.50, 'unpaid', '6-month billing cycle - pending payment', 1, '2025-07-10 03:00:00', '2025-07-10 03:00:00'),
(14, 'INV-2025-0014', 6, '2025-03-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-03-05 03:00:00', '2025-03-10 05:20:00'),
(15, 'INV-2025-0015', 7, '2025-03-12', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-03-12 03:00:00', '2025-03-17 08:30:00'),
(16, 'INV-2025-0016', 8, '2025-03-20', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'First monthly bill - overdue', 1, '2025-03-20 03:00:00', '2025-03-20 03:00:00'),
(17, 'INV-2025-0017', 9, '2025-04-08', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-04-08 03:00:00', '2025-04-13 05:20:00'),
(18, 'INV-2025-0018', 10, '2025-09-18', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 0.00, 3307.50, 'unpaid', '6-month cycle bill - pending', 1, '2025-09-18 03:00:00', '2025-09-18 03:00:00'),
(19, 'INV-2025-0019', 11, '2025-05-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-05-05 03:00:00', '2025-05-10 08:30:00'),
(20, 'INV-2025-0020', 12, '2025-05-15', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 500.00, 602.50, 'partial', 'First monthly bill - partial payment', 1, '2025-05-15 03:00:00', '2025-05-25 03:45:00'),
(21, 'INV-2025-0021', 13, '2025-06-10', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-06-10 03:00:00', '2025-06-15 05:20:00'),
(22, 'INV-2025-0022', 14, '2025-11-20', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 0.00, 3307.50, 'unpaid', '6-month cycle bill - future', 1, '2025-11-20 03:00:00', '2025-11-20 03:00:00'),
(23, 'INV-2025-0023', 15, '2025-07-12', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-07-12 03:00:00', '2025-07-17 08:30:00'),
(24, 'INV-2025-0024', 16, '2026-07-08', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 0.00, 3307.50, 'unpaid', '12-month cycle bill - future', 1, '2026-07-08 03:00:00', '2026-07-08 03:00:00'),
(25, 'INV-2025-0025', 17, '2025-09-05', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-09-05 03:00:00', '2025-09-10 05:20:00'),
(26, 'INV-2025-0026', 4, '2025-07-20', 2205.00, 50.00, 5.00, 52.50, 1050.00, 3307.50, 3307.50, 0.00, 'paid', 'Cleared all overdue amounts', 1, '2025-07-20 03:00:00', '2025-07-30 10:45:00'),
(27, 'INV-2025-0027', 18, '2025-09-18', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 1102.50, 0.00, 'paid', 'First monthly bill', 1, '2025-09-18 03:00:00', '2025-09-23 08:30:00'),
(28, 'INV-2025-0028', 19, '2025-10-10', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'First monthly bill - new customer', 1, '2025-10-10 03:00:00', '2025-10-10 03:00:00'),
(29, 'INV-2025-0029', 20, '2026-03-22', 0.00, 50.00, 5.00, 157.50, 3150.00, 3307.50, 0.00, 3307.50, 'unpaid', '6-month cycle bill - future', 1, '2026-03-22 03:00:00', '2026-03-22 03:00:00'),
(30, 'INV-2025-0030', 8, '2025-08-20', 1102.50, 50.00, 5.00, 52.50, 1050.00, 2205.00, 2205.00, 0.00, 'paid', 'Cleared overdue amount', 1, '2025-08-20 03:00:00', '2025-08-28 09:20:00'),
(31, 'INV-2025-0031', 21, '2025-11-01', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'First monthly bill - new Oct customer', 1, '2025-11-01 03:00:00', '2025-11-01 03:00:00'),
(32, 'INV-2025-0032', 22, '2025-11-02', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'First monthly bill - new Oct customer', 1, '2025-11-02 03:00:00', '2025-11-02 03:00:00'),
(33, 'INV-2025-0033', 23, '2025-11-03', 0.00, 50.00, 5.00, 52.50, 1050.00, 1102.50, 0.00, 1102.50, 'unpaid', 'First monthly bill - new Oct customer', 1, '2025-11-03 03:00:00', '2025-11-03 03:00:00'),
(34, 'INV-2025-0034', 2, '2025-09-10', 3307.50, 50.00, 5.00, 52.50, 1050.00, 4410.00, 4410.00, 0.00, 'paid', 'Cleared 6-month cycle overdue', 1, '2025-09-10 03:00:00', '2025-09-25 08:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(8, '2014_10_12_100000_create_password_resets_table', 2),
(9, '2025_10_29_051029_create_monthly_billing_summaries_table', 3),
(10, '2025_10_30_043000_create_customer_packages_table', 4),
(11, '2025_10_30_050000_create_monthly_billing_view', 5),
(12, '2025_11_06_101329_create_audit_logs_table', 0),
(13, '2025_11_06_101329_create_customer_to_packages_table', 0),
(14, '2025_11_06_101329_create_customers_table', 0),
(15, '2025_11_06_101329_create_failed_jobs_table', 0),
(16, '2025_11_06_101329_create_invoices_table', 0),
(17, '2025_11_06_101329_create_notifications_table', 0),
(18, '2025_11_06_101329_create_packages_table', 0),
(19, '2025_11_06_101329_create_password_resets_table', 0),
(20, '2025_11_06_101329_create_payments_table', 0),
(21, '2025_11_06_101329_create_personal_access_tokens_table', 0),
(22, '2025_11_06_101329_create_settings_table', 0),
(23, '2025_11_06_101329_create_subscriptions_table', 0),
(24, '2025_11_06_101329_create_system_settings_table', 0),
(25, '2025_11_06_101329_create_users_table', 0),
(26, '2025_11_06_101330_create_monthly_billing_summaries_dynamic_view', 0),
(27, '2025_11_06_101330_create_monthly_revenue_summary_view', 0),
(28, '2025_11_06_101332_add_foreign_keys_to_customer_to_packages_table', 0),
(29, '2025_11_06_101332_add_foreign_keys_to_customers_table', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_billing_summaries_dynamic`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `monthly_billing_summaries_dynamic`;
CREATE TABLE IF NOT EXISTS `monthly_billing_summaries_dynamic` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_revenue_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `monthly_revenue_summary`;
CREATE TABLE IF NOT EXISTS `monthly_revenue_summary` (
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

DROP TABLE IF EXISTS `packages`;
CREATE TABLE IF NOT EXISTS `packages` (
  `p_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `package_type` enum('regular','special') NOT NULL DEFAULT 'regular',
  `description` text,
  `monthly_price` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`p_id`, `name`, `package_type`, `description`, `monthly_price`, `created_at`, `updated_at`) VALUES
(1, 'Basic Plan', 'regular', 'Home broadband 10 Mbps connection', 700.00, '2025-01-01 05:50:20', NULL),
(2, 'Standard Plan', 'regular', 'Home broadband 25 Mbps connection', 1000.00, '2025-01-01 05:50:20', NULL),
(3, 'Premium Plan', 'regular', 'Home broadband 50 Mbps connection', 1500.00, '2025-01-01 05:50:20', NULL),
(4, 'Gaming Boost', 'special', 'Enhanced gaming experience with optimized connectivity', 200.00, '2025-01-01 07:01:22', '2025-01-01 07:01:22'),
(5, 'Streaming Plus', 'special', 'Improved streaming quality and bandwidth for video content', 150.00, '2025-01-01 07:01:22', '2025-01-01 07:01:22'),
(6, 'Family Pack', 'special', 'Additional connections and shared benefits for family members', 300.00, '2025-01-01 07:01:22', '2025-01-01 07:01:22');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int UNSIGNED NOT NULL,
  `c_id` int UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','mobile_banking','card','online') NOT NULL,
  `payment_date` date NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_invoice` (`invoice_id`),
  KEY `idx_payments_customer` (`c_id`),
  KEY `idx_payments_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `tokenable_id` (`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'NetBill BD ISP Solutions', '2025-01-01 05:50:20', NULL),
(2, 'company_address', 'Gazipur, Dhaka, Bangladesh', '2025-01-01 05:50:20', NULL),
(3, 'company_phone', '+8801730844732', '2025-01-01 05:50:20', NULL),
(4, 'company_email', 'support@netbill.com', '2025-01-01 05:50:20', NULL),
(5, 'invoice_prefix', 'INV-2025-', '2025-01-01 05:50:20', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'fixed_monthly_charge', '50', 'Fixed monthly charge for all customers', '2025-01-01 03:52:06', '2025-01-01 03:52:06'),
(2, 'vat_percentage', '7', 'VAT percentage applied to invoices', '2025-01-01 03:52:06', '2025-01-01 03:52:06'),
(3, 'company_name', 'NetBill BD', 'Company name for invoices', '2025-01-01 03:52:06', '2025-01-01 03:52:06'),
(4, 'company_address', 'Dhaka, Bangladesh', 'Company address for invoices', '2025-01-01 03:52:06', '2025-01-01 03:52:06'),
(5, 'invoice_prefix', 'INV-2025-', 'Invoice number prefix', '2025-01-01 03:52:06', '2025-01-01 03:52:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `password` varchar(191) NOT NULL,
  `role` varchar(50) DEFAULT 'customer',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@netbill.com', NULL, '$2y$12$QLo9cr29855m64bGJ7aQyeha4XXzLGjUk7UKdArCUiih0k3k/0N8.', 'admin', NULL, '2025-01-01 06:40:02', '2025-01-01 00:49:19'),
(2, 'Sumaiya Akter', 'sumaiya@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-05 06:40:02', NULL),
(3, 'Nayeem Hasan', 'nayeem@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-10 06:40:02', NULL),
(4, 'Afsana Rahman', 'afsana@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-15 06:40:02', NULL),
(5, 'Nadia Zaman', 'nadiyazaman@gmail.com', NULL, '$2y$12$UtWg4WGBYipkf95ev43UFOoOjjfwxFEsJosw6vmveZXKZyCiX7wSa', 'customer', NULL, '2025-01-20 06:10:27', '2025-01-20 06:10:27'),
(6, 'Rahim Khan', 'rahim@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-01-25 06:40:02', NULL),
(7, 'Fatima Begum', 'fatima@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-05 06:40:02', NULL),
(8, 'Kamal Hossain', 'kamal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-12 06:40:02', NULL),
(9, 'Sadia Islam', 'sadia@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-02-20 06:40:02', NULL),
(10, 'Arif Mahmud', 'arif@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-03-08 06:40:02', NULL),
(11, 'Tania Ahmed', 'tania@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-03-18 06:40:02', NULL),
(12, 'Sohel Rana', 'sohel@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-04-05 06:40:02', NULL),
(13, 'Nusrat Jahan', 'nusrat@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-04-15 06:40:02', NULL),
(14, 'Imran Hossain', 'imran@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-05-10 06:40:02', NULL),
(15, 'Moumita Rahman', 'moumita@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-05-20 06:40:02', NULL),
(16, 'Faisal Ahmed', 'faisal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-06-12 06:40:02', NULL),
(17, 'Sabrina Chowdhury', 'sabrina@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-07-08 06:40:02', NULL),
(18, 'Rashidul Islam', 'rashidul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-08-05 06:40:02', NULL),
(19, 'Anika Tasnim', 'anika@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-08-18 06:40:02', NULL),
(20, 'Shahriar Manzoor', 'shahriar@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-09-10 06:40:02', NULL),
(21, 'Jannatul Ferdous', 'jannatul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-09-22 06:40:02', NULL),
(22, 'Rafiqul Islam', 'rafiqul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-01 06:40:02', NULL),
(23, 'Sharmin Akter', 'sharmin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-02 06:40:02', NULL),
(24, 'Nasir Uddin', 'nasir@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-03 06:40:02', NULL),
(25, 'Mitu Rahman', 'mitu@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-04 06:40:02', NULL),
(26, 'Sajal Hossain', 'sajal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-05 06:40:02', NULL),
(27, 'Poly Begum', 'poly@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-06 06:40:02', NULL),
(28, 'Bappi Sarker', 'bappi@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-07 06:40:02', NULL),
(29, 'Tumpa Akter', 'tumpa@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-08 06:40:02', NULL),
(30, 'Mamun Or Rashid', 'mamun@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-09 06:40:02', NULL),
(31, 'Shirin Sultana', 'shirin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-10 06:40:02', NULL),
(32, 'Rokonuzzaman', 'rokon@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-11 06:40:02', NULL),
(33, 'Farhana Yesmin', 'farhana@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-12 06:40:02', NULL),
(34, 'Alamgir Hossain', 'alamgir@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-13 06:40:02', NULL),
(35, 'Nazma Begum', 'nazma@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-14 06:40:02', NULL),
(36, 'Sazzad Hossain', 'sazzad@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-15 06:40:02', NULL),
(37, 'Morshed Alam', 'morshed@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-16 06:40:02', NULL),
(38, 'Shahinur Rahman', 'shahinur@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-17 06:40:02', NULL),
(39, 'Nargis Akter', 'nargis@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-18 06:40:02', NULL),
(40, 'Babul Miah', 'babul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-19 06:40:02', NULL),
(41, 'Rina Akter', 'rina@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-20 06:40:02', NULL),
(42, 'Shafiqul Islam', 'shafiqul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-21 06:40:02', NULL),
(43, 'Mousumi Rahman', 'mousumi@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-22 06:40:02', NULL),
(44, 'Jamal Uddin', 'jamal@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-23 06:40:02', NULL),
(45, 'Ruma Akter', 'ruma@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-24 06:40:02', NULL),
(46, 'Salam Miah', 'salam@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-25 06:40:02', NULL),
(47, 'Nipa Moni', 'nipa@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-26 06:40:02', NULL),
(48, 'Khalid Hasan', 'khalid@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-27 06:40:02', NULL),
(49, 'Mitu Akter', 'mitu2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-28 06:40:02', NULL),
(50, 'Ratan Sarker', 'ratan@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-29 06:40:02', NULL),
(51, 'Shamima Nasrin', 'shamima@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-30 06:40:02', NULL),
(52, 'Bappi Das', 'bappi2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(53, 'Moriom Begum', 'moriom@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(54, 'Raju Ahmed', 'raju@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(55, 'Shila Akter', 'shila@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(56, 'Mizanur Rahman', 'mizan@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(57, 'Naznin Sultana', 'naznin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(58, 'Alauddin', 'alauddin@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(59, 'Rokeya Begum', 'rokeya@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(60, 'Sohag Miah', 'sohag@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(61, 'Laboni Akter', 'laboni@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(62, 'Shahidul Islam', 'shahidul@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(63, 'Mita Rahman', 'mita@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(64, 'Rony Ahmed', 'rony@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(65, 'Tania Islam', 'tania2@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL),
(66, 'Sajib Hasan', 'sajib@example.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', NULL, '2025-10-31 06:40:02', NULL);

-- --------------------------------------------------------

--
-- Structure for view `monthly_billing_summaries_dynamic`
--
DROP TABLE IF EXISTS `monthly_billing_summaries_dynamic`;

DROP VIEW IF EXISTS `monthly_billing_summaries_dynamic`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_billing_summaries_dynamic`  AS SELECT `m`.`id` AS `id`, `m`.`billing_month` AS `billing_month`, `m`.`display_month` AS `display_month`, (select count(0) from `customers` `c` where ((`c`.`is_active` = 1) and (`c`.`created_at` <= last_day(str_to_date(`m`.`billing_month`,'%Y-%m-%d'))))) AS `total_customers`, `m`.`total_amount` AS `total_amount`, `m`.`received_amount` AS `received_amount`, `m`.`due_amount` AS `due_amount`, `m`.`status` AS `status`, `m`.`notes` AS `notes`, `m`.`is_locked` AS `is_locked`, `m`.`created_by` AS `created_by`, `m`.`created_at` AS `created_at`, `m`.`updated_at` AS `updated_at` FROM `monthly_billing_summaries` AS `m` ORDER BY `m`.`billing_month` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `monthly_revenue_summary`
--
DROP TABLE IF EXISTS `monthly_revenue_summary`;

DROP VIEW IF EXISTS `monthly_revenue_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_revenue_summary`  AS SELECT date_format(`i`.`issue_date`,'%Y-%m') AS `month_year`, count(`i`.`id`) AS `invoice_count`, sum(`i`.`total_amount`) AS `total_revenue`, sum(`i`.`received_amount`) AS `collected_revenue`, sum((`i`.`total_amount` - `i`.`received_amount`)) AS `pending_revenue` FROM `invoices` AS `i` GROUP BY date_format(`i`.`issue_date`,'%Y-%m') ORDER BY `month_year` DESC ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `customer_to_packages`
--
ALTER TABLE `customer_to_packages`
  ADD CONSTRAINT `fk_customer_packages_customer` FOREIGN KEY (`c_id`) REFERENCES `customers` (`c_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_customer_packages_package` FOREIGN KEY (`p_id`) REFERENCES `packages` (`p_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
