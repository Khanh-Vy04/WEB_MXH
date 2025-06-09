<?php
// Set timezone for PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

$host = 'localhost';
$dbname = 'web_project';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    // Set timezone for MySQL
    $conn->query("SET time_zone = '+07:00'");
} catch (Exception $e) {
    // Don't die, let the calling script handle the error
    throw new Exception("Lỗi kết nối database: " . $e->getMessage());
}
?> 