<?php
require_once '../../config/database.php';
require_once '../includes/session.php';

// Set content type to JSON
header('Content-Type: application/json');

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method không được phép']);
    exit();
}

$action = $_POST['action'] ?? '';

// Xử lý action không cần đăng nhập
if ($action === 'get_cart' && !isLoggedIn()) {
    echo json_encode([
        'success' => true,
        'cart_items' => [],
        'total_amount' => 0,
        'total_items' => 0,
        'message' => 'Chưa đăng nhập'
    ]);
    exit();
}

// Các action cần đăng nhập
$login_required_actions = ['add_to_cart', 'update_quantity', 'remove_item'];
if (in_array($action, $login_required_actions) && !isLoggedIn()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng',
        'require_login' => true
    ]);
    exit();
}

$current_user = getCurrentUser();
$user_id = $current_user['user_id'] ?? 0;

try {
    switch ($action) {
        case 'add_to_cart':
            addToCart($conn, $user_id);
            break;
        case 'get_cart':
            getCart($conn, $user_id);
            break;
        case 'update_quantity':
            updateQuantity($conn, $user_id);
            break;
        case 'remove_item':
            removeItem($conn, $user_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    error_log("Cart Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
}

function addToCart($conn, $user_id) {
    $item_type = $_POST['item_type'] ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if (!in_array($item_type, ['product', 'accessory']) || $item_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }
    
    // Kiểm tra tồn kho
    if ($item_type == 'product') {
        $check_sql = "SELECT stock, product_name, price FROM products WHERE product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $item_id);
    } else {
        $check_sql = "SELECT stock, accessory_name as product_name, price FROM accessories WHERE accessory_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $item_id);
    }
    
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        return;
    }
    
    $item = $result->fetch_assoc();
    if ($item['stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho']);
        return;
    }
    
    // Kiểm tra xem item đã có trong giỏ chưa
    if ($item_type == 'product') {
        $existing_sql = "SELECT cart_id, quantity FROM shopping_cart WHERE user_id = ? AND product_id = ? AND item_type = 'product'";
        $existing_stmt = $conn->prepare($existing_sql);
        $existing_stmt->bind_param("ii", $user_id, $item_id);
    } else {
        $existing_sql = "SELECT cart_id, quantity FROM shopping_cart WHERE user_id = ? AND accessory_id = ? AND item_type = 'accessory'";
        $existing_stmt = $conn->prepare($existing_sql);
        $existing_stmt->bind_param("ii", $user_id, $item_id);
    }
    
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    
    if ($existing_result->num_rows > 0) {
        // Update quantity
        $existing_item = $existing_result->fetch_assoc();
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        if ($new_quantity > $item['stock']) {
            echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
            return;
        }
        
        $update_sql = "UPDATE shopping_cart SET quantity = ? WHERE cart_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_quantity, $existing_item['cart_id']);
        $update_stmt->execute();
    } else {
        // Insert new item
        if ($item_type == 'product') {
            $insert_sql = "INSERT INTO shopping_cart (user_id, product_id, quantity, item_type) VALUES (?, ?, ?, 'product')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $user_id, $item_id, $quantity);
        } else {
            $insert_sql = "INSERT INTO shopping_cart (user_id, accessory_id, quantity, item_type) VALUES (?, ?, ?, 'accessory')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $user_id, $item_id, $quantity);
        }
        $insert_stmt->execute();
    }
    
    // Lấy tổng số items trong giỏ
    $count_sql = "SELECT SUM(quantity) as total_items FROM shopping_cart WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total_items'] ?? 0;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm vào giỏ hàng',
        'total_items' => $total_items
    ]);
}

