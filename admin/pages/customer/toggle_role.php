<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Sử dụng function isAdmin() từ session.php thay vì tạo function riêng

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

$current_admin_id = $_SESSION['user_id'];

// Kiểm tra quyền admin của user hiện tại
$admin_check_sql = "SELECT user_id, username, role_id FROM users WHERE user_id = ?";
$admin_stmt = $conn->prepare($admin_check_sql);
$admin_stmt->bind_param("i", $current_admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$current_admin = $admin_result->fetch_assoc();

if (!isAdmin($current_admin)) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit();
}

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID người dùng không hợp lệ']);
    exit();
}

// Không cho phép thay đổi role của chính mình
if ($user_id == $current_admin_id) {
    echo json_encode(['success' => false, 'message' => 'Bạn không thể thay đổi quyền của chính mình']);
    exit();
}

try {
    // Lấy thông tin user cần thay đổi
    $user_sql = "SELECT user_id, username, role_id FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    
    if ($user_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng']);
        exit();
    }
    
    $user = $user_result->fetch_assoc();
    
    // Xác định role mới
    if (isAdmin($user)) {
        // Hiện tại là admin, chuyển thành user
        $new_role = 2;
        $new_role_name = 'User';
    } else {
        // Hiện tại là user, chuyển thành admin
        $new_role = 1;
        $new_role_name = 'Admin';
    }
    
    // Cập nhật role
    $update_sql = "UPDATE users SET role_id = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_role, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cập nhật quyền thành công',
            'new_role' => $new_role,
            'new_role_name' => $new_role_name,
            'user_id' => $user_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật database: ' . $conn->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?> 