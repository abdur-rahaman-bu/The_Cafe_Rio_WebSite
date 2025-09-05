-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 09:01 PM
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
-- Database: `caferio`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category` varchar(60) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `description`, `price`, `category`, `image`, `status`, `created_at`, `updated_at`) VALUES
(16, 'Phitron', '', 100.00, 'coding', 'menu-16-1756923286-20250903201446-5787909a.jpg', 'available', '2025-09-03 18:14:46', '2025-09-03 18:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `status`, `total_amount`, `payment_method`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'confirmed', 1070.00, 'cod', 'paid', '', '2025-09-02 10:41:55', '2025-09-02 20:16:17'),
(2, 4, 'pending', 650.00, 'cod', 'unpaid', '', '2025-09-02 11:20:48', '2025-09-02 11:20:48'),
(3, 4, 'pending', 0.00, 'cod', 'unpaid', '', '2025-09-02 11:21:03', '2025-09-02 17:05:01'),
(4, 1, 'confirmed', 400000.00, 'cod', 'paid', '', '2025-09-02 19:43:53', '2025-09-02 20:16:20'),
(5, 1, 'confirmed', 400000.00, 'cod', 'paid', '', '2025-09-02 19:51:20', '2025-09-02 20:16:21'),
(6, 1, 'confirmed', 260.00, 'cod', 'paid', '', '2025-09-02 19:54:54', '2025-09-02 20:16:10'),
(7, 1, 'confirmed', 520.00, 'bkash', 'paid', '', '2025-09-02 19:59:37', '2025-09-02 20:16:05'),
(8, 1, 'confirmed', 420.00, 'cod', 'paid', '', '2025-09-02 20:16:33', '2025-09-02 20:16:38'),
(9, 1, 'pending', 260.00, 'cod', 'unpaid', '', '2025-09-02 20:16:54', '2025-09-02 20:16:54'),
(10, 1, 'pending', 260.00, 'cod', 'unpaid', '', '2025-09-02 20:17:07', '2025-09-02 20:17:07'),
(11, 1, 'pending', 420.00, 'cod', 'unpaid', '', '2025-09-02 20:19:21', '2025-09-02 20:19:21'),
(12, 1, 'confirmed', 200.00, 'cod', 'paid', '', '2025-09-03 18:17:25', '2025-09-03 18:17:27'),
(13, 5, 'confirmed', 200.00, 'cod', 'paid', '', '2025-09-03 18:31:11', '2025-09-03 18:31:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` int(11) NOT NULL DEFAULT 1,
  `line_total` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `name`, `unit_price`, `qty`, `line_total`) VALUES
(1, 1, 1, 'Chicken BBQ Pizza', 650.00, 1, 650.00),
(2, 1, 2, 'Creamy Pasta', 420.00, 1, 420.00),
(3, 2, 1, 'Chicken BBQ Pizza', 650.00, 1, 650.00),
(4, 4, 4, '', 100000.00, 4, 0.00),
(5, 5, 4, 'yeatasim', 100000.00, 4, 400000.00),
(6, 6, 3, 'Garden Salad', 260.00, 1, 260.00),
(7, 7, 3, 'Garden Salad', 260.00, 2, 520.00),
(8, 8, 2, 'Creamy Pasta', 420.00, 1, 420.00),
(9, 9, 3, 'Garden Salad', 260.00, 1, 260.00),
(10, 10, 3, 'Garden Salad', 260.00, 1, 260.00),
(11, 11, 2, 'Creamy Pasta', 420.00, 1, 420.00),
(12, 12, 16, 'fff', 100.00, 2, 200.00),
(13, 13, 16, 'Phitron', 100.00, 2, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 90,
  `people_count` int(11) NOT NULL,
  `table_type` enum('family','couple','window') NOT NULL DEFAULT 'family',
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `special_request` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `user_id`, `reservation_date`, `reservation_time`, `duration_minutes`, `people_count`, `table_type`, `status`, `special_request`, `created_at`, `updated_at`) VALUES
(1, 4, '2025-09-02', '17:30:00', 90, 2, '', 'pending', '', '2025-09-02 11:26:52', '2025-09-02 11:26:52'),
(2, 4, '2025-09-02', '17:30:00', 90, 2, '', 'pending', '', '2025-09-02 11:27:20', '2025-09-02 11:27:20'),
(3, 1, '2025-09-03', '01:30:00', 90, 2, '', 'pending', '', '2025-09-02 19:19:00', '2025-09-02 19:19:00'),
(4, 1, '2025-09-03', '01:30:00', 60, 2, '', 'confirmed', '', '2025-09-02 19:22:46', '2025-09-02 19:24:38'),
(5, 1, '2025-09-03', '03:00:00', 90, 2, '', 'pending', '', '2025-09-02 19:39:50', '2025-09-02 19:39:50'),
(6, 1, '2025-09-03', '02:30:00', 90, 2, '', 'confirmed', '', '2025-09-02 20:26:02', '2025-09-02 20:26:02'),
(7, 1, '2025-09-03', '00:00:00', 90, 2, '', 'confirmed', '', '2025-09-03 17:34:23', '2025-09-03 17:34:23'),
(8, 1, '2025-09-04', '00:30:00', 90, 2, '', 'confirmed', '', '2025-09-03 18:20:27', '2025-09-03 18:20:27'),
(9, 1, '2025-09-04', '00:30:00', 90, 2, '', 'confirmed', '', '2025-09-03 18:21:03', '2025-09-03 18:21:03');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_tables`
--

CREATE TABLE `reservation_tables` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `from_time` time NOT NULL,
  `to_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_tables`
--

INSERT INTO `reservation_tables` (`id`, `reservation_id`, `table_id`, `from_time`, `to_time`) VALUES
(1, 1, 6, '17:30:00', '19:00:00'),
(2, 2, 3, '17:30:00', '19:00:00'),
(3, 3, 1, '01:30:00', '03:00:00'),
(4, 4, 2, '01:30:00', '02:30:00'),
(5, 5, 2, '03:00:00', '04:30:00'),
(6, 6, 3, '02:30:00', '04:00:00'),
(7, 7, 1, '00:00:00', '01:30:00'),
(8, 8, 2, '00:30:00', '02:00:00'),
(9, 9, 6, '00:30:00', '02:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` varchar(400) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `item_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, NULL, 4, '', '2025-09-03 17:40:29'),
(2, 1, 16, 3, 'খাবার ভালো না', '2025-09-03 18:18:43'),
(3, 5, 16, 5, '', '2025-09-03 18:31:56'),
(4, 6, 16, 5, '', '2025-09-03 18:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`table_id`, `name`, `capacity`, `zone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'T1', 2, 'Couple', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(2, 'T2', 2, 'Couple', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(3, 'T3', 4, 'Family', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(4, 'T4', 4, 'Family', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(5, 'T5', 6, 'Family', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(6, 'W1', 2, 'Window', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27'),
(7, 'W2', 4, 'Window', 'active', '2025-09-02 10:00:27', '2025-09-02 10:00:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'yeatasim', 'shahin@gmail.com', '$2y$10$6TIZT6x9.G7Qz.HoYLhN0uG1MuNWDSDP0r5ey3KZBlzUUnIFzKp3q', 'admin', '2025-09-02 10:37:22', '2025-09-02 11:05:23'),
(4, 'yeatasim', 'shahin12@gmail.com', '$2y$10$2PuGh0/Jv3MlfABr74o2H.zox4wCkTKHxBYfupb9rdhEH9r3OGkWS', 'user', '2025-09-02 11:20:36', '2025-09-02 11:20:36'),
(5, 'fffffff', 'shahin121@gmail.com', '$2y$10$ccZS64YcpCvLJhxxKf2CwumQYjAuc/xwFWzvk57oMEJR9jFto.woK', 'user', '2025-09-03 18:23:54', '2025-09-03 18:23:54'),
(6, 'ffffffff', 'ffffffff@gmail.com', '$2y$10$Bzx5PKtFhJkfJ1F/H/zq2.rGD9u5cKB5cAszApd1UC3P2nSC4L0L2', 'user', '2025-09-03 18:51:26', '2025-09-03 18:51:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_menu_status` (`status`),
  ADD KEY `idx_menu_category` (`category`),
  ADD KEY `idx_menu_price` (`price`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oi_order` (`order_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `idx_res_date_status` (`reservation_date`,`status`),
  ADD KEY `idx_res_user` (`user_id`);

--
-- Indexes for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rt_res` (`reservation_id`),
  ADD KEY `idx_rt_tbl_range` (`table_id`,`from_time`,`to_time`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_reviews_item` (`item_id`),
  ADD KEY `idx_reviews_user` (`user_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_id`),
  ADD KEY `idx_tables_status` (`status`),
  ADD KEY `idx_tables_capacity` (`capacity`),
  ADD KEY `idx_tables_zone` (`zone`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `reservation_tables`
--
ALTER TABLE `reservation_tables`
  ADD CONSTRAINT `fk_rt_res` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rt_tbl` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
