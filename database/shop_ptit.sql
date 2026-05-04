-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 04, 2026 lúc 03:22 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_ptit`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `baiviet`
--

CREATE TABLE `baiviet` (
  `maBV` int(11) NOT NULL,
  `tieuDe` varchar(255) NOT NULL,
  `tomTat` text DEFAULT NULL,
  `noiDung` longtext DEFAULT NULL,
  `hinhAnh` varchar(255) DEFAULT NULL,
  `ngayDang` datetime DEFAULT current_timestamp(),
  `tacGia` varchar(100) DEFAULT NULL,
  `trangThai` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `baiviet`
--

INSERT INTO `baiviet` (`maBV`, `tieuDe`, `tomTat`, `noiDung`, `hinhAnh`, `ngayDang`, `tacGia`, `trangThai`) VALUES
(1, 'Xu hướng thời trang hè 2026', 'Các mẫu trang phục nổi bật cho mùa hè năm nay.', 'Nội dung bài viết mẫu 1...', 'assets/images/blog1.jpg', '2026-04-17 12:45:35', 'Admin', 1),
(2, 'Cách phối đồ basic nhưng vẫn đẹp', 'Gợi ý mix đồ đơn giản cho nam và nữ.', 'Nội dung bài viết mẫu 2...', 'assets/images/blog2.jpg', '2026-04-17 12:45:35', 'Admin', 1),
(3, 'Mẹo bảo quản quần áo bền đẹp', 'Một số mẹo giúp quần áo luôn mới.', 'Nội dung bài viết mẫu 3...', 'assets/images/blog3.jpg', '2026-04-17 12:45:35', 'Admin', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbotmessages`
--

