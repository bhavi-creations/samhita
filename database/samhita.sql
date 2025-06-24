-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 12:40 PM
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
(2, 'DSSS-250619-0002', 'reddy farmings', 'mohan', '9898989399', 'raja', '9988899889', 'GSTIne28402vv', 'reddy@gmail.com', 'ewfae', 'Active', '', '2025-06-19 06:44:43', '2025-06-19 06:49:40'),
(3, 'DSSS-250619-0003', 'mohan pesticides', 'mohan', '9898989898', 'raja', '9090909090', 'Gar34ernlknl', 'moeanoesticides@gmail.com', 'gsddfger', 'Active', '', '2025-06-19 11:08:14', '2025-06-19 11:08:14'),
(4, 'DSSS-250624-0004', 'manikanta greens', 'manikanta', '9944558866', 'sai', '1231234567', 'Gar34ernlknlef', 'reddywed@gmail.com', 'sgefbt', 'Active', '', '2025-06-24 05:13:02', '2025-06-24 05:13:02');

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
(2, 2, '2025-06-19', 100.00, 'Cash', '', '', '2025-06-19 12:16:33', '2025-06-19 12:16:33'),
(3, 3, '2025-06-19', 300.00, 'Credit', '', '', '2025-06-19 12:17:02', '2025-06-19 12:17:02'),
(4, 2, '2025-06-19', 855.94, 'cash', '', '', '2025-06-19 12:23:27', '2025-06-19 12:23:27'),
(5, 2, '2025-06-19', 3000.00, 'UPI', '', '', '2025-06-19 12:30:27', '2025-06-19 12:30:27'),
(6, 4, '2025-06-20', 100.00, 'Cash', '14', 'weee', '2025-06-20 06:31:36', '2025-06-20 06:31:36'),
(10, 9, '2025-06-20', 50.00, 'Cash', '', '', '2025-06-20 07:21:01', '2025-06-20 07:21:01'),
(11, 9, '2025-06-20', 50.18, '', '', '', '2025-06-20 07:22:25', '2025-06-20 07:22:25');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_sales_orders`
--

CREATE TABLE `distributor_sales_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `distributor_id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount_before_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `final_total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
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

