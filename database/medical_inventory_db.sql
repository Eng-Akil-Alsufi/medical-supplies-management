-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 10:41 PM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medical_inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 10,
  `department` varchar(100) DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `name`, `batch_number`, `quantity`, `min_quantity`, `department`, `expiry_date`, `location`, `barcode`, `notes`, `supplier_id`, `category`, `unit_price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'باراسيتامول 500mg', 'BATCH-001', 500, 50, 'الصيدلية العامة', '2026-06-15', 'الرف A1', 'BC-001', 'دواء خافض للحرارة', 1, 'مسكنات', 2.50, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41'),
(2, 'أموكسيسيلين 250mg', 'BATCH-002', 200, 30, 'الصيدلية العامة', '2025-12-20', 'الرف A2', 'BC-002', 'مضاد حيوي', 1, 'مضادات حيوية', 5.00, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41'),
(3, 'محقنات معقمة 5ml', 'BATCH-003', 1000, 100, 'الصيدلية', '2026-03-30', 'الرف B1', 'BC-003', 'محقنات طبية', 2, 'مستلزمات', 0.50, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41'),
(4, 'قطن طبي 500g', 'BATCH-004', 300, 50, 'المخزن الرئيسي', '2026-12-31', 'الرف C1', 'BC-004', 'مستلزمات تنظيف', 3, 'مستلزمات', 15.00, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41'),
(5, 'أسبيرين 100mg', 'BATCH-005', 150, 40, 'الصيدلية العامة', '2025-11-10', 'الرف A3', 'BC-005', 'مانع للتجلط', 1, 'مسكنات', 1.50, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41'),
(6, 'كحول طبي 70%', 'BATCH-006', 250, 30, 'المخزن الرئيسي', '2026-05-15', 'الرف D1', 'BC-006', 'معقم', 2, 'معقمات', 8.00, 1, '2025-11-24 21:38:41', '2025-11-24 21:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `log_date`) VALUES
(1, 1, 'تسجيل خروج', 'تسجيل خروج ناجح', '::1', '2025-11-24 21:39:51');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `notification_type` enum('low_stock','expiry_warning','request_update','system') NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `item_id`, `notification_type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 5, 'low_stock', 'تنبيه مخزون منخفض', 'المخزون من الأسبيرين وصل إلى 150 وحدة', 0, '2025-11-24 21:38:43'),
(2, 2, 2, 'expiry_warning', 'تنبيه انتهاء الصلاحية', 'صلاحية الأموكسيسيلين تنتهي بتاريخ 2025-12-20', 0, '2025-11-24 21:38:43'),
(3, 1, NULL, 'system', 'إشعار النظام', 'تم تحديث إعدادات النظام بنجاح', 1, '2025-11-24 21:38:43'),
(4, 2, 1, 'low_stock', 'تنبيه مخزون منخفض 2', 'مخزون الباراسيتامول انخفض', 0, '2025-11-24 21:38:43'),
(5, 1, 6, 'expiry_warning', 'تنبيه صلاحية', 'صلاحية الكحول الطبي تقترب', 0, '2025-11-24 21:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_requested` int(11) NOT NULL,
  `quantity_approved` int(11) DEFAULT 0,
  `requested_by` int(11) NOT NULL,
  `requested_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','fulfilled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `item_id`, `quantity_requested`, `quantity_approved`, `requested_by`, `requested_date`, `status`, `approved_by`, `approval_date`, `notes`) VALUES
(1, 1, 50, 50, 3, '2025-11-24 21:38:42', 'fulfilled', 2, '2025-11-25 00:38:42', 'تم صرف 50 قرص باراسيتامول'),
(2, 2, 30, 30, 3, '2025-11-24 21:38:42', 'approved', 2, '2025-11-25 00:38:42', 'موافقة على طلب 30 كبسولة أموكسيسيلين'),
(3, 3, 100, 0, 3, '2025-11-24 21:38:42', 'pending', NULL, NULL, 'طلب معلق: 100 محقنة'),
(4, 4, 25, 25, 3, '2025-11-24 21:38:42', 'fulfilled', 2, '2025-11-23 00:38:42', 'تم صرف 25 عبوة قطن');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'app_name', 'نظام إدارة المخزون الطبي', 'اسم التطبيق', '2025-11-24 21:38:43'),
(2, 'expiry_warning_days', '60', 'عدد الأيام لتحذير الصلاحية', '2025-11-24 21:38:43'),
(3, 'min_stock_alert', 'true', 'تفعيل تنبيهات المخزون المنخفض', '2025-11-24 21:38:43'),
(4, 'system_language', 'ar', 'لغة النظام', '2025-11-24 21:38:43');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `name`, `contact_person`, `phone`, `email`, `address`, `city`, `country`, `is_active`, `created_at`) VALUES
(1, 'شركة تبوك للصناعات الدوائية', 'خالد صالح', '0114774959', 'info@tabukpharmaceuticals.com', 'طريق الإمام سعود بن عبدالعزيز بن محمد', 'الرياض', 'المملكة العربية السعودية', 1, '2025-11-24 21:38:41'),
(2, 'شركة سبيماكو الدوائية', 'عبدالله القرني', '0114634040', 'info@spimaco.com.sa', 'طريق الملك فهد', 'الرياض', 'المملكة العربية السعودية', 1, '2025-11-24 21:38:41'),
(3, 'شركة دار المعدات الطبية والعلمية', 'سعود العريفي', '0114624848', 'info@smeh.com.sa', 'شارع العليا', 'الرياض', 'المملكة العربية السعودية', 1, '2025-11-24 21:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `transaction_type` enum('delivery','receipt','dispensing','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `item_id`, `transaction_type`, `quantity`, `user_id`, `notes`, `transaction_date`) VALUES
(1, 1, 'dispensing', 10, 3, 'صرف لقسم الطوارئ', '2025-10-30 21:38:42'),
(2, 1, 'dispensing', 8, 3, 'صرف لقسم الطوارئ', '2025-11-04 21:38:42'),
(3, 1, 'dispensing', 12, 3, 'صرف لقسم الطوارئ', '2025-11-09 21:38:42'),
(4, 1, 'delivery', 100, 2, 'استقبال من الموردين', '2025-11-14 21:38:42'),
(5, 2, 'dispensing', 5, 3, 'صرف لقسم الطوارئ', '2025-11-06 21:38:42'),
(6, 3, 'dispensing', 50, 2, 'صرف محقنات', '2025-11-12 21:38:42'),
(7, 4, 'dispensing', 20, 2, 'صرف قطن', '2025-11-16 21:38:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','pharmacist','purchasing','doctor') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `role`, `department`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'admin@hospital.com', 'مسؤول النظام', 'admin', 'الإدارة', '0501234567', 1, '2025-11-24 21:38:40', '2025-11-24 21:38:40'),
(2, 'pharmacist', 'pharm123', 'pharmacist@hospital.com', 'صيدلاني المخزن', 'pharmacist', 'الصيدلية', '0502345678', 1, '2025-11-24 21:38:40', '2025-11-24 21:38:40'),
(3, 'doctor', 'doc123', 'doctor@hospital.com', 'الدكتور أحمد', 'doctor', 'الطوارئ', '0503456789', 1, '2025-11-24 21:38:40', '2025-11-24 21:38:40'),
(4, 'purchasing', 'purch123', 'purchasing@hospital.com', 'مسؤول المشتريات', 'purchasing', 'المشتريات', '0504567890', 1, '2025-11-24 21:38:40', '2025-11-24 21:38:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `logs_ibfk_1` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `requested_by` (`requested_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
