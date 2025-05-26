<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

echo "=== KIỂM TRA CẤU TRÚC DATABASE ===\n\n";

// Kiểm tra bảng users
echo "1. Cấu trúc bảng users:\n";
$sql = "DESCRIBE users";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n2. Dữ liệu trong bảng users:\n";
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "\nUser ID: " . $row['user_id'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Role ID: " . $row['role_id'] . "\n";
        echo "Password: " . substr($row['password'], 0, 20) . "...\n";
    }
}

echo "\n3. Cấu trúc bảng roles:\n";
$sql = "DESCRIBE roles";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

echo "\n4. Dữ liệu trong bảng roles:\n";
$sql = "SELECT * FROM roles";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        echo "\nRole ID: " . $row['role_id'] . "\n";
        echo "Role Name: " . $row['role_name'] . "\n";
    }
}

$conn->close(); 