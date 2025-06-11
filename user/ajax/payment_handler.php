<?php
// Kết nối database và khởi tạo session
require_once '../../config/database.php';
require_once '../includes/session.php';

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận POST request']);
    exit();
}

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$current_user = getCurrentUser();
$user_id = $current_user['user_id'];

// Lấy action
$action = $_POST['action'] ?? '';

if ($action === 'process_payment') {
    processPayment();
} else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

function processPayment() {
    global $conn, $user_id;
    
    try {
        // Bắt đầu transaction
        $conn->begin_transaction();
        
                 // Lấy và validate data
        $selectedItems = json_decode($_POST['selected_items'] ?? '[]', true);
        $selectedVoucher = $_POST['selected_voucher'] !== 'null' ? json_decode($_POST['selected_voucher'] ?? 'null', true) : null;
        $selectedTotal = floatval($_POST['selected_total'] ?? 0);
        $discount = floatval($_POST['discount'] ?? 0);
        $finalAmount = floatval($_POST['final_amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'wallet';
        $vnpayOrderId = $_POST['vnpay_order_id'] ?? null;
        
        // Debug log
        error_log("Payment Debug - Selected Items: " . print_r($selectedItems, true));
        error_log("Payment Debug - Voucher: " . print_r($selectedVoucher, true));
        error_log("Payment Debug - Amounts: Total={$selectedTotal}, Discount={$discount}, Final={$finalAmount}");
        error_log("Payment Debug - Payment Method: {$paymentMethod}");
        error_log("Payment Debug - User ID: {$user_id}");
        
        // Validate input
        if (empty($selectedItems) || $finalAmount <= 0) {
            throw new Exception('Dữ liệu thanh toán không hợp lệ');
        }
        
        // 1. Xử lý theo phương thức thanh toán
        $user_data = null;
        if ($paymentMethod === 'wallet') {
            // Kiểm tra số dư người dùng cho thanh toán ví
        $user_query = "SELECT balance FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        
        if (!$user_data) {
            throw new Exception('Không tìm thấy thông tin người dùng');
        }
        
        if ($user_data['balance'] < $finalAmount) {
            throw new Exception('Số dư không đủ để thanh toán. Số dư hiện tại: ' . number_format($user_data['balance']) . '₫');
        }
        }
        
        // 2. Tạo order trong bảng orders với payment_method
        $stage_id = 0; // Mặc định tất cả đơn hàng đều có stage_id = 0 (chờ xác nhận)
        
        // Debug log for order creation
        error_log("Order Debug - UserID: {$user_id}, Total: {$selectedTotal}, Discount: {$discount}, Final: {$finalAmount}, Method: {$paymentMethod}, Stage: {$stage_id}");
        
        $order_query = "INSERT INTO orders (buyer_id, total_amount, payment_method, stage_id, voucher_discount, final_amount) VALUES (?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_query);
        
        if (!$order_stmt) {
            throw new Exception('Không thể prepare order query: ' . $conn->error);
        }
        
        $order_stmt->bind_param("idsidd", $user_id, $selectedTotal, $paymentMethod, $stage_id, $discount, $finalAmount);
        
        if (!$order_stmt->execute()) {
            throw new Exception('Không thể tạo đơn hàng: ' . $order_stmt->error);
        }
        
        $order_id = $conn->insert_id;
        
                 // 3. Xử lý từng sản phẩm được chọn
        foreach ($selectedItems as $item) {
            $cart_id = intval($item['cart_id']);
            $item_type = $item['item_type'];
            $item_id = intval($item['item_type'] === 'product' ? $item['product_id'] : $item['accessory_id']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);
            $total_price = $price * $quantity;
            
            // Debug log
            error_log("Processing item: CartID={$cart_id}, Type={$item_type}, ItemID={$item_id}, Qty={$quantity}, Price={$price}");
            
            // Kiểm tra và trừ stock
            if ($item_type === 'product') {
                // Kiểm tra stock product
                $product_query = "SELECT product_name, stock FROM products WHERE product_id = ?";
                $product_stmt = $conn->prepare($product_query);
                $product_stmt->bind_param("i", $item_id);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                $product_data = $product_result->fetch_assoc();
                
                if (!$product_data) {
                    throw new Exception('Sản phẩm không tồn tại: ID ' . $item_id);
                }
                
                if ($product_data['stock'] < $quantity) {
                    throw new Exception('Không đủ stock cho sản phẩm: ' . $product_data['product_name'] . ' (Còn: ' . $product_data['stock'] . ')');
                }
                
                // Trừ stock product
                $update_product_query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
                $update_product_stmt = $conn->prepare($update_product_query);
                $update_product_stmt->bind_param("ii", $quantity, $item_id);
                
                if (!$update_product_stmt->execute()) {
                    throw new Exception('Không thể cập nhật stock sản phẩm');
                }
                
                $item_name = $product_data['product_name'];
                
            } else {
                // Kiểm tra stock accessory
                $accessory_query = "SELECT accessory_name, stock FROM accessories WHERE accessory_id = ?";
                $accessory_stmt = $conn->prepare($accessory_query);
                $accessory_stmt->bind_param("i", $item_id);
                $accessory_stmt->execute();
                $accessory_result = $accessory_stmt->get_result();
                $accessory_data = $accessory_result->fetch_assoc();
                
                if (!$accessory_data) {
                    throw new Exception('Phụ kiện không tồn tại: ID ' . $item_id);
                }
                
                if ($accessory_data['stock'] < $quantity) {
                    throw new Exception('Không đủ stock cho phụ kiện: ' . $accessory_data['accessory_name'] . ' (Còn: ' . $accessory_data['stock'] . ')');
                }
                
                // Trừ stock accessory
                $update_accessory_query = "UPDATE accessories SET stock = stock - ? WHERE accessory_id = ?";
                $update_accessory_stmt = $conn->prepare($update_accessory_query);
                $update_accessory_stmt->bind_param("ii", $quantity, $item_id);
                
                if (!$update_accessory_stmt->execute()) {
                    throw new Exception('Không thể cập nhật stock phụ kiện');
                }
                
                $item_name = $accessory_data['accessory_name'];
            }
            
            // Thêm vào order_items
            $order_item_query = "INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, total_price, item_type, item_id, item_name, unit_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $order_item_stmt = $conn->prepare($order_item_query);
            $order_item_stmt->bind_param("iisdidsisd", $order_id, $item_id, $item_name, $price, $quantity, $total_price, $item_type, $item_id, $item_name, $price);
            
            if (!$order_item_stmt->execute()) {
                throw new Exception('Không thể thêm chi tiết đơn hàng');
            }
            
            // Xóa sản phẩm khỏi giỏ hàng
            $delete_cart_query = "DELETE FROM shopping_cart WHERE cart_id = ? AND user_id = ?";
            $delete_cart_stmt = $conn->prepare($delete_cart_query);
            $delete_cart_stmt->bind_param("ii", $cart_id, $user_id);
            
            if (!$delete_cart_stmt->execute()) {
                throw new Exception('Không thể xóa sản phẩm khỏi giỏ hàng');
            }
        }
        
        // 4. Xử lý voucher nếu có
        if ($selectedVoucher && $discount > 0) {
            $user_voucher_id = intval($selectedVoucher['user_voucher_id']);
            
            // Cập nhật voucher đã sử dụng trong user_vouchers
            $update_user_voucher_query = "UPDATE user_vouchers SET is_used = 1, used_date = CURRENT_TIMESTAMP WHERE user_voucher_id = ? AND user_id = ? AND is_used = 0";
            $update_user_voucher_stmt = $conn->prepare($update_user_voucher_query);
            $update_user_voucher_stmt->bind_param("ii", $user_voucher_id, $user_id);
            
            if (!$update_user_voucher_stmt->execute() || $update_user_voucher_stmt->affected_rows === 0) {
                throw new Exception('Không thể cập nhật trạng thái voucher');
            }
            
            // Lấy voucher_id để cập nhật used_count
            $get_voucher_query = "SELECT voucher_id FROM user_vouchers WHERE user_voucher_id = ?";
            $get_voucher_stmt = $conn->prepare($get_voucher_query);
            $get_voucher_stmt->bind_param("i", $user_voucher_id);
            $get_voucher_stmt->execute();
            $voucher_result = $get_voucher_stmt->get_result();
            $voucher_data = $voucher_result->fetch_assoc();
            
            if ($voucher_data) {
                // Tăng used_count trong bảng vouchers
                $update_voucher_query = "UPDATE vouchers SET used_count = used_count + 1 WHERE voucher_id = ?";
                $update_voucher_stmt = $conn->prepare($update_voucher_query);
                $update_voucher_stmt->bind_param("i", $voucher_data['voucher_id']);
                $update_voucher_stmt->execute();
            }
        }
        
        // 5. Lưu thông tin VNPay order ID nếu có
        if ($paymentMethod === 'vnpay' && $vnpayOrderId) {
            $update_payment_query = "UPDATE orders SET payment_id = ? WHERE order_id = ?";
            $update_payment_stmt = $conn->prepare($update_payment_query);
            $update_payment_stmt->bind_param("si", $vnpayOrderId, $order_id);
            $update_payment_stmt->execute();
        }
        
        // 6. Trừ tiền trong tài khoản user chỉ cho phương thức ví
        if ($paymentMethod === 'wallet') {
        $update_balance_query = "UPDATE users SET balance = balance - ? WHERE user_id = ?";
        $update_balance_stmt = $conn->prepare($update_balance_query);
        $update_balance_stmt->bind_param("di", $finalAmount, $user_id);
        
        if (!$update_balance_stmt->execute()) {
            throw new Exception('Không thể trừ tiền từ tài khoản');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Trả về kết quả thành công
        $success_message = '';
        $response_data = [
            'success' => true,
            'order_id' => $order_id,
            'final_amount' => $finalAmount,
            'payment_method' => $paymentMethod
        ];
        
        switch($paymentMethod) {
            case 'wallet':
                $success_message = 'Thanh toán bằng ví thành công!';
                $response_data['remaining_balance'] = $user_data['balance'] - $finalAmount;
                break;
            case 'vnpay':
                $success_message = 'Thanh toán VNPay thành công!';
                if ($vnpayOrderId) {
                    $response_data['vnpay_order_id'] = $vnpayOrderId;
                }
                break;
            case 'cash':
                $success_message = 'Đặt hàng COD thành công! Bạn sẽ thanh toán khi nhận hàng.';
                break;
        }
        
        $response_data['message'] = $success_message;
        echo json_encode($response_data);
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?> 