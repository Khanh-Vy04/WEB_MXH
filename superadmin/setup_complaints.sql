CREATE TABLE IF NOT EXISTS `complaints` (
  `complaint_id` int(11) NOT NULL AUTO_INCREMENT,
  `complaint_code` varchar(20) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User who filed the complaint',
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` enum('pending', 'resolved_buyer', 'resolved_freelancer', 'rejected') DEFAULT 'pending',
  `resolution_note` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert Mock Data
INSERT INTO `complaints` (`complaint_code`, `project_id`, `user_id`, `title`, `description`, `status`, `created_at`) VALUES
('CP-2023-001', 101, 2, 'Freelancer không giao bài đúng hạn', 'Đã quá hạn 3 ngày nhưng tôi chưa nhận được phản hồi.', 'pending', NOW()),
('CP-2023-002', 105, 3, 'Sản phẩm không đúng mô tả', 'Logo thiết kế không giống style đã cam kết.', 'pending', NOW()),
('CP-2023-003', 110, 4, 'Khách hàng không thanh toán', 'Tôi đã giao file nhưng khách hàng yêu cầu hoàn tiền vô lý.', 'pending', NOW());

