-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 09:06 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lab_automation`
--

-- --------------------------------------------------------

--
-- Table structure for table `financial_tracking`
--

CREATE TABLE `financial_tracking` (
  `cost_id` int(11) NOT NULL,
  `record_id_fk` int(11) NOT NULL,
  `product_code` varchar(10) NOT NULL,
  `testing_id` varchar(12) NOT NULL,
  `result` enum('Passed','Failed') NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') NOT NULL,
  `tester_name` varchar(100) DEFAULT NULL,
  `random_cost` int(11) NOT NULL,
  `approved_by_cpri` int(11) DEFAULT NULL,
  `checking_manager` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `financial_tracking`
--

INSERT INTO `financial_tracking` (`cost_id`, `record_id_fk`, `product_code`, `testing_id`, `result`, `approval_status`, `tester_name`, `random_cost`, `approved_by_cpri`, `checking_manager`, `created_at`, `updated_at`) VALUES
(10, 1, '0020100001', '002010000117', 'Passed', 'Approved', 'Lab Tester 01', 1500, NULL, NULL, '2025-12-02 19:36:28', '2025-12-02 19:36:28'),
(11, 2, '0020100002', '002010000218', 'Failed', 'Rejected', 'Lab Tester 02', 2200, NULL, NULL, '2025-12-02 19:36:28', '2025-12-02 19:36:28'),
(12, 3, '0020200003', '002020000319', 'Passed', 'Approved', 'Lab Tester 03', 1800, NULL, NULL, '2025-12-02 19:36:28', '2025-12-02 19:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `status`) VALUES
(1, 4, '2025-12-02 19:36:59', 'Pending'),
(2, 4, '2025-12-02 19:36:59', 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_code` varchar(10) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_code`, `quantity`, `price`) VALUES
(4, 1, '0020100001', 2, '1200.00'),
(5, 1, '0020100002', 1, '2400.00'),
(6, 2, '0020200003', 3, '900.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_code` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_img` varchar(255) DEFAULT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `revise_number` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_code`, `product_name`, `product_img`, `manufacturer_id`, `revise_number`, `created_by`, `created_at`, `updated_at`) VALUES
('0020100001', 'High Voltage Fuse', 'images/fuse.jpg', 2, 1, 1, '2025-12-02 19:11:36', '2025-12-02 19:11:36'),
('0020100002', 'Industrial Capacitor', 'images/capacitor.jpg', 2, 1, 1, '2025-12-02 19:11:36', '2025-12-02 19:11:36'),
('0020200003', 'Heavy Duty Resistor', 'images/resistor.jpg', 2, 2, 1, '2025-12-02 19:11:36', '2025-12-02 19:11:36');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_product_code` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
    INSERT INTO product_counter VALUES (NULL);

    SET NEW.product_code = CONCAT(
        LPAD(NEW.manufacturer_id, 3, '0'),
        LPAD(NEW.revise_number, 2, '0'),
        LPAD(LAST_INSERT_ID(), 5, '0')
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_counter`
--

CREATE TABLE `product_counter` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_counter`
--

INSERT INTO `product_counter` (`id`) VALUES
(1),
(2),
(3);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'System Administrator with full access', '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(2, 'Manufacturer', 'Product manufacturer role', '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(3, 'CPRI', 'CPRI Testing Authority', '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(4, 'Customer', 'End user customer who purchases products', '2025-12-02 18:58:03', '2025-12-02 18:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `testing_type`
--

CREATE TABLE `testing_type` (
  `test_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `test_code` varchar(10) NOT NULL,
  `is_modular` tinyint(1) DEFAULT 1,
  `parent_type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `testing_type`
--

INSERT INTO `testing_type` (`test_type_id`, `type_name`, `test_code`, `is_modular`, `parent_type_id`, `created_at`, `updated_at`) VALUES
(1, 'Thermal Test', 'THR01', 1, NULL, '2025-12-02 19:11:35', '2025-12-02 19:11:35'),
(2, 'Mechanical Strength Test', 'MEC01', 1, NULL, '2025-12-02 19:11:35', '2025-12-02 19:11:35'),
(3, 'Insulation Test', 'INS01', 1, NULL, '2025-12-02 19:11:35', '2025-12-02 19:11:35'),
(4, 'Thermal Shock Test', 'THR02', 0, 1, '2025-12-02 19:11:36', '2025-12-02 19:11:36'),
(5, 'Heat Endurance Test', 'THR03', 0, 1, '2025-12-02 19:11:36', '2025-12-02 19:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `test_counter`
--

CREATE TABLE `test_counter` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `test_counter`
--

INSERT INTO `test_counter` (`id`) VALUES
(17),
(18),
(19);

-- --------------------------------------------------------

--
-- Table structure for table `test_records`
--

CREATE TABLE `test_records` (
  `record_id` int(11) NOT NULL,
  `testing_id` varchar(12) NOT NULL,
  `product_id_fk` varchar(10) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `tester_user_id` int(11) NOT NULL,
  `test_result` enum('Passed','Failed') NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `tester_remarks` text DEFAULT NULL,
  `manager_remarks` text DEFAULT NULL,
  `validated_by_user_id` int(11) DEFAULT NULL,
  `validation_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `test_records`
--

INSERT INTO `test_records` (`record_id`, `testing_id`, `product_id_fk`, `test_type_id`, `test_date`, `tester_user_id`, `test_result`, `approval_status`, `tester_remarks`, `manager_remarks`, `validated_by_user_id`, `validation_date`, `created_at`, `updated_at`) VALUES
(1, '002010000117', '0020100001', 1, '2025-12-01', 2, 'Passed', 'Approved', 'Thermal parameters stable.', NULL, 3, NULL, '2025-12-02 19:30:52', '2025-12-02 19:35:51'),
(2, '002010000218', '0020100002', 2, '2025-12-02', 2, 'Failed', 'Rejected', 'Mechanical strength below threshold.', NULL, 3, NULL, '2025-12-02 19:30:52', '2025-12-02 19:36:02'),
(3, '002020000319', '0020200003', 3, '2025-12-03', 2, 'Passed', 'Approved', 'Insulation resistance within limits.', NULL, 3, NULL, '2025-12-02 19:30:52', '2025-12-02 19:36:12');

--
-- Triggers `test_records`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_testing_id` BEFORE INSERT ON `test_records` FOR EACH ROW BEGIN
    INSERT INTO test_counter VALUES (NULL);

    SET NEW.testing_id = CONCAT(
        NEW.product_id_fk,
        LPAD(LAST_INSERT_ID(), 2, '0')
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password_hash`, `role_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin', 'admin123', 1, 1, '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(2, 'Manufacture', 'mfg01', 'maf123', 2, 1, '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(3, 'CPRI Testing Lab', 'cpri01', 'cpri123', 3, 1, '2025-12-02 18:58:03', '2025-12-02 18:58:03'),
(4, 'Ali Khan', 'ali', 'cust123', 4, 1, '2025-12-02 18:58:03', '2025-12-02 18:58:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `financial_tracking`
--
ALTER TABLE `financial_tracking`
  ADD PRIMARY KEY (`cost_id`),
  ADD KEY `record_id_fk` (`record_id_fk`),
  ADD KEY `product_code` (`product_code`),
  ADD KEY `approved_by_cpri` (`approved_by_cpri`),
  ADD KEY `checking_manager` (`checking_manager`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_code` (`product_code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_code`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `product_counter`
--
ALTER TABLE `product_counter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `testing_type`
--
ALTER TABLE `testing_type`
  ADD PRIMARY KEY (`test_type_id`),
  ADD KEY `parent_type_id` (`parent_type_id`);

--
-- Indexes for table `test_counter`
--
ALTER TABLE `test_counter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_records`
--
ALTER TABLE `test_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `testing_id` (`testing_id`),
  ADD KEY `product_id_fk` (`product_id_fk`),
  ADD KEY `test_type_id` (`test_type_id`),
  ADD KEY `tester_user_id` (`tester_user_id`),
  ADD KEY `validated_by_user_id` (`validated_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `financial_tracking`
--
ALTER TABLE `financial_tracking`
  MODIFY `cost_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_counter`
--
ALTER TABLE `product_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `testing_type`
--
ALTER TABLE `testing_type`
  MODIFY `test_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `test_counter`
--
ALTER TABLE `test_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `test_records`
--
ALTER TABLE `test_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `financial_tracking`
--
ALTER TABLE `financial_tracking`
  ADD CONSTRAINT `financial_tracking_ibfk_1` FOREIGN KEY (`record_id_fk`) REFERENCES `test_records` (`record_id`),
  ADD CONSTRAINT `financial_tracking_ibfk_2` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`),
  ADD CONSTRAINT `financial_tracking_ibfk_3` FOREIGN KEY (`approved_by_cpri`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `financial_tracking_ibfk_4` FOREIGN KEY (`checking_manager`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_code`) REFERENCES `products` (`product_code`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `testing_type`
--
ALTER TABLE `testing_type`
  ADD CONSTRAINT `testing_type_ibfk_1` FOREIGN KEY (`parent_type_id`) REFERENCES `testing_type` (`test_type_id`);

--
-- Constraints for table `test_records`
--
ALTER TABLE `test_records`
  ADD CONSTRAINT `test_records_ibfk_1` FOREIGN KEY (`product_id_fk`) REFERENCES `products` (`product_code`),
  ADD CONSTRAINT `test_records_ibfk_2` FOREIGN KEY (`test_type_id`) REFERENCES `testing_type` (`test_type_id`),
  ADD CONSTRAINT `test_records_ibfk_3` FOREIGN KEY (`tester_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `test_records_ibfk_4` FOREIGN KEY (`validated_by_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
