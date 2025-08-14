-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 05:17 PM
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distributors`
--
ALTER TABLE `distributors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distributor_payments`
--
ALTER TABLE `distributor_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distributor_sales_orders`
--
ALTER TABLE `distributor_sales_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distributor_sales_order_items`
--
ALTER TABLE `distributor_sales_order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eway_bills`
--
ALTER TABLE `eway_bills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gst_rates`
--
ALTER TABLE `gst_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketing_distribution`
--
ALTER TABLE `marketing_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketing_persons`
--
ALTER TABLE `marketing_persons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchased_products`
--
ALTER TABLE `purchased_products`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_payments`
--
ALTER TABLE `sale_payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `selling_products`
--
ALTER TABLE `selling_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sequences`
--
ALTER TABLE `sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_consumption`
--
ALTER TABLE `stock_consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_in_gst`
--
ALTER TABLE `stock_in_gst`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_in_payments`
--
ALTER TABLE `stock_in_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_in_products`
--
ALTER TABLE `stock_in_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
