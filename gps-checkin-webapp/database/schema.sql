-- GPS Checkin WebApp 數據庫 Schema
-- 版本：1.0
-- 創建日期：2026-04-01

-- 設置字符集
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- 1. 用戶表
-- ============================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '用戶 ID',
  `name` VARCHAR(100) NOT NULL COMMENT '姓名',
  `email` VARCHAR(100) UNIQUE NOT NULL COMMENT '郵箱',
  `password_hash` VARCHAR(255) NOT NULL COMMENT '密碼哈希',
  `role` ENUM('admin', 'employee') DEFAULT 'employee' COMMENT '角色',
  `phone` VARCHAR(20) NULL COMMENT '電話號碼',
  `status` TINYINT DEFAULT 1 COMMENT '狀態 (1=啟用，0=禁用)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '創建時間',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用戶表';

-- ============================================
-- 2. 打卡記錄表
-- ============================================
DROP TABLE IF EXISTS `checkins`;
CREATE TABLE `checkins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '記錄 ID',
  `user_id` INT NOT NULL COMMENT '用戶 ID',
  `latitude` DECIMAL(10, 8) NOT NULL COMMENT '緯度',
  `longitude` DECIMAL(11, 8) NOT NULL COMMENT '經度',
  `checkin_time` DATETIME NOT NULL COMMENT '上班時間',
  `checkout_time` DATETIME NULL COMMENT '下班時間',
  `photo_path` VARCHAR(255) NULL COMMENT '照片路徑',
  `device_fingerprint` VARCHAR(255) NULL COMMENT '設備指紋',
  `note` TEXT NULL COMMENT '備註',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '創建時間',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_checkin_time` (`checkin_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='打卡記錄表';

-- ============================================
-- 3. 作弊日誌表
-- ============================================
DROP TABLE IF EXISTS `fraud_logs`;
CREATE TABLE `fraud_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '日誌 ID',
  `user_id` INT NOT NULL COMMENT '用戶 ID',
  `checkin_id` INT NOT NULL COMMENT '打卡記錄 ID',
  `fraud_type` VARCHAR(50) NOT NULL COMMENT '作弊類型 (gps_spoof/time_anomaly/device_mismatch)',
  `risk_score` INT DEFAULT 0 COMMENT '風險評分 (0-100)',
  `description` TEXT NULL COMMENT '描述',
  `status` TINYINT DEFAULT 0 COMMENT '狀態 (0=待審查，1=已確認，2=已忽略)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '創建時間',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`checkin_id`) REFERENCES `checkins`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_fraud_type` (`fraud_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='作弊日誌表';

-- ============================================
-- 4. 設備表（可選）
-- ============================================
DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '設備 ID',
  `user_id` INT NOT NULL COMMENT '用戶 ID',
  `device_fingerprint` VARCHAR(255) NOT NULL COMMENT '設備指紋',
  `device_name` VARCHAR(100) NULL COMMENT '設備名稱',
  `last_used_at` DATETIME NULL COMMENT '最後使用時間',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '創建時間',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_device` (`user_id`, `device_fingerprint`),
  INDEX `idx_fingerprint` (`device_fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='設備表';

-- ============================================
-- 5. 初始數據
-- ============================================

-- 插入默認管理員賬戶
-- 密碼：admin123 (bcrypt 哈希)
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`) VALUES
('系統管理員', 'admin@gps-checkin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================
-- 6. 視圖（可選）
-- ============================================

-- 今日打卡統計視圖
DROP VIEW IF EXISTS `v_today_stats`;
CREATE VIEW `v_today_stats` AS
SELECT 
  COUNT(DISTINCT u.id) as total_employees,
  COUNT(DISTINCT c.user_id) as checked_in,
  COUNT(DISTINCT CASE WHEN c.checkout_time < DATE_FORMAT(NOW(), '%Y-%m-%d 18:00:00') THEN c.user_id END) as early_leave,
  COUNT(DISTINCT u.id) - COUNT(DISTINCT c.user_id) as absent
FROM users u
LEFT JOIN checkins c ON u.id = c.user_id AND DATE(c.checkin_time) = CURDATE()
WHERE u.role = 'employee' AND u.status = 1;

SET FOREIGN_KEY_CHECKS = 1;
