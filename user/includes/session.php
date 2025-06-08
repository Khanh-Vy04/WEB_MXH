<?php
session_start();

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Hàm lấy thông tin user hiện tại
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'fullname' => $_SESSION['fullname'] ?? ''
    ];
}

// Hàm login user
function loginUser($userData) {
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['email'] = $userData['email'];
    $_SESSION['fullname'] = $userData['fullname'] ?? '';
}

// Hàm logout user
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
}

// Hàm redirect nếu chưa login
function requireLogin($redirectUrl = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectUrl");
        exit();
    }
}
?> 