function getCart($conn, $user_id) {
    try {
        // Debug log
        error_log("getCart called for user_id: $user_id");
        
        // Lấy dữ liệu từ shopping_cart trước
        $cart_sql = "SELECT * FROM shopping_cart WHERE user_id = ? ORDER BY added_at DESC";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        error_log("Found " . $cart_result->num_rows . " items in shopping_cart for user $user_id");
        
        $cart_items = [];
        $total_amount = 0;
        $total_items = 0;
        
        while ($cart_row = $cart_result->fetch_assoc()) {
            error_log("Processing cart item: " . json_encode($cart_row));
            
            $item_data = $cart_row; // Bắt đầu với dữ liệu cart
            
            // Truy vấn thông tin sản phẩm dựa vào item_type
            if ($cart_row['item_type'] == 'product' && !empty($cart_row['product_id'])) {
                error_log("Querying product with ID: " . $cart_row['product_id']);
                
                $product_sql = "SELECT product_name as item_name, price, image_url, stock FROM products WHERE product_id = ?";
                $product_stmt = $conn->prepare($product_sql);
                $product_stmt->bind_param("i", $cart_row['product_id']);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                
                if ($product_result->num_rows > 0) {
                    $product_data = $product_result->fetch_assoc();
                    error_log("Product found: " . json_encode($product_data));
                    $item_data = array_merge($item_data, $product_data);
                } else {
                    error_log("No product found with ID: " . $cart_row['product_id']);
                }
            } 
            else if ($cart_row['item_type'] == 'accessory' && !empty($cart_row['accessory_id'])) {
                error_log("Querying accessory with ID: " . $cart_row['accessory_id']);
                
                $accessory_sql = "SELECT accessory_name as item_name, price, image_url, stock FROM accessories WHERE accessory_id = ?";
                $accessory_stmt = $conn->prepare($accessory_sql);
                $accessory_stmt->bind_param("i", $cart_row['accessory_id']);
                $accessory_stmt->execute();
                $accessory_result = $accessory_stmt->get_result();
                
                if ($accessory_result->num_rows > 0) {
                    $accessory_data = $accessory_result->fetch_assoc();
                    error_log("Accessory found: " . json_encode($accessory_data));
                    $item_data = array_merge($item_data, $accessory_data);
                } else {
                    error_log("No accessory found with ID: " . $cart_row['accessory_id']);
                }
            }
            
            // Chỉ thêm vào cart nếu có thông tin sản phẩm
            if (isset($item_data['item_name']) && isset($item_data['price'])) {
                $cart_items[] = $item_data;
                $total_amount += floatval($item_data['price']) * intval($item_data['quantity']);
                $total_items += intval($item_data['quantity']);
                error_log("Added item to cart_items: " . $item_data['item_name']);
            } else {
                error_log("Item missing required fields - not added to cart");
            }
        }
        
        // Final debug log
        error_log("Final cart result: " . count($cart_items) . " items, total: $" . $total_amount);
        
        echo json_encode([
            'success' => true,
            'cart_items' => $cart_items,
            'total_amount' => floatval($total_amount),
            'total_items' => intval($total_items),
            'debug_info' => [
                'user_id' => $user_id,
                'raw_cart_count' => $cart_result->num_rows,
                'processed_items' => count($cart_items)
            ]
        ]);
        
    } catch (Exception $e) {
        error_log("Error in getCart: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error loading cart: ' . $e->getMessage(),
            'debug_info' => [
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ]
        ]);
    }
}

function updateQuantity($conn, $user_id) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($cart_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }
    
    // Kiểm tra cart item thuộc về user
    $check_sql = "SELECT sc.*, 
                         CASE 
                             WHEN sc.item_type = 'product' THEN p.stock
                             ELSE a.stock
                         END as stock
                  FROM shopping_cart sc
                  LEFT JOIN products p ON sc.product_id = p.product_id AND sc.item_type = 'product'
                  LEFT JOIN accessories a ON sc.accessory_id = a.accessory_id AND sc.item_type = 'accessory'
                  WHERE sc.cart_id = ? AND sc.user_id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Item không tồn tại']);
        return;
    }
    
    $item = $result->fetch_assoc();
    if ($quantity > $item['stock']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        return;
    }
    
    $update_sql = "UPDATE shopping_cart SET quantity = ? WHERE cart_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $quantity, $cart_id);
    $update_stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật số lượng']);
}

function removeItem($conn, $user_id) {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }
    
    $delete_sql = "DELETE FROM shopping_cart WHERE cart_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa khỏi giỏ hàng']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể xóa item']);
    }
}

$conn->close();
?> 