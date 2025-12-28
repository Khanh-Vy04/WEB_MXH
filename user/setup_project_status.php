<?php
require_once '../config/database.php';

// SQL to create the temporary project status table
$sql = "CREATE TABLE IF NOT EXISTS `project_status_demo` (
  `project_id` int(11) NOT NULL,
  `quote_accepted` tinyint(1) DEFAULT 0,
  `user_confirmed` tinyint(1) DEFAULT 0,
  `freelancer_confirmed` tinyint(1) DEFAULT 0,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "<h3>Table 'project_status_demo' created successfully!</h3>";
    // Init demo project
    $conn->query("INSERT IGNORE INTO project_status_demo (project_id) VALUES (101)");
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
<br>
<a href="index.php">Go back to Home</a>

