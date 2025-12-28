<?php
require_once '../config/database.php';

// SQL to create the temporary demo messages table
$sql = "CREATE TABLE IF NOT EXISTS `messages_demo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL COMMENT 'Optional: link to project',
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_type` enum('text','image','file','quote') DEFAULT 'text',
  `message_content` text COMMENT 'Text body or File Path',
  `file_name` varchar(255) DEFAULT NULL COMMENT 'Original filename for display',
  `file_size` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_project` (`project_id`),
  INDEX `idx_sender` (`sender_id`),
  INDEX `idx_receiver` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

// Create table
if ($conn->query($sql) === TRUE) {
    echo "<h3>Table 'messages_demo' created successfully!</h3>";
    
    // Insert some dummy data for testing
    echo "<p>Inserting sample data...</p>";
    
    $insertSql = "INSERT INTO `messages_demo` (`project_id`, `sender_id`, `receiver_id`, `message_type`, `message_content`, `created_at`) VALUES 
    (101, 2, 1, 'text', 'Chào bạn, mình muốn hỏi về dịch vụ thiết kế logo.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
    (101, 1, 2, 'text', 'Chào bạn! Mình có thể hỗ trợ gì cho bạn ạ?', DATE_SUB(NOW(), INTERVAL 23 HOUR)),
    (101, 2, 1, 'text', 'Mình cần thiết kế logo cho quán cafe, style vintage.', DATE_SUB(NOW(), INTERVAL 22 HOUR)),
    (101, 1, 2, 'quote', '{\"price\": 500000, \"time\": \"3 ngày\", \"service\": \"Thiết kế Logo\"}', DATE_SUB(NOW(), INTERVAL 20 HOUR)),
    (101, 1, 2, 'image', '/WEB_MXH/user/assets/images/collection/arrivals1.png', DATE_SUB(NOW(), INTERVAL 19 HOUR));";
    
    if ($conn->query($insertSql) === TRUE) {
        echo "<p>Sample data inserted successfully.</p>";
    } else {
        echo "<p>Error inserting sample data: " . $conn->error . "</p>";
    }

} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
<br>
<a href="index.php">Go back to Home</a>

