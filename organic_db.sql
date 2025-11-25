-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th10 24, 2025 lúc 04:23 PM
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
  `action` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `ward` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_type` enum('home','office','other') COLLATE utf8mb4_unicode_ci DEFAULT 'home',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `author_id` int DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `view_count` int DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'grass',
  `description` text COLLATE utf8mb4_unicode_ci,
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
(3, 'Trứng &amp; Bơ sữa', 'trung-bo-sua', 'images/categories/1763973681_sua-trung-organic-1763692144.jpg', 'Sản phẩm từ trứng và sữa', NULL, 3, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:21'),
(4, 'Bánh mì &amp; Bánh ngọt', 'banh-mi', 'images/categories/1763973688_gao-ngu-coc-1763692122.png', 'Bánh mì và bánh ngọt tươi mỗi ngày', NULL, 4, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:28'),
(5, 'Thịt &amp; Hải sản', 'thit-hai-san', 'images/categories/1763973697_thit-hai-san-sach-1763692154.jpg', 'Thịt và hải sản tươi sống', NULL, 5, 1, '2025-11-24 08:14:59', '2025-11-24 08:41:37');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','replied','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Minh Tài', 'buiminhtai97@gmail.com', '0966330634', 'other', 'chủ sốp đẹp trai quá cần up ảnh đại diện', 'pending', '2025-11-24 16:02:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
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
-- Cấu trúc bảng cho bảng `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','unsubscribed') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `order_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_amount` decimal(12,0) NOT NULL,
  `discount_amount` decimal(10,0) DEFAULT '0',
  `shipping_fee` decimal(10,0) DEFAULT '25000',
  `tax_amount` decimal(10,0) DEFAULT '0',
  `final_amount` decimal(12,0) NOT NULL,
  `status` enum('pending','confirmed','processing','shipping','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `shipping_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_ward` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `coupon_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cancelled_reason` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_order_code` (`order_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, 3, 'ORD202511249083', 127000, 0, 25000, 0, 152000, 'confirmed', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', 'Xã tân Thới Nhì', 'Hóc Môn', 'TP. Hồ Chí Minh', 'Đơn hàng đầu như đơn hàng cuối', NULL, NULL, NULL, NULL, NULL, '2025-11-24 15:36:46', '2025-11-24 15:37:12'),