CREATE TABLE `chatbotmessages` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `maND` int(11) DEFAULT NULL,
  `sender` enum('user','bot','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbotmessages`
--

INSERT INTO `chatbotmessages` (`id`, `session_id`, `maND`, `sender`, `message`, `created_at`) VALUES
(1, 'sess_3_blcc3l0iyu_1777174388767', 3, 'user', 'Tư vấn áo', '2026-04-26 10:33:08'),
(2, 'sess_3_blcc3l0iyu_1777174388767', 3, 'bot', '\r\n        Bạn muốn mình tư vấn theo hướng nào?\r\n        Áo\r\n        Quần\r\n        Váy\r\n        Dưới 500k\r\n        Mã giảm giá\r\n    ', '2026-04-26 10:33:08'),
(3, 'sess_3_blcc3l0iyu_1777174388767', 3, 'user', 'Tư vấn áo', '2026-04-26 10:33:11'),
(4, 'sess_3_blcc3l0iyu_1777174388767', 3, 'bot', '\r\n        Bạn muốn mình tư vấn theo hướng nào?\r\n        Áo\r\n        Quần\r\n        Váy\r\n        Dưới 500k\r\n        Mã giảm giá\r\n    ', '2026-04-26 10:33:11'),
(5, 'sess_3_blcc3l0iyu_1777174388767', 3, 'user', 'Sản phẩm dưới 500k', '2026-04-26 10:33:13'),
(6, 'sess_3_blcc3l0iyu_1777174388767', 3, 'bot', 'Mình lọc vài sản phẩm dưới 500.000₫ cho bạn:\r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun Uniqlo Airism\r\n                    250.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun H&amp;M Basic\r\n                    250.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun Puma Classic\r\n                    280.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Quần short Puma Basic\r\n                    280.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        ', '2026-04-26 10:33:13'),
(7, 'sess_guest_hcckjj92rcm_1777191616404', NULL, 'user', 'Tư vấn áo', '2026-04-26 15:20:16'),
(8, 'sess_guest_hcckjj92rcm_1777191616404', NULL, 'bot', '\r\n        Bạn muốn chọn áo theo phong cách nào?\r\n        Đi làm\r\n        Đi chơi\r\n        Basic\r\n        Giá rẻ\r\n        Áo sơ mi\r\n        Áo thun\r\n    ', '2026-04-26 15:20:16'),
(9, 'sess_guest_hcckjj92rcm_1777191616404', NULL, 'user', 'Áo basic', '2026-04-26 15:20:18'),
(10, 'sess_guest_hcckjj92rcm_1777191616404', NULL, 'bot', 'Phong cách áo basic/tối giản thì mình gợi ý vài mẫu dễ mặc này:\r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun Uniqlo Airism\r\n                    250.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun H&amp;M Basic\r\n                    250.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun Puma Classic\r\n                    280.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo thun Adidas Originals\r\n                    320.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        ', '2026-04-26 15:20:18'),
(11, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'Tư vấn áo', '2026-04-26 15:22:03'),
(12, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '\r\n        Bạn muốn chọn áo theo phong cách nào?\r\n        Đi làm\r\n        Đi chơi\r\n        Basic\r\n        Giá rẻ\r\n        Áo sơ mi\r\n        Áo thun\r\n    ', '2026-04-26 15:22:03'),
(13, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'Áo sơ mi', '2026-04-26 15:22:05'),
(14, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', 'Mình chọn vài mẫu áo sơ mi hợp với phong cách SimpleFit cho bạn:\r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi Uniqlo Premium\r\n                    650.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi kẻ sọc H&amp;M\r\n                    480.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi trắng Zara\r\n                    550.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n    Bạn muốn mình lọc tiếp theo giá rẻ hơn, đi chơi hay đi làm?', '2026-04-26 15:22:05'),
(15, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'giá rẻ hơn', '2026-04-26 15:22:16'),
(16, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', 'Mình lọc vài mẫu giá mềm hơn cho bạn:\r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi kẻ sọc H&amp;M\r\n                    480.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi trắng Zara\r\n                    550.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi Uniqlo Premium\r\n                    650.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        ', '2026-04-26 15:22:16'),
(17, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'Có mã giảm giá không', '2026-04-26 15:22:22'),
(18, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '\r\n        Shop đang có mã giảm giá 🎁\r\n        • SALE10: giảm 10% cho đơn từ 300.000₫\r\n        • FREESTYLE20: giảm 20% cho đơn từ 800.000₫\r\n        Bạn có thể nhập mã ở trang giỏ hàng khi thanh toán nhé.\r\n        Gợi ý sản phẩm dễ áp mã\r\n    ', '2026-04-26 15:22:22'),
(19, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'Ship bao lâu', '2026-04-26 15:22:27'),
(20, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '\r\n        Shop hỗ trợ giao hàng toàn quốc 🚚\r\n        • Nội thành: khoảng 1 - 2 ngày\r\n        • Ngoại tỉnh: khoảng 2 - 5 ngày\r\n        • Đơn từ 500.000₫ có thể được miễn phí ship\r\n        Bạn muốn mình gợi ý vài sản phẩm dễ đủ điều kiện freeship không?\r\n        Xem hàng mới\r\n        Dưới 500k\r\n    ', '2026-04-26 15:22:27'),
(21, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'cần 1 bộ vest', '2026-04-26 15:22:50'),
(22, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '\r\n        Shop đã ghi nhận nội dung này, nhân viên shop sẽ kiểm tra và phản hồi bạn sớm nhất.\r\n        Trong lúc chờ, bạn có thể chọn nhanh một nhu cầu bên dưới:\r\n        Áo\r\n        Quần\r\n        Váy\r\n        Dưới 500k\r\n        Mã giảm giá\r\n    ', '2026-04-26 15:22:50'),
(23, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'user', 'cần 1 bộ vest', '2026-05-01 11:15:42'),
(24, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '\r\n        Shop đã ghi nhận nội dung này, nhân viên shop sẽ kiểm tra và phản hồi bạn sớm nhất.\r\n        Trong lúc chờ, bạn có thể chọn nhanh một nhu cầu bên dưới:\r\n        Áo\r\n        Quần\r\n        Váy\r\n        Dưới 500k\r\n        Mã giảm giá\r\n    ', '2026-05-01 11:15:42'),
(25, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'admin', 'Xin chào quý khách! Hiện tại thì shop chưa kinh doanh các mặt hàng liên quan đến vest ạ!', '2026-05-01 11:56:05'),
(26, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'user', 'hello', '2026-05-01 17:03:45'),
(27, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'bot', 'Xin chào 👋 Mình có thể tư vấn áo, quần, váy, sản phẩm theo giá hoặc hỗ trợ thông tin ship/đổi trả cho bạn.', '2026-05-01 17:03:45'),
(28, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'user', 'Tư vấn áo', '2026-05-01 17:03:48'),
(29, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'bot', '\r\n        Bạn muốn chọn áo theo phong cách nào?\r\n        Đi làm\r\n        Đi chơi\r\n        Basic\r\n        Giá rẻ\r\n        Áo sơ mi\r\n        Áo thun\r\n    ', '2026-05-01 17:03:48'),
(30, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'user', 'Áo đi chơi', '2026-05-01 17:03:49'),
(31, 'sess_1_1qrfq5yx4mf_1777626547919', 1, 'bot', 'Nếu đi chơi, bạn có thể chọn vài mẫu áo thoải mái, dễ phối này:\r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo len Zara\r\n                    1.280.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo nỉ H&amp;M\r\n                    399.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo len Gucci\r\n                    1.500.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        \r\n            \r\n                \r\n                    \r\n                \r\n\r\n                \r\n                    Áo sơ mi Uniqlo Premium\r\n                    650.000₫\r\n\r\n                    \r\n                        Xem\r\n                        + Giỏ\r\n                    \r\n                \r\n            \r\n        ', '2026-05-01 17:03:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbotrequests`
--

CREATE TABLE `chatbotrequests` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `maND` int(11) DEFAULT NULL,
  `customer_message` text NOT NULL,
  `status` enum('pending','answered') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbotrequests`
--

INSERT INTO `chatbotrequests` (`id`, `session_id`, `maND`, `customer_message`, `status`, `created_at`) VALUES
(1, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'cần 1 bộ vest', 'answered', '2026-04-26 15:22:50'),
(2, 'sess_guest_hb40kj9q71q_1777191723787', NULL, 'cần 1 bộ vest', 'answered', '2026-05-01 11:15:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbotsessions`
--

CREATE TABLE `chatbotsessions` (
  `session_id` varchar(100) NOT NULL,
  `maND` int(11) DEFAULT NULL,
  `mode` enum('bot','admin') DEFAULT 'bot',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbotsessions`
--

INSERT INTO `chatbotsessions` (`session_id`, `maND`, `mode`, `updated_at`) VALUES
('sess_guest_hb40kj9q71q_1777191723787', NULL, 'bot', '2026-05-04 18:23:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `maCTDH` bigint(20) NOT NULL,
  `maDH` bigint(20) DEFAULT NULL,
  `maSP` bigint(20) DEFAULT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `donGia` decimal(15,2) DEFAULT NULL,
  `thanhTien` decimal(15,2) DEFAULT NULL,
  `kichCo` varchar(50) DEFAULT NULL,
  `mauSac` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietgiohang`
--

CREATE TABLE `chitietgiohang` (
  `maCTGH` bigint(20) NOT NULL,
  `maGH` bigint(20) DEFAULT NULL,
  `maSP` bigint(20) DEFAULT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `kichCo` varchar(50) DEFAULT NULL,
  `mauSac` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `maDM` bigint(20) NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `hinhAnh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`maDM`, `ten`, `hinhAnh`) VALUES
(1, 'Áo Thun', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=500'),
(2, 'Quần Jeans', 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=500'),
(3, 'Áo Khoác', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500'),
(4, 'Đầm/Váy', 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500'),
(5, 'Áo Sơ Mi', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500'),
(6, 'Quần Short', 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500'),
(7, 'Áo Len', 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `discount_codes`
--

CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `discount_codes`
--

INSERT INTO `discount_codes` (`id`, `code`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(3, 'DINHNOCKICHTRAN', 'percent', 15.00, 1199999.00, 200000.00, NULL, 0, NULL, NULL, 1, '2026-05-04 11:26:52'),
(4, 'PTIT2026', 'fixed', 50000.00, 999999.00, NULL, NULL, 0, NULL, NULL, 1, '2026-05-04 11:27:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `maDH` bigint(20) NOT NULL,
  `maKH` bigint(20) DEFAULT NULL,
  `maNV` bigint(20) DEFAULT NULL,
  `hoTen` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `tongTien` decimal(15,2) DEFAULT NULL,
  `phiShip` decimal(15,2) DEFAULT 0.00,
  `phuongThucThanhToan` varchar(50) DEFAULT NULL,
  `trangThai` varchar(50) DEFAULT 'Chờ xác nhận',
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`maDH`, `maKH`, `maNV`, `hoTen`, `email`, `soDienThoai`, `diaChi`, `tongTien`, `phiShip`, `phuongThucThanhToan`, `trangThai`, `ngayTao`) VALUES
(1, 1, NULL, 'Người Dùng', 'user@email.com', '0912345678', '123 Đường ABC, Hà Nội', 3680000.00, 30000.00, 'COD', 'DA_GIAO_HANG', '2025-11-05 07:20:30'),
(2, 1, NULL, 'Người Dùng', 'user@email.com', '0912345678', '123 Đường ABC, Hà Nội', 1330000.00, 30000.00, 'Chuyển khoản', 'DA_GIAO_HANG', '2026-04-17 03:56:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `maGH` bigint(20) NOT NULL,
  `maKH` bigint(20) DEFAULT NULL,
  `trangThai` varchar(50) DEFAULT 'Đang mua'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `homereviews`
--

CREATE TABLE `homereviews` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `rating` int(11) DEFAULT 5,
  `status` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `homereviews`
--

INSERT INTO `homereviews` (`id`, `customer_name`, `content`, `rating`, `status`, `created_at`) VALUES
(1, 'Nguyễn An', 'Chất vải rất mịn, form áo cực kỳ tôn dáng.', 5, 1, '2026-05-01 17:10:13'),
(2, 'Trần Bình', 'Giao hàng nhanh, đóng gói cẩn thận, sản phẩm giống ảnh.', 5, 1, '2026-05-01 17:10:13'),
(3, 'Lê Chi', 'Áo sơ mi mặc đi làm rất lịch sự, dễ phối với quần âu.', 4, 1, '2026-05-01 17:10:13'),
(4, 'Minh Anh', 'Shop tư vấn nhiệt tình, chất liệu mặc thoải mái.', 5, 1, '2026-05-01 17:10:13'),
(5, 'Bảo Ngọc', 'Phong cách tối giản, màu sắc đẹp, rất hợp gu mình.', 5, 1, '2026-05-01 17:10:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `maKH` bigint(20) NOT NULL,
  `maND` bigint(20) DEFAULT NULL,
  `diemTichLuy` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`maKH`, `maND`, `diemTichLuy`) VALUES
(1, 2, 0),
(2, 3, 0),
(5, 6, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `magiamgia`
--

CREATE TABLE `magiamgia` (
  `maGG` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `loaiGiam` enum('PERCENT','FIXED') NOT NULL DEFAULT 'PERCENT',
  `giaTriGiam` decimal(15,2) NOT NULL DEFAULT 0.00,
  `phanTramGiam` int(11) NOT NULL,
  `donToiThieu` int(11) DEFAULT 0,
  `giamToiDa` decimal(15,2) DEFAULT NULL,
  `trangThai` enum('ACTIVE','HIDDEN') DEFAULT 'ACTIVE',
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `magiamgia`
--

INSERT INTO `magiamgia` (`maGG`, `code`, `loaiGiam`, `giaTriGiam`, `phanTramGiam`, `donToiThieu`, `giamToiDa`, `trangThai`, `ngayTao`) VALUES
(1, 'MAU10', 'PERCENT', 10.00, 10, 200000, 50000.00, 'ACTIVE', '2026-04-18 00:14:50'),
(2, 'GIAM50K', 'FIXED', 50000.00, 0, 500000, NULL, 'ACTIVE', '2026-04-18 00:14:50'),
(3, 'GIAM100K', 'FIXED', 100000.00, 0, 1000000, NULL, 'ACTIVE', '2026-04-18 00:14:50'),
(4, 'SALE15', 'PERCENT', 15.00, 15, 800000, 120000.00, 'ACTIVE', '2026-04-18 00:14:50');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `maND` bigint(20) NOT NULL,
  `tenDangNhap` varchar(50) NOT NULL,
  `matKhau` varchar(255) NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `ngaySinh` date DEFAULT NULL,
  `gioiTinh` varchar(10) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `soDienThoai` varchar(20) DEFAULT NULL,
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp(),
  `vaiTro` varchar(20) DEFAULT 'USER',
  `trangThai` varchar(20) DEFAULT 'ACTIVE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoidung`
--

INSERT INTO `nguoidung` (`maND`, `tenDangNhap`, `matKhau`, `ten`, `ngaySinh`, `gioiTinh`, `diaChi`, `soDienThoai`, `ngayTao`, `vaiTro`, `trangThai`) VALUES
(1, 'admin', '1', 'Quản Trị Viên', NULL, NULL, NULL, NULL, '2025-10-13 17:14:54', 'ADMIN', 'ACTIVE'),
(2, 'user', '2', 'Người Dùng', NULL, NULL, NULL, NULL, '2025-10-13 17:14:54', 'USER', 'ACTIVE'),
(3, 'anhvu', 'vu12122005@@', 'Anh Vũ Trần Lê', NULL, NULL, NULL, '1', '2026-04-17 05:25:57', 'USER', 'ACTIVE'),
(6, 'abcd', '1', 'Trần Hà Linh Côn', NULL, NULL, NULL, '0945123789', '2026-05-04 13:17:35', 'USER', 'ACTIVE');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `maNV` bigint(20) NOT NULL,
  `maND` bigint(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `luong` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `maKH` bigint(20) DEFAULT NULL,
  `maNV` bigint(20) DEFAULT NULL,
  `subtotal` bigint(20) DEFAULT NULL,
  `shipping` bigint(20) DEFAULT NULL,
  `tongTien` bigint(20) DEFAULT NULL,
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trangThai` varchar(50) DEFAULT NULL,
  `diaChi` varchar(255) DEFAULT NULL,
  `soDienThoai` varchar(15) DEFAULT NULL,
  `phuongThucThanhToan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `productreviews`
--

CREATE TABLE `productreviews` (
  `id` int(11) NOT NULL,
  `product_id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `content` text NOT NULL,
  `is_purchased` tinyint(4) DEFAULT 0,
  `status` enum('visible','hidden') DEFAULT 'visible',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `productreviews`
--

INSERT INTO `productreviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `content`, `is_purchased`, `status`, `created_at`) VALUES
(1, 28, 3, 'Anh Vũ Trần Lê', 5, 'Sản phẩm chất lượng cao, rất đáng mua!', 0, 'visible', '2026-05-01 17:19:24'),
(2, 27, 3, 'Anh Vũ Trần Lê', 5, 'Sản phẩm chất lượng xuất sắc', 0, 'visible', '2026-05-04 19:51:10'),
(3, 25, 6, 'Trần Hà Linh Côn', 2, 'quần không đúng chất lượng', 0, 'visible', '2026-05-04 20:18:02'),
(4, 27, 6, 'Trần Hà Linh Côn', 4, 'Sản phẩm chất lượng ổn, giá cả phải chăng. Nên mua hàng ở shop', 0, 'visible', '2026-05-04 20:21:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quantrivien`
--

CREATE TABLE `quantrivien` (
  `maQTV` bigint(20) NOT NULL,
  `maND` bigint(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `maSP` bigint(20) NOT NULL,
  `maDM` bigint(20) DEFAULT NULL,
  `maTH` bigint(20) DEFAULT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `moTa` text DEFAULT NULL,
  `gia` decimal(15,2) DEFAULT NULL,
  `soLuong` int(11) DEFAULT NULL,
  `kichCo` varchar(50) DEFAULT NULL,
  `mauSac` varchar(50) DEFAULT NULL,
  `chatLieu` varchar(100) DEFAULT NULL,
  `hinhAnh` varchar(255) DEFAULT NULL,
  `daBan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`maSP`, `maDM`, `maTH`, `ten`, `moTa`, `gia`, `soLuong`, `kichCo`, `mauSac`, `chatLieu`, `hinhAnh`, `daBan`) VALUES
(1, 1, 1, 'Áo thun Nike Sportswear', 'Áo thun thể thao cotton thoáng mát', 350000.00, 50, 'S,M,L,XL', 'Đen', 'Cotton 100%', 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto/025fcce7-dec4-455d-b089-4a45c4aed01d/AS+M+NK+DF+TEE+RUN+ENERGY+SP25.png', 120),
(2, 1, 2, 'Áo thun Adidas Originals', 'Áo thun cổ tròn in logo Adidas', 320000.00, 40, 'M,L,XL', 'Trắng', 'Cotton', 'https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/4eb95e8f57de44f099accc38e8fa0e75_9366/Ao_DJau_Jacquard_adidas_Adicolor_Mau_xanh_da_troi_JW5879_21_model.jpg', 95),
(3, 1, 7, 'Áo thun Puma Classic', 'Áo thun basic Puma, chất liệu mềm mại', 280000.00, 35, 'S,M,L', 'Xám', 'Cotton pha Spandex', 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_2000,h_2000/global/629636/02/mod01/fnd/VNM/fmt/png/%C3%81o-thun-nam-GRAPHICS-PUMA-Hotel-Relaxed', 75),
(4, 1, 4, 'Áo thun Uniqlo Airism', 'Áo thun công nghệ Airism', 250000.00, 60, 'S,M,L,XL,XXL', 'Xanh navy', 'Cotton Airism', 'https://image.uniqlo.com/UQ/ST3/vn/imagesgoods/465185/item/vngoods_19_465185_3x4.jpg?width=369', 150),
(5, 1, 5, 'Áo thun H&M Basic', 'Áo thun basic giá rẻ', 250000.00, 100, 'S,M,L,XL', 'Trắng', 'Cotton', 'https://image.hm.com/assets/hm/99/be/99be7acc36e38e86c91bfe40d30fe53215cd9843.jpg?imwidth=1260', 200),
(6, 1, 6, 'Áo thun Gucci Logo', 'Áo thun cao cấp in logo Gucci', 1300000.00, 10, 'M,L', 'Đen', 'Cotton cao cấp', 'https://media.gucci.com/style/DarkGray_Center_0_0_2400x2400/1758013221/796395_XJHC9_3254_003_100_0000_Light-Printed-cotton-jersey-T-shirt.jpg', 25),
(7, 2, 2, 'Quần jeans Adidas Slim Fit', 'Quần jeans form slim fit co giãn', 650000.00, 30, '28,29,30,31,32', 'Xanh đậm', 'Denim co giãn', 'https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/448001745b654961b96fdf127a19d0e7_9366/Quan_Denim_Om_Dang_Adilenium_Season_4_Teamgeist_LR_Mau_xanh_da_troi_KE9801_21_model.jpg', 85),
(8, 2, 1, 'Quần jeans Nike Destroyed', 'Quần jeans destroyed streetwear', 720000.00, 15, '29,30,31,32', 'Xanh nhạt', 'Denim', 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto,u_126ab356-44d8-4a06-89b4-fcdcc8df0245,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/7ffed27f-7cdd-42f5-aa53-976b7efe1491/M+J+FLT+UTILITY+PANT.png', 35),
(9, 2, 3, 'Quần jeans Zara Skinny', 'Quần jeans form skinny, ôm chân', 550000.00, 25, '26,27,28,29', 'Đen', 'Denim skinny', 'https://static.zara.net/assets/public/85de/afe9/392a4e4ab113/080e6d1cc93d/00103250400-p/00103250400-p.jpg?ts=1756308141969&w=1024', 60),
(10, 2, 5, 'Quần jeans H&M Regular', 'Quần jeans form regular, thoải mái', 450000.00, 40, '30,31,32,33,34', 'Xanh trung', 'Denim regular', 'https://image.hm.com/assets/hm/7d/a5/7da57056ae5d35fa3b8302407bb1e686ae0af8cf.jpg?imwidth=1260', 90),
(11, 2, 4, 'Quần jeans Uniqlo Selvedge', 'Quần jeans selvedge cao cấp', 850000.00, 20, '30,31,32', 'Xanh indigo', 'Denim selvedge', 'https://image.uniqlo.com/UQ/ST3/vn/imagesgoods/470542/item/vngoods_08_470542_3x4.jpg?width=423', 40),
(12, 3, 3, 'Áo khoác Zara Basic', 'Áo khoác basic phong cách Hàn Quốc', 850000.00, 20, 'S,M,L', 'Be', 'Kaki', 'https://static.zara.net/assets/public/f852/9407/317e48ca980c/c14ce0f81999/06318406811-a1/06318406811-a1.jpg?ts=1758270016198&w=1125', 45),
(13, 3, 1, 'Áo khoác Nike Windrunner', 'Áo khoác gió Nike, nhẹ gọn', 1200000.00, 15, 'M,L,XL', 'Đen', 'Polyester', 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto/b9bf7777-8d74-4c4e-8b43-55e834ad9e6a/AS+W+NSW+NK+WR+WVN+UV+FZ+JKT.png', 30),
(14, 3, 2, 'Áo khoác Adidas Essentials', 'Áo khoác mùa đông Adidas', 950000.00, 18, 'S,M,L,XL', 'Xám', 'Nỉ', 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/6392033da0da429aac12e1812d2372e8_9366/Ao_Gio_3_Soc_Essentials_Mau_vang_IM7847_25_model.jpg', 55),
(15, 3, 4, 'Áo khoác Uniqlo Ultra Light', 'Áo khoác siêu nhẹ, gấp gọn được', 650000.00, 30, 'S,M,L', 'Xanh pastel', 'Ultra light down', 'https://image.uniqlo.com/UQ/ST3/vn/imagesgoods/478270/item/vngoods_77_478270_3x4.jpg?width=423', 70),
(16, 4, 4, 'Đầm body Uniqlo', 'Đầm body dáng ôm, phù hợp đi làm', 550000.00, 25, 'S,M,L', 'Đỏ', 'Vải tổng hợp', 'https://image.uniqlo.com/UQ/ST3/vn/imagesgoods/477316/item/vngoods_09_477316_3x4.jpg?width=423', 60),
(17, 4, 3, 'Váy liền Zara', 'Váy liền thân dáng suông, thoải mái', 750000.00, 20, 'S,M', 'Hoa', 'Vải voan', 'https://static.zara.net/assets/public/de67/d3b5/94ce488aa6ef/1448119253d1/05919201800-p/05919201800-p.jpg?ts=1752661919205&w=1024', 45),
(18, 4, 6, 'Đầm dạ hội Gucci', 'Đầm dạ hội cao cấp, sang trọng', 850000.00, 5, 'S,M', 'Đen', 'Lụa', 'https://media.gucci.com/style/DarkGray_Center_0_0_490x490/1758134773/837157_Z8B2Y_1000_003_100_0000_Light-satin-dress-with-double-g-belt.jpg', 8),
(19, 4, 5, 'Váy công sở H&M', 'Váy công sở lịch sự, form A-line', 450000.00, 35, 'S,M,L', 'Xanh dương', 'Vải Kate', 'https://image.hm.com/assets/hm/08/fc/08fccb668e385a4700cc17d85945df15e301a93b.jpg?imwidth=2160', 80),
(20, 5, 3, 'Áo sơ mi trắng Zara', 'Áo sơ mi trắng basic, form regular', 550000.00, 30, 'S,M,L,XL', 'Trắng', 'Cotton', 'https://static.zara.net/assets/public/de99/42ba/89bf4c679bb9/f973cd227dbe/04043253250-a3/04043253250-a3.jpg?ts=1760026339972&w=1379', 65),
(21, 5, 5, 'Áo sơ mi kẻ sọc H&M', 'Áo sơ mi kẻ sọc thanh lịch', 480000.00, 25, 'M,L,XL', 'Xanh trắng', 'Cotton', 'https://image.hm.com/assets/hm/b3/6a/b36aab21dfc6f406007e252e840259cbc3646d04.jpg?imwidth=1260', 50),
(22, 5, 4, 'Áo sơ mi Uniqlo Premium', 'Áo sơ mi cao cấp, không nhăn', 650000.00, 20, 'S,M,L', 'Xanh pastel', 'Cotton premium', 'https://image.uniqlo.com/UQ/ST3/vn/imagesgoods/462369/item/vngoods_65_462369_3x4.jpg?width=369', 40),
(23, 6, 1, 'Quần short Nike Sport', 'Quần short thể thao Nike, thoáng mát', 350000.00, 40, 'M,L,XL', 'Đen', 'Polyester', 'https://static.nike.com/a/images/t_web_pdp_535_v2/f_auto/e5f44e14-f6c6-4f49-997a-ab041b2a4be0/AS+M+NK+DF+FORM+7IN+UL+SHORT.png', 85),
(24, 6, 2, 'Quần short Adidas Originals', 'Quần short casual Adidas', 320000.00, 35, 'S,M,L', 'Xám', 'Cotton', 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/6739ddc25daa4df3875e8497ca3f01bd_9366/Own_The_Run_Shorts_Blue_JX2247_21_model.jpg', 70),
(25, 6, 7, 'Quần short Puma Basic', 'Quần short basic Puma, nhiều màu', 280000.00, 45, 'S,M,L,XL', 'Navy', 'Cotton', 'https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_2000,h_2000/global/682598/03/mod03/fnd/PNA/fmt/png/PUMA-Essentials-Men\'s-10%22-Shorts', 95),
(26, 7, 6, 'Áo len Gucci', 'Áo len vải thun cotton Gucci', 1500000.00, 40, 'M,L,XL', 'Đen', 'Vải thun cotton', 'https://media.gucci.com/style/DarkGray_Center_0_0_2400x2400/1757932225/838450_XJHJO_1043_003_100_0000_Light-Brushed-cotton-jersey-sweatshirt.jpg', 40),
(27, 7, 5, 'Áo nỉ H&M', 'Áo nỉ dáng thụng H&M Basics', 399000.00, 45, 'XS,S,M,L', 'Xanh Navy', 'Cotton, Polyester', 'https://image.hm.com/assets/hm/d2/29/d229e5925fab8f6b9db1cdf4f386a269616e632b.jpg?imwidth=2160', 60),
(28, 7, 3, 'Áo len Zara', 'Áo len Polo cổ tay Zara', 1280000.00, 28, 'S,M,L,XL', 'Navy Blue', 'Cotton, Polyester', 'https://static.zara.net/assets/public/7f3d/981f/a95a48fda38b/12e72a1415da/00526310401-a1/00526310401-a1.jpg?ts=1754995160359&w=1125', 65);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuonghieu`
--

CREATE TABLE `thuonghieu` (
  `maTH` bigint(20) NOT NULL,
  `ten` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `thuonghieu`
--

INSERT INTO `thuonghieu` (`maTH`, `ten`, `logo`) VALUES
(1, 'Nike', 'https://logos-world.net/wp-content/uploads/2020/04/Nike-Logo.png'),
(2, 'Adidas', 'https://logos-world.net/wp-content/uploads/2020/04/Adidas-Logo.png'),
(3, 'Zara', 'https://logos-world.net/wp-content/uploads/2020/04/Zara-Logo.png'),
(4, 'Uniqlo', 'https://logos-world.net/wp-content/uploads/2020/12/Uniqlo-Logo.png'),
(5, 'H&M', 'https://logos-world.net/wp-content/uploads/2020/04/HM-Logo.png'),
(6, 'Gucci', 'https://logos-world.net/wp-content/uploads/2020/04/Gucci-Logo.png'),
(7, 'Puma', 'https://logos-world.net/wp-content/uploads/2020/04/Puma-Logo.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeuthich`
--

CREATE TABLE `yeuthich` (
  `maYT` bigint(20) NOT NULL,
  `maND` bigint(20) NOT NULL,
  `maSP` bigint(20) NOT NULL,
  `ngayTao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `baiviet`
--
ALTER TABLE `baiviet`
  ADD PRIMARY KEY (`maBV`);

--
-- Chỉ mục cho bảng `chatbotmessages`
--
ALTER TABLE `chatbotmessages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chatbotrequests`
--
ALTER TABLE `chatbotrequests`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chatbotsessions`
--
ALTER TABLE `chatbotsessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`maCTDH`),
  ADD KEY `maDH` (`maDH`),
  ADD KEY `maSP` (`maSP`);

--
-- Chỉ mục cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD PRIMARY KEY (`maCTGH`),
  ADD KEY `maGH` (`maGH`),
  ADD KEY `maSP` (`maSP`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`maDM`);

--
-- Chỉ mục cho bảng `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`maDH`),
  ADD KEY `maKH` (`maKH`),
  ADD KEY `maNV` (`maNV`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`maGH`),
  ADD KEY `maKH` (`maKH`);

--
-- Chỉ mục cho bảng `homereviews`
--
ALTER TABLE `homereviews`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`maKH`),
  ADD KEY `maND` (`maND`);

--
-- Chỉ mục cho bảng `magiamgia`
--
ALTER TABLE `magiamgia`
  ADD PRIMARY KEY (`maGG`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`maND`),
  ADD UNIQUE KEY `tenDangNhap` (`tenDangNhap`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`maNV`),
  ADD KEY `maND` (`maND`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `maKH` (`maKH`),
  ADD KEY `maNV` (`maNV`);

--
-- Chỉ mục cho bảng `productreviews`
--
ALTER TABLE `productreviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_reviews_product` (`product_id`),
  ADD KEY `idx_product_reviews_user` (`user_id`);

--
-- Chỉ mục cho bảng `quantrivien`
--
ALTER TABLE `quantrivien`
  ADD PRIMARY KEY (`maQTV`),
  ADD KEY `maND` (`maND`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`maSP`),
  ADD KEY `maDM` (`maDM`),
  ADD KEY `maTH` (`maTH`);

--
-- Chỉ mục cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  ADD PRIMARY KEY (`maTH`);

--
-- Chỉ mục cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD PRIMARY KEY (`maYT`),
  ADD KEY `fk_yeuthich_nguoidung` (`maND`),
  ADD KEY `fk_yeuthich_sanpham` (`maSP`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `baiviet`
--
ALTER TABLE `baiviet`
  MODIFY `maBV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `chatbotmessages`
--
ALTER TABLE `chatbotmessages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `chatbotrequests`
--
ALTER TABLE `chatbotrequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `maCTDH` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  MODIFY `maCTGH` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `maDM` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `maDH` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `maGH` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `homereviews`
--
ALTER TABLE `homereviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `maKH` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `magiamgia`
--
ALTER TABLE `magiamgia`
  MODIFY `maGG` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `maND` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  MODIFY `maNV` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `productreviews`
--
ALTER TABLE `productreviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `quantrivien`
--
ALTER TABLE `quantrivien`
  MODIFY `maQTV` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `maSP` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `thuonghieu`
--
ALTER TABLE `thuonghieu`
  MODIFY `maTH` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  MODIFY `maYT` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`maDH`) REFERENCES `donhang` (`maDH`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chitietgiohang`
--
ALTER TABLE `chitietgiohang`
  ADD CONSTRAINT `chitietgiohang_ibfk_1` FOREIGN KEY (`maGH`) REFERENCES `giohang` (`maGH`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietgiohang_ibfk_2` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON DELETE CASCADE,
  ADD CONSTRAINT `donhang_ibfk_2` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD CONSTRAINT `khachhang_ibfk_1` FOREIGN KEY (`maND`) REFERENCES `nguoidung` (`maND`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `nhanvien_ibfk_1` FOREIGN KEY (`maND`) REFERENCES `nguoidung` (`maND`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`maKH`) REFERENCES `khachhang` (`maKH`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`maNV`) REFERENCES `nhanvien` (`maNV`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `productreviews`
--
ALTER TABLE `productreviews`
  ADD CONSTRAINT `fk_product_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `sanpham` (`maSP`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `nguoidung` (`maND`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `quantrivien`
--
ALTER TABLE `quantrivien`
  ADD CONSTRAINT `quantrivien_ibfk_1` FOREIGN KEY (`maND`) REFERENCES `nguoidung` (`maND`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`maDM`) REFERENCES `danhmuc` (`maDM`) ON DELETE SET NULL,
  ADD CONSTRAINT `sanpham_ibfk_2` FOREIGN KEY (`maTH`) REFERENCES `thuonghieu` (`maTH`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD CONSTRAINT `fk_yeuthich_nguoidung` FOREIGN KEY (`maND`) REFERENCES `nguoidung` (`maND`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_yeuthich_sanpham` FOREIGN KEY (`maSP`) REFERENCES `sanpham` (`maSP`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
