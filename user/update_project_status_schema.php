<?php
require_once '../config/database.php';

// Add 'freelancer_work_done' column to project_status_demo if not exists
$checkCol = $conn->query("SHOW COLUMNS FROM project_status_demo LIKE 'freelancer_work_done'");
if ($checkCol->num_rows == 0) {
    $sql = "ALTER TABLE project_status_demo ADD COLUMN freelancer_work_done TINYINT(1) DEFAULT 0 AFTER quote_accepted";
    if ($conn->query($sql)) {
        echo "Added 'freelancer_work_done' column to project_status_demo table.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'freelancer_work_done' already exists.<br>";
}

echo "Database updated successfully.";
?>
<br>
<a href="index.php">Back</a>

