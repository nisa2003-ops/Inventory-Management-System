-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 12:35 PM
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
-- Database: `inventory_store`
--
CREATE DATABASE IF NOT EXISTS `inventory_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `inventory_store`;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `category_id` varchar(10) NOT NULL,
  `category_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` VALUES
('BK', 'Bakery'),
('BV', 'Beverages'),
('CL', 'Cleaning Supplies'),
('DR', 'Dairy Products'),
('FD', 'Foods'),
('FR', 'Fruits'),
('MT', 'Meat & Seafood'),
('PH', 'Personal Care'),
('SN', 'Snacks'),
('STA', 'Stationary'),
('VG', 'Vegetables');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_email` varchar(30) DEFAULT NULL,
  `customer_phone` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `product_id` varchar(10) NOT NULL,
  `category_id` varchar(10) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_name` varchar(40) NOT NULL,
  `price` decimal(6,2) DEFAULT NULL,
  `unit` varchar(10) DEFAULT NULL,
  `current_stock` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` VALUES
('PR101', 'FR', 2, 'Apple', 120.00, 'Kg', 80),
('PR102', 'FR', 2, 'Banana', 60.00, 'Kg', 120),
('PR103', 'VG', 2, 'Carrot', 90.00, 'Kg', 69),
('PR104', 'VG', 2, 'Potato', 75.00, 'Kg', 200),
('PR105', 'DR', 2, 'Fresh Milk', 450.00, 'Liter', 50),
('PR106', 'DR', 2, 'Cheese', 980.00, 'Pack', 40),
('PR107', 'MT', 2, 'Chicken Breast', 1350.00, 'Kg', 30),
('PR108', 'MT', 2, 'Fish (Tuna)', 1600.00, 'Kg', 25),
('PR109', 'BK', 2, 'Bread Loaf', 160.00, 'Piece', 100),
('PR110', 'BK', 2, 'Butter Cake', 750.00, 'Piece', 18),
('PR111', 'BV', 2, 'Orange Juice', 320.00, 'Bottle', 60),
('PR112', 'BV', 2, 'Mineral Water', 120.00, 'Bottle', 200),
('PR113', 'SN', 2, 'Potato Chips', 180.00, 'Pack', 150),
('PR114', 'SN', 2, 'Chocolate Bar', 250.00, 'Piece', 90),
('PR115', 'CL', 2, 'Dishwashing Liquid', 420.00, 'Bottle', 40),
('PR116', 'CL', 2, 'Laundry Detergent', 890.00, 'Pack', 35),
('PR117', 'PH', 2, 'Toothpaste', 380.00, 'Tube', 70),
('PR118', 'PH', 2, 'Bath Soap', 160.00, 'Bar', 115);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE `purchase_order` (
  `purchase_id` int(11) NOT NULL,
  `sup_id` int(11) DEFAULT NULL,
  `product_id` varchar(10) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `total_amount` decimal(7,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_item`
--

DROP TABLE IF EXISTS `sales_item`;
CREATE TABLE `sales_item` (
  `order_id` int(11) NOT NULL,
  `salesItem_id` int(11) NOT NULL,
  `unit_price` decimal(6,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`unit_price` * `quantity`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_item`
--

INSERT INTO `sales_item` VALUES
(0, 1, 100.00, 20),
(3, 3, 750.00, 2),
(3, 4, 90.00, 1),
(3, 5, 160.00, 5);

-- --------------------------------------------------------

--
-- Table structure for table `sales_order`
--

DROP TABLE IF EXISTS `sales_order`;
CREATE TABLE `sales_order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sales_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_order`
--

INSERT INTO `sales_order` VALUES
(3, NULL, 3, '2026-01-27');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
CREATE TABLE `supplier` (
  `sup_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone_no` char(10) DEFAULT NULL,
  `address` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplies`
--

DROP TABLE IF EXISTS `supplies`;
CREATE TABLE `supplies` (
  `sup_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `user_role` enum('Admin','Manager','Cashier') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` VALUES
(1, 'admin01', 'admin123', 'Admin'),
(2, 'manager01', 'manager123', 'Manager'),
(3, 'cashier01', 'cashier123', 'Cashier');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `sup_id` (`sup_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_item`
--
ALTER TABLE `sales_item`
  ADD PRIMARY KEY (`salesItem_id`,`order_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`sup_id`);

--
-- Indexes for table `supplies`
--
ALTER TABLE `supplies`
  ADD PRIMARY KEY (`sup_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales_item`
--
ALTER TABLE `sales_item`
  MODIFY `salesItem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sales_order`
--
ALTER TABLE `sales_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `sup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`sup_id`) REFERENCES `supplier` (`sup_id`),
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `sales_item`
--
ALTER TABLE `sales_item`
  ADD CONSTRAINT `sales_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `sales_order` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD CONSTRAINT `sales_order_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `sales_order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `supplies`
--
ALTER TABLE `supplies`
  ADD CONSTRAINT `supplies_ibfk_1` FOREIGN KEY (`sup_id`) REFERENCES `supplier` (`sup_id`),
  ADD CONSTRAINT `supplies_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
