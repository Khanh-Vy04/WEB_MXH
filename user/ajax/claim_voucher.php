<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để lấy voucher'
    ]);
    exit();
}

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method không hợp lệ'
    ]);
    exit();
}

// Lấy dữ liệu
$voucher_id = isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0;
$current_user = getCurrentUser();

if (!$voucher_id || !$current_user) {
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ'
    ]);
    exit();
}

try {
    $conn->begin_transaction();
    
    // Kiểm tra voucher có tồn tại và còn hiệu lực không
    $check_voucher_sql = "SELECT * FROM vouchers WHERE voucher_id = ? AND is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()";
    $check_stmt = $conn->prepare($check_voucher_sql);
    $check_stmt->bind_param("i", $voucher_id);
    $check_stmt->execute();
    $voucher_result = $check_stmt->get_result();
    
    if ($voucher_result->num_rows == 0) {
        throw new Exception('Voucher không tồn tại hoặc đã hết hạn');
    }
    
    $voucher = $voucher_result->fetch_assoc();
    
    // Kiểm tra user đã sở hữu voucher này chưa
    $check_owned_sql = "SELECT * FROM user_vouchers WHERE user_id = ? AND voucher_id = ?";
    $check_owned_stmt = $conn->prepare($check_owned_sql);
    $check_owned_stmt->bind_param("ii", $current_user['user_id'], $voucher_id);
    $check_owned_stmt->execute();
    $owned_result = $check_owned_stmt->get_result();
    
    if ($owned_result->num_rows > 0) {
        throw new Exception('Bạn đã sở hữu voucher này rồi');
    }
    
    // Kiểm tra giới hạn sử dụng tổng
    if ($voucher['usage_limit'] && $voucher['used_count'] >= $voucher['usage_limit']) {
        throw new Exception('Voucher đã hết lượt sử dụng');
    }
    
    // Thêm voucher vào user_vouchers
    $insert_sql = "INSERT INTO user_vouchers (user_id, voucher_id, assigned_date) VALUES (?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $current_user['user_id'], $voucher_id);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Lỗi khi lưu voucher: ' . $insert_stmt->error);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Lấy voucher thành công! Voucher đã được thêm vào ví của bạn.',
        'voucher_name' => $voucher['voucher_name'],
        'voucher_code' => $voucher['voucher_code']
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 