(10, 4, 'ORD202511248463', 127000, 0, 25000, 0, 152000, 'delivered', 'cod', 'pending', 'admin', '0966330634', 'TTN', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:00:46', '2025-11-24 16:01:42'),
(11, 3, 'ORD202511248059', 63000, 0, 25000, 0, 88000, 'delivered', 'cod', 'pending', 'Minh Tài', '0966330634', 'TPHCM', '', '', 'TP. Hồ Chí Minh', '', NULL, NULL, NULL, NULL, NULL, '2025-11-24 16:01:21', '2025-11-24 16:01:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,0) NOT NULL,
  `total_price` decimal(12,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(24, 11, 2, 'Bông cải xanh', 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', 1, 28000, 28000, '2025-11-24 16:01:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,0) NOT NULL,
  `sale_price` decimal(10,0) DEFAULT NULL,
  `cost_price` decimal(10,0) DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'kg',
  `weight` decimal(8,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text COLLATE utf8mb4_unicode_ci,
  `stock` int DEFAULT '0',
  `sku` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_organic` tinyint(1) DEFAULT '1',
  `is_new` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_bestseller` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `view_count` int DEFAULT '0',
  `sold_count` int DEFAULT '0',
  `rating_avg` decimal(3,2) DEFAULT '0.00',
  `rating_count` int DEFAULT '0',
  `meta_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `cost_price`, `unit`, `weight`, `image`, `gallery`, `stock`, `sku`, `barcode`, `is_organic`, `is_new`, `is_featured`, `is_bestseller`, `is_active`, `view_count`, `sold_count`, `rating_avg`, `rating_count`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cà rốt hữu cơ', 'ca-rot-huu-co', 'Cà rốt tươi ngon từ Đà Lạt, giàu vitamin A tốt cho mắt', NULL, 35000, NULL, NULL, '500g', NULL, 'images/product/1763973816_carot.png', NULL, 93, 'VEG001', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-24 16:01:21'),
(2, 1, 'Bông cải xanh', 'bong-cai-xanh', 'Bông cải xanh giàu vitamin C và chất xơ', NULL, 33000, 28000, NULL, 'cái', NULL, 'images/product/1763973825_bong-cai-xanh-organic-1763783111-87a73afeaf.png', NULL, 42, 'VEG002', NULL, 1, 0, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-24 16:01:21'),
(3, 1, 'Cà chua bi', 'ca-chua-bi', 'Cà chua bi ngọt tự nhiên, hoàn hảo cho salad', NULL, 25000, NULL, NULL, 'hộp 250g', NULL, 'images/product/1763973835_ca-chua-bi-organic-1763772009-3acfe533b4.png', NULL, 75, 'VEG003', NULL, 1, 1, 0, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-24 15:29:31'),
(4, 2, 'Táo Envy', 'tao-envy', 'Táo nhập khẩu New Zealand, giòn ngọt', NULL, 99000, NULL, NULL, '0.5kg', NULL, 'images/product/1763973845_tao-fuji-organic-1763771911-ddf5bd12b6.png', NULL, 26, 'FRU001', NULL, 1, 1, 1, 0, 1, 0, 0, 0.00, 0, NULL, NULL, NULL, '2025-11-24 08:14:59', '2025-11-24 16:00:46'),
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
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `images` text COLLATE utf8mb4_unicode_ci,
  `helpful_count` int DEFAULT '0',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_reply` text COLLATE utf8mb4_unicode_ci,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_status` (`status`)
) ;

--
-- Đang đổ dữ liệu cho bảng `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `images`, `helpful_count`, `status`, `admin_reply`, `replied_at`, `created_at`, `updated_at`) VALUES
(1, 1, 3, NULL, 5, NULL, 'sản phẩm ngon nha', NULL, 0, 'approved', NULL, NULL, '2025-11-24 16:15:45', '2025-11-24 16:15:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(7, 'smtp_password', '', 'password', 'SMTP Password', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(8, 'free_shipping_threshold', '500000', 'number', 'Miễn phí ship từ', '2025-11-24 08:14:59', '2025-11-24 08:14:59'),
(9, 'default_shipping_fee', '25000', 'number', 'Phí ship mặc định', '2025-11-24 08:14:59', '2025-11-24 08:14:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `membership` enum('bronze','silver','gold','platinum') COLLATE utf8mb4_unicode_ci DEFAULT 'bronze',
  `points` int DEFAULT '0',
  `role` enum('customer','admin','staff') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `avatar`, `gender`, `birthdate`, `membership`, `points`, `role`, `is_active`, `email_verified`, `email_verified_at`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`, `status`) VALUES
(3, 'Minh Tài', 'buiminhtai97@gmail.com', '0966330634', '$2y$10$piCbeGjCkGQ3nhytfonGh..fER3m2macffMqVkoQ88sriXztIgvY.', NULL, NULL, '0000-00-00', 'gold', 0, 'customer', 1, 0, NULL, NULL, NULL, '2025-11-24 08:23:40', '2025-11-24 15:38:14', 'active'),
(4, 'admin', 'admin@xanhorganic.com', '0966330634', '$2y$10$fXjLRd/NLOBh/7l4v2KdDOxowj9MOGfc6dF3u/3aAKFS4VV4932gy', NULL, NULL, '0000-00-00', 'bronze', 0, 'admin', 1, 0, NULL, NULL, NULL, '2025-11-24 08:24:00', '2025-11-24 10:09:46', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_addresses`
--

DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address`, `note`, `created_at`) VALUES
(3, 4, 'TTN', 'không', '2025-11-24 10:11:06'),
(4, 3, 'TTN', 'không', '2025-11-24 12:19:20'),
(5, 3, 'TPHCM', 'dc2', '2025-11-24 12:23:16');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Các ràng buộc cho bảng `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

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
