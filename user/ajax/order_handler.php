<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_order':
            createOrder();
            break;
        case 'update_stage':
            updateOrderStage();
            break;
        case 'get_user_orders':
            getUserOrders();
            break;
        case 'get_order_details':
            getOrderDetails();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
            break;
    }
}

// Tạo đơn hàng từ giỏ hàng
function createOrder() {
    global $pdo;
    
    try {
        $user_id = $_SESSION['user_id'];
        $shipping_address = $_POST['shipping_address'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $voucher_discount = floatval($_POST['voucher_discount'] ?? 0);
        
        // Bắt đầu transaction
        $pdo->beginTransaction();
        
        // Lấy items từ giỏ hàng
        $cart_query = "SELECT * FROM shopping_cart WHERE user_id = ?";
        $cart_stmt = $pdo->prepare($cart_query);
        $cart_stmt->execute([$user_id]);
        $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cart_items)) {
            throw new Exception('Giỏ hàng trống');
        }
        
        // Tính tổng tiền
        $total_amount = 0;
        $order_items_data = [];
        
        foreach ($cart_items as $item) {
            if ($item['item_type'] === 'product') {
                $product_query = "SELECT name, price FROM products WHERE product_id = ?";
                $product_stmt = $pdo->prepare($product_query);
                $product_stmt->execute([$item['item_id']]);
                $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($product) {
                    $item_total = $product['price'] * $item['quantity'];
                    $total_amount += $item_total;
                    
                    $order_items_data[] = [
                        'item_type' => 'product',
                        'item_id' => $item['item_id'],
                        'item_name' => $product['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $product['price'],
                        'total_price' => $item_total
                    ];
                }
            } elseif ($item['item_type'] === 'accessory') {
                $accessory_query = "SELECT accessory_name, price FROM accessories WHERE accessory_id = ?";
                $accessory_stmt = $pdo->prepare($accessory_query);
                $accessory_stmt->execute([$item['item_id']]);
                $accessory = $accessory_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($accessory) {
                    $item_total = $accessory['price'] * $item['quantity'];
                    $total_amount += $item_total;
                    
                    $order_items_data[] = [
                        'item_type' => 'accessory',
                        'item_id' => $item['item_id'],
                        'item_name' => $accessory['accessory_name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $accessory['price'],
                        'total_price' => $item_total
                    ];
                }
            }
        }
        
        $final_amount = $total_amount - $voucher_discount;
        if ($final_amount < 0) $final_amount = 0;
        
        // Tạo đơn hàng
        $order_query = "INSERT INTO orders (buyer_id, total_amount, voucher_discount, final_amount, 
                               shipping_address_id, notes, stage_id) 
                       VALUES (?, ?, ?, ?, ?, ?, 0)";
        $order_stmt = $pdo->prepare($order_query);
        
        // Tạm thời dùng shipping_address_id = 1, bạn có thể tạo bảng addresses riêng
        $order_stmt->execute([$user_id, $total_amount, $voucher_discount, $final_amount, 1, $notes]);
        $order_id = $pdo->lastInsertId();
        
        // Thêm order items
        foreach ($order_items_data as $item_data) {
            $item_query = "INSERT INTO order_items (order_id, item_type, item_id, item_name, 
                                  quantity, unit_price, total_price,
                                  product_id, product_name, product_price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 
                                  CASE WHEN ? = 'product' THEN ? ELSE 0 END,
                                  ?, ?)";
            $item_stmt = $pdo->prepare($item_query);
            $item_stmt->execute([
                $order_id,
                $item_data['item_type'],
                $item_data['item_id'],
                $item_data['item_name'],
                $item_data['quantity'],
                $item_data['unit_price'],
                $item_data['total_price'],
                $item_data['item_type'],
                $item_data['item_id'],
                $item_data['item_name'],
                $item_data['unit_price']
            ]);
        }
        
        // Xóa giỏ hàng sau khi tạo đơn
        $clear_cart_query = "DELETE FROM shopping_cart WHERE user_id = ?";
        $clear_cart_stmt = $pdo->prepare($clear_cart_query);
        $clear_cart_stmt->execute([$user_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đặt hàng thành công!',
            'order_id' => $order_id
        ]);
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Cập nhật trạng thái đơn hàng
function updateOrderStage() {
    global $pdo;
    
    try {
        $order_id = intval($_POST['order_id'] ?? 0);
        $stage_id = intval($_POST['stage_id'] ?? 0);
        
        // Kiểm tra stage_id hợp lệ
        $stage_check = "SELECT stage_name FROM order_stages WHERE stage_id = ? AND is_active = 1";
        $stage_stmt = $pdo->prepare($stage_check);
        $stage_stmt->execute([$stage_id]);
        
        if (!$stage_stmt->fetch()) {
            throw new Exception('Trạng thái không hợp lệ');
        }
        
        // Cập nhật stage_id
        $update_query = "UPDATE orders SET stage_id = ? WHERE order_id = ?";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->execute([$stage_id, $order_id]);
        
        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Lấy danh sách đơn hàng của user
function getUserOrders() {
    global $pdo;
    
    try {
        $user_id = $_SESSION['user_id'];
        
        $query = "SELECT o.*, os.stage_name, os.color_code 
                 FROM orders o 
                 LEFT JOIN order_stages os ON o.stage_id = os.stage_id 
                 WHERE o.buyer_id = ? 
                 ORDER BY o.order_date DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'orders' => $orders]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Lấy chi tiết đơn hàng
function getOrderDetails() {
    global $pdo;
    
    try {
        $order_id = intval($_POST['order_id'] ?? 0);
        $user_id = $_SESSION['user_id'];
        
        // Lấy thông tin đơn hàng
        $order_query = "SELECT o.*, os.stage_name, os.color_code 
                       FROM orders o 
                       LEFT JOIN order_stages os ON o.stage_id = os.stage_id 
                       WHERE o.order_id = ? AND o.buyer_id = ?";
        
        $order_stmt = $pdo->prepare($order_query);
        $order_stmt->execute([$order_id, $user_id]);
        $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception('Không tìm thấy đơn hàng');
        }
        
        // Lấy chi tiết sản phẩm trong đơn hàng
        $items_query = "SELECT oi.*, 
                              CASE 
                                  WHEN oi.item_type = 'product' THEN p.image_url
                                  WHEN oi.item_type = 'accessory' THEN a.image_url
                              END as image_url
                       FROM order_items oi
                       LEFT JOIN products p ON oi.item_type = 'product' AND oi.item_id = p.product_id
                       LEFT JOIN accessories a ON oi.item_type = 'accessory' AND oi.item_id = a.accessory_id
                       WHERE oi.order_id = ?";
        
        $items_stmt = $pdo->prepare($items_query);
        $items_stmt->execute([$order_id]);
        $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'order' => $order,
            'items' => $items
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?> 