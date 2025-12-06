-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2025 at 07:02 AM
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
-- Database: `smartbiz_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_id`, `name`, `cost_price`, `selling_price`, `quantity`, `created_at`) VALUES
(44, NULL, 'Product1', 1000000.00, 1600000.00, 7, '2025-10-13 09:05:07'),
(45, NULL, 'Product2', 600000.00, 1000000.00, 3, '2025-10-13 09:05:28'),
(46, NULL, 'Product3', 15000.00, 22000.00, 24, '2025-10-13 09:05:53'),
(47, NULL, 'Hp computers', 200000.00, 340000.00, 5, '2025-10-27 13:13:52'),
(48, NULL, 'Lenovo ThinkPad i5', 350000.00, 450000.00, 3, '2025-11-29 14:58:51');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `quantity_sold` int(11) NOT NULL,
  `total_sale` decimal(12,2) NOT NULL,
  `gain` decimal(12,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `company_id`, `product_id`, `customer_name`, `quantity_sold`, `total_sale`, `gain`, `date`, `created_at`) VALUES
(37, NULL, 46, 'Eddy', 16, 352000.00, 112000.00, '2025-10-13 09:06:41', '2025-10-13 09:06:41'),
(38, NULL, 44, 'NTABWOBA', 3, 4800000.00, 1800000.00, '2025-10-13 09:06:52', '2025-10-13 09:06:52'),
(39, NULL, 45, '', 4, 4000000.00, 1600000.00, '2025-10-13 09:07:16', '2025-10-13 09:07:16'),
(40, NULL, 47, 'Tresor', 2, 680000.00, 280000.00, '2025-10-27 13:14:45', '2025-10-27 13:14:45'),
(41, NULL, 48, 'Tuyishime', 2, 900000.00, 200000.00, '2025-11-29 14:59:24', '2025-11-29 14:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','developer') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `full_name` varchar(26) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.png',
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(36) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `payment_reference` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `payment_amount` decimal(10,2) DEFAULT 5000.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `full_name`, `dob`, `profile_photo`, `phone`, `email`, `status`, `payment_reference`, `payment_status`, `payment_amount`) VALUES
(1, 'developer', '$2y$10$j749I4dkBnDAO607kc2GHuqfg.q0f7s2hkCgkNnCkmns6HI9tAreO', 'developer', '2025-10-13 09:04:06', 'Etienne NGUWENEZA', '2004-02-06', '1760346245_foto.jpg', '0784133255', 'kanyetienne42@gmail.com', 'inactive', NULL, 'pending', 5000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_company` (`company_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_sales_company` (`company_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
