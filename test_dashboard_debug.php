<?php
echo "<h1>Dashboard Debug Test</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    echo "<p style='color: green;'>✅ Database connection: SUCCESS</p>";
    
    // Test basic query
    $result = $conn->query("SELECT COUNT(*) as total FROM orders");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color: green;'>✅ Orders count: " . $row['total'] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test file paths
$files_to_check = [
    '/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css',
    '/WEB_MXH/admin/pages/dashboard/css/style.css',
    '/WEB_MXH/admin/lib/chart/chart.min.js',
    '/WEB_MXH/admin/pages/dashboard/sidebar.php',
    '/WEB_MXH/admin/pages/dashboard/navbar.php',
    '/WEB_MXH/admin/pages/dashboard/footer.php'
];

echo "<h2>File Existence Check:</h2>";
foreach ($files_to_check as $file) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $file;
    if (file_exists($full_path)) {
        echo "<p style='color: green;'>✅ $file - EXISTS</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - NOT FOUND</p>";
    }
}

echo "<h2>Server Info:</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Script: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
?> 