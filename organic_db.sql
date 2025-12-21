-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th12 13, 2025 lúc 04:30 AM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `organic_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `entity_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author_id` int DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `view_count` int DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `author_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `status`, `view_count`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'Tại sao phải chọn đồ ăn organic', 't-i-sao-ph-i-ch-n-n-organic', '', 'Chọn đồ ăn organic không chỉ giúp bảo vệ sức khỏe mà còn bảo vệ môi trường. Thực phẩm organic không sử dụng hóa chất, hormone tăng trưởng, nguyên liệu biến đổi gen, và không bị chiếu xạ tiệt trùng. Điều này giúp giảm thiểu nguy cơ dị ứng ở trẻ nhỏ, đặc biệt là trong 2 năm đầu đời. Ngoài ra, thực phẩm organic còn chứa nhiều chất chống oxy hóa và vi chất dinh dưỡng hơn, giúp nâng cao hệ miễn dịch.', 'https://th.bing.com/th/id/OIP.z6JtiUBzT7seBJIqFHxt7QHaEo?w=227&amp;amp;h=180&amp;amp;c=7&amp;amp;r=0&amp;amp;o=7&amp;amp;pid=1.7&amp;amp;rm=3', 'published', 0, '2025-11-25 06:50:29', '2025-11-25 06:47:06', '2025-11-25 06:50:29'),
(2, 4, 'Tin mới', 'tin-m-i', '', '', 'https://file.hstatic.net/200000940115/article/tuyen_dai_ly_si__1__896b97c7b0aa477480b6564b797c8381_large_005ad678e2a1425cb484358cbceea4fa.webp', 'published', 0, '2025-11-25 06:52:20', '2025-11-25 06:52:20', '2025-11-25 06:52:20'),
(3, 4, 'Tin mới nè', 'tin-m-i-n-', '', '', 'https://file.hstatic.net/200000940115/article/qua_tang_2011__5__a78763b2b92b41d788a62e2c4b074386_large_eb8241f3808b40dda13685972540a562.webp', 'published', 0, '2025-11-25 06:53:00', '2025-11-25 06:53:00', '2025-11-25 06:53:00'),
(4, 4, 'Tin mới nữa nè đọc đi', 'tin-m-i-n-a-n-c-i', '', '', 'https://file.hstatic.net/200000940115/article/462912960_2912678215548977_8199243112812513949_n_17d1f59c1e3c4fc783693aad4fdc7395_grande.jpg', 'published', 0, '2025-11-25 06:53:27', '2025-11-25 06:53:23', '2025-11-25 06:53:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=643 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(638, 14, 15, 8, '2025-12-13 03:42:55', '2025-12-13 03:42:55'),
(639, 14, 60, 6, '2025-12-13 03:42:55', '2025-12-13 03:42:55'),
(640, 14, 65, 6, '2025-12-13 03:42:55', '2025-12-13 03:42:55'),
(641, 14, 103, 7, '2025-12-13 03:42:55', '2025-12-13 03:42:55'),
(642, 14, 105, 7, '2025-12-13 03:42:55', '2025-12-13 03:42:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'grass',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parent_id` int DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `parent_id`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Rau củ', 'rau-cu', 'images/categories/1763973666_rau-cu-qua-huu-co-1763692176.jpg', 'Rau củ tươi ngon hữu cơ', NULL, 1, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:07'),
(2, 'Trái cây', 'trai-cay', 'images/categories/1763973673_trai-cay-huu-co-1763692166.jpg', 'Trái cây nhập khẩu và trong nước', NULL, 2, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:13'),
(3, 'Trứng & Bơ Sữa', 'trung-bo-sua', 'images/categories/1763973681_sua-trung-organic-1763692144.jpg', 'Sản phẩm từ trứng và sữa', NULL, 3, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:21'),
(4, 'Bánh mì & Bánh ngọt', 'banh-mi', 'images/categories/1763973688_gao-ngu-coc-1763692122.png', 'Bánh mì và bánh ngọt tươi mỗi ngày', NULL, 4, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:28'),
(5, 'Thịt & Hải sản', 'thit-hai-san', 'images/categories/1763973697_thit-hai-san-sach-1763692154.jpg', 'Thịt và hải sản tươi sống', NULL, 5, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','replied','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Minh Tài', 'buiminhtai97@gmail.com', '0966330634', 'other', 'chủ sốp đẹp trai quá cần up ảnh đại diện', 'pending', '2025-11-24 16:02:12'),
(2, 'Hậu Nguyễn', 'haunguyen@gmail.com', '', 'other', 'hay quá', 'pending', '2025-11-25 03:59:43'),
(3, 'WinhTei', 'buiminhtai97@gmail.com', '0966330634', 'order', 'cập nhật đơn hàng của tôi đi', 'pending', '2025-11-25 04:34:56');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `discount_type` enum('percentage','fixed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `discount_value` decimal(10,0) NOT NULL,
  `min_order_value` decimal(10,0) DEFAULT '0',
  `max_discount` decimal(10,0) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10, 200000, 50000, 100, 3, '2025-11-24 08:14:00', '2025-12-24 08:14:00', 1, '2025-11-24 08:14:59', '2025-12-12 18:40:37'),
(2, 'FREESHIP', 'Miễn phí vận chuyển cho đơn từ 500k', 'fixed', 25000, 200000, NULL, 10, 1, '2025-11-24 08:14:00', '2026-01-23 08:14:00', 1, '2025-11-24 08:14:59', '2025-12-12 18:36:03'),
(3, 'ABC123', 'Mã giảm đặc biệt', 'percentage', 10, 100000, 50000, 5, 0, '2025-12-11 22:24:00', '2025-12-12 17:00:00', 1, '2025-12-12 16:23:34', '2025-12-12 16:27:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
CREATE TABLE IF NOT EXISTS `customer_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'TP. Hồ Chí Minh',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `user_id`, `name`, `phone`, `address`, `ward`, `district`, `city`, `note`, `is_default`, `created_at`, `updated_at`) VALUES
(12, 5, 'Minh Hậu', '0666666666', 'Hà Nội', NULL, NULL, 'TP. Hồ Chí Minh', '', 0, '2025-11-28 07:24:09', '2025-11-28 07:25:02'),
(13, 5, 'Minh Hậu', '033333333', 'TPHCM', NULL, NULL, 'TP. Hồ Chí Minh', '', 1, '2025-11-28 07:25:02', '2025-11-28 07:25:02'),
(18, 7, 'Minh Nhật', '0966340635', '65 Tình Nghĩa', 'Xã Xuân Tình', 'Huyện Tình Bạn', 'TP. Hồ Chí Minh', '', 1, '2025-12-07 11:30:52', '2025-12-07 11:30:52'),
(19, 8, 'Toàn', '0927832131', 'Hêjjdndnd', '', 'Hóc mno', 'TP. Hồ Chí Minh', '', 1, '2025-12-07 11:56:10', '2025-12-07 11:56:16'),
(20, 3, 'Minh Tài', '0966330634', '65 An Xương', 'Tân Thới Nhì', 'Huyện Hóc Môn', 'TP. Hồ Chí Minh', '', 0, '2025-12-07 16:30:43', '2025-12-07 17:40:46'),
(21, 6, 'Bui Minh Tai', '0528837261', 'lô o16 khu dân cư thới an', 'thới an', '12', 'TP. Hồ Chí Minh', '', 0, '2025-12-07 17:09:27', '2025-12-07 17:09:27'),
(22, 3, 'Thảo Vi', '0967890199', 'Tân An', 'Xã Đông Thạnh', 'Huyện Cái bè', 'TP. Hồ Chí Minh', '', 1, '2025-12-07 17:40:46', '2025-12-07 17:40:46'),
(26, 12, 'Nguyễn Trung Hậu', '0931878932', '123/1232', 'Thạnh Mỹ Lợi', 'Thủ Đức', 'TP. Hồ Chí Minh', '', 1, '2025-12-12 17:00:51', '2025-12-12 17:00:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','unsubscribed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `subscribed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `order_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,0) NOT NULL,
  `discount_amount` decimal(10,0) DEFAULT '0',
  `shipping_fee` decimal(10,0) DEFAULT '25000',
  `tax_amount` decimal(10,0) DEFAULT '0',
  `final_amount` decimal(12,0) NOT NULL,
  `status` enum('pending','confirmed','processing','shipping','delivered','cancelled','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `shipping_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shipping_ward` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `coupon_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order_code` (`order_code`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_amount`, `discount_amount`, `shipping_fee`, `tax_amount`, `final_amount`, `status`, `payment_method`, `payment_status`, `shipping_name`, `shipping_phone`, `shipping_email`, `shipping_address`, `shipping_ward`, `shipping_district`, `shipping_city`, `note`, `coupon_code`, `tracking_number`, `cancelled_reason`, `cancelled_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'ORD202511244735', 63000, 0, 25000, 0, 88000, 'shipping', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TTN', '1', '1', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 12:19:39', '2025-11-24 15:54:10'),
(2, 3, 'ORD202511241837', 35000, 0, 25000, 0, 60000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:26:48', '2025-11-24 15:54:07'),
(3, 3, 'ORD202511246683', 113000, 0, 25000, 0, 138000, 'delivered', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:28:16', '2025-11-24 15:37:28'),
(4, 3, 'ORD202511246349', 88000, 0, 25000, 0, 113000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:28:30', '2025-11-24 15:37:26'),
(5, 3, 'ORD202511244967', 113000, 0, 25000, 0, 138000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:29:31', '2025-11-24 15:37:22'),
(6, 3, 'ORD202511248026', 63000, 0, 25000, 0, 88000, 'shipping', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:32:10', '2025-11-24 15:37:20'),
(7, 3, 'ORD202511247304', 228000, 0, 25000, 0, 253000, 'confirmed', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:33:21', '2025-11-24 15:37:17'),
(8, 3, 'ORD202511245491', 744000, 0, 0, 0, 744000, 'confirmed', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', 'dơn đầu tiên cũng như cuối cùng', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:35:16', '2025-11-24 15:37:15'),
(9, 3, 'ORD202511249083', 127000, 0, 25000, 0, 152000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', 'Đơn hàng đầu như đơn hàng cuối', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:36:46', '2025-11-25 03:58:20'),
(10, 4, 'ORD202511248463', 127000, 0, 25000, 0, 152000, 'delivered', 'cod', 'pending', 'admin', '0966330634', NULL, 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:00:46', '2025-11-24 16:01:42'),
(11, 3, 'ORD202511248059', 63000, 0, 25000, 0, 88000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:01:21', '2025-11-24 16:01:40'),
(12, 3, 'ORD202511252231', 127000, 0, 25000, 0, 152000, 'delivered', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', NULL, 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 03:57:39', '2025-11-25 03:58:17'),
(13, 3, 'ORD202511258313', 60000, 0, 25000, 0, 85000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, 'Quận 1', 'xã đông cú', 'huyện trảng  bom', 'TP. Hồ Chí Minh', 'khogn', NULL, NULL, NULL, NULL, NULL, '2025-11-25 04:02:08', '2025-11-25 04:06:34'),
(14, 3, 'ORD202511257565', 63000, 0, 25000, 0, 88000, 'delivered', 'bank_transfer', 'pending', 'Trung Tấn', '0966330634', NULL, 'Quận 1', '1', '22', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 04:06:11', '2025-11-25 04:06:31'),
(15, 3, 'ORD202511252579', 127000, 0, 25000, 0, 152000, 'shipping', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 05:29:01', '2025-11-27 17:01:18'),
(16, 3, 'ORD202511253941', 63000, 0, 25000, 0, 88000, 'pending', 'bank_transfer', 'pending', 'Hiếu Toàn', '0966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hà Nội', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 05:48:03', '2025-11-25 05:48:03'),
(17, 3, 'ORD202511252656', 53000, 0, 25000, 0, 78000, 'cancelled', 'bank_transfer', 'pending', 'Hiếu Toàn', '0966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hà Nọi', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 06:08:30', '2025-11-27 17:42:09'),
(18, 3, 'ORD202511271490', 152000, 0, 25000, 0, 177000, 'shipping', 'cod', 'pending', 'Hiếu Toàn', '0966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:17:38', '2025-11-27 17:01:14'),
(19, 3, 'ORD202511273678', 255000, 0, 25000, 0, 280000, 'confirmed', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', NULL, 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:18:06', '2025-11-27 17:01:11'),
(23, 3, 'ORD202511279883', 495000, 0, 25000, 0, 520000, 'delivered', 'cod', 'pending', 'Minh Tài buiminhtai', '0966330634', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:43:19', '2025-11-27 17:01:09'),
(24, 3, 'ORD202511279024', 135000, 0, 25000, 0, 160000, 'cancelled', 'cod', 'pending', 'dâu tây hữu cơ', '0966330634', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', '1', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:44:16', '2025-11-27 17:41:55'),
(25, 3, 'ORD202511279226', 124000, 0, 25000, 0, 149000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330643', NULL, 'nhà bồn ha', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-11-27 16:56:25', NULL, '2025-11-27 16:47:06', '2025-11-27 16:57:02'),
(26, 5, 'ORD202511289896', 255000, 0, 25000, 0, 280000, 'cancelled', 'cod', 'pending', 'phuc', '096663333', NULL, 'TTN', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-28 07:13:24', '2025-11-28 07:13:57'),
(27, 3, 'ORD202511309645', 148000, 0, 25000, 0, 173000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '0966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-29 18:44:00', '2025-11-29 18:44:00'),
(28, 3, 'ORD202511305288', 148000, 0, 25000, 0, 173000, 'pending', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', NULL, 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-29 18:45:37', '2025-11-29 18:45:37'),
(29, 3, 'ORD202511304194', 63000, 0, 25000, 0, 88000, 'pending', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', NULL, 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-30 05:17:08', '2025-11-30 05:17:08'),
(30, 3, 'ORD202511308036', 63000, 0, 25000, 0, 88000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330643', NULL, 'nhà bồn ha', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-30 05:28:45', '2025-11-30 05:28:45'),
(31, 6, 'ORD202512063434', 228000, 0, 25000, 0, 253000, 'cancelled', 'cod', 'pending', 'Thảo Vi', '+84528837261', NULL, 'lô O16A khu dân cư Thới An phường Thới An Quận 12', 'Thới An', '12', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-05 18:27:03', '2025-12-05 18:30:02'),
(32, 3, 'ORD202512064346', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 07:35:10', '2025-12-06 07:35:10'),
(33, 3, 'ORD202512066089', 28000, 0, 25000, 0, 53000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 11:25:44', '2025-12-06 11:25:44'),
(34, 3, 'ORD202512067850', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 11:27:25', '2025-12-06 11:27:25'),
(35, 3, 'ORD202512065476', 63000, 0, 25000, 0, 88000, 'pending', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 11:29:07', '2025-12-06 11:29:07'),
(36, 3, 'ORD202512069435', 63000, 0, 25000, 0, 88000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 11:29:23', '2025-12-06 11:29:23'),
(37, 3, 'ORD202512065508', 1548000, 0, 0, 0, 1548000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-06 11:30:43', '2025-12-06 11:30:43'),
(38, 3, 'ORD202512061640', 28000, 0, 25000, 0, 53000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-12-06 14:01:12', NULL, '2025-12-06 11:31:45', '2025-12-06 14:01:12'),
(39, 3, 'ORD202512071271', 7735000, 0, 0, 0, 7735000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330634', NULL, '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-12-07 07:45:17', NULL, '2025-12-07 07:42:09', '2025-12-07 07:45:17'),
(40, 3, 'ORD202512072901', 85000, 0, 25000, 0, 110000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '+84966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 07:49:20', '2025-12-07 07:49:20'),
(41, 3, 'ORD202512079387', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '+84966330649', NULL, '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 07:50:34', '2025-12-07 07:50:34'),
(42, 3, 'ORD202512074839', 130000, 0, 25000, 0, 155000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330634', 'buiminhtai97@gmail.com', '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 07:56:03', '2025-12-07 07:56:03'),
(43, 3, 'ORD202512073240', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '+84966330649', 'hieutoan@gmail.com', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 07:56:25', '2025-12-07 07:56:25'),
(44, 3, 'ORD202512075977', 99000, 0, 25000, 0, 124000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330634', 'buiminhtai97@gmail.com', '1TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-12-07 08:11:51', NULL, '2025-12-07 08:02:42', '2025-12-07 08:11:51'),
(45, 3, 'ORD202512076533', 32000, 0, 25000, 0, 57000, 'pending', 'cod', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 08:26:34', '2025-12-07 08:26:34'),
(46, 3, 'ORD202512079235', 49000, 0, 25000, 0, 74000, 'cancelled', 'bank_transfer', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-12-07 08:27:26', NULL, '2025-12-07 08:27:15', '2025-12-07 08:27:26'),
(47, 3, 'ORD202512079657', 49000, 0, 25000, 0, 74000, 'pending', 'bank_transfer', 'pending', 'Hiếu Toàn', '+84966330649', 'hieutoan@gmail.com', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 08:27:44', '2025-12-07 08:27:44'),
(48, 3, 'ORD202512071509', 110000, 0, 25000, 0, 135000, 'pending', 'bank_transfer', 'pending', 'Hiếu Toàn', '+84966330649', 'hieutoan@gmail.com', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:36:40', '2025-12-07 09:36:40'),
(49, 3, 'ORD202512071132', 130000, 0, 25000, 0, 155000, 'pending', 'bank_transfer', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:37:08', '2025-12-07 09:37:08'),
(50, 3, 'ORD202512076213', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:40:49', '2025-12-07 09:40:49'),
(51, 3, 'ORD202512078785', 117000, 0, 25000, 0, 142000, 'pending', 'cod', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:43:22', '2025-12-07 09:43:22'),
(52, 3, 'ORD202512075605', 28000, 0, 25000, 0, 53000, 'pending', 'cod', 'pending', 'Minh Kê', '0966666666', 'buiminhtai97@gmail.com', 'Bà Rịa Vũng Tàu', 'Xã Tân Phong', 'Huyện Thống Kê', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:45:44', '2025-12-07 09:45:44'),
(53, 3, 'ORD202512072316', 32000, 0, 25000, 0, 57000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '+84966330649', 'buiminhtai97@gmail.com', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hà Nọi', '', NULL, NULL, NULL, NULL, NULL, '2025-12-07 09:53:08', '2025-12-07 09:53:08'),
(54, 7, 'ORD202512078479', 234000, 0, 25000, 0, 259000, 'cancelled', 'cod', 'pending', 'Minh Nhật', '0966340635', 'buiminhtai3114@gmail.com', '65 Tình Nghĩa', 'Xã Xuân Tình', 'Huyện Tình Bạn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-12-07 11:33:10', NULL, '2025-12-07 11:31:45', '2025-12-07 11:33:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,0) NOT NULL,
  `total_price` decimal(12,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 12:19:39'),
(2, 1, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 12:19:39'),
(3, 2, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:26:48'),
(4, 3, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:28:16'),
(5, 3, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:28:16'),
(6, 3, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 2, 25000, 50000, '2025-11-24 15:28:16'),
(7, 4, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:28:30'),
(8, 4, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:28:30'),
(9, 4, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-24 15:28:30'),
(10, 5, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:29:31'),
(11, 5, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:29:31'),
(12, 5, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 2, 25000, 50000, '2025-11-24 15:29:31'),
(13, 6, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:32:10'),
(14, 6, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:32:10'),
(15, 7, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:33:21'),
(16, 7, 4, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 1, 129000, 129000, '2025-11-24 15:33:21'),
(17, 8, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:35:16'),
(18, 8, 4, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 5, 129000, 645000, '2025-11-24 15:35:16'),
(19, 9, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:36:46'),
(20, 9, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:36:46'),
(21, 10, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 16:00:46'),
(22, 10, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 16:00:46'),
(23, 11, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 16:01:21'),
(24, 11, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 16:01:21'),
(25, 12, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 03:57:39'),
(26, 12, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-25 03:57:39'),
(27, 13, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 04:02:08'),
(28, 13, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-25 04:02:08'),
(29, 14, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 04:06:11'),
(30, 14, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 04:06:11'),
(31, 15, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 05:29:01'),
(32, 15, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-25 05:29:01'),
(33, 16, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 05:48:03'),
(34, 16, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 05:48:03'),
(35, 17, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 06:08:30'),
(36, 17, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-25 06:08:30'),
(37, 18, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-27 16:17:38'),
(38, 18, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-27 16:17:38'),
(39, 18, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-27 16:17:38'),
(40, 19, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:18:06'),
(41, 19, NULL, 'Cá heo', '', 1, 120000, 120000, '2025-11-27 16:18:06'),
(42, 19, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:18:06'),
(46, 23, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:43:19'),
(47, 23, NULL, 'Cá heo', '', 3, 120000, 360000, '2025-11-27 16:43:19'),
(48, 23, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:43:19'),
(49, 24, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:44:16'),
(50, 24, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:44:16'),
(51, 25, 2, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-27 16:47:06'),
(52, 25, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-27 16:47:06'),
(53, 26, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-28 07:13:24'),
(54, 26, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-28 07:13:24'),
(55, 26, NULL, 'Cá mập', 'images/product/1764263239_c___m___p.png', 1, 100000, 100000, '2025-11-28 07:13:24'),
(56, 27, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-29 18:44:00'),
(57, 27, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-29 18:44:00'),
(58, 28, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-29 18:45:37'),
(59, 28, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-29 18:45:37'),
(60, 29, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-30 05:17:08'),
(61, 29, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-30 05:17:08'),
(62, 30, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-30 05:28:45'),
(63, 30, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-30 05:28:45'),
(64, 31, 3, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-12-05 18:27:03'),
(65, 31, 4, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 1, 129000, 129000, '2025-12-05 18:27:03'),
(66, 32, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-06 07:35:10'),
(67, 32, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-06 07:35:10'),
(68, 33, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-12-06 11:25:44'),
(69, 34, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-06 11:27:25'),
(70, 34, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-06 11:27:25'),
(71, 35, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-12-06 11:29:07'),
(72, 35, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-12-06 11:29:07'),
(73, 36, NULL, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-12-06 11:29:23'),
(74, 36, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-12-06 11:29:23'),
(75, 37, 4, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 12, 129000, 1548000, '2025-12-06 11:30:43'),
(76, 38, NULL, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-12-06 11:31:45'),
(77, 39, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 91, 85000, 7735000, '2025-12-07 07:42:09'),
(78, 40, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 07:49:20'),
(79, 41, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 07:50:34'),
(80, 41, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-07 07:50:34'),
(81, 42, NULL, 'Trà xanh túi lọc', 'tra_xanh.jpg', 1, 45000, 45000, '2025-12-07 07:56:03'),
(82, 42, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 07:56:03'),
(83, 43, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 07:56:25'),
(84, 43, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-07 07:56:25'),
(85, 44, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 08:02:42'),
(86, 44, NULL, 'Nước khoáng thiên nhiên', 'images/product/1765042613_hat.png', 1, 14000, 14000, '2025-12-07 08:02:42'),
(87, 45, NULL, 'Nước khoáng thiên nhiên', 'images/product/1765042613_hat.png', 1, 14000, 14000, '2025-12-07 08:26:34'),
(88, 45, NULL, 'Sữa đậu nành', 'sua_dau_nanh.jpg', 1, 18000, 18000, '2025-12-07 08:26:34'),
(89, 46, 44, 'Sữa tươi không đường', 'sua_tuoi.jpg', 1, 35000, 35000, '2025-12-07 08:27:15'),
(90, 46, NULL, 'Nước khoáng thiên nhiên', 'images/product/1765042613_hat.png', 1, 14000, 14000, '2025-12-07 08:27:15'),
(91, 47, 44, 'Sữa tươi không đường', 'sua_tuoi.jpg', 1, 35000, 35000, '2025-12-07 08:27:44'),
(92, 47, NULL, 'Nước khoáng thiên nhiên', 'images/product/1765042613_hat.png', 1, 14000, 14000, '2025-12-07 08:27:44'),
(93, 48, 5, 'Rau cải xanh hữu cơ', 'cai_xanh.jpg', 1, 25000, 25000, '2025-12-07 09:36:40'),
(94, 48, 15, 'Táo Fuji nhập khẩu', 'tao_fuji.jpg', 1, 85000, 85000, '2025-12-07 09:36:40'),
(95, 49, NULL, 'Trà xanh túi lọc', 'tra_xanh.jpg', 1, 45000, 45000, '2025-12-07 09:37:08'),
(96, 49, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 09:37:08'),
(97, 50, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 09:40:49'),
(98, 50, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-07 09:40:49'),
(99, 51, NULL, 'Cà phê rang xay', 'ca_phe.jpg', 1, 85000, 85000, '2025-12-07 09:43:22'),
(100, 51, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-07 09:43:22'),
(101, 52, NULL, 'Nước khoáng thiên nhiên', 'images/product/1765042613_hat.png', 2, 14000, 28000, '2025-12-07 09:45:44'),
(102, 53, NULL, 'Sữa hạt óc chó', 'sua_oc_cho.jpg', 1, 32000, 32000, '2025-12-07 09:53:08'),
(103, 54, 7, 'Khoai tây vàng', 'khoai_tay.jpg', 2, 32000, 64000, '2025-12-07 11:31:45'),
(104, 54, 8, 'Hành lá hữu cơ', 'hanh_la.jpg', 2, 15000, 30000, '2025-12-07 11:31:45'),
(105, 54, 44, 'Sữa tươi không đường', 'sua_tuoi.jpg', 4, 35000, 140000, '2025-12-07 11:31:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(13, 'buiminhtai3114@gmail.com', '2a0f8426cec37ef53ba176c2a7cbf53cffd758c3f33882737f4e17070ac943ad', '2025-12-08 11:26:51', '2025-12-07 11:26:51'),
(15, 'admin@xanhorganic.com', 'c643664100d5bfbc2d958942fc49e941c4a399eb79e5c3fe82e5174036ad8d46', '2025-12-13 18:36:51', '2025-12-12 18:36:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `sale_price` decimal(10,0) DEFAULT NULL,
  `cost_price` decimal(10,0) DEFAULT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'kg',
  `weight` decimal(8,2) DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stock` int DEFAULT '0',
  `sku` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_organic` tinyint(1) DEFAULT '1',
  `is_new` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `view_count` int DEFAULT '0',
  `sold_count` int DEFAULT '0',
  `rating_avg` decimal(3,2) DEFAULT '0.00',
  `rating_count` int DEFAULT '0',
  `meta_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `cost_price`, `unit`, `weight`, `image`, `gallery`, `stock`, `sku`, `barcode`, `is_organic`, `is_new`, `is_featured`, `is_bestseller`, `is_active`, `view_count`, `sold_count`, `rating_avg`, `rating_count`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(2, 1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên, hoàn hảo cho salad', NULL, 25000, NULL, NULL, 'hộp 250g', NULL, 'images/product/1765265603_ca-chua.png', NULL, 71, 'VEG003', NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-12-09 07:33:23'),
(3, 2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand, giòn ngọt', NULL, 99000, NULL, NULL, '0.5kg', NULL, 'images/product/1765265611_tao-envy.jpg', NULL, 20, 'FRU001', NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-12-12 17:49:28'),
(4, 3, 'Trứng gà thả vườn', 'trung-ga-tha-vuon', 'Trứng gà sạch tự nhiên, giàu dinh dưỡng', NULL, 129000, NULL, NULL, 'vỉ 10 trứng', NULL, 'images/product/1765265623_trung-ga.jpg', NULL, 26, 'DAI001', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-12-09 07:33:43'),
(5, 1, 'Rau cải xanh hữu cơ', 'rau-cai-xanh-huu-co', 'Rau cải xanh trồng theo tiêu chuẩn hữu cơ.', NULL, 25000, NULL, NULL, 'kg', NULL, 'images/product/1765260808_cai-xanh.png', NULL, 106, NULL, NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-12 18:36:03'),
(6, 1, 'Cà rốt Đà Lạt', 'ca-rot-da-lat', 'Cà rốt tươi, giòn, giàu vitamin A.', NULL, 28000, NULL, NULL, 'kg', NULL, 'images/product/1765260819_carot.jpg', NULL, 99, NULL, NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-12 18:33:21'),
(7, 1, 'Khoai tây vàng', 'khoai-tay-vang', 'Khoai tây loại 1, củ lớn.', NULL, 32000, NULL, NULL, 'kg', NULL, 'images/product/1765260825_khoaitay.jpg', NULL, 140, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:13:45'),
(8, 1, 'Hành lá hữu cơ', 'hanh-la-huu-co', 'Hành lá sạch, không hóa chất.', NULL, 15000, NULL, NULL, 'kg', NULL, 'images/product/1765260841_hanh-la.jpg', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:14:01'),
(9, 1, 'Rau muống hữu cơ', 'rau-muong-huu-co', 'Rau muống tươi, trồng thủy canh.', NULL, 22000, NULL, NULL, 'kg', NULL, 'images/product/1765260853_raumuong.jpg', NULL, 150, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:14:13'),
(10, 1, 'Bí đỏ hồ lô', 'bi-do-ho-lo', 'Bí đỏ giàu vitamin A.', NULL, 30000, NULL, NULL, 'kg', NULL, 'images/product/1765260861_bidoholo.jpg', NULL, 80, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:14:21'),
(11, 1, 'Cải ngọt hữu cơ', 'cai-ngot-huu-co', 'Cải ngọt sạch, an toàn.', NULL, 24000, NULL, NULL, 'kg', NULL, 'images/product/1765260868_cai-ngot.jpg', NULL, 110, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:14:28'),
(12, 1, 'Su su non', 'su-su-non', 'Su su Đà Lạt loại non.', NULL, 26000, NULL, NULL, 'kg', NULL, 'images/product/1765260904_cu-su-su.jpg', NULL, 127, NULL, NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-12 18:36:03'),
(13, 1, 'Dưa leo hữu cơ', 'dua-leo-huu-co', 'Dưa leo giòn, ngọt.', NULL, 20000, NULL, NULL, 'kg', NULL, 'images/product/1765260912_dua-leo.jpg', NULL, 160, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:15:12'),
(14, 1, 'Bắp cải trắng', 'bap-cai-trang', 'Bắp cải trắng, tươi giòn.', NULL, 27000, NULL, NULL, 'kg', NULL, 'images/product/1765260920_bap-cai-trang.jpg', NULL, 95, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:12', '2025-12-09 06:15:20'),
(15, 2, 'Táo Fuji nhập khẩu', 'tao-fuji', 'Táo Fuji giòn ngọt.', NULL, 85000, NULL, NULL, 'kg', NULL, 'images/product/1765260931_tao-envy.jpg', NULL, 84, NULL, NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-12 18:36:03'),
(16, 2, 'Cam vàng Úc', 'cam-vang-uc', 'Cam vàng mọng nước, không hạt.', NULL, 75000, NULL, NULL, 'kg', NULL, 'images/product/1765260938_cam-vang-uc.png', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:15:38'),
(17, 2, 'Nho đen không hạt', 'nho-den-khong-hat', 'Nho đen tươi ngọt.', NULL, 120000, NULL, NULL, 'kg', NULL, 'images/product/1765260958_nho-den-khong-hat.jpg', NULL, 60, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:15:58'),
(18, 2, 'Xoài cát Hòa Lộc', 'xoai-cat-hoa-loc', 'Xoài chín tự nhiên.', NULL, 65000, NULL, NULL, 'kg', NULL, 'images/product/1765260967_xoai.png', NULL, 80, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:16:07'),
(19, 2, 'Chuối già Nam Mỹ', 'chuoi-gia', 'Chuối chín vàng, thơm.', NULL, 30000, NULL, NULL, 'kg', NULL, 'images/product/1765260975_chuoi-gia.png', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:16:15'),
(21, 2, 'Lê Hàn Quốc', 'le-han-quoc', 'Lê giòn, mọng nước.', NULL, 95000, NULL, NULL, 'kg', NULL, 'images/product/1765260995_le-han-quoc_jpg.jpg', NULL, 70, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:16:35'),
(22, 2, 'Bơ sáp Đắk Lắk', 'bo-sap', 'Bơ béo, ít xơ.', NULL, 55000, NULL, NULL, 'kg', NULL, 'images/product/1765261005_bo-sap-dak-lak-.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:16:45'),
(23, 2, 'Thanh long ruột đỏ', 'thanh-long-do', 'Thanh long ruột đỏ ngọt.', NULL, 40000, NULL, NULL, 'kg', NULL, 'images/product/1765261014_thanh-long-ruot-do-.jpg', NULL, 90, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:16:54'),
(24, 2, 'Mận hậu Sơn La', 'man-hau', 'Mận tươi giòn.', NULL, 45000, NULL, NULL, 'kg', NULL, 'images/product/1765261033_man-hau-son-la.png', NULL, 110, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:11:53', '2025-12-09 06:17:13'),
(25, 3, 'Thịt bò Úc', 'thit-bo-uc', 'Thịt bò Úc tươi mềm.', NULL, 250000, NULL, NULL, 'kg', NULL, 'images/product/1765261043_thit-bo-uc.jpg', NULL, 40, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:17:23'),
(26, 3, 'Thịt heo sạch', 'thit-heo-sach', 'Thịt heo chuẩn VietGAP.', NULL, 120000, NULL, NULL, 'kg', NULL, 'images/product/1765261052_thit-heo-sach.jpg', NULL, 70, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:17:32'),
(27, 3, 'Cá hồi Na Uy', 'ca-hoi', 'Phi lê cá hồi tươi.', NULL, 320000, NULL, NULL, 'kg', NULL, 'images/product/1765261061_ca-hoi-nauy.jpg', NULL, 50, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:17:41'),
(28, 3, 'Tôm sú loại 1', 'tom-su', 'Tôm sú tươi sống.', NULL, 280000, NULL, NULL, 'kg', NULL, 'images/product/1765261074_tom-su.jpg', NULL, 60, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:17:54'),
(29, 3, 'Mực ống tươi', 'muc-ong', 'Mực ống loại lớn.', NULL, 230000, NULL, NULL, 'kg', NULL, 'images/product/1765261083_muc-ong-tuoi.jpg', NULL, 45, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:18:03'),
(30, 3, 'Ức gà phi lê', 'uc-ga', 'Ức gà ít mỡ.', NULL, 95000, NULL, NULL, 'kg', NULL, 'images/product/1765261092_uc-ga-phi-le.jpg', NULL, 90, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:18:12'),
(31, 3, 'Cá thu cắt lát', 'ca-thu', 'Cá thu biển tươi.', NULL, 200000, NULL, NULL, 'kg', NULL, 'images/product/1765261113_ca-thu-cat-lat.jpg', NULL, 50, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:18:33'),
(32, 3, 'Ghẹ xanh sống', 'ghe-xanh', 'Ghẹ xanh loại 1.', NULL, 350000, NULL, NULL, 'kg', NULL, 'images/product/1765261120_ghe-xanh.jpg', NULL, 30, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:18:40'),
(33, 3, 'Sườn non heo', 'suon-non', 'Sườn non heo mềm.', NULL, 180000, NULL, NULL, 'kg', NULL, 'images/product/1765261129_suon-non-heo.jpg', NULL, 60, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:18:49'),
(34, 3, 'Cá basa phi lê', 'ca-basa', 'Cá basa ít xương.', NULL, 90000, NULL, NULL, 'kg', NULL, 'images/product/1765261142_ca-basa-phi-le.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:07', '2025-12-09 06:19:02'),
(35, 4, 'Hạt điều rang muối', 'hat-dieu', 'Hạt điều Bình Phước.', NULL, 150000, NULL, NULL, 'kg', NULL, 'images/product/1765261158_hat-dieu-rang-muoi.jpg', NULL, 80, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:19:18'),
(36, 4, 'Gạo ST25', 'gao-st25', 'Gạo thơm nhất thế giới.', NULL, 30000, NULL, NULL, 'kg', NULL, 'images/product/1765261166_gao-st25.jpg', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:19:26'),
(37, 4, 'Nước mắm Phú Quốc', 'nuoc-mam-phu-quoc', 'Nước mắm cốt loại 1.', NULL, 90000, NULL, NULL, 'kg', NULL, 'images/product/1765261180_nuoc-mam-phu-quoc.jpg', NULL, 90, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:19:40'),
(38, 4, 'Muối hồng Himalaya', 'muoi-hong', 'Muối tinh khiết.', NULL, 35000, NULL, NULL, 'kg', NULL, 'images/product/1765261192_muoi-hong-hymalaya.jpg', NULL, 150, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:19:52'),
(39, 4, 'Đường thốt nốt', 'duong-thot-not', 'Đường thốt nốt An Giang.', NULL, 40000, NULL, NULL, 'kg', NULL, 'images/product/1765261215_duong-thot-not.jpg', NULL, 120, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:20:15'),
(40, 4, 'Hạt chia Úc', 'hat-chia', 'Hạt chia giàu omega 3.', NULL, 110000, NULL, NULL, 'kg', NULL, 'images/product/1765261223_hat-chia.png', NULL, 70, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:20:23'),
(41, 4, 'Mì gạo lứt', 'mi-gao-lut', 'Mì gạo lứt nguyên cám.', NULL, 45000, NULL, NULL, 'kg', NULL, 'images/product/1765261240_mi-gao-lut.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:20:40'),
(42, 4, 'Đậu xanh hạt', 'dau-xanh', 'Đậu xanh loại 1.', NULL, 50000, NULL, NULL, 'kg', NULL, 'images/product/1765261248_dau-xanh-hat.jpg', NULL, 130, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:20:48'),
(43, 4, 'Đậu đỏ hạt', 'dau-do', 'Đậu đỏ sạch.', NULL, 55000, NULL, NULL, 'kg', NULL, 'images/product/1765261260_dau-do-hat.jpg', NULL, 110, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:17', '2025-12-09 06:21:00'),
(44, 3, 'Sữa tươi không đường', 'sua-tuoi', 'Sữa tươi nguyên chất.', NULL, 35000, NULL, NULL, 'kg', NULL, 'images/product/1765261273_sua-tuoi.jpg', NULL, 199, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-05 18:12:27', '2025-12-09 07:23:48'),
(58, 1, 'Rau mồng tơi hữu cơ', 'rau-mong-toi-huu-co', 'Rau mồng tơi xanh tươi, thích hợp nấu canh.', NULL, 15000, NULL, NULL, 'bó', NULL, 'images/product/1765264339_rau-mong-toi.png', NULL, 88, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 18:40:37'),
(59, 1, 'Khoai lang mật Đà Lạt', 'khoai-lang-mat', 'Khoai lang mật ngọt lịm, dẻo thơm.', NULL, 35000, NULL, NULL, 'kg', NULL, 'images/product/1765264353_khoai-lang.jpg', NULL, 150, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:12:33'),
(60, 1, 'Cà tím quả dài', 'ca-tim', 'Cà tím tươi, vỏ bóng mượt, ít hạt.', NULL, 22000, NULL, NULL, 'kg', NULL, 'images/product/1765264365_ca-tim.jpg', NULL, 77, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 18:40:37'),
(61, 1, 'Ớt chuông đỏ', 'ot-chuong-do', 'Ớt chuông đỏ giàu vitamin C, giòn ngọt.', NULL, 65000, NULL, NULL, 'kg', NULL, 'images/product/1765264382_ot-chuong.jpg', NULL, 60, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:13:02'),
(62, 1, 'Đậu bắp xanh', 'dau-bap', 'Đậu bắp tươi non, tốt cho xương khớp.', NULL, 20000, NULL, NULL, 'kg', NULL, 'images/product/1765264391_dau-bap.jpg', NULL, 90, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:13:11'),
(63, 1, 'Bí xanh (Bí đao)', 'bi-xanh', 'Bí xanh tươi mát, giải nhiệt tốt.', NULL, 18000, NULL, NULL, 'kg', NULL, 'images/product/1765264402_bi-dao.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:13:22'),
(64, 1, 'Mướp hương', 'muop-huong', 'Mướp hương thơm nhẹ, nấu canh rất ngon.', NULL, 25000, NULL, NULL, 'kg', NULL, 'images/product/1765264418_muop-huong.jpg', NULL, 69, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 18:40:37'),
(65, 1, 'Rau ngót sạch', 'rau-ngot', 'Rau ngót trồng chuẩn organic, nhiều đạm.', NULL, 15000, NULL, NULL, 'bó', NULL, 'images/product/1765264446_rau-ngot.jpg', NULL, 117, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 18:40:37'),
(66, 1, 'Củ dền đỏ', 'cu-den-do', 'Củ dền đỏ bổ máu, màu sắc đẹp tự nhiên.', NULL, 30000, NULL, NULL, 'kg', NULL, 'images/product/1765264458_cu-den.jpg', NULL, 48, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 17:42:09'),
(67, 1, 'Nấm đùi gà', 'nam-dui-ga', 'Nấm đùi gà tươi, giòn sần sật.', NULL, 85000, NULL, NULL, 'kg', NULL, 'images/product/1765264469_nam-dui-ga.jpg', NULL, 40, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:14:29'),
(68, 2, 'Dưa hấu đỏ không hạt', 'dua-hau-khong-hat', 'Dưa hấu đỏ ngọt lịm, mọng nước.', NULL, 18000, NULL, NULL, 'kg', NULL, 'images/product/1765264479_dua-hau-ruot-do.jpg', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:14:39'),
(69, 2, 'Ổi nữ hoàng', 'oi-nu-hoang', 'Ổi giòn, ít hạt, vị ngọt thanh.', NULL, 25000, NULL, NULL, 'kg', NULL, 'images/product/1765264491_oi-nu-hoang.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:14:51'),
(70, 2, 'Măng cụt Lái Thiêu', 'mang-cut', 'Măng cụt vỏ mỏng, múi trắng tinh.', NULL, 75000, NULL, NULL, 'kg', NULL, 'images/product/1765264512_mang-cut.jpg', NULL, 50, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:15:12'),
(71, 2, 'Chôm chôm nhãn', 'chom-chom-nhan', 'Chôm chôm tróc hạt, cơm dày.', NULL, 40000, NULL, NULL, 'kg', NULL, 'images/product/1765264522_chom-chom.jpg', NULL, 80, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:15:22'),
(72, 2, 'Sầu riêng Ri6', 'sau-rieng-ri6', 'Sầu riêng cơm vàng hạt lép, thơm nức.', NULL, 150000, NULL, NULL, 'kg', NULL, 'images/product/1765264532_sau-rieng.jpg', NULL, 30, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:15:32'),
(73, 2, 'Vải thiều Bắc Giang', 'vai-thieu', 'Vải thiều hạt nhỏ, nước ngọt đậm.', NULL, 55000, NULL, NULL, 'kg', NULL, 'images/product/1765264554_vai-thieu.jpg', NULL, 60, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:15:54'),
(74, 2, 'Đu đủ ruột vàng', 'du-du-ruot-vang', 'Đu đủ chín cây, ngọt tự nhiên.', NULL, 22000, NULL, NULL, 'kg', NULL, 'images/product/1765264565_du-du.jpg', NULL, 90, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:16:05'),
(75, 2, 'Bưởi da xanh', 'buoi-da-xanh', 'Bưởi tép hồng, không bị khô.', NULL, 60000, NULL, NULL, 'kg', NULL, 'images/product/1765264576_buoi-da-xanh.jpg', NULL, 70, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:16:16'),
(76, 2, 'Dừa xiêm Bến Tre', 'dua-xiem', 'Dừa xiêm nước ngọt, mát lành.', NULL, 15000, NULL, NULL, 'trái', NULL, 'images/product/1765264589_dua.jpg', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:16:29'),
(77, 2, 'Kiwi xanh New Zealand', 'kiwi-xanh', 'Kiwi nhập khẩu, vị chua ngọt hài hòa.', NULL, 130000, NULL, NULL, 'kg', NULL, 'images/product/1765264601_kiwi-xanh.jpg', NULL, 40, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:16:41'),
(78, 3, 'Trứng vịt chạy đồng', 'trung-vit-chay-dong', 'Trứng vịt lòng đỏ to, béo ngậy.', NULL, 35000, NULL, NULL, 'chục', NULL, 'images/product/1765264623_trung-vit.jpg', NULL, 300, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:17:03'),
(79, 3, 'Trứng cút tươi', 'trung-cut', 'Trứng cút tươi mới mỗi ngày.', NULL, 25000, NULL, NULL, 'vỉ 30', NULL, 'images/product/1765264636_trung-cut.jpg', NULL, 500, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:17:16'),
(80, 3, 'Trứng gà ác', 'trung-ga-ac', 'Trứng gà ác bổ dưỡng cho người ốm.', NULL, 45000, NULL, NULL, 'chục', NULL, 'images/product/1765264648_trung_ga_ac.jpg', NULL, 100, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:17:28'),
(81, 3, 'Bơ lạt Anchor', 'bo-lat-anchor', 'Bơ lạt nhập khẩu dùng làm bánh.', NULL, 85000, NULL, NULL, 'gói 250g', NULL, 'images/product/1765264660_bo-lat-anchor.jpg', NULL, 50, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:17:40'),
(82, 3, 'Phô mai lát Cheddar', 'pho-mai-lat', 'Phô mai lát dùng kẹp bánh mì.', NULL, 65000, NULL, NULL, 'gói 12 lát', NULL, 'images/product/1765264675_Pho-mai-lat-Cheddar.jpg', NULL, 80, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:17:55'),
(83, 3, 'Sữa chua nha đam', 'sua-chua-nha-dam', 'Sữa chua nhà làm với nha đam giòn.', NULL, 8000, NULL, NULL, 'hũ', NULL, 'images/product/1765264687_sua-chua-nha-dam.jpg', NULL, 200, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:18:07'),
(84, 3, 'Sữa đặc Ông Thọ', 'sua-dac', 'Sữa đặc có đường truyền thống.', NULL, 28000, NULL, NULL, 'hộp', NULL, 'images/product/1765264699_sua-ong-tho.jpg', NULL, 100, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:18:19'),
(85, 3, 'Váng sữa Đức', 'vang-sua', 'Váng sữa giàu canxi cho bé.', NULL, 55000, NULL, NULL, 'vỉ 4 hộp', NULL, 'images/product/1765264710_vang-sua.jpg', NULL, 60, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:18:30'),
(86, 3, 'Trứng vịt muối', 'trung-vit-muoi', 'Trứng muối tro bếp thủ công.', NULL, 45000, NULL, NULL, 'chục', NULL, 'images/product/1765264724_trung-vit-muoi.jpg', NULL, 100, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:18:44'),
(87, 3, 'Kem tươi Whipping Cream', 'whipping-cream', 'Kem tươi đánh bông làm bánh.', NULL, 145000, NULL, NULL, 'hộp 1L', NULL, 'images/product/1765264769_kem-tuoi-whipping-cream.jpg', NULL, 30, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:19:29'),
(88, 4, 'Bánh mì Sandwich nguyên cám', 'sandwich-nguyen-cam', 'Bánh mì sandwich tốt cho ăn kiêng.', NULL, 35000, NULL, NULL, 'gói', NULL, 'images/product/1765264785_banh-mi-sanwich.jpg', NULL, 50, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:19:45'),
(89, 4, 'Bánh mì Baguette', 'banh-mi-baguette', 'Bánh mì pháp vỏ giòn ruột mềm.', NULL, 15000, NULL, NULL, 'ổ', NULL, 'images/product/1765264800_Baguette.jpg', NULL, 100, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:20:00'),
(90, 4, 'Bánh sừng trâu (Croissant)', 'banh-croissant', 'Bánh ngàn lớp thơm mùi bơ.', NULL, 25000, NULL, NULL, 'cái', NULL, 'images/product/1765264810_banh-sung-trau.jpg', NULL, 60, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:20:10'),
(92, 4, 'Bánh quy yến mạch', 'banh-quy-yen-mach', 'Bánh quy healthy từ yến mạch.', NULL, 55000, NULL, NULL, 'hũ 200g', NULL, 'images/product/1765264820_banh-quy-yen-mach.jpg', NULL, 80, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:20:20'),
(103, 5, 'Bạch tuộc tươi', 'bach-tuoc', 'Bạch tuộc tươi, giòn ngọt.', NULL, 160000, NULL, NULL, 'kg', NULL, 'images/product/1765264886_bach-tuoc.jpg', NULL, 39, NULL, NULL, 0, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 17:01:42'),
(104, 5, 'Cá diêu hồng', 'ca-dieu-hong', 'Cá diêu hồng sống, làm sạch.', NULL, 65000, NULL, NULL, 'kg', NULL, 'images/product/1765264875_ca-dieu-hong.jpg', NULL, 70, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-09 07:21:15'),
(105, 5, 'Hàu sữa Pháp', 'hau-sua', 'Hàu sữa béo, nhiều dinh dưỡng.', NULL, 50000, NULL, NULL, 'kg', NULL, 'images/product/1765264867_hau-sua.jpg', NULL, 96, NULL, NULL, 0, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 18:36:03'),
(106, 5, 'Nghêu sạch Gò Công', 'ngheu-sach', 'Nghêu sạch cát, thịt đầy.', NULL, 35000, NULL, NULL, 'kg', NULL, 'images/product/1765264855_ngheu-sach3.jpg', NULL, 149, NULL, NULL, 0, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 17:26:20'),
(107, 5, 'Cá lóc đồng', 'ca-loc-dong', 'Cá lóc đồng thịt dai, ngọt.', NULL, 110000, NULL, NULL, 'kg', NULL, 'images/product/1765264834_ca-loc.jpg', NULL, 28, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-12-09 06:00:44', '2025-12-12 17:42:09');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE IF NOT EXISTS `product_reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `rating` int NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `helpful_count` int DEFAULT '0',
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_reply` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `images`, `helpful_count`, `status`, `admin_reply`, `replied_at`, `created_at`, `updated_at`) VALUES
(9, 105, 12, NULL, 5, NULL, 'Hàu cũng tươi á', NULL, 0, 'approved', NULL, NULL, '2025-12-12 17:51:19', '2025-12-12 17:52:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `setting_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Xanh Organic', 'text', 'Tên website', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(2, 'site_email', 'info@xanhorganic.vn', 'email', 'Email liên hệ', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(3, 'site_phone', '1900123456', 'text', 'Số điện thoại', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(4, 'smtp_host', 'smtp.gmail.com', 'text', 'SMTP Host', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(5, 'smtp_port', '587', 'text', 'SMTP Port', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(6, 'smtp_username', '', 'text', 'SMTP Username', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(7, 'smtp_password', 'admin123', 'password', 'SMTP Password', '2025-11-24 08:14:59', '2025-11-29 17:45:42'),
(8, 'free_shipping_threshold', '200000', 'number', 'Miễn phí ship từ', '2025-11-24 08:14:59', '2025-12-12 18:34:45'),
(9, 'default_shipping_fee', '25000', 'number', 'Phí ship mặc định', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(13, 'site_address', '123 Đường Xanh, Q.1, TP.HCM', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(14, 'site_description', 'Rau sạch hữu cơ, giao tận nhà', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(15, 'site_keywords', 'rau sạch, thực phẩm hữu cơ, organic', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(16, 'social_facebook', 'https://www.facebook.com/', 'text', NULL, '2025-11-29 17:45:42', '2025-12-07 10:28:47'),
(17, 'social_instagram', 'https://www.instagram.com/', 'text', NULL, '2025-11-29 17:45:42', '2025-12-07 10:28:47'),
(18, 'social_zalo', '', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(19, 'social_youtube', 'https://www.youtube.com/watch?v=WCm2elbTEZQ&list=RDmi648p8uDPc&index=9', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(22, 'smtp_encryption', 'tls', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(25, 'mail_from_address', 'noreply@xanhorganic.vn', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(26, 'mail_from_name', 'Xanh Organic', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(29, 'estimated_delivery', '2-3 ngày', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(30, 'cod_enabled', '1', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(31, 'payment_cod', '1', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(32, 'payment_bank', '1', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(33, 'bank_name', 'Vietcombank', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(34, 'bank_account', '1234567890', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(35, 'bank_account_name', 'CONG TY XANH ORGANIC', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(36, 'vnpay_tmn_code', '', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(37, 'vnpay_hash_secret', '', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(38, 'payment_vnpay', '0', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(39, 'site_logo', 'images/logo/logo_1764444683_logo.png', 'text', NULL, '2025-11-29 17:48:30', '2025-11-29 19:31:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `membership` enum('bronze','silver','gold','platinum') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'bronze',
  `points` int DEFAULT '0',
  `role` enum('customer','admin','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `birthdate`, `membership`, `points`, `role`, `is_active`, `email_verified`, `email_verified_at`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `status`) VALUES
(3, 'Bùi Minh Tài', 'buiminhtai97@gmail.com', '0966330634', '$2y$10$GBEKwBCOxBUUWXwxiFRbhuQ9XKO4/Xd4HqHZjfDvSk.FLFcPJkdba', 'images/avatars/user_3_1764480499.jpg', NULL, '2025-11-19', 'gold', 0, 'staff', 1, 0, NULL, NULL, NULL, '2025-11-24 08:23:40', '2025-12-09 07:37:32', 'active'),
(4, 'admin', 'admin@xanhorganic.com', '0966330634', '$2y$10$fXjLRd/NLOBh/7l4v2KdDOxowj9MOGfc6dF3u/3aAKFS4VV4932gy', NULL, NULL, '0000-00-00', 'bronze', 0, 'admin', 1, 0, NULL, NULL, NULL, '2025-11-24 08:24:00', '2025-11-24 10:09:46', 'active'),
(5, 'dâu tây hữu cơ', 'dautayhuuco@gmail.com', '0966330634', '$2y$10$kIMlg1gnDJQZn5MZ5cEjVOisEaSGZFV/uHi45ST8Sm/o7woyxW4Ay', NULL, NULL, NULL, 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-11-28 06:18:29', '2025-11-28 06:18:29', 'active'),
(6, 'người iu của Minh Tài', 'thaovimk0902@gmail.com', '0528837261', '$2y$10$GvYK6IFYYFUH2tSI/YAUwOQc9xKkynHKfa031nrJkMH5insez9Kji', 'images/avatars/user_6_1765127309.jpeg', NULL, '0000-00-00', 'gold', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-12-05 18:21:32', '2025-12-07 17:08:29', 'active'),
(7, 'Bùi Minh Nhật', 'buiminhtai3114@gmail.com', '0966330635', '$2y$10$q3svyK9L/msMUV4/TgnIe.osjUTNYFvKw5TesjhpvoZWNP3VepGlW', 'images/avatars/user_7_1765107084.jpeg', NULL, '2025-12-31', 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-12-07 11:26:09', '2025-12-07 12:12:00', 'active'),
(8, 'Tài', 'thijenphuc@gmail.com', '0898489754', '$2y$10$mAcPer6s3YDUG.C8tFgD7.Sj.KtwHUkJ/qLNWvjwa53ciRWyYtPaW', NULL, NULL, NULL, 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-12-07 11:53:52', '2025-12-07 12:13:48', 'active'),
(12, 'Nguyễn Trung Hậu', 'haunguyen04012000@gmail.com', '0931878932', '$2y$10$wVeD6reeki3PgWepFIyrke9XcWICP1ooaH1msGhgvkQmVl23x6M9a', NULL, NULL, NULL, 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-12-12 16:18:48', '2025-12-12 16:18:48', 'active'),
(14, 'user', 'user@xanhorganic.com', '096452626', '$2y$10$Wu34MOt2RSOhTQVoQ4JA/escuZ9e78CFo.73Pc8uEXnzjH1E/VSjm', NULL, NULL, NULL, 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-12-12 18:45:00', '2025-12-12 18:45:00', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(50, 12, 60, '2025-12-12 16:35:14'),
(51, 12, 65, '2025-12-12 16:35:15'),
(52, 12, 2, '2025-12-12 16:36:41'),
(62, 3, 105, '2025-12-12 18:32:11'),
(63, 3, 15, '2025-12-12 18:32:12'),
(64, 3, 5, '2025-12-12 18:32:13'),
(65, 3, 6, '2025-12-12 18:32:14'),
(66, 3, 12, '2025-12-12 18:32:15'),
(69, 14, 103, '2025-12-13 03:42:09'),
(70, 14, 105, '2025-12-13 03:42:10'),
(71, 14, 15, '2025-12-13 03:42:12'),
(72, 14, 60, '2025-12-13 03:42:21'),
(73, 14, 65, '2025-12-13 03:42:22');

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