INSERT INTO `distributor_sales_orders` (`id`, `distributor_id`, `invoice_number`, `invoice_date`, `total_amount_before_gst`, `total_gst_amount`, `final_total_amount`, `discount_amount`, `amount_paid`, `due_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(2, 2, 'INV-20250619-0001', '2025-06-19', 5100.00, 6.12, 5106.12, 0.00, 3955.94, 1150.18, 'Partially Paid', '', '2025-06-19 12:16:33', '2025-06-20 05:19:21'),
(3, 3, 'INV-20250619-0002', '2025-06-19', 7300.00, 11.82, 6311.82, 0.00, 300.00, 6011.82, 'Partially Paid', '', '2025-06-19 12:17:02', '2025-06-20 05:49:12'),
(4, 3, 'INV-20250620-0001', '2025-06-20', 500.00, 0.60, 500.60, 0.00, 100.00, 400.60, 'Partially Paid', 'hello there', '2025-06-20 06:31:36', '2025-06-20 06:31:36'),
(9, 2, 'INV-20250620-0002', '2025-06-20', 1500.00, 1.80, 1451.80, 50.00, 100.18, 1351.62, 'Partially Paid', 'dd', '2025-06-20 07:21:01', '2025-06-20 07:39:22'),
(11, 2, 'INV-20250624-00003', '2025-06-24', 1500.00, 1.80, 1401.80, 100.00, 0.00, 1401.80, 'Pending', '', '2025-06-24 05:10:25', '2025-06-24 05:11:02'),
(17, 2, 'INV-20250624-00004', '2025-06-24', 3450.00, 4.14, 3454.14, 0.00, 0.00, 3454.14, 'Pending', '', '2025-06-24 06:09:39', '2025-06-24 06:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `distributor_sales_order_items`
--

CREATE TABLE `distributor_sales_order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `distributor_sales_order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `gst_rate_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price_at_sale` decimal(10,2) NOT NULL,
  `gst_rate_at_sale` decimal(5,2) NOT NULL,
  `item_total_before_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_gst_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `item_final_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `distributor_sales_order_items`
--

INSERT INTO `distributor_sales_order_items` (`id`, `distributor_sales_order_id`, `product_id`, `gst_rate_id`, `quantity`, `unit_price_at_sale`, `gst_rate_at_sale`, `item_total_before_gst`, `item_gst_amount`, `item_final_total`, `created_at`, `updated_at`) VALUES
(2, 2, 3, 1, 34, 150.00, 0.12, 5100.00, 6.12, 5106.12, '2025-06-19 12:16:33', '2025-06-20 05:19:21'),
(3, 3, 4, 1, 88, 25.00, 0.12, 2200.00, 2.64, 2202.64, '2025-06-19 12:17:02', '2025-06-20 05:49:12'),
(4, 3, 3, 2, 34, 150.00, 0.18, 5100.00, 9.18, 5109.18, '2025-06-19 12:17:02', '2025-06-20 05:49:12'),
(5, 4, 4, 1, 20, 25.00, 0.12, 500.00, 0.60, 500.60, '2025-06-20 06:31:36', '2025-06-20 06:31:36'),
(9, 9, 3, 1, 10, 150.00, 0.12, 1500.00, 1.80, 1501.80, '2025-06-20 07:21:01', '2025-06-20 07:39:22'),
(15, 11, 3, 1, 10, 150.00, 0.12, 1500.00, 1.80, 1501.80, '2025-06-24 05:11:02', '2025-06-24 05:11:02'),
(27, 17, 3, 1, 23, 150.00, 0.12, 3450.00, 4.14, 3454.14, '2025-06-24 06:10:00', '2025-06-24 06:10:00');

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
(1, 'GST 12%', 0.12, '2025-06-10 12:04:15', '2025-06-10 12:04:15'),
(2, 'GST 18%', 0.18, '2025-06-10 12:04:35', '2025-06-11 04:47:12'),
(4, 'GST 22%', 0.22, '2025-06-11 04:42:56', '2025-06-11 04:47:19');

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
(1, 3, 8, 3, NULL, '2025-06-04'),
(2, 3, 9, 6, NULL, '2025-06-03'),
(3, 4, 10, 20, NULL, '2025-06-06'),
(4, 3, 10, 2, NULL, '2025-06-05'),
(5, 5, 8, 30, NULL, '2025-06-05'),
(6, 6, 9, 10, NULL, '2025-06-06'),
(7, 6, 10, 5, NULL, '2025-06-06'),
(8, 3, 14, 80, '', '2025-06-12'),
(9, 3, 14, 90, '', '2025-06-12'),
(10, 4, 10, 90, '', '2025-06-13'),
(11, 4, 8, 20, '', '2025-06-12'),
(12, 4, 12, 400, '', '2025-06-14'),
(13, 3, 8, 50, '', '2025-06-14'),
(14, 3, 12, 20, '', '2025-06-14'),
(15, 3, 9, 50, '', '2025-06-16');

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
(8, 'SSS-250604-0001', 'ramesh', '1231231231', NULL, 'rameshpilli1428@gmail.com', 'r', NULL, NULL, NULL, NULL, '2025-06-04 12:00:20', '2025-06-04 12:00:20'),
(9, 'SSS-250604-0002', 'safi', '9000082299', NULL, 'bhavicreations@gmail.com', 'ef', NULL, NULL, NULL, NULL, '2025-06-04 12:00:33', '2025-06-04 12:00:48'),
(10, 'SSS-250605-0003', 'mani', '9000082299', NULL, 'neurostarkakinada@gmail.com', 'kkd', NULL, NULL, NULL, NULL, '2025-06-05 07:22:07', '2025-06-05 07:22:07'),
(11, 'SSS-250612-0004', 'reddy', '1324567892', '7894561230', 'bhavicreationsthkhg@gmail.com', 'kkd', NULL, NULL, NULL, NULL, '2025-06-12 05:45:30', '2025-06-12 05:45:30'),
(12, 'SSS-250612-0005', 'sds', '9239423434', '7894561230', 'bhavicreatiowefns3022@gmail.come', 'e', '1749707750_550eb549538f0b21eebe.png', '1749707750_f4dac3e07b4908d6648f.png', '1749707750_51de664faa9ffcbaccc1.jpg', '1749707750_51a73e397c037c57c5ec.png', '2025-06-12 05:55:50', '2025-06-12 05:55:50'),
(13, 'SSS-250612-0006', 'bggfnb', '9239423434', '7894561230', 'bhavicreatiogfns3022@gmail.come', 'et', '1749707797_2013c0f714ecb2bae82a.png', '1749707797_a8b16c3bb2704da6c859.jpg', '1749707797_1aad87739dbd60c8969b.jpg', '1749707797_3aebe95f566e495c389f.png', '2025-06-12 05:56:37', '2025-06-12 05:56:37'),
(14, 'SSS-250612-0007', 'nanna', '9239423434', '7894561230', 'bhavicrereations3022@gmail.come', 'rffzz', '1749707869_1cadafa498bb1440d177.jpg', '1749707869_2dbd4f81bcc6f190e6ee.jpg', '1749707869_df566fbd5758f4d964dc.jpg', '1749709718_3eab1189db9795e8a1de.png', '2025-06-12 05:57:49', '2025-06-12 06:28:38');

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `default_selling_price` decimal(10,2) DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `unit_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `selling_price`, `default_selling_price`, `current_stock`, `unit_id`, `created_at`, `updated_at`) VALUES
(3, 'soil mixture', 'this is the powder to mix into soil', 150.00, NULL, 1319, 1, '2025-06-04 07:44:05', '2025-06-24 06:10:00'),
(4, 'water mixture', 'this can be add in the water and spray to the cropes', 25.00, NULL, 8878, 2, '2025-06-05 07:20:13', '2025-06-24 06:07:28'),
(5, 'miniral tablets', 'need to mix in the water and have to spary to plants', 20.00, NULL, 51, 7, '2025-06-05 12:02:34', '2025-06-24 05:19:57'),
(6, 'chock powder', '', 5.00, NULL, 1312, 1, '2025-06-06 11:36:07', '2025-06-24 05:20:03'),
(7, 'wood sticks', '', 500.00, NULL, 499, 10, '2025-06-24 05:18:28', '2025-06-24 07:02:53');

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
(1, 3, 8, 8, 50.00, 0.00, NULL, 0.00, 0.00, 'Pending', NULL, '2025-06-05', NULL, NULL, NULL, '2025-06-09 15:25:40', '2025-06-09 15:25:40'),
(2, 3, 9, 4, 50.00, 0.00, NULL, 0.00, 0.00, 'Pending', NULL, '2025-06-05', NULL, NULL, NULL, '2025-06-09 15:25:40', '2025-06-09 15:25:40'),
(3, 4, 10, 10, 10.00, 0.00, NULL, 0.00, 0.00, 'Pending', NULL, '2025-06-06', NULL, NULL, NULL, '2025-06-09 15:25:40', '2025-06-09 15:25:40'),
(4, 5, 8, 25, 10.00, 0.00, NULL, 0.00, 0.00, 'Pending', NULL, '2025-06-05', NULL, NULL, NULL, '2025-06-09 15:25:40', '2025-06-09 15:25:40'),
(5, 6, 9, 5, 10.00, 0.00, NULL, 0.00, 0.00, 'Pending', NULL, '2025-06-06', NULL, NULL, NULL, '2025-06-09 15:25:40', '2025-06-09 15:25:40'),
(7, 3, 10, 1, 20.00, 1.00, 19.00, 0.00, 0.00, 'Pending', NULL, '2025-06-09', 'chandra', '1234512345', 'fff', '2025-06-09 11:58:50', '2025-06-09 11:58:50'),
(9, 3, 9, 1, 20.00, 1.00, 19.00, 0.00, 0.00, 'Pending', NULL, '2025-06-08', 'chandra', '1234512345', 'oo', '2025-06-09 12:27:50', '2025-06-09 12:27:50'),
(10, 3, 9, 1, 20.00, 0.00, 20.00, 0.00, 0.00, 'Pending', NULL, '2025-06-09', 'gdf', '1111111111', 'o', '2025-06-09 12:27:50', '2025-06-09 12:27:50'),
(11, 3, 14, 50, 150.00, 0.00, 7500.00, 7500.00, 0.00, 'Paid', '2025-06-14', '2025-06-13', 'redy', '9705605208', 'e', '2025-06-13 09:46:58', '2025-06-14 05:51:10'),
(12, 3, 14, 60, 150.00, 50.00, 8950.00, 3950.00, 5000.00, 'Partial', '2025-06-14', '2025-06-13', 'mani', '1111111111', 'hb', '2025-06-13 09:46:58', '2025-06-14 06:01:51'),
(13, 3, 14, 35, 150.00, 10.00, 5240.00, 5240.00, 0.00, 'Paid', '2025-06-13', '2025-06-13', 'deVI', '3434343434', 'gnn', '2025-06-13 10:19:10', '2025-06-13 11:47:45'),
(14, 4, 8, 5, 25.00, 0.00, 125.00, 0.00, 125.00, 'Pending', NULL, '2025-06-14', 'surendra', '1234512345', 'gg', '2025-06-14 07:31:25', '2025-06-14 07:31:25'),
(15, 3, 8, 40, 150.00, 0.00, 6000.00, 0.00, 6000.00, 'Pending', NULL, '2025-06-14', 'ramu', '2323232324', 'df', '2025-06-14 08:04:19', '2025-06-14 08:04:19'),
(16, 4, 10, 10, 25.00, 0.00, 250.00, 0.00, 250.00, 'Pending', NULL, '2025-06-14', 'zedda', '1234512345', 'ff', '2025-06-14 08:05:26', '2025-06-14 08:05:26'),
(17, 3, 12, 5, 150.00, 0.00, 750.00, 0.00, 750.00, 'Pending', NULL, '2025-06-14', 'asha', '1234512345', 'fr', '2025-06-14 08:06:23', '2025-06-14 08:06:23'),
(18, 3, 8, 2, 150.00, 0.00, 300.00, 0.00, 300.00, 'Pending', NULL, '2025-06-16', 'ramana', '1234512345', 'ff', '2025-06-16 11:40:29', '2025-06-16 11:40:29');

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
(1, 12, '2025-06-14', 555.00, '', '', '2025-06-14 04:54:47', '2025-06-14 04:54:47'),
(2, 12, '2025-06-14', 555.00, '', '', '2025-06-14 04:58:33', '2025-06-14 04:58:33'),
(3, 11, '2025-06-14', 500.00, '', '', '2025-06-14 05:16:02', '2025-06-14 05:16:02'),
(4, 11, '2025-06-14', 2000.00, '', '', '2025-06-14 05:17:32', '2025-06-14 05:17:32'),
(5, 11, '2025-06-14', 5000.00, '', '', '2025-06-14 05:51:10', '2025-06-14 05:51:10'),
(6, 12, '2025-06-14', 840.00, '', '', '2025-06-14 05:53:23', '2025-06-14 05:53:23'),
(7, 12, '2025-06-14', 840.00, '', '', '2025-06-14 05:53:43', '2025-06-14 05:53:43'),
(8, 12, '2025-06-14', 160.00, '', '', '2025-06-14 05:54:08', '2025-06-14 05:54:08'),
(9, 12, '2025-06-14', 300.00, '', '', '2025-06-14 06:01:43', '2025-06-14 06:01:43'),
(10, 12, '2025-06-14', 700.00, '', '', '2025-06-14 06:01:51', '2025-06-14 06:01:51');

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
(1, 'distributor_custom_id', 4, '2025-06-24 05:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `current_quantity` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `total_amount_before_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_rate_id` int(11) DEFAULT NULL,
  `gst_amount` decimal(10,2) DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `amount_pending` decimal(10,2) DEFAULT NULL,
  `date_received` date NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`id`, `product_id`, `quantity`, `current_quantity`, `vendor_id`, `purchase_price`, `total_amount_before_gst`, `gst_rate_id`, `gst_amount`, `grand_total`, `amount_paid`, `amount_pending`, `date_received`, `notes`) VALUES
