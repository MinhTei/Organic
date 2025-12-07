-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th12 04, 2025 lúc 03:29 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10, 200000, 50000, 100, 0, '2025-11-24 08:14:59', '2025-12-24 08:14:59', 1, '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(2, 'FREESHIP', 'Miễn phí vận chuyển cho đơn từ 500k', 'fixed', 25000, 500000, NULL, NULL, 0, '2025-11-24 08:14:59', '2026-01-23 08:14:59', 1, '2025-11-24 08:14:59', '2025-11-24 08:14:59');

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
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_is_default` (`is_default`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `user_id`, `name`, `phone`, `address`, `note`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 3, 'Minh Hậu', '0666666666', 'Nhà bè', '', 0, '2025-11-25 05:28:22', '2025-11-25 05:39:59'),
(4, 3, 'Minh Tài', '0966330643', 'nhà bồn ha', '', 1, '2025-11-25 05:39:43', '2025-11-25 05:39:59'),
(6, 3, 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'địa chỉ người nhận gần đây', 0, '2025-11-25 06:08:30', '2025-11-25 06:08:30'),
(7, 3, 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'địa chỉ người nhận gần đây', 0, '2025-11-27 16:17:38', '2025-11-27 16:17:38'),
(8, 3, 'Bùi Minh Tài', '0966330655', 'TTN', 'địa chỉ người nhận gần đây', 0, '2025-11-27 16:18:06', '2025-11-27 16:18:06'),
(9, 3, 'Minh Tài buiminhtai', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'địa chỉ người nhận gần đây', 0, '2025-11-27 16:43:19', '2025-11-27 16:43:19'),
(10, 3, 'dâu tây hữu cơ', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'địa chỉ người nhận gần đây', 0, '2025-11-27 16:44:16', '2025-11-27 16:44:16'),
(13, 5, 'Minh Hậu', '033333333', 'TPHCM', '', 1, '2025-11-28 07:25:02', '2025-11-28 07:25:02'),
(12, 5, 'Minh Hậu', '0666666666', 'Hà Nội', '', 0, '2025-11-28 07:24:09', '2025-11-28 07:25:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_amount`, `discount_amount`, `shipping_fee`, `tax_amount`, `final_amount`, `status`, `payment_method`, `payment_status`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_ward`, `shipping_district`, `shipping_city`, `note`, `coupon_code`, `tracking_number`, `cancelled_reason`, `cancelled_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'ORD202511244735', 63000, 0, 25000, 0, 88000, 'shipping', 'cod', 'pending', 'Minh Tài', '0966330634', 'TTN', '1', '1', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 12:19:39', '2025-11-24 15:54:10'),
(2, 3, 'ORD202511241837', 35000, 0, 25000, 0, 60000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:26:48', '2025-11-24 15:54:07'),
(3, 3, 'ORD202511246683', 113000, 0, 25000, 0, 138000, 'delivered', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:28:16', '2025-11-24 15:37:28'),
(4, 3, 'ORD202511246349', 88000, 0, 25000, 0, 113000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:28:30', '2025-11-24 15:37:26'),
(5, 3, 'ORD202511244967', 113000, 0, 25000, 0, 138000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:29:31', '2025-11-24 15:37:22'),
(6, 3, 'ORD202511248026', 63000, 0, 25000, 0, 88000, 'shipping', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:32:10', '2025-11-24 15:37:20'),
(7, 3, 'ORD202511247304', 228000, 0, 25000, 0, 253000, 'confirmed', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:33:21', '2025-11-24 15:37:17'),
(8, 3, 'ORD202511245491', 744000, 0, 0, 0, 744000, 'confirmed', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', 'dơn đầu tiên cũng như cuối cùng', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:35:16', '2025-11-24 15:37:15'),
(9, 3, 'ORD202511249083', 127000, 0, 25000, 0, 152000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', 'Đơn hàng đầu như đơn hàng cuối', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:36:46', '2025-11-25 03:58:20'),
(10, 4, 'ORD202511248463', 127000, 0, 25000, 0, 152000, 'delivered', 'cod', 'pending', 'admin', '0966330634', 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:00:46', '2025-11-24 16:01:42'),
(11, 3, 'ORD202511248059', 63000, 0, 25000, 0, 88000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:01:21', '2025-11-24 16:01:40'),
(12, 3, 'ORD202511252231', 127000, 0, 25000, 0, 152000, 'delivered', 'bank_transfer', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 03:57:39', '2025-11-25 03:58:17'),
(13, 3, 'ORD202511258313', 60000, 0, 25000, 0, 85000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'Quận 1', 'xã đông cú', 'huyện trảng  bom', 'TP. Hồ Chí Minh', 'khogn', NULL, NULL, NULL, NULL, NULL, '2025-11-25 04:02:08', '2025-11-25 04:06:34'),
(14, 3, 'ORD202511257565', 63000, 0, 25000, 0, 88000, 'delivered', 'bank_transfer', 'pending', 'Trung Tấn', '0966330634', 'Quận 1', '1', '22', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 04:06:11', '2025-11-25 04:06:31'),
(15, 3, 'ORD202511252579', 127000, 0, 25000, 0, 152000, 'shipping', 'cod', 'pending', 'Minh Tài', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 05:29:01', '2025-11-27 17:01:18'),
(16, 3, 'ORD202511253941', 63000, 0, 25000, 0, 88000, 'pending', 'bank_transfer', 'pending', 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hà Nội', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 05:48:03', '2025-11-25 05:48:03'),
(17, 3, 'ORD202511252656', 53000, 0, 25000, 0, 78000, 'cancelled', 'bank_transfer', 'pending', 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hà Nọi', '', NULL, NULL, NULL, NULL, NULL, '2025-11-25 06:08:30', '2025-11-27 17:42:09'),
(18, 3, 'ORD202511271490', 152000, 0, 25000, 0, 177000, 'shipping', 'cod', 'pending', 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:17:38', '2025-11-27 17:01:14'),
(19, 3, 'ORD202511273678', 255000, 0, 25000, 0, 280000, 'confirmed', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:18:06', '2025-11-27 17:01:11'),
(23, 3, 'ORD202511279883', 495000, 0, 25000, 0, 520000, 'delivered', 'cod', 'pending', 'Minh Tài buiminhtai', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:43:19', '2025-11-27 17:01:09'),
(24, 3, 'ORD202511279024', 135000, 0, 25000, 0, 160000, 'cancelled', 'cod', 'pending', 'dâu tây hữu cơ', '0966330634', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', 'Xã tân Thới Nhì', '1', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-27 16:44:16', '2025-11-27 17:41:55'),
(25, 3, 'ORD202511279226', 124000, 0, 25000, 0, 149000, 'cancelled', 'cod', 'pending', 'Minh Tài', '0966330643', 'nhà bồn ha', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, '2025-11-27 16:56:25', NULL, '2025-11-27 16:47:06', '2025-11-27 16:57:02'),
(26, 5, 'ORD202511289896', 255000, 0, 25000, 0, 280000, 'cancelled', 'cod', 'pending', 'phuc', '096663333', 'TTN', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-28 07:13:24', '2025-11-28 07:13:57'),
(27, 3, 'ORD202511309645', 148000, 0, 25000, 0, 173000, 'pending', 'cod', 'pending', 'Hiếu Toàn', '0966330649', '65/13A,Ấp Dân Thằng 1, Xã Tân Thới Nhì, Hóc Môn', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-29 18:44:00', '2025-11-29 18:44:00'),
(28, 3, 'ORD202511305288', 148000, 0, 25000, 0, 173000, 'pending', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-29 18:45:37', '2025-11-29 18:45:37'),
(29, 3, 'ORD202511304194', 63000, 0, 25000, 0, 88000, 'pending', 'cod', 'pending', 'Bùi Minh Tài', '0966330655', 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-30 05:17:08', '2025-11-30 05:17:08'),
(30, 3, 'ORD202511308036', 63000, 0, 25000, 0, 88000, 'pending', 'cod', 'pending', 'Minh Tài', '0966330643', 'nhà bồn ha', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-30 05:28:45', '2025-11-30 05:28:45');

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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_image`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 12:19:39'),
(2, 1, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 12:19:39'),
(3, 2, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:26:48'),
(4, 3, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:28:16'),
(5, 3, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:28:16'),
(6, 3, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 2, 25000, 50000, '2025-11-24 15:28:16'),
(7, 4, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:28:30'),
(8, 4, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:28:30'),
(9, 4, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-24 15:28:30'),
(10, 5, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:29:31'),
(11, 5, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:29:31'),
(12, 5, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 2, 25000, 50000, '2025-11-24 15:29:31'),
(13, 6, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 15:32:10'),
(14, 6, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:32:10'),
(15, 7, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:33:21'),
(16, 7, 5, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 1, 129000, 129000, '2025-11-24 15:33:21'),
(17, 8, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:35:16'),
(18, 8, 5, 'Trứng gà thả vườn', 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', 5, 129000, 645000, '2025-11-24 15:35:16'),
(19, 9, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 15:36:46'),
(20, 9, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 15:36:46'),
(21, 10, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 16:00:46'),
(22, 10, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-24 16:00:46'),
(23, 11, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-24 16:01:21'),
(24, 11, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 16:01:21'),
(25, 12, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 03:57:39'),
(26, 12, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-25 03:57:39'),
(27, 13, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 04:02:08'),
(28, 13, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-25 04:02:08'),
(29, 14, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 04:06:11'),
(30, 14, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 04:06:11'),
(31, 15, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 05:29:01'),
(32, 15, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-25 05:29:01'),
(33, 16, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-25 05:48:03'),
(34, 16, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 05:48:03'),
(35, 17, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-25 06:08:30'),
(36, 17, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-25 06:08:30'),
(37, 18, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-27 16:17:38'),
(38, 18, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-27 16:17:38'),
(39, 18, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-27 16:17:38'),
(40, 19, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:18:06'),
(41, 19, NULL, 'Cá heo', '', 1, 120000, 120000, '2025-11-27 16:18:06'),
(42, 19, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:18:06'),
(46, 23, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:43:19'),
(47, 23, NULL, 'Cá heo', '', 3, 120000, 360000, '2025-11-27 16:43:19'),
(48, 23, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:43:19'),
(49, 24, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-27 16:44:16'),
(50, 24, NULL, 'Cá mập', '', 1, 100000, 100000, '2025-11-27 16:44:16'),
(51, 25, 3, 'Cà chua bi', 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', 1, 25000, 25000, '2025-11-27 16:47:06'),
(52, 25, 4, 'Táo Envy', 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', 1, 99000, 99000, '2025-11-27 16:47:06'),
(53, 26, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-28 07:13:24'),
(54, 26, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-28 07:13:24'),
(55, 26, NULL, 'Cá mập', 'images/product/1764263239_c___m___p.png', 1, 100000, 100000, '2025-11-28 07:13:24'),
(56, 27, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-29 18:44:00'),
(57, 27, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-29 18:44:00'),
(58, 28, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-29 18:45:37'),
(59, 28, NULL, 'Cá heo', 'images/product/1764263229_Dolphin_.jpg', 1, 120000, 120000, '2025-11-29 18:45:37'),
(60, 29, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-30 05:17:08'),
(61, 29, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-30 05:17:08'),
(62, 30, 1, 'Cà rốt hữu cơ', 'images/product/1763973816_carot.png', 1, 35000, 35000, '2025-11-30 05:28:45'),
(63, 30, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-30 05:28:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(2, 'buiminhtai97@gmail.com', '909a66dc54bc9967618d0270e3795e1f3de6250a46930c18ab480b1e5f978d96', '2025-11-29 18:52:47', '2025-11-29 18:49:47');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `cost_price`, `unit`, `weight`, `image`, `gallery`, `stock`, `sku`, `barcode`, `is_organic`, `is_new`, `is_featured`, `is_bestseller`, `is_active`, `view_count`, `sold_count`, `rating_avg`, `rating_count`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cà rốt hữu cơ', 'ca-rot-huu-co', 'Cà rốt tươi ngon từ Đà Lạt, giàu vitamin A tốt cho mắt', NULL, 35000, NULL, NULL, '500g', NULL, 'images/product/1763973816_carot.png', NULL, 84, 'VEG001', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-30 05:28:45'),
(2, 1, 'Bông cải xanh', 'bong-cai-xanh', 'Bông cải xanh giàu vitamin C và chất xơ', NULL, 33000, 28000, NULL, 'cái', NULL, 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', NULL, 32, 'VEG002', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-30 05:28:45'),
(3, 1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên, hoàn hảo cho salad', NULL, 25000, NULL, NULL, 'hộp 250g', NULL, 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', NULL, 71, 'VEG003', NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-27 16:47:06'),
(4, 2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand, giòn ngọt', NULL, 99000, NULL, NULL, '0.5kg', NULL, 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', NULL, 22, 'FRU001', NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-27 16:47:06'),
(5, 3, 'Trứng gà thả vườn', 'trung-ga-tha-vuon', 'Trứng gà sạch tự nhiên, giàu dinh dưỡng', NULL, 129000, NULL, NULL, 'vỉ 10 trứng', NULL, 'images/product/1763973852_trung-ga-huu-co-1763771854-c6cf9de503.jpg', NULL, 39, 'DAI001', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-24 15:35:16');

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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `images`, `helpful_count`, `status`, `admin_reply`, `replied_at`, `created_at`, `updated_at`) VALUES
(4, 1, 3, NULL, 5, NULL, 'sản phẩm đjep', NULL, 0, 'approved', NULL, NULL, '2025-11-25 04:34:23', '2025-11-25 04:35:23'),
(5, 3, 3, NULL, 5, NULL, 'cà chua ngon', NULL, 0, 'approved', NULL, NULL, '2025-11-25 04:34:36', '2025-11-25 04:35:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(8, 'free_shipping_threshold', '500000', 'number', 'Miễn phí ship từ', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(9, 'default_shipping_fee', '25000', 'number', 'Phí ship mặc định', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(13, 'site_address', '123 Đường Xanh, Q.1, TP.HCM', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(14, 'site_description', 'Rau sạch hữu cơ, giao tận nhà', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(15, 'site_keywords', 'rau sạch, thực phẩm hữu cơ, organic', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(16, 'social_facebook', '', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
(17, 'social_instagram', '', 'text', NULL, '2025-11-29 17:45:42', '2025-11-29 17:45:42'),
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `birthdate`, `membership`, `points`, `role`, `is_active`, `email_verified`, `email_verified_at`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `status`) VALUES
(3, 'Bùi Minh Tài', 'buiminhtai97@gmail.com', '0966330634', '$2y$10$piCbeGjCkGQ3nhytfonGh..fER3m2macffMqVkoQ88sriXztIgvY.', 'images/avatars/user_3_1764480499.jpg', NULL, '2025-11-19', 'gold', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-11-24 08:23:40', '2025-11-30 05:28:19', 'active'),
(4, 'admin', 'admin@xanhorganic.com', '0966330634', '$2y$10$fXjLRd/NLOBh/7l4v2KdDOxowj9MOGfc6dF3u/3aAKFS4VV4932gy', NULL, NULL, '0000-00-00', 'bronze', 0, 'admin', 1, 0, NULL, NULL, NULL, '2025-11-24 08:24:00', '2025-11-24 10:09:46', 'active'),
(5, 'dâu tây hữu cơ', 'dautayhuuco@gmail.com', '0966330634', '$2y$10$kIMlg1gnDJQZn5MZ5cEjVOisEaSGZFV/uHi45ST8Sm/o7woyxW4Ay', NULL, NULL, NULL, 'bronze', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-11-28 06:18:29', '2025-11-28 06:18:29', 'active');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Các ràng buộc cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
