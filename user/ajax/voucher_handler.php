<?php
// Voucher AJAX Handler
require_once '../../config/database.php';
require_once '../includes/session.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_user_vouchers':
        getUserVouchers($conn, $user_id);
        break;
    case 'use_voucher':
        useVoucher($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

function getUserVouchers($conn, $user_id) {
    try {
        $sql = "SELECT 
                    uv.user_voucher_id,
                    uv.voucher_id,
                    uv.assigned_date,
                    uv.used_date,
                    uv.is_used,
                    v.voucher_code,
                    v.voucher_name,
                    v.description,
                    v.discount_type,
                    v.discount_value,
                    v.min_order_amount,
                    v.max_discount_amount,
                    v.start_date,
                    v.end_date,
                    v.is_active,
                    CASE 
                        WHEN uv.is_used = 1 THEN 1
                        WHEN v.end_date < CURDATE() THEN 1
                        WHEN v.start_date > CURDATE() THEN 1
                        WHEN v.is_active = 0 THEN 1
                        ELSE 0
                    END as is_expired
                FROM user_vouchers uv
                JOIN vouchers v ON uv.voucher_id = v.voucher_id
                WHERE uv.user_id = ?
                ORDER BY 
                    CASE 
                        WHEN uv.is_used = 0 AND v.end_date >= CURDATE() AND v.start_date <= CURDATE() AND v.is_active = 1 THEN 0
                        ELSE 1
                    END,
                    v.end_date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $available_vouchers = [];
        $unavailable_vouchers = [];
        
        while ($row = $result->fetch_assoc()) {
            // Format dates
            $row['start_date'] = date('d/m/Y', strtotime($row['start_date']));
            $row['end_date'] = date('d/m/Y', strtotime($row['end_date']));
            
            // Categorize vouchers
            if ($row['is_used'] == 0 && $row['is_expired'] == 0 && $row['is_active'] == 1) {
                $available_vouchers[] = $row;
            } else {
                $unavailable_vouchers[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'available_vouchers' => $available_vouchers,
            'unavailable_vouchers' => $unavailable_vouchers,
            'total_available' => count($available_vouchers),
            'total_unavailable' => count($unavailable_vouchers)
        ]);
        
    } catch (Exception $e) {
        error_log("Voucher error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi khi tải vouchers: ' . $e->getMessage()
        ]);
    }
}

function useVoucher($conn, $user_id) {
    try {
        $user_voucher_id = $_POST['user_voucher_id'] ?? 0;
        $cart_total = floatval($_POST['cart_total'] ?? 0);
        
        if (!$user_voucher_id || !$cart_total) {
            echo json_encode(['success' => false, 'message' => 'Thông tin không đầy đủ']);
            return;
        }
        
        // Verify voucher belongs to user and is valid
        $sql = "SELECT 
                    uv.user_voucher_id,
                    uv.is_used,
                    v.voucher_code,
                    v.discount_type,
                    v.discount_value,
                    v.min_order_amount,
                    v.max_discount_amount,
                    v.start_date,
                    v.end_date,
                    v.is_active
                FROM user_vouchers uv
                JOIN vouchers v ON uv.voucher_id = v.voucher_id
                WHERE uv.user_voucher_id = ? AND uv.user_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_voucher_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        
        if (!$voucher) {
            echo json_encode(['success' => false, 'message' => 'Voucher không tồn tại']);
            return;
        }
        
        // Check if voucher is valid
        if ($voucher['is_used']) {
            echo json_encode(['success' => false, 'message' => 'Voucher đã được sử dụng']);
            return;
        }
        
        if (!$voucher['is_active']) {
            echo json_encode(['success' => false, 'message' => 'Voucher không còn hoạt động']);
            return;
        }
        
        if (strtotime($voucher['end_date']) < time()) {
            echo json_encode(['success' => false, 'message' => 'Voucher đã hết hạn']);
            return;
        }
        
        if (strtotime($voucher['start_date']) > time()) {
            echo json_encode(['success' => false, 'message' => 'Voucher chưa có hiệu lực']);
            return;
        }
        
        // Check minimum order amount
        if ($voucher['min_order_amount'] > 0 && $cart_total < $voucher['min_order_amount']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($voucher['min_order_amount']) . '₫'
            ]);
            return;
        }
        
        // Calculate discount
        $discount_amount = 0;
        if ($voucher['discount_type'] === 'percentage') {
            $discount_amount = ($cart_total * $voucher['discount_value']) / 100;
            // Apply max discount limit if set
            if ($voucher['max_discount_amount'] > 0) {
                $discount_amount = min($discount_amount, $voucher['max_discount_amount']);
            }
        } else {
            $discount_amount = min($voucher['discount_value'], $cart_total);
        }
        
        // Mark voucher as used
        $update_sql = "UPDATE user_vouchers SET is_used = 1, used_date = NOW() WHERE user_voucher_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $user_voucher_id);
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Áp dụng voucher thành công',
                'voucher_code' => $voucher['voucher_code'],
                'discount_amount' => $discount_amount,
                'final_total' => $cart_total - $discount_amount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật voucher']);
        }
        
    } catch (Exception $e) {
        error_log("Use voucher error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Lỗi khi sử dụng voucher: ' . $e->getMessage()
        ]);
    }
}
?> 