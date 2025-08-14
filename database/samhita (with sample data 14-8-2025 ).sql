-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 04:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `samhita`
--

-- --------------------------------------------------------

--
-- Table structure for table `available_purchased_stock`
--

CREATE TABLE `available_purchased_stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `available_purchased_stock`
--

INSERT INTO `available_purchased_stock` (`id`, `product_id`, `balance`, `created_at`, `updated_at`) VALUES
(1, 4, 131.00, '2025-08-07 08:22:33', '2025-08-07 11:18:54'),
(2, 5, 80.00, '2025-08-07 10:13:22', '2025-08-07 10:40:51'),
(3, 7, 122.00, '2025-08-11 04:58:56', '2025-08-11 04:58:56'),
(4, 8, 13.00, '2025-08-11 04:58:56', '2025-08-11 04:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(5) UNSIGNED NOT NULL,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `setting_name`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'company_logo', 'company_logo_1753253364_425fcde2cef350d6968f.png', '2025-07-23 10:11:46', '2025-07-23 06:49:24'),
(2, 'company_stamp', 'company_stamp_1755181581_f2ad9bc8a8e440a9e85f.jpg', '2025-07-23 10:11:46', '2025-08-14 14:26:21'),
(3, 'company_signature', 'company_signature_1753247867_ef7da14e43871ffad39f.png', '2025-07-23 10:11:46', '2025-07-23 05:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `distributors`
--

CREATE TABLE `distributors` (
  `id` int(11) NOT NULL,
  `custom_id` varchar(20) DEFAULT NULL,
  `agency_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `owner_phone` varchar(15) NOT NULL,
  `agent_name` varchar(255) DEFAULT NULL,
  `agent_phone` varchar(15) DEFAULT NULL,
  `agency_gst_number` varchar(15) DEFAULT NULL,
  `gmail` varchar(255) DEFAULT NULL,
  `agency_address` text NOT NULL,
  `status` enum('Active','Inactive','On Hold') NOT NULL DEFAULT 'Active',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `distributors`
--

INSERT INTO `distributors` (`id`, `custom_id`, `agency_name`, `owner_name`, `owner_phone`, `agent_name`, `agent_phone`, `agency_gst_number`, `gmail`, `agency_address`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'DSSS-250725-0001', 'Sri Venkateswara Enterprises', ' Gunnam Srinivas', '8919616123', 'Surya Narayana', '9573636186', '37AVWPG9703D1Z9', '', 'main road, kaleru, kapileswarapuram mandalam', 'Active', '', '2025-07-25 03:32:29', '2025-08-05 15:57:42'),
(2, 'DSSS-250730-0002', 'Gandepalli Pharma Producer Company Limited', 'Kola Kumaraswamy', '9133341117', 'M.Raviteja', '8106900876', '37AAKCG7619J1Z3', '', 'Talluru village,Highway road,Opposite Sivayalam,Gandepalli Mandalam,Kakinada District', 'Active', '', '2025-07-30 09:21:51', '2025-08-05 16:02:14'),
(3, 'DSSS-250730-0003', 'SIVA DURGA AGENCIES', 'MV.Srinivasarao', '9440897142', 'KVV.Suryanarayana', '9573636186', '37AASFS3055M1ZH', '', 'D.NO-1-78,Pamaru,Pamaru Mandalam,Konaseema district', 'On Hold', '', '2025-07-30 12:03:01', '2025-08-05 16:02:25'),
(4, 'DSSS-250801-0004', 'CH.Lovaraju', 'CH.Lovaraju', '8297757517', 'M.Raviteja', '8106900876', '', '', 'Vannipudi,Prathipadu Mandalam,Kakinada district', 'Active', '', '2025-08-01 05:44:29', '2025-08-05 16:02:32'),
(5, 'DSSS-250801-0005', 'D.Nagabhushanam', 'D.Nagabhushanam', '8328337070', 'M.Raviteja', '8106900876', '', '', 'Dharmavaram,Prathipadu Mandalam,Kakinada District', 'On Hold', '', '2025-08-01 06:30:35', '2025-08-05 16:02:45'),
(6, 'DSSS-250801-0011', 'K.Suribabu', 'K.Suribabu', '9000255828', 'M.Raviteja', '8106900876', '', '', 'Tadiparthy,Gollaprolu Mandalam,Kakinada District', 'Active', 'PAID', '2025-08-01 06:34:35', '2025-08-05 16:02:54'),
(7, 'DSSS-250801-0012', 'Ganapathi Fertilizers', 'M.Chakkarayya', '9849742839', 'M.Raviteja', '8106900876', '', '', 'Chandrapalem,Samalkot Mandalam', 'On Hold', '', '2025-08-01 06:45:50', '2025-08-05 16:03:04'),
(8, 'DSSS-250801-0013', 'Jai Ganesh Traders', 'T.Venkatesh', '9030460455', 'M.Raviteja', '8106900876', '', '', 'Veldurthi,Pithapuram Mandalam,Kakinada District', 'Active', '', '2025-08-01 06:53:01', '2025-08-05 16:03:15'),
(9, 'DSSS-250802-0014', 'Siri Agro Agencies', 'K.Ramadoralu', '9676457782', 'M.Raviteja', '8106900876', '37AEBPK4280B1ZZ', '', 'D.NO-16-118-119,Narsipatnam Road,Eleswaram,Eleswaram mandalam', 'On Hold', 'CREDIT', '2025-08-02 09:05:45', '2025-08-05 16:03:22'),
(10, 'DSSS-250802-0015', 'U.Krishna', 'U.Krishna', '7013587561', 'M.Raviteja', '8106900876', '', '', 'Surampalem,Rajavommangi Mandalam', 'Active', '', '2025-08-02 10:06:36', '2025-08-05 16:03:41'),
(11, 'DSSS-250802-0016', 'Nethaji Orgo Chemicals', 'Nethaji Orgo Chemicals', '9676709655', 'M.Raviteja', '8106900876', '', '', 'Tuni Area', 'On Hold', '', '2025-08-02 10:10:08', '2025-08-05 16:03:49'),
(16, 'DSSS-250812-0016', 'mohan pesticides', 'mohan', '9898989898', 'ravi', '9123456789', 'GSTIne28402', 'mohanoesticides@gmail.com', 'mndrssrgs', 'Active', '', '2025-08-12 13:37:06', '2025-08-12 13:37:06'),
(17, 'DSSS-250812-0017', 'ram ram', 'wef', '7897895246', 'dfvdf', '8884484825', 'Gar34ernlknl', 'mohanoesy8ides@gmail.com', 'regerer', 'Active', 'dde', '2025-08-12 13:38:12', '2025-08-13 12:12:57');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_payments`
--

CREATE TABLE `distributor_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `distributor_sales_order_id` int(11) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributor_payments`
--

INSERT INTO `distributor_payments` (`id`, `distributor_sales_order_id`, `payment_date`, `amount`, `payment_method`, `transaction_id`, `notes`, `created_at`, `updated_at`) VALUES
(26, 47, '2025-08-12', 2.00, 'Bank Transfer', '2', 'Initial payment for sales order INV-202508120001', '2025-08-12 04:16:18', '2025-08-12 04:16:18'),
(27, 48, '2025-08-12', 3.00, 'Bank Transfer', 'e', 'Initial payment for sales order INV-202508120002', '2025-08-12 05:34:39', '2025-08-12 05:34:39'),
(28, 49, '2025-08-12', 3.00, 'Cash', '3', 'Initial payment for sales order INV-202508120003', '2025-08-12 05:36:15', '2025-08-12 05:36:15'),
(29, 50, '2025-08-12', 8.00, 'UPI', '8', 'Initial payment for sales order INV-202508120004', '2025-08-12 05:48:47', '2025-08-12 05:48:47'),
(30, 51, '2025-08-12', 2.00, 'Cash', '4', 'Initial payment for sales order INV-202508120005', '2025-08-12 05:50:05', '2025-08-12 05:50:05'),
(45, 69, '2025-08-14', 90.00, 'Cash', '2', 'Initial payment for sales order INV-202508140011', '2025-08-14 08:39:03', '2025-08-14 08:39:03');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_sales_orders`
--

CREATE TABLE `distributor_sales_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `distributor_id` int(11) NOT NULL,
  `marketing_person_id` int(11) DEFAULT NULL,
  `pricing_tier` enum('dealer','farmer') NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount_before_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overall_gst_rate_ids` text DEFAULT NULL,
  `overall_gst_percentage_at_sale` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Partially Paid','Paid','Cancelled') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributor_sales_orders`
--

INSERT INTO `distributor_sales_orders` (`id`, `distributor_id`, `marketing_person_id`, `pricing_tier`, `invoice_number`, `invoice_date`, `sub_total`, `total_amount_before_gst`, `total_gst_amount`, `final_total_amount`, `overall_gst_rate_ids`, `overall_gst_percentage_at_sale`, `discount_amount`, `amount_paid`, `due_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(42, 11, 8, 'farmer', 'INV-20250811164511', '2025-08-11', 0.00, 745.00, 156.03, 899.03, NULL, NULL, 0.00, 22.00, 877.03, 'Pending', NULL, '2025-08-11 16:45:11', '2025-08-11 16:45:11'),
(43, 1, 9, 'farmer', 'INV-20250811170457', '2025-08-11', 0.00, 800.00, 213.30, 1003.30, '[\"12\",\"19\"]', 27.00, 10.00, 3.00, 1000.30, 'Pending', NULL, '2025-08-11 17:04:57', '2025-08-11 17:04:57'),
(44, 6, 8, 'farmer', 'INV-202508110458', '2025-08-11', 0.00, 101.00, 16.59, 95.59, '[\"19\",\"18\"]', 21.00, 22.00, 5.00, 90.59, 'Pending', NULL, '2025-08-11 17:19:24', '2025-08-11 17:19:24'),
(45, 11, 9, 'farmer', 'INV-202508110459', '2025-08-11', 0.00, 745.00, 151.20, 871.20, '[\"18\",\"19\"]', 21.00, 25.00, 71.20, 800.00, 'Pending', NULL, '2025-08-11 17:29:15', '2025-08-11 17:29:15'),
(46, 11, 8, 'farmer', 'INV-202508110460', '2025-08-11', 0.00, 101.00, 30.00, 130.00, '[\"12\",\"18\"]', 30.00, 1.00, 30.00, 100.00, 'Pending', NULL, '2025-08-11 17:37:24', '2025-08-11 17:37:24'),
(47, 9, 8, 'farmer', 'INV-202508120001', '2025-08-12', 745.00, 745.00, 0.00, 743.00, '[]', 0.00, 2.00, 2.00, 741.00, 'Partially Paid', '', '2025-08-12 04:16:18', '2025-08-12 04:16:18'),
(48, 11, 9, 'farmer', 'INV-202508120002', '2025-08-12', 745.00, 743.00, 85.45, 828.45, '19,17', 11.50, 2.00, 3.00, 825.45, 'Partially Paid', '', '2025-08-12 05:34:39', '2025-08-12 05:34:39'),
(49, 6, 9, 'farmer', 'INV-202508120003', '2025-08-12', 124.00, 121.00, 13.92, 134.92, '19,17', 11.50, 3.00, 3.00, 131.92, 'Partially Paid', '', '2025-08-12 05:36:15', '2025-08-12 05:36:15'),
(50, 6, 9, 'dealer', 'INV-202508120004', '2025-08-12', 104.00, 99.00, 29.70, 128.70, '12,18', 30.00, 5.00, 8.00, 120.70, 'Partially Paid', '', '2025-08-12 05:48:47', '2025-08-12 05:48:47'),
(51, 8, 8, 'dealer', 'INV-202508120005', '2025-08-12', 44.00, 40.00, 12.00, 52.00, '12,18', 30.00, 4.00, 2.00, 50.00, 'Partially Paid', '', '2025-08-12 05:50:05', '2025-08-12 05:50:05'),
(65, 8, 8, 'dealer', 'INV-202508130009', '2025-08-13', 212.00, 212.00, 38.16, 250.16, '[\"12\"]', 18.00, 0.00, 0.00, 250.16, 'Pending', '', '2025-08-13 04:24:37', '2025-08-13 04:24:37'),
(66, 17, 8, 'dealer', 'INV-202508130010', '2025-08-13', 124.00, 100.00, 21.00, 121.00, '[\"19\",\"18\"]', 21.00, 24.00, 21.00, 100.00, 'Partially Paid', '', '2025-08-13 05:17:41', '2025-08-13 05:17:41'),
(69, 10, 8, 'farmer', 'INV-202508140011', '2025-08-14', 1069.00, 1046.00, 407.94, 1453.94, '[{\"gst_rate_id\":\"12\",\"amount\":188.28},{\"gst_rate_id\":\"18\",\"amount\":125.52},{\"gst_rate_id\":\"19\",\"amount\":94.14}]', 18.00, 23.00, 90.00, 1363.94, 'Pending', '', '2025-08-14 08:39:03', '2025-08-14 12:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_sales_order_items`
--

CREATE TABLE `distributor_sales_order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `distributor_sales_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `gst_rate_id` int(11) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price_at_sale` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributor_sales_order_items`
--

INSERT INTO `distributor_sales_order_items` (`id`, `distributor_sales_order_id`, `product_id`, `gst_rate_id`, `quantity`, `unit_price_at_sale`, `item_total`, `created_at`, `updated_at`) VALUES
(73, 45, 15, NULL, 1, 55.00, 55.00, '2025-08-11 17:29:15', '2025-08-11 17:29:15'),
(74, 45, 17, NULL, 2, 345.00, 690.00, '2025-08-11 17:29:15', '2025-08-11 17:29:15'),
(75, 46, 15, NULL, 1, 55.00, 55.00, '2025-08-11 17:37:24', '2025-08-11 17:37:24'),
(76, 46, 16, NULL, 2, 23.00, 46.00, '2025-08-11 17:37:24', '2025-08-11 17:37:24'),
(77, 47, 15, NULL, 1, 55.00, 55.00, '2025-08-12 04:16:18', '2025-08-12 04:16:18'),
(78, 47, 17, NULL, 2, 345.00, 690.00, '2025-08-12 04:16:18', '2025-08-12 04:16:18'),
(79, 48, 15, NULL, 1, 55.00, 55.00, '2025-08-12 05:34:39', '2025-08-12 05:34:39'),
(80, 48, 17, NULL, 2, 345.00, 690.00, '2025-08-12 05:34:39', '2025-08-12 05:34:39'),
(81, 49, 15, NULL, 1, 55.00, 55.00, '2025-08-12 05:36:15', '2025-08-12 05:36:15'),
(82, 49, 16, NULL, 3, 23.00, 69.00, '2025-08-12 05:36:15', '2025-08-12 05:36:15'),
(83, 50, 15, NULL, 1, 44.00, 44.00, '2025-08-12 05:48:47', '2025-08-12 05:48:47'),
(84, 50, 16, NULL, 5, 12.00, 60.00, '2025-08-12 05:48:47', '2025-08-12 05:48:47'),
(130, 69, 15, NULL, 1, 200.00, 200.00, '2025-08-14 12:50:18', '2025-08-14 12:50:18'),
(131, 69, 16, NULL, 3, 23.00, 69.00, '2025-08-14 12:50:18', '2025-08-14 12:50:18'),
(132, 69, 17, NULL, 4, 200.00, 800.00, '2025-08-14 12:50:18', '2025-08-14 12:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `eway_bills`
--

CREATE TABLE `eway_bills` (
  `id` int(11) NOT NULL,
  `distributor_sales_order_id` int(11) UNSIGNED NOT NULL,
  `eway_bill_no` varchar(50) NOT NULL,
  `vehicle_number` varchar(15) NOT NULL,
  `generated_at` datetime NOT NULL,
  `valid_until` datetime NOT NULL,
  `api_response` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `place_of_dispatch` varchar(255) NOT NULL,
  `place_of_delivery` varchar(255) NOT NULL,
  `reason_for_transportation` text NOT NULL,
  `bill_generated_by` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(50) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  `transport_mode` varchar(50) DEFAULT NULL,
  `multiple_veh_info` text DEFAULT NULL,
  `cewb_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eway_bills`
--

INSERT INTO `eway_bills` (`id`, `distributor_sales_order_id`, `eway_bill_no`, `vehicle_number`, `generated_at`, `valid_until`, `api_response`, `created_at`, `updated_at`, `deleted_at`, `place_of_dispatch`, `place_of_delivery`, `reason_for_transportation`, `bill_generated_by`, `transaction_type`, `driver_name`, `distance`, `transport_mode`, `multiple_veh_info`, `cewb_no`) VALUES
(1, 65, 'EWAY-202508130001', 'Ap39ml2020', '2025-08-13 04:59:26', '2025-08-20 04:59:26', NULL, '2025-08-13 04:59:26', '2025-08-13 04:59:26', NULL, 'Kakinada', 'Rajamundry', 'purchasing \r\n', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 44, 'EWAY-202508130002', 'Ap39ml2021', '2025-08-13 05:20:07', '2025-08-20 05:20:07', NULL, '2025-08-13 05:20:07', '2025-08-13 08:03:59', NULL, 'Kakinadas', 'Vizags', 'ffe', 'tata', 'Other', 'billa', NULL, NULL, NULL, NULL),
(3, 66, 'EWAY-202508130003', 'Ap39ml2022', '2025-08-13 06:18:06', '2025-08-20 06:18:06', NULL, '2025-08-13 06:18:06', '2025-08-14 06:47:19', '2025-08-14 06:47:19', 'Kakinada', 'hybd', 'dfhteb', 'asha', 'Regular', 'raja', NULL, NULL, NULL, NULL),
(4, 66, 'EWAY-202508130004', 'Ap39ml2027', '2025-08-13 11:32:44', '2025-08-20 11:32:44', NULL, '2025-08-13 11:32:44', '2025-08-14 04:47:53', NULL, 'Kakinada', 'Rajamundry', 'rverer', 'asha', 'Regular', 'mani', 256, 'Road', 'Ap39ml2027, Ap39ml2029,Ap39ml2026', '34tr5t45');

-- --------------------------------------------------------

--
-- Table structure for table `gst_rates`
--

CREATE TABLE `gst_rates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `rate` decimal(5,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gst_rates`
--

INSERT INTO `gst_rates` (`id`, `name`, `rate`, `created_at`, `updated_at`) VALUES
(12, 'IGST', 18.00, '2025-08-06 09:56:47', '2025-08-06 09:56:47'),
(17, 'IGST', 2.50, '2025-08-06 10:02:14', '2025-08-06 10:02:14'),
(18, 'CGST', 12.00, '2025-08-06 10:02:47', '2025-08-06 10:02:47'),
(19, 'AGST', 9.00, '2025-08-06 10:03:00', '2025-08-06 10:03:49'),
(20, 'OWN', 0.00, '2025-08-06 10:03:57', '2025-08-12 12:05:23');

-- --------------------------------------------------------

--
-- Table structure for table `marketing_distribution`
--

CREATE TABLE `marketing_distribution` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `marketing_person_id` int(11) NOT NULL,
  `quantity_issued` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `date_issued` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_distribution`
--

INSERT INTO `marketing_distribution` (`id`, `product_id`, `marketing_person_id`, `quantity_issued`, `notes`, `date_issued`) VALUES
(19, 7, 8, 120, '', '2025-07-25'),
(20, 8, 9, 1, '', '2025-07-11'),
(21, 7, 9, 1, '', '2025-07-07'),
(22, 7, 9, 10, '', '2025-07-31'),
(23, 8, 8, 20, '', '2025-07-25'),
(24, 7, 8, 39, '', '2025-08-05');

-- --------------------------------------------------------

--
-- Table structure for table `marketing_persons`
--

CREATE TABLE `marketing_persons` (
  `id` int(11) NOT NULL,
  `custom_id` varchar(30) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `secondary_phone_num` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `aadhar_card_image` varchar(255) DEFAULT NULL,
  `pan_card_image` varchar(255) DEFAULT NULL,
  `driving_license_image` varchar(255) DEFAULT NULL,
  `address_proof_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_persons`
--

INSERT INTO `marketing_persons` (`id`, `custom_id`, `name`, `phone`, `secondary_phone_num`, `email`, `address`, `aadhar_card_image`, `pan_card_image`, `driving_license_image`, `address_proof_image`, `created_at`, `updated_at`) VALUES
(8, 'SSS-250604-0001', 'K.V.V Suryanarayana', '9573636186', '9573636186', 'kvvsuryanarayana3@gmail.com', 'Ramachandrapuram', NULL, NULL, NULL, NULL, '2025-06-04 12:00:20', '2025-07-25 05:24:50'),
(9, 'SSS-250604-0002', 'M.Raviteja', '8106900876', '8688450394', 'ravitejamudadha0000@gmail.com', 'Digumarthivari street,Sri Vikasa junior college,Kakinada', NULL, NULL, NULL, NULL, '2025-06-04 12:00:33', '2025-07-30 09:56:27'),
(16, 'SSS-250814-0003', 'ranga ward', '9239423434', '', '', '', NULL, NULL, NULL, NULL, '2025-08-14 14:37:18', '2025-08-14 14:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-06-12-051655', 'App\\Database\\Migrations\\AddFieldsToMarketingPersons', 'default', 'App', 1749705622, 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchased_products`
--

CREATE TABLE `purchased_products` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `purchased_products`
--

INSERT INTO `purchased_products` (`id`, `name`, `description`, `unit_id`, `created_at`, `updated_at`) VALUES
(4, 'Raw powders', '', 11, '2025-08-06 10:42:33', '2025-08-07 07:21:10'),
(5, 'Raw Chock', '', 10, '2025-08-06 10:42:55', '2025-08-07 07:15:17'),
(6, 'Belly bag', '', 11, '2025-08-06 10:43:12', '2025-08-07 07:09:39'),
(7, 'Beens', '', 7, '2025-08-07 17:10:44', '2025-08-07 17:10:44'),
(8, 'fluffygs', '', 6, '2025-08-07 17:10:56', '2025-08-07 17:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `marketing_person_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_price` decimal(10,2) DEFAULT NULL,
  `amount_received_from_person` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_from_person` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status_from_person` varchar(50) NOT NULL DEFAULT 'Pending',
  `last_remittance_date` date DEFAULT NULL,
  `date_sold` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `marketing_person_id`, `quantity_sold`, `price_per_unit`, `discount`, `total_price`, `amount_received_from_person`, `balance_from_person`, `payment_status_from_person`, `last_remittance_date`, `date_sold`, `customer_name`, `customer_phone`, `customer_address`, `created_at`, `updated_at`) VALUES
(20, 8, 9, 1, 250.00, 0.00, 250.00, 0.00, 250.00, 'Pending', NULL, '2025-07-11', 'CH.Lovaraju', '8297757517', 'Vannipudi,Prathipadu Mandalam,Kakinada District', '2025-08-01 06:00:59', '2025-08-01 06:13:31'),
(21, 7, 9, 1, 300.00, 0.00, 300.00, 0.00, 300.00, 'Pending', NULL, '2025-07-11', 'CH.Lovaraju', '8297757517', 'Vannipudi,Prathipadu Mandalam,Kakinada District', '2025-08-01 06:05:29', '2025-08-01 06:05:29'),
(22, 7, 9, 10, 300.00, 0.00, 3000.00, 0.00, 3000.00, 'Pending', NULL, '2025-07-31', 'K.Kumaraswamy', '9133341117', 'Talluru Village,Gandepalli Mandalam,Kakinada District', '2025-08-01 06:08:58', '2025-08-01 06:08:58'),
(23, 8, 8, 20, 250.00, 0.00, 5000.00, 0.00, 5000.00, 'Pending', NULL, '2025-07-25', 'Gunnam Srinivas', '8919616123', 'Kaleru,Main Road,Kapileswaram Mandalam', '2025-08-01 06:12:59', '2025-08-01 06:12:59'),
(24, 7, 8, 20, 300.00, 0.00, 6000.00, 4000.00, 2000.00, 'Partial', '2025-08-05', '2025-08-05', 'sample', '0000000000', 'kkd', '2025-08-05 11:11:34', '2025-08-05 11:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `sale_payments`
--

CREATE TABLE `sale_payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `sale_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_payments`
--

INSERT INTO `sale_payments` (`id`, `sale_id`, `payment_date`, `amount_paid`, `payment_method`, `remarks`, `created_at`, `updated_at`) VALUES
(11, 24, '2025-08-05', 4000.00, '', '', '2025-08-05 11:23:14', '2025-08-05 11:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `selling_products`
--

CREATE TABLE `selling_products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `dealer_price` decimal(10,2) DEFAULT NULL,
  `farmer_price` decimal(10,2) DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `unit_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `selling_products`
--

INSERT INTO `selling_products` (`id`, `name`, `description`, `dealer_price`, `farmer_price`, `current_stock`, `unit_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'ARKA MICROBIAL CONSORTIUM', 'need to mix in the water and have to spary to plants', 300.00, 600.00, 300, 2, '2025-06-24 05:18:28', '2025-08-07 12:38:55', '2025-08-07 12:38:55'),
(8, 'MONAS', '', 250.00, 500.00, 399999, 1, '2025-07-25 05:29:23', '2025-08-07 12:40:54', '2025-08-07 12:40:54'),
(13, 'Sample', 'revs', 13.00, 23.00, 9993, 1, '2025-08-11 04:29:43', '2025-08-11 04:36:24', '2025-08-11 04:36:24'),
(14, 'kajasg', 'tatasg', 35.00, 55.00, 55554, 10, '2025-08-11 04:32:48', '2025-08-11 04:36:33', '2025-08-11 04:36:33'),
(15, 'monoSe', 'ytjdt', 100.00, 200.00, 8917, 10, '2025-08-11 04:36:57', '2025-08-14 08:39:03', NULL),
(16, 'chock powder', 'f', 12.00, 23.00, 101, 10, '2025-08-11 04:37:30', '2025-08-14 08:39:03', NULL),
(17, 'thaatik', 'y', 100.00, 200.00, 567600, 4, '2025-08-11 04:37:46', '2025-08-14 06:54:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sequences`
--

CREATE TABLE `sequences` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `current_value` int(11) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sequences`
--

INSERT INTO `sequences` (`id`, `name`, `current_value`, `updated_at`) VALUES
(1, 'distributor_custom_id', 17, '2025-08-12 13:38:12');

-- --------------------------------------------------------

--
-- Table structure for table `stock_consumption`
--

CREATE TABLE `stock_consumption` (
  `id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity_consumed` decimal(10,2) NOT NULL,
  `date_consumed` date NOT NULL,
  `used_by` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_consumption`
--

INSERT INTO `stock_consumption` (`id`, `product_id`, `quantity_consumed`, `date_consumed`, `used_by`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 20.00, '0000-00-00', 'raja', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 6, 10.00, '0000-00-00', 'e', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 6, 23.00, '0000-00-00', 'f', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 5, 20.00, '0000-00-00', 'ramu', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 4, 9.00, '2025-08-06', 'kam', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 8, 3.00, '2025-08-12', 'raj', NULL, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `notes` text DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `initial_amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_type` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `total_amount_before_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`id`, `vendor_id`, `date_received`, `notes`, `discount_amount`, `initial_amount_paid`, `balance_amount`, `payment_type`, `transaction_id`, `payment_notes`, `total_amount_before_gst`, `gst_amount`, `grand_total`, `final_grand_total`, `created_at`, `updated_at`) VALUES
(10, 4, '2025-08-07', 'e', 35.00, 600.00, 6000.20, '', '', '', 5104.00, 1531.20, 6600.20, 6565.20, '2025-08-07 07:09:02', '2025-08-07 07:09:02'),
(11, 7, '2025-08-07', '', 0.00, 0.00, 308.00, '', '', '', 275.00, 33.00, 308.00, 308.00, '2025-08-07 07:09:39', '2025-08-07 07:09:39'),
(12, 7, '2025-08-07', '', 0.00, 0.00, 116.82, '', '', '', 99.00, 17.82, 116.82, 116.82, '2025-08-07 07:11:17', '2025-08-07 07:11:17'),
(13, 7, '2025-08-07', '', 0.00, 0.00, 184.80, '', '', '', 165.00, 19.80, 184.80, 184.80, '2025-08-07 07:15:17', '2025-08-07 07:15:17'),
(14, 5, '2025-08-07', '', 0.00, 0.00, 0.00, '', '', '', 0.00, 0.00, 0.00, 0.00, '2025-08-07 07:21:10', '2025-08-07 07:21:10'),
(15, 4, '2025-08-07', '', 4.00, 14.00, 100.00, '', '', '', 100.00, 18.00, 114.00, 110.00, '2025-08-07 08:22:33', '2025-08-07 08:22:33'),
(16, 5, '2025-08-07', '', 0.00, 0.00, 6.15, '', '', '', 6.00, 0.15, 6.15, 6.15, '2025-08-07 09:05:02', '2025-08-07 09:05:02'),
(17, 6, '2025-08-07', '', 0.00, 0.00, 24.00, '', '', '', 24.00, 0.00, 24.00, 24.00, '2025-08-07 09:58:07', '2025-08-07 09:58:07'),
(18, 5, '2025-08-07', '', 0.00, 0.00, 5.90, '', '', '', 5.00, 0.90, 5.90, 5.90, '2025-08-07 10:13:22', '2025-08-07 10:13:22'),
(19, 5, '2025-08-07', '', 0.00, 0.00, 22.40, '', '', '', 20.00, 2.40, 22.40, 22.40, '2025-08-07 10:35:08', '2025-08-07 10:35:08'),
(20, 5, '2025-08-07', '', 0.00, 0.00, 1100.00, '', '', '', 1100.00, 0.00, 1100.00, 1100.00, '2025-08-07 10:40:51', '2025-08-07 10:40:51'),
(21, 4, '2025-08-07', 'hi ', 43.00, 1500.00, 3000.00, '', '', '', 3850.00, 693.00, 4500.00, 4457.00, '2025-08-07 11:18:54', '2025-08-07 11:19:37'),
(22, 5, '2025-08-11', '', 0.00, 0.00, 4104.00, '', '', '', 4104.00, 0.00, 4104.00, 4104.00, '2025-08-11 04:58:56', '2025-08-11 04:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_gst`
--

CREATE TABLE `stock_in_gst` (
  `id` int(11) NOT NULL,
  `stock_in_id` int(11) NOT NULL,
  `gst_rate_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in_gst`
--

INSERT INTO `stock_in_gst` (`id`, `stock_in_id`, `gst_rate_id`, `created_at`) VALUES
(14, 10, 12, '2025-08-07 12:39:02'),
(15, 10, 18, '2025-08-07 12:39:02'),
(16, 11, 18, '2025-08-07 12:39:39'),
(17, 12, 12, '2025-08-07 12:41:17'),
(18, 13, 18, '2025-08-07 12:45:17'),
(19, 14, 12, '2025-08-07 12:51:10'),
(20, 15, 12, '2025-08-07 13:52:33'),
(21, 16, 17, '2025-08-07 14:35:02'),
(22, 17, 20, '2025-08-07 15:28:07'),
(23, 18, 12, '2025-08-07 15:43:22'),
(24, 19, 18, '2025-08-07 16:05:08'),
(25, 20, 20, '2025-08-07 16:10:51'),
(26, 21, 12, '2025-08-07 16:48:54'),
(27, 22, 20, '2025-08-11 10:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_payments`
--

CREATE TABLE `stock_in_payments` (
  `id` int(11) NOT NULL,
  `stock_in_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in_payments`
--

INSERT INTO `stock_in_payments` (`id`, `stock_in_id`, `payment_amount`, `payment_date`, `payment_type`, `transaction_id`, `notes`, `created_at`) VALUES
(7, 10, 600.00, '2025-08-07', '', '', '', '2025-08-07 12:39:02'),
(8, 15, 14.00, '2025-08-07', '', '', '', '2025-08-07 13:52:33'),
(9, 21, 500.00, '2025-08-07', '', '', '', '2025-08-07 16:48:54'),
(10, 21, 1000.00, '2025-08-07', 'cash', NULL, '', '2025-08-07 16:49:37');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_products`
--

CREATE TABLE `stock_in_products` (
  `id` int(11) NOT NULL,
  `stock_in_id` int(11) NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `item_total` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in_products`
--

INSERT INTO `stock_in_products` (`id`, `stock_in_id`, `product_id`, `quantity`, `purchase_price`, `item_total`, `created_at`, `updated_at`) VALUES
(17, 10, 4, 11.00, 20.00, 220.00, '2025-08-07 07:09:02', '2025-08-07 07:09:02'),
(18, 10, 5, 22.00, 222.00, 4884.00, '2025-08-07 07:09:02', '2025-08-07 07:09:02'),
(19, 11, 6, 55.00, 5.00, 275.00, '2025-08-07 07:09:39', '2025-08-07 07:09:39'),
(20, 12, 4, 3.00, 33.00, 99.00, '2025-08-07 07:11:17', '2025-08-07 07:11:17'),
(21, 13, 5, 3.00, 55.00, 165.00, '2025-08-07 07:15:17', '2025-08-07 07:15:17'),
(22, 14, 4, 1.00, 0.00, 0.00, '2025-08-07 07:21:10', '2025-08-07 07:21:10'),
(23, 15, 4, 50.00, 2.00, 100.00, '2025-08-07 08:22:33', '2025-08-07 08:22:33'),
(24, 16, 4, 2.00, 3.00, 6.00, '2025-08-07 09:05:02', '2025-08-07 09:05:02'),
(25, 17, 4, 2.00, 12.00, 24.00, '2025-08-07 09:58:07', '2025-08-07 09:58:07'),
(26, 18, 5, 5.00, 1.00, 5.00, '2025-08-07 10:13:22', '2025-08-07 10:13:22'),
(27, 19, 5, 20.00, 1.00, 20.00, '2025-08-07 10:35:08', '2025-08-07 10:35:08'),
(28, 20, 5, 55.00, 20.00, 1100.00, '2025-08-07 10:40:51', '2025-08-07 10:40:51'),
(29, 21, 4, 77.00, 50.00, 3850.00, '2025-08-07 11:18:54', '2025-08-07 11:18:54'),
(30, 22, 7, 122.00, 33.00, 4026.00, '2025-08-11 04:58:56', '2025-08-11 04:58:56'),
(31, 22, 8, 13.00, 6.00, 78.00, '2025-08-11 04:58:56', '2025-08-11 04:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'kg', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(2, 'liters', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(3, 'packets', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(4, 'bottles', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(5, 'ml', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(6, 'g', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(7, 'others', '2025-06-04 11:52:38', '2025-06-04 11:53:27'),
(10, 'Tones', '2025-06-09 04:30:12', '2025-06-09 04:30:12'),
(11, 'Bagss', '2025-08-05 10:49:11', '2025-08-12 12:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$Sh/3KsbkbQ12FXx.ZBngVOi1ZOTuKa4swBJBgjtIhvZ7HhXQ.Dh3W', 'admin', '2025-06-28 15:47:55', '2025-06-28 16:25:31');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `owner_phone` varchar(20) DEFAULT NULL,
  `agency_name` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `owner_phone`, `agency_name`, `contact_person`, `contact_phone`, `phone`, `email`, `address`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Raja Ramsw', '', 'agri tech', 'mani', '', '9239423434', 'abhi@gmail.com', 'kkdg\r\n', '2025-06-07 07:12:47', '2025-08-06 11:00:05', '2025-08-06 11:00:05'),
(2, 'shyam', NULL, 'Ever Green', 'shaym', NULL, '934959459', 'vgv@gmail.com', 'rjy\r\n', '2025-06-07 07:13:32', '2025-08-06 11:00:08', '2025-08-06 11:00:08'),
(3, 'mohana', '9789789778', 'south soilss', 'ramu', '9872651', NULL, 'latha@gmail.com', 'kkd', '2025-06-07 10:51:15', '2025-08-06 11:22:57', '2025-08-06 11:22:57'),
(4, 'Parvathi Rajyam', '9848549349', 'Samhita soil solutions', 'Anil', '7288822176', NULL, 'samhithasoilsolutions@gmail.com', 'Kakinadadd', '2025-07-25 05:41:34', '2025-08-14 14:38:15', NULL),
(5, 'K.Kumaraswamy', '9133341117', 'Gandepalli Pharma Producer Company Limited', 'M.Raviteja', '8106969721', NULL, '', 'kakinada', '2025-08-02 09:42:22', '2025-08-14 14:38:05', '2025-08-14 14:38:05'),
(6, 'annapoorna agencies', '08842373170', 'Annapoorna agencies', 'annapoorna agencies', '08842373170', NULL, '', 'Kakinada', '2025-08-05 10:46:39', '2025-08-12 12:10:57', '2025-08-12 12:10:57'),
(7, 'ram', '1231231231', 'rambo', 'ramu', '1231231231', NULL, 'visoi@gmail.com', 'dw', '2025-08-06 11:23:29', '2025-08-12 12:10:54', '2025-08-12 12:10:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `available_purchased_stock`
--
ALTER TABLE `available_purchased_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `distributors`
--
ALTER TABLE `distributors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_distributor_payments_sales_order_id` (`distributor_sales_order_id`);

--
-- Indexes for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `fk_distributor_sales_orders_distributor_id` (`distributor_id`),
  ADD KEY `fk_marketing_person` (`marketing_person_id`);

--
-- Indexes for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_distributor_sales_order_items_sales_order_id` (`distributor_sales_order_id`),
  ADD KEY `fk_distributor_sales_order_items_product_id` (`product_id`);

--
-- Indexes for table `eway_bills`
--
ALTER TABLE `eway_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_eway_bills_distributor_sales_order_id` (`distributor_sales_order_id`);

--
-- Indexes for table `gst_rates`
--
ALTER TABLE `gst_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `marketing_person_id` (`marketing_person_id`);

--
-- Indexes for table `marketing_persons`
--
ALTER TABLE `marketing_persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `custom_id` (`custom_id`),
  ADD UNIQUE KEY `custom_id_2` (`custom_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchased_products`
--
ALTER TABLE `purchased_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_purchased_product_unit` (`unit_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `marketing_person_id` (`marketing_person_id`);

--
-- Indexes for table `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sale_id` (`sale_id`);

--
-- Indexes for table `selling_products`
--
ALTER TABLE `selling_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `sequences`
--
ALTER TABLE `sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_consumption`
--
ALTER TABLE `stock_consumption`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_consumption_purchased_products_2` (`product_id`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_si_vendor` (`vendor_id`);

--
-- Indexes for table `stock_in_gst`
--
ALTER TABLE `stock_in_gst`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_in_id` (`stock_in_id`),
  ADD KEY `gst_rate_id` (`gst_rate_id`);

--
-- Indexes for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_in_id` (`stock_in_id`);

--
-- Indexes for table `stock_in_products`
--
ALTER TABLE `stock_in_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_in_id` (`stock_in_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `available_purchased_stock`
--
ALTER TABLE `available_purchased_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `distributors`
--
ALTER TABLE `distributors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `eway_bills`
--
ALTER TABLE `eway_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gst_rates`
--
ALTER TABLE `gst_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `marketing_persons`
--
ALTER TABLE `marketing_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchased_products`
--
ALTER TABLE `purchased_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `sale_payments`
--
ALTER TABLE `sale_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `selling_products`
--
ALTER TABLE `selling_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `sequences`
--
ALTER TABLE `sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_consumption`
--
ALTER TABLE `stock_consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_in_gst`
--
ALTER TABLE `stock_in_gst`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_in_products`
--
ALTER TABLE `stock_in_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `available_purchased_stock`
--
ALTER TABLE `available_purchased_stock`
  ADD CONSTRAINT `fk_product_balance_v2` FOREIGN KEY (`product_id`) REFERENCES `purchased_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  ADD CONSTRAINT `fk_distributor_payments_sales_order_id` FOREIGN KEY (`distributor_sales_order_id`) REFERENCES `distributor_sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  ADD CONSTRAINT `fk_distributor_sales_orders_distributor_id` FOREIGN KEY (`distributor_id`) REFERENCES `distributors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_marketing_person` FOREIGN KEY (`marketing_person_id`) REFERENCES `marketing_persons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  ADD CONSTRAINT `fk_distributor_sales_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `selling_products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distributor_sales_order_items_sales_order_id` FOREIGN KEY (`distributor_sales_order_id`) REFERENCES `distributor_sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `eway_bills`
--
ALTER TABLE `eway_bills`
  ADD CONSTRAINT `fk_eway_bills_distributor_sales_order_id` FOREIGN KEY (`distributor_sales_order_id`) REFERENCES `distributor_sales_orders` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  ADD CONSTRAINT `marketing_distribution_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `selling_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marketing_distribution_ibfk_2` FOREIGN KEY (`marketing_person_id`) REFERENCES `marketing_persons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchased_products`
--
ALTER TABLE `purchased_products`
  ADD CONSTRAINT `fk_purchased_product_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_selling_product` FOREIGN KEY (`product_id`) REFERENCES `selling_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`marketing_person_id`) REFERENCES `marketing_persons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD CONSTRAINT `fk_sale_id` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `selling_products`
--
ALTER TABLE `selling_products`
  ADD CONSTRAINT `selling_products_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_consumption`
--
ALTER TABLE `stock_consumption`
  ADD CONSTRAINT `fk_stock_consumption_purchased_products` FOREIGN KEY (`product_id`) REFERENCES `purchased_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stock_consumption_purchased_products_2` FOREIGN KEY (`product_id`) REFERENCES `purchased_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `fk_si_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `stock_in_gst`
--
ALTER TABLE `stock_in_gst`
  ADD CONSTRAINT `fk_sig_gst_rate` FOREIGN KEY (`gst_rate_id`) REFERENCES `gst_rates` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sig_stock_in` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  ADD CONSTRAINT `fk_sip_stock_in_2` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_in_products`
--
ALTER TABLE `stock_in_products`
  ADD CONSTRAINT `fk_sip_product` FOREIGN KEY (`product_id`) REFERENCES `purchased_products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sip_stock_in` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