(3, 4, 30, NULL, NULL, NULL, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-05', ''),
(4, 5, 50, NULL, NULL, NULL, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-05', ''),
(5, 6, 100, NULL, NULL, NULL, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-06', ''),
(6, 5, 1000, NULL, NULL, NULL, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-07', ''),
(7, 6, 100, NULL, NULL, NULL, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-07', ''),
(8, 3, 222, NULL, 1, 10.00, 0.00, NULL, NULL, 0.00, NULL, NULL, '2025-06-07', ''),
(9, 3, 1000, NULL, 1, 10.00, 0.00, 1, 1200.00, 0.00, 5000.00, 6200.00, '2025-06-11', ''),
(10, 4, 10, NULL, 2, 20.00, 0.00, 1, 24.00, 0.00, 0.00, 224.00, '2025-06-11', ''),
(12, 6, 50, NULL, 2, 20.00, 0.00, 1, 120.00, 0.00, 200.00, 920.00, '2025-06-11', ''),
(13, 5, 55, 55, 2, 1.00, 55.00, 1, 6.60, 61.60, 61.60, 0.00, '2025-06-11', ''),
(14, 6, 100, 100, 3, 5.00, 500.00, 1, 60.00, 560.00, NULL, NULL, '2025-06-11', ''),
(15, 6, 100, 100, 3, 5.00, 500.00, 1, 60.00, 560.00, NULL, NULL, '2025-06-11', ''),
(16, 6, 50, 50, 3, 1.00, 50.00, 1, 6.00, 56.00, NULL, NULL, '2025-06-11', ''),
(17, 6, 70, 70, 3, 1.00, 70.00, 1, 8.40, 78.40, NULL, NULL, '2025-06-11', ''),
(18, 6, 14, 14, 3, 1.00, 14.00, 1, 1.68, 15.68, NULL, NULL, '2025-06-11', ''),
(19, 6, 14, 14, 3, 1.00, 14.00, 1, 1.68, 15.68, NULL, NULL, '2025-06-11', ''),
(20, 6, 30, 30, 3, 1.00, 30.00, 1, 3.60, 33.60, NULL, NULL, '2025-06-11', ''),
(21, 6, 30, 30, 3, 1.00, 30.00, 1, 3.60, 33.60, NULL, NULL, '2025-06-11', ''),
(22, 6, 44, 44, 3, 2.00, 88.00, 1, 10.56, 98.56, NULL, NULL, '2025-06-11', ''),
(23, 6, 18, 188, 3, 2.00, 36.00, 1, NULL, 40.32, 2.00, 38.32, '2025-06-11', ''),
(24, 4, 20, 200, 2, 20.00, 400.00, 1, NULL, 448.00, 4000.00, -3552.00, '2025-06-11', ''),
(25, 4, 500, 500, 2, 5.00, 2500.00, 1, 300.00, 2800.00, 800.00, 2000.00, '2025-06-11', ''),
(26, 6, 7000, 7000, 1, 7.00, 49000.00, 1, 5880.00, 54880.00, 7777.00, 47103.00, '2025-06-11', ''),
(27, 5, 500, 500, 1, 20.00, 10000.00, 1, 1200.00, 11200.00, 12.00, 11188.00, '2025-06-11', ''),
(28, 6, 3000, 3000, 1, 3.00, 9000.00, 2, 1620.00, 10620.00, 5000.00, 5620.00, '2025-06-11', ''),
(29, 5, 444, 444, 1, 4.00, 1776.00, 1, 213.12, 1989.12, 720.00, 1269.12, '2025-06-11', ''),
(34, 4, 33, 33, 1, 3.00, 99.00, 1, 11.88, 110.88, 3.00, 107.88, '2025-06-11', ''),
(35, 4, 500, 500, 2, 20.00, 10000.00, 1, 1200.00, 11200.00, 10000.00, 11200.00, '2025-06-14', ''),
(36, 4, 500, 500, 3, 5.00, 2500.00, 1, 300.00, 2800.00, 800.00, 2800.00, '2025-06-14', ''),
(37, 3, 1000, 1000, 1, 12.00, 12000.00, 1, 1440.00, 13440.00, 13000.00, 13440.00, '2025-06-14', ''),
(38, 4, 500, 500, 1, 20.00, 10000.00, 1, 1200.00, 11200.00, 1100.00, 11200.00, '2025-06-14', ''),
(39, 5, 1000, 1000, 1, 20.00, 20000.00, 1, 2400.00, 22400.00, 2200.00, 22400.00, '2025-06-14', ''),
(42, 6, 3000, 3000, 1, 20.00, 60000.00, 1, 7200.00, 67200.00, 50000.00, 67200.00, '2025-06-14', ''),
(43, 3, 777, 777, 1, 7.00, 5439.00, 1, 652.68, 6091.68, 6000.00, 6091.68, '2025-06-16', ''),
(44, 4, 8889, 8889, 1, 8.00, 71112.00, 1, 8533.44, 79645.44, 7000.00, 72645.44, '2025-06-16', ''),
(46, 6, 999, 999, 1, 9.00, 8991.00, 1, 1078.92, 10069.92, 9999.00, 10069.92, '2025-06-16', ''),
(47, 6, 111, 111, 1, 1.00, 111.00, 1, 13.32, 124.32, 111.00, 124.32, '2025-06-16', ''),
(48, 5, 111, 111, 1, 11.00, 1221.00, 1, 146.52, 1367.52, 1111.00, 1367.52, '2025-06-16', ''),
(49, 3, 50, 50, 1, 5.00, 250.00, 1, 30.00, 280.00, 200.00, 280.00, '2025-06-16', ''),
(50, 6, 222, 222, 1, 2.00, 444.00, 1, 53.28, 497.28, 222.00, 497.28, '2025-06-16', ''),
(51, 5, 22, 22, 3, 20.00, 440.00, 1, 52.80, 492.80, 100.00, 392.80, '2025-06-16', ''),
(53, 3, 500, 500, 1, 20.00, 10000.00, 1, 1200.00, 11200.00, 400.00, 11000.00, '2025-06-24', ''),
(54, 7, 500, 500, 3, 20.00, 10000.00, 1, 1200.00, 11200.00, 1200.00, 11200.00, '2025-06-24', '');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in_payments`
--

CREATE TABLE `stock_in_payments` (
  `id` int(11) NOT NULL,
  `stock_in_id` int(11) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_in_payments`
--

INSERT INTO `stock_in_payments` (`id`, `stock_in_id`, `payment_amount`, `payment_date`, `notes`, `created_at`) VALUES
(1, 23, 2.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 16:23:28'),
(2, 24, 4000.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 16:24:06'),
(3, 25, 800.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:01:19'),
(4, 26, 7777.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:05:40'),
(5, 27, 12.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:10:51'),
(6, 28, 5000.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:14:53'),
(7, 29, 200.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:22:13'),
(8, 34, 3.00, '2025-06-11', 'Initial payment upon stock-in', '2025-06-11 11:24:18'),
(9, 29, 500.00, '2025-06-11', '', '2025-06-11 11:47:19'),
(12, 29, 20.00, '2025-06-11', '', '2025-06-11 12:10:50'),
(13, 35, 10000.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 07:32:42'),
(14, 36, 800.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 07:55:08'),
(15, 37, 13000.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 12:30:47'),
(16, 38, 1100.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 12:31:06'),
(17, 39, 2200.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 12:31:26'),
(18, 42, 50000.00, '2025-06-14', 'Initial payment upon stock-in', '2025-06-14 12:32:10'),
(19, 43, 6000.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:19:58'),
(20, 44, 7000.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:21:26'),
(21, 46, 9999.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:22:02'),
(22, 47, 111.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:22:22'),
(23, 48, 1111.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:23:13'),
(24, 49, 200.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:24:00'),
(25, 50, 222.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 09:24:36'),
(26, 51, 100.00, '2025-06-16', 'Initial payment upon stock-in', '2025-06-16 10:54:33'),
(28, 53, 200.00, '2025-06-24', 'Initial payment upon stock-in', '2025-06-24 05:16:59'),
(29, 53, 200.00, '2025-06-24', '', '2025-06-24 10:47:25'),
(30, 54, 1200.00, '2025-06-24', 'Initial payment upon stock-in', '2025-06-24 05:21:19');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_out` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL COMMENT 'e.g., marketing_distribution, direct_sale, damage_loss',
  `transaction_id` int(11) UNSIGNED DEFAULT NULL,
  `transaction_item_id` int(11) UNSIGNED DEFAULT NULL,
  `issued_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`id`, `product_id`, `quantity_out`, `transaction_type`, `transaction_id`, `transaction_item_id`, `issued_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 80, 'marketing_distribution', 8, NULL, '2025-06-12', '', '2025-06-12 09:48:08', '2025-06-12 10:32:33'),
(2, 3, 90, 'marketing_distribution', 9, NULL, '2025-06-12', '', '2025-06-12 09:49:22', '2025-06-12 10:32:15'),
(3, 4, 90, 'marketing_distribution', 10, NULL, '2025-06-13', '', '2025-06-12 10:10:25', '2025-06-12 10:53:07'),
(4, 4, 20, 'marketing_distribution', 11, NULL, '2025-06-12', '', '2025-06-12 11:30:06', '2025-06-12 11:30:06'),
(5, 4, 400, 'marketing_distribution', 12, NULL, '2025-06-14', '', '2025-06-14 07:55:32', '2025-06-14 07:55:32'),
(6, 3, 50, 'marketing_distribution', 13, NULL, '2025-06-14', '', '2025-06-14 08:03:52', '2025-06-14 08:03:52'),
(7, 3, 20, 'marketing_distribution', 14, NULL, '2025-06-14', '', '2025-06-14 08:05:58', '2025-06-14 08:05:58'),
(8, 3, 20, 'Sale', 14, NULL, '2025-06-14', '', '2025-06-14 12:06:29', '2025-06-14 12:06:29'),
(9, 3, 50, 'marketing_distribution', 15, NULL, '2025-06-16', '', '2025-06-16 12:09:57', '2025-06-16 12:09:57'),
(10, 3, 12, 'Damage', NULL, NULL, '2025-06-17', '', '2025-06-17 04:50:35', '2025-06-17 04:50:35'),
(11, 4, 11, 'Sample', NULL, NULL, '2025-06-17', '', '2025-06-17 04:56:25', '2025-06-17 04:56:25'),
(12, 5, 82, 'Internal Use', NULL, NULL, '2025-06-17', '', '2025-06-17 04:56:47', '2025-06-17 04:56:47'),
(13, 6, 20, 'Other', NULL, NULL, '2025-06-17', '', '2025-06-17 04:57:08', '2025-06-17 04:57:08'),
(14, 3, 15, 'distributor_sale', 10, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 10', '2025-06-24 04:34:19', '2025-06-24 04:34:19'),
(15, 3, 10, 'distributor_sale', 10, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 11 (New Item Added During Edit)', '2025-06-24 04:44:24', '2025-06-24 04:44:24'),
(16, 3, 20, 'distributor_sale', 10, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 12 (New Item Added During Edit)', '2025-06-24 04:45:14', '2025-06-24 04:45:14'),
(17, 3, 15, 'distributor_sale', 10, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 13 (New Item Added During Edit)', '2025-06-24 04:45:41', '2025-06-24 04:45:41'),
(20, 3, 5, 'distributor_sale', 11, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 14', '2025-06-24 05:10:25', '2025-06-24 05:10:25'),
(21, 3, 10, 'distributor_sale', 11, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00003, Item ID: 15 (New Item Added During Edit)', '2025-06-24 05:11:02', '2025-06-24 05:11:02'),
(24, 4, 870, 'distributor_sale', 13, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00005, Item ID: 17', '2025-06-24 05:27:07', '2025-06-24 05:27:07'),
(25, 4, 800, 'distributor_sale', 13, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00005, Item ID: 18 (New Item Added During Edit)', '2025-06-24 05:27:42', '2025-06-24 05:27:42'),
(26, 7, 50, 'distributor_sale', 14, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00005, Item ID: 19', '2025-06-24 05:41:55', '2025-06-24 05:41:55'),
(27, 7, 30, 'distributor_sale', 14, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00005, Item ID: 20 (New Item Added During Edit)', '2025-06-24 05:42:38', '2025-06-24 05:42:38'),
(28, 7, 60, 'distributor_sale', 14, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00005, Item ID: 21 (New Item Added During Edit)', '2025-06-24 05:42:55', '2025-06-24 05:42:55'),
(34, 3, 23, 'distributor_sale', 17, NULL, '2025-06-24', 'Distributor Sale for Invoice INV-20250624-00004, Item ID: 27', '2025-06-24 06:10:00', '2025-06-24 06:10:00'),
(35, 7, 1, 'Damage', NULL, NULL, '2025-06-24', '', '2025-06-24 07:02:53', '2025-06-24 07:02:53');

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
(10, 'Tones', '2025-06-09 04:30:12', '2025-06-09 04:30:12');

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `owner_phone`, `agency_name`, `contact_person`, `contact_phone`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'Raja Ram', NULL, 'agri tech', 'mani', NULL, '9239423434', 'abhi@gmail.com', 'kkd', '2025-06-07 07:12:47'),
(2, 'shyam', NULL, 'Ever Green', 'shaym', NULL, '934959459', 'vgv@gmail.com', 'rjy\r\n', '2025-06-07 07:13:32'),
(3, 'mohan', '9789789778', 'south soils', 'ramu', '9872651', NULL, 'latha@gmail.com', 'kkd', '2025-06-07 10:51:15');

--
-- Indexes for dumped tables
--

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
  ADD KEY `fk_distributor_sales_orders_distributor_id` (`distributor_id`);

--
-- Indexes for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_distributor_sales_order_items_sales_order_id` (`distributor_sales_order_id`),
  ADD KEY `fk_distributor_sales_order_items_product_id` (`product_id`),
  ADD KEY `fk_distributor_sales_order_items_gst_rate_id` (`gst_rate_id`);

--
-- Indexes for table `gst_rates`
--
ALTER TABLE `gst_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `unit_id` (`unit_id`);

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
-- Indexes for table `sequences`
--
ALTER TABLE `sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_vendor` (`vendor_id`);

--
-- Indexes for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_in_payments_stock_in_id` (`stock_in_id`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_out_product` (`product_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `distributors`
--
ALTER TABLE `distributors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `gst_rates`
--
ALTER TABLE `gst_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `marketing_persons`
--
ALTER TABLE `marketing_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `sale_payments`
--
ALTER TABLE `sale_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sequences`
--
ALTER TABLE `sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  ADD CONSTRAINT `fk_distributor_payments_sales_order_id` FOREIGN KEY (`distributor_sales_order_id`) REFERENCES `distributor_sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  ADD CONSTRAINT `fk_distributor_sales_orders_distributor_id` FOREIGN KEY (`distributor_id`) REFERENCES `distributors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  ADD CONSTRAINT `fk_distributor_sales_order_items_gst_rate_id` FOREIGN KEY (`gst_rate_id`) REFERENCES `gst_rates` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distributor_sales_order_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_distributor_sales_order_items_sales_order_id` FOREIGN KEY (`distributor_sales_order_id`) REFERENCES `distributor_sales_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  ADD CONSTRAINT `marketing_distribution_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marketing_distribution_ibfk_2` FOREIGN KEY (`marketing_person_id`) REFERENCES `marketing_persons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`marketing_person_id`) REFERENCES `marketing_persons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD CONSTRAINT `fk_sale_id` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD CONSTRAINT `fk_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  ADD CONSTRAINT `stock_in_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  ADD CONSTRAINT `fk_stock_in_payments_stock_in_id` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD CONSTRAINT `fk_stock_out_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
