-- ============================================================
-- GACHA TÚI MÙ LIÊN QUÂN - MIGRATION SCRIPT
-- Chạy toàn bộ script này trong phpMyAdmin
-- Tạo ngày: 2026-06-17
-- ============================================================

-- --------------------------------------------------------
-- Bảng 1: boxes - Danh mục túi mù
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `boxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(500) DEFAULT 'assets/img/giftbox.png',
  `price` decimal(15,2) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng 2: box_items - Kho item thực tế (acc/code)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `box_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `box_id` int(11) NOT NULL,
  `item_tier` varchar(100) NOT NULL DEFAULT 'Chung',
  `account_info` text NOT NULL,
  `is_sold` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_box_tier_sold` (`box_id`, `item_tier`, `is_sold`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng 3: box_rates - Tỉ lệ rớt đồ theo tier
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `box_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `box_id` int(11) NOT NULL,
  `item_tier` varchar(100) NOT NULL,
  `win_rate` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_box_id` (`box_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Bảng 4: user_inventory - Lịch sử mở túi của user
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `box_id` int(11) NOT NULL,
  `box_name` varchar(255) DEFAULT NULL,
  `item_won_info` text DEFAULT NULL,
  `item_tier` varchar(100) DEFAULT 'Chung',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA: 2 túi mù mẫu
-- ============================================================
INSERT INTO `boxes` (`name`, `description`, `image`, `price`, `status`, `created_at`) VALUES
('🎒 Túi Tân Thủ', 'Túi mù dành cho tân thủ - acc ngẫu nhiên giá cực tốt, phù hợp để trải nghiệm!', 'assets/img/box_tan_thu.png', 5000, 1, NOW()),
('👑 Túi VIP Xịn', 'Túi cao cấp - tỷ lệ trúng acc VIP cao vượt trội, dành cho game thủ chân ái!', 'assets/img/box_vip.png', 20000, 1, NOW());

-- Tỉ lệ cho Túi Tân Thủ (box_id=1): Tổng = 100%
INSERT INTO `box_rates` (`box_id`, `item_tier`, `win_rate`) VALUES
(1, 'VIP', 10),
(1, 'Xịn', 30),
(1, 'Chung', 60);

-- Tỉ lệ cho Túi VIP Xịn (box_id=2): Tổng = 100%
INSERT INTO `box_rates` (`box_id`, `item_tier`, `win_rate`) VALUES
(2, 'VIP', 25),
(2, 'Xịn', 45),
(2, 'Chung', 30);

-- Seed 15 item mẫu vào Túi Tân Thủ (box_id=1)
INSERT INTO `box_items` (`box_id`, `item_tier`, `account_info`, `is_sold`, `created_at`) VALUES
(1, 'Chung', 'acc_chung_001|Pass@2024', 0, NOW()),
(1, 'Chung', 'acc_chung_002|Qwerty#456', 0, NOW()),
(1, 'Chung', 'acc_chung_003|GameOn!789', 0, NOW()),
(1, 'Chung', 'acc_chung_004|LQM2024@kk', 0, NOW()),
(1, 'Chung', 'acc_chung_005|Tangtoc#88', 0, NOW()),
(1, 'Chung', 'acc_chung_006|NewUser@321', 0, NOW()),
(1, 'Chung', 'acc_chung_007|Casual!Game9', 0, NOW()),
(1, 'Chung', 'acc_chung_008|LienQuan$567', 0, NOW()),
(1, 'Xịn', 'acc_xin_001|VanCho@2024!', 0, NOW()),
(1, 'Xịn', 'acc_xin_002|Nghinh%Chien9', 0, NOW()),
(1, 'Xịn', 'acc_xin_003|RareDrop#111', 0, NOW()),
(1, 'VIP', 'acc_vip_001|KhaiPha@SuperVIP', 0, NOW()),
(1, 'VIP', 'acc_vip_002|TuMuVIP#2024!', 0, NOW());

-- Seed 10 item mẫu vào Túi VIP Xịn (box_id=2)
INSERT INTO `box_items` (`box_id`, `item_tier`, `account_info`, `is_sold`, `created_at`) VALUES
(2, 'Chung', 'vip_box_chung_01|CommonPw@99', 0, NOW()),
(2, 'Chung', 'vip_box_chung_02|Entry!Level7', 0, NOW()),
(2, 'Xịn', 'vip_box_xin_01|RareFind@888', 0, NOW()),
(2, 'Xịn', 'vip_box_xin_02|EpicGear#777', 0, NOW()),
(2, 'Xịn', 'vip_box_xin_03|PurpWing@2024', 0, NOW()),
(2, 'VIP', 'vip_box_vip_01|DiamondRank!9', 0, NOW()),
(2, 'VIP', 'vip_box_vip_02|LegendSkin@VN', 0, NOW()),
(2, 'VIP', 'vip_box_vip_03|MasterAcc#LQM', 0, NOW());

-- ============================================================
-- 3 TÀI KHOẢN ADMIN TIÊU CHUẨN
-- Mật khẩu đã hash MD5 (cách hash mặc định của SHOPCLONE6)
-- Admin 1: gacha_master   / GachaMaster@2024!
-- Admin 2: tuimu_admin    / TuiMuAdmin#9988
-- Admin 3: lienquan_boss  / LQBoss$2024Vip
-- ============================================================
-- Mật khẩu hash: md5(password) - dùng đúng format của hệ thống
-- GachaMaster@2024! => md5 = fa55c4e8f7b7d6ae1b61e9f3b2e4a8d1 (example, will be overridden by actual MD5)
-- Hệ thống dùng MD5: thay thế bằng hash thực

INSERT INTO `users` 
(`username`, `password`, `email`, `fullname`, `phone`, `admin`, `banned`, `active`, `create_date`, `update_date`, `money`, `total_money`, `token`) VALUES
(
  'gacha_master',
  MD5('GachaMaster@2024!'),
  'gacha.master@tumu.vn',
  'Gacha Master Admin',
  '0901234567',
  1, 0, 1,
  NOW(), NOW(),
  0, 0,
  MD5(CONCAT('gacha_master', NOW(), RAND()))
),
(
  'tuimu_admin',
  MD5('TuiMuAdmin#9988'),
  'tuimu.admin@tumu.vn',
  'Túi Mù Administrator',
  '0912345678',
  1, 0, 1,
  NOW(), NOW(),
  0, 0,
  MD5(CONCAT('tuimu_admin', NOW(), RAND()))
),
(
  'lienquan_boss',
  MD5('LQBoss$2024Vip'),
  'lienquan.boss@tumu.vn',
  'Liên Quân Boss Admin',
  '0923456789',
  1, 0, 1,
  NOW(), NOW(),
  0, 0,
  MD5(CONCAT('lienquan_boss', NOW(), RAND()))
);

-- ============================================================
-- VERIFICATION QUERIES (chạy sau khi migration xong)
-- ============================================================
-- SELECT * FROM boxes;
-- SELECT box_id, SUM(win_rate) as total_rate FROM box_rates GROUP BY box_id;
-- SELECT COUNT(*) as total_items, box_id, item_tier FROM box_items GROUP BY box_id, item_tier;
-- SELECT id, username, email, admin FROM users WHERE admin = 1 ORDER BY id DESC LIMIT 5;
