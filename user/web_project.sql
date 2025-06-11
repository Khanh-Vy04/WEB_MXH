-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 06:41 PM
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
-- Database: `web_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `accessories`
--

CREATE TABLE `accessories` (
  `accessory_id` int(11) NOT NULL,
  `accessory_name` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accessories`
--

INSERT INTO `accessories` (`accessory_id`, `accessory_name`, `description`, `price`, `stock`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'Tai nghe Sony WH-1000XM4', 'Tai nghe không dây chống ồn cao cấp với chất lượng âm thanh tuyệt vời và thời lượng pin lên đến 30 giờ', 8399760.00, 25, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 06:39:03'),
(2, 'Loa Marshall Acton II', 'Loa Bluetooth phong cách vintage với âm thanh Marshall đặc trưng, thiết kế nhỏ gọn phù hợp mọi không gian.', 6719760.00, 14, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 07:41:19'),
(3, 'Đầu đĩa Audio-Technica AT-LP60X', 'Đầu đĩa than tự động đầy đủ tính năng với chất lượng âm thanh analog ấm áp và thiết kế hiện đại.', 3599760.00, 12, 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 06:39:03'),
(4, 'Microphone Blue Yeti', 'Microphone USB chuyên nghiệp với 4 chế độ pickup pattern, hoàn hảo cho recording và streaming.', 2399760.00, 20, 'https://images.unsplash.com/photo-1590602847861-f357a9332bbc?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 06:39:03'),
(5, 'Cable XLR Mogami Gold', 'Cáp XLR chất lượng cao từ Mogami với độ bền và chất lượng truyền tín hiệu tuyệt vời.', 1103760.00, 50, 'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 06:39:03'),
(6, 'Kệ đĩa nhạc gỗ tre', 'Kệ để đĩa nhạc làm từ gỗ tre tự nhiên, thiết kế đơn giản và thân thiện với môi trường.', 2159760.00, 8, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop', '2025-05-26 08:11:52', '2025-06-08 06:39:03');

-- --------------------------------------------------------

--
-- Table structure for table `artists`
--

CREATE TABLE `artists` (
  `artist_id` int(11) NOT NULL,
  `artist_name` varchar(255) NOT NULL,
  `bio` longtext NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artists`
--

INSERT INTO `artists` (`artist_id`, `artist_name`, `bio`, `image_url`, `status`) VALUES
(1, 'The Beatles', 'The Beatles là ban nhạc rock người ANH được thành lập tại Liverpool năm 1960. Gồm John Lennon, Paul McCartney, George Harrison và Ringo Starr, họ được coi là ban nhạc có ảnh hưởng nhất trong lịch sử âm nhạc đại chúng. Với những album kinh điển như \"Abbey Road\", \"Sgt. Pepper\'s Lonely Hearts Club Band\", The Beatles đã thay đổi hoàn toàn bộ mặt của âm nhạc rock và pop.', 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/Beatles_press_conference_1965.jpg/250px-Beatles_press_conference_1965.jpg', 1),
(2, 'Adele', 'Adele Laurie Blue Adkins MBE là. một ca sĩ kiêm nhạc sĩ người Anh. Cô nổi tiếng với giọng hát nội lực đầy cảm xúc và khả năng sáng tác những bản ballad sâu sắc về tình yêu và cuộc sống. Với các album \"19\", \"21\", \"25\" và \"30\", Adele đã giành được nhiều giải Grammy và trở thành một trong những nghệ sĩ bán đĩa nhạc thành công nhất thế giới.', 'https://ss-images.saostar.vn/2024/8/5/pc/1722845768031/y4xewqe03p1-o4c7mrobxp2-4s8jssorsm3.jpg', 0),
(3, 'Jack', 'Con mèo kêu sao ☺️', 'https://kenh14cdn.com/203336854389633024/2024/3/10/photo-3-1710036886336500412043.jpg', 1),
(4, 'BLACKPINK', 'Được mệnh danh là \"nhóm nhạc nữ lớn nhất thế giới\", Blackpink là nhóm nhạc nữ Hàn Quốc thành công nhất trên trường quốc tế', 'https://kenh14cdn.com/203336854389633024/2024/8/9/220819-blackpink-pink-venom-global-press-conference-documents-1-17231795151351501301878.jpeg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `artist_products`
--

CREATE TABLE `artist_products` (
  `artist_product_id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artist_products`
--

INSERT INTO `artist_products` (`artist_product_id`, `artist_id`, `product_id`) VALUES
(1, 1, 1),
(4, 2, 2),
(6, 4, 3),
(7, 1, 4),
(8, 2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `auth_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auth_token` varchar(255) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `banner_id` int(11) NOT NULL,
  `banner_url` varchar(500) NOT NULL,
  `banner_title` varchar(255) DEFAULT NULL,
  `banner_description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`banner_id`, `banner_url`, `banner_title`, `banner_description`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES
(2, 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=1200&h=400&fit=crop', 'New Releases Banner', 'Check out the latest music releases', 1, 2, '2025-05-26 02:20:32', '2025-05-26 02:20:32'),
(3, 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=1200&h=400&fit=crop&sat=-100', 'Special Offers Banner', 'Limited time offers on selected albums', 1, 3, '2025-05-26 02:20:32', '2025-05-26 02:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `genre_name` varchar(100) NOT NULL,
  `description` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `genre_name`, `description`) VALUES
(1, 'Rock', 'Thể loại nhạc rock với âm thanh mạnh mẽ, sử dụng guitar điện, bass và trống. Bao gồm các phong cách như classic rock, hard rock, alternative rock.'),
(2, 'Pop', 'Nhạc pop đại chúng với giai điệu dễ nghe, dễ nhớ. Thường có cấu trúc bài hát đơn giản và hướng đến số đông người nghe.'),
(3, 'Jazz', 'Thể loại nhạc jazz với đặc trưng là sự ứng tác, hòa âm phức tạp và nhịp điệu đa dạng. Bao gồm swing, bebop, smooth jazz.'),
(4, 'Classical', 'Nhạc cổ điển với các tác phẩm của những nhà soạn nhạc vĩ đại như Mozart, Beethoven, Bach. Sử dụng dàn nhạc giao hưởng.'),
(6, 'Hip Hop', 'Thể loại nhạc hip hop với đặc trưng là rap, beat mạnh và văn hóa đường phố. Bao gồm old school, trap, conscious rap.'),
(7, 'Country', 'Nhạc country truyền thống của Mỹ với guitar acoustic, banjo và lời ca kể chuyện về cuộc sống nông thôn.'),
(8, 'R&B', 'Rhythm and Blues với âm thanh soulful, vocal mạnh mẽ và nhịp điệu groove. Bao gồm classic R&B và contemporary R&B.'),
(9, 'Soul', 'Test');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('wallet','vnpay','cash') DEFAULT 'wallet',
  `payment_id` int(11) DEFAULT NULL,
  `stage_id` int(11) NOT NULL DEFAULT 0 COMMENT '0: Đặt hàng, 1: Xử lý, 2: Vận chuyển, 3: Hoàn thành, -1: Hủy',
  `voucher_discount` decimal(15,2) DEFAULT 0.00,
  `final_amount` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `order_date`, `total_amount`, `payment_method`, `payment_id`, `stage_id`, `voucher_discount`, `final_amount`) VALUES
(1, 4, '2025-06-08 07:36:35', 800000.00, 'wallet', NULL, 1, 0.00, 800000.00),
(2, 4, '2025-06-08 07:39:18', 2200000.00, 'wallet', NULL, 1, 0.00, 2200000.00),
(3, 4, '2025-06-08 07:41:19', 9719760.00, 'wallet', NULL, 1, 50000.00, 9669760.00),
(8, 4, '2025-06-09 12:17:26', 1600000.00, 'wallet', NULL, 0, 100000.00, 1500000.00),
(10, 4, '2025-06-10 14:09:02', 700000.00, 'wallet', NULL, 1, 30000.00, 670000.00),
(11, 4, '2025-06-10 15:18:01', 700000.00, 'wallet', NULL, 1, 0.00, 0.00),
(12, 4, '2025-06-10 15:19:12', 3200000.00, 'wallet', NULL, 1, 0.00, 0.00),
(13, 4, '2025-06-10 15:22:03', 4200000.00, 'vnpay', 59, 1, 0.00, 0.00),
(14, 4, '2025-06-10 15:25:18', 800000.00, 'cash', NULL, 0, 0.00, 0.00),
(15, 4, '2025-06-10 15:35:04', 1600000.00, 'wallet', NULL, -1, 0.00, 1600000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `item_type` enum('product','accessory') NOT NULL DEFAULT 'product',
  `item_id` int(11) NOT NULL DEFAULT 0,
  `item_name` varchar(255) NOT NULL DEFAULT '',
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `total_price`, `item_type`, `item_id`, `item_name`, `unit_price`) VALUES
(1, 1, 1, 'Abbey Road - The Beatles', 800000.00, 1, 800000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00),
(2, 2, 1, 'Abbey Road - The Beatles', 800000.00, 1, 800000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00),
(3, 2, 2, '21 - Adele', 700000.00, 2, 1400000.00, 'product', 2, '21 - Adele', 700000.00),
(4, 3, 3, 'BORN PINK', 1000000.00, 3, 3000000.00, 'product', 3, 'BORN PINK', 1000000.00),
(5, 3, 2, 'Loa Marshall Acton II', 6719760.00, 1, 6719760.00, 'accessory', 2, 'Loa Marshall Acton II', 6719760.00),
(8, 8, 1, 'Abbey Road - The Beatles', 800000.00, 2, 1600000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00),
(10, 10, 2, '21 - Adele', 700000.00, 1, 700000.00, 'product', 2, '21 - Adele', 700000.00),
(11, 11, 2, '21 - Adele', 700000.00, 1, 700000.00, 'product', 2, '21 - Adele', 700000.00),
(12, 12, 1, 'Abbey Road - The Beatles', 800000.00, 4, 3200000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00),
(13, 13, 2, '21 - Adele', 700000.00, 6, 4200000.00, 'product', 2, '21 - Adele', 700000.00),
(14, 14, 1, 'Abbey Road - The Beatles', 800000.00, 1, 800000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00),
(15, 15, 1, 'Abbey Road - The Beatles', 800000.00, 2, 1600000.00, 'product', 1, 'Abbey Road - The Beatles', 800000.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_stages`
--

CREATE TABLE `order_stages` (
  `stage_id` int(11) NOT NULL,
  `stage_name` varchar(100) NOT NULL,
  `stage_description` text DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#007bff',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_stages`
--

INSERT INTO `order_stages` (`stage_id`, `stage_name`, `stage_description`, `color_code`, `is_active`, `created_at`) VALUES
(-1, 'Đã hủy', 'Đơn hàng bị hủy', '#dc3545', 1, '2025-06-08 06:52:48'),
(0, 'Đặt hàng', 'Đơn hàng vừa được tạo', '#ffc107', 1, '2025-06-08 06:52:48'),
(1, 'Xử lý', 'Đang xử lý và chuẩn bị hàng', '#17a2b8', 1, '2025-06-08 06:52:48'),
(2, 'Vận chuyển', 'Đơn hàng đang được vận chuyển', '#007bff', 1, '2025-06-08 06:52:48'),
(3, 'Hoàn thành', 'Đơn hàng đã được giao thành công', '#28a745', 1, '2025-06-08 06:52:48');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `user_id`, `reset_token`, `expires_at`) VALUES
(6, 4, '3dc30a3f1e95f3d3c981006a854591ee7982ca9716d524a2dc327f54dd6f94be', '2025-06-09 11:46:16');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `genre_id`, `product_name`, `price`, `stock`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Abbey Road - The Beatles', 800000.00, 38, 'Album huyền thoại của The Beatles được phát hành năm 1969. Bao gồm các ca khúc nổi tiếng như \"Come Together\", \"Something\", \"Here Comes the Sun\". Đây là album cuối cùng được thu âm bởi ban nhạc.', 'https://upload.wikimedia.org/wikipedia/en/4/42/Beatles_-_Abbey_Road.jpg', '2025-05-23 06:59:34', '2025-05-25 07:59:37'),
(2, 2, '21 - Adele', 700000.00, 35, 'Album phòng thu thứ hai của Adele, phát hành năm 2011. Album bao gồm các hit như \"Rolling in the Deep\", \"Someone Like You\", \"Set Fire to the Rain\". Đã giành được nhiều giải thưởng Grammy.', 'https://upload.wikimedia.org/wikipedia/en/1/1b/Adele_-_21.png', '2025-05-23 06:59:34', '2025-05-25 07:59:37'),
(3, 2, 'BORN PINK', 1000000.00, 0, 'Born Pink là album phòng thu thứ hai của nhóm nhạc nữ Hàn Quốc Blackpink, được phát hành vào ngày 16 tháng 9 năm 2022, bởi YG Entertainment và Interscope Records', 'https://upload.wikimedia.org/wikipedia/vi/e/e7/Born_Pink_Digital.jpeg', '2025-05-21 06:59:34', '2025-05-25 09:12:03'),
(4, 1, 'Sgt. Pepper\'s Lonely Hearts Club Band - The Beatles', 800000.00, 30, 'Album thứ tám của The Beatles, được phát hành năm 1967. Được coi là một trong những album có ảnh hưởng nhất trong lịch sử âm nhạc rock.', 'https://upload.wikimedia.org/wikipedia/en/5/50/Sgt._Pepper%27s_Lonely_Hearts_Club_Band.jpg', '2025-05-21 06:59:34', '2025-05-26 02:09:59'),
(5, 2, '25 - Adele', 12000000.00, 40, 'Album phòng thu thứ ba của Adele, phát hành năm 2015. Bao gồm hit \"Hello\" và nhiều bản ballad cảm động khác.', 'https://upload.wikimedia.org/wikipedia/en/9/96/Adele_-_25_%28Official_Album_Cover%29.png', '2025-05-11 06:59:34', '2025-05-26 02:09:59');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `minimum_order_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `start_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` longtext NOT NULL,
  `feedback_image_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `accessory_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `item_type` enum('product','accessory') NOT NULL DEFAULT 'product',
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`cart_id`, `user_id`, `product_id`, `accessory_id`, `quantity`, `item_type`, `added_at`) VALUES
(7, 1, 1, NULL, 2, 'product', '2025-06-08 05:58:11'),
(8, 1, NULL, 1, 1, 'accessory', '2025-06-08 05:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `support_replies`
--

CREATE TABLE `support_replies` (
  `reply_id` int(11) NOT NULL,
  `support_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reply_message` longtext NOT NULL,
  `is_customer_reply` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_replies`
--

INSERT INTO `support_replies` (`reply_id`, `support_id`, `admin_id`, `user_id`, `reply_message`, `is_customer_reply`, `created_at`) VALUES
(1, 922394942, NULL, 4, 'hi', 1, '2025-06-06 15:32:29'),
(2, 922394942, NULL, NULL, 'Cảm ơn bạn đã liên hệ với AuraDisc! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi sớm nhất. Vui lòng đợi trong giây lát.', 0, '2025-06-06 15:32:29'),
(3, 922394942, NULL, 4, 'Test', 1, '2025-06-06 15:32:44'),
(4, 922394942, NULL, NULL, 'Test 2', 0, '2025-06-06 16:08:10'),
(5, 922394942, NULL, 4, 'aaaaaa', 1, '2025-06-06 16:08:41'),
(6, 922614064, NULL, 4, 'hiii', 1, '2025-06-06 16:09:00'),
(7, 922614064, NULL, NULL, 'Cảm ơn bạn đã liên hệ với AuraDisc! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi sớm nhất. Vui lòng đợi trong giây lát.', 0, '2025-06-06 16:09:00'),
(8, 922614064, NULL, NULL, 'hi', 0, '2025-06-06 16:09:11'),
(9, 922394942, NULL, 4, 'nnn', 1, '2025-06-06 16:09:34'),
(10, 922394942, NULL, NULL, 'ok', 0, '2025-06-06 16:09:41'),
(11, 922394942, NULL, 4, 'hhhh', 1, '2025-06-08 04:35:05'),
(12, 922394942, NULL, NULL, 'hi bạn', 0, '2025-06-08 04:36:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('Nam','Nữ','Khác') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Số dư tài khoản của user',
  `address` text DEFAULT NULL COMMENT 'Địa chỉ chi tiết của user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `username`, `password`, `email`, `full_name`, `gender`, `phone`, `created_at`, `balance`, `address`) VALUES
(1, 1, 'phampho1103', '$2y$10$9TYZLphZloW8KJtEPdnMz.DmLaFSyaLRNf0zIALt.P2z2Ugc.xER.', 'phampho1103@gmail.com', 'Phạm Phố', 'Nam', '0971662642', '2025-06-10 16:01:42', 5000000.00, NULL),
(2, 1, 'admin', '$2y$10$2dEPTbAa1lX2b41kugZV2eCgfODzokteFQ8B/hpr2vlo.NAiq9hye', 'a@gmail.com', 'a', 'Nam', '098', '2025-05-26 14:37:22', 0.00, NULL),
(3, 1, 'test', '$2y$10$IREKgYQk.ncwjsHLgfGAYul1ZInFyU5D44wbGTw/pYZMQk3vagCv.', 'ae@gmail.com', 'a2', 'Nam', '124', '2025-05-26 15:10:00', 0.00, NULL),
(4, 2, 'demo', '$2y$10$pfM2AxZq43rKAi0VvygdC.G/ovCRZx1xE2xWyTWD5cinAjT.CUt12', 'demo@gmail.com', 'De Mo', 'Nữ', '1111111111', '2025-06-10 15:35:04', 980760240.00, 'Khu Phố 6, Hàn Thuyên\n2025-06-10 21:07:33 - Nạp 50000₫ - Order: 58');

-- --------------------------------------------------------

--
-- Table structure for table `user_vouchers`
--

CREATE TABLE `user_vouchers` (
  `user_voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_used` tinyint(1) DEFAULT 0,
  `used_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_vouchers`
--

INSERT INTO `user_vouchers` (`user_voucher_id`, `user_id`, `voucher_id`, `assigned_date`, `is_used`, `used_date`) VALUES
(1, 4, 3, '2025-06-06 10:07:31', 1, '2025-06-09 12:17:26'),
(4, 3, 4, '2025-06-08 05:29:29', 0, NULL),
(5, 4, 4, '2025-06-08 05:31:36', 1, '2025-06-10 14:09:02');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `voucher_code` varchar(50) NOT NULL,
  `voucher_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed_amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`voucher_id`, `voucher_code`, `voucher_name`, `description`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `usage_limit`, `used_count`, `per_user_limit`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'VIP20', 'Voucher VIP', 'Giảm 20% cho khách hàng VIP', 'percentage', 20.00, 200000.00, 100000.00, NULL, 2, 1, '2025-06-06', '2025-09-04', 1, '2025-06-06 09:51:17', '2025-06-09 12:17:26'),
(4, 'FREESHIP', 'Miễn phí vận chuyển', 'Giảm 30,000đ phí vận chuyển', 'fixed_amount', 30000.00, 0.00, NULL, 200, 2, 1, '2025-06-06', '2025-09-04', 1, '2025-06-06 09:51:17', '2025-06-10 14:09:02');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL COMMENT 'Can be product_id or accessory_id based on item_type',
  `item_type` enum('product','accessory') NOT NULL DEFAULT 'product',
  `variant_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accessories`
--
ALTER TABLE `accessories`
  ADD PRIMARY KEY (`accessory_id`);

--
-- Indexes for table `artists`
--
ALTER TABLE `artists`
  ADD PRIMARY KEY (`artist_id`);

--
-- Indexes for table `artist_products`
--
ALTER TABLE `artist_products`
  ADD PRIMARY KEY (`artist_product_id`),
  ADD KEY `ArtistProduct_ArtistID` (`artist_id`),
  ADD KEY `ArtistProduct_ProductID` (`product_id`);

--
-- Indexes for table `authentication`
--
ALTER TABLE `authentication`
  ADD PRIMARY KEY (`auth_id`),
  ADD UNIQUE KEY `auth_token` (`auth_token`),
  ADD KEY `authentication_user_id` (`user_id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`banner_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_buyerID` (`buyer_id`),
  ADD KEY `orders_payment_id` (`payment_id`),
  ADD KEY `idx_order_stage` (`stage_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_item_type_id` (`item_type`,`item_id`);

--
-- Indexes for table `order_stages`
--
ALTER TABLE `order_stages`
  ADD PRIMARY KEY (`stage_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `password_resets_user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `products_genre_id` (`genre_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `promotions_product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `reviews_product_id` (`product_id`),
  ADD KEY `reviews_buyer_id` (`buyer_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `support_replies`
--
ALTER TABLE `support_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `support_replies_support_id` (`support_id`),
  ADD KEY `support_replies_admin_id` (`admin_id`),
  ADD KEY `support_replies_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`,`email`),
  ADD KEY `users_roleid` (`role_id`);

--
-- Indexes for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD PRIMARY KEY (`user_voucher_id`),
  ADD UNIQUE KEY `unique_user_voucher` (`user_id`,`voucher_id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `voucher_code` (`voucher_code`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `wishlist_user_id` (`user_id`),
  ADD KEY `wishlist_product_id` (`product_id`),
  ADD KEY `wishlist_variant_id` (`variant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accessories`
--
ALTER TABLE `accessories`
  MODIFY `accessory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `artists`
--
ALTER TABLE `artists`
  MODIFY `artist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `artist_products`
--
ALTER TABLE `artist_products`
  MODIFY `artist_product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `authentication`
--
ALTER TABLE `authentication`
  MODIFY `auth_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `support_replies`
--
ALTER TABLE `support_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  MODIFY `user_voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `authentication`
--
ALTER TABLE `authentication`
  ADD CONSTRAINT `authentication_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD CONSTRAINT `user_vouchers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_vouchers_ibfk_2` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`voucher_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
