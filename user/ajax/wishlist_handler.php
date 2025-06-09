<?php
// Wishlist Handler - Xử lý các thao tác với danh sách yêu thích

// Bắt đầu session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng chức năng này!']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_to_wishlist':
            $item_id = intval($_POST['item_id'] ?? 0);
            $item_type = $_POST['item_type'] ?? 'product'; // product hoặc accessory
            
            if ($item_id <= 0) {
                throw new Exception("ID sản phẩm không hợp lệ!");
            }
            
            if (!in_array($item_type, ['product', 'accessory'])) {
                throw new Exception("Loại sản phẩm không hợp lệ!");
            }
            
            // Kiểm tra sản phẩm có tồn tại không
            if ($item_type === 'product') {
                $check_sql = "SELECT product_name FROM products WHERE product_id = ?";
            } else {
                $check_sql = "SELECT accessory_name as product_name FROM accessories WHERE accessory_id = ?";
            }
            
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $item_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                throw new Exception("Sản phẩm không tồn tại!");
            }
            
            $product_info = $check_result->fetch_assoc();
            
            // Kiểm tra đã có trong wishlist chưa
            $existing_sql = "SELECT wishlist_id FROM wishlist 
                            WHERE user_id = ? AND product_id = ? AND item_type = ?";
            $existing_stmt = $conn->prepare($existing_sql);
            $existing_stmt->bind_param("iis", $user_id, $item_id, $item_type);
            $existing_stmt->execute();
            $existing_result = $existing_stmt->get_result();
            
            if ($existing_result->num_rows > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Sản phẩm đã có trong danh sách yêu thích!',
                    'already_exists' => true
                ]);
                break;
            }
            
            // Thêm vào wishlist (variant_id tạm thời set = 0)
            $insert_sql = "INSERT INTO wishlist (user_id, product_id, item_type, variant_id) 
                          VALUES (?, ?, ?, 0)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iis", $user_id, $item_id, $item_type);
            
            if ($insert_stmt->execute()) {
                // Đếm tổng số items trong wishlist
                $count_sql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
                $count_stmt = $conn->prepare($count_sql);
                $count_stmt->bind_param("i", $user_id);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $total_items = $count_result->fetch_assoc()['total'];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã thêm "' . $product_info['product_name'] . '" vào danh sách yêu thích!',
                    'total_items' => $total_items,
                    'wishlist_id' => $conn->insert_id
                ]);
            } else {
                throw new Exception("Lỗi khi thêm vào danh sách yêu thích!");
            }
            break;
            
        case 'remove_from_wishlist':
            $item_id = intval($_POST['item_id'] ?? 0);
            $item_type = $_POST['item_type'] ?? 'product';
            
            if ($item_id <= 0) {
                throw new Exception("ID sản phẩm không hợp lệ!");
            }
            
            // Lấy thông tin sản phẩm trước khi xóa
            if ($item_type === 'product') {
                $info_sql = "SELECT product_name FROM products WHERE product_id = ?";
            } else {
                $info_sql = "SELECT accessory_name as product_name FROM accessories WHERE accessory_id = ?";
            }
            
            $info_stmt = $conn->prepare($info_sql);
            $info_stmt->bind_param("i", $item_id);
            $info_stmt->execute();
            $info_result = $info_stmt->get_result();
            $product_name = $info_result->num_rows > 0 ? $info_result->fetch_assoc()['product_name'] : 'Sản phẩm';
            
            // Xóa khỏi wishlist
            $delete_sql = "DELETE FROM wishlist 
                          WHERE user_id = ? AND product_id = ? AND item_type = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("iis", $user_id, $item_id, $item_type);
            
            if ($delete_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    // Đếm tổng số items còn lại
                    $count_sql = "SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?";
                    $count_stmt = $conn->prepare($count_sql);
                    $count_stmt->bind_param("i", $user_id);
                    $count_stmt->execute();
                    $count_result = $count_stmt->get_result();
                    $total_items = $count_result->fetch_assoc()['total'];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã xóa "' . $product_name . '" khỏi danh sách yêu thích!',
                        'total_items' => $total_items
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Sản phẩm không có trong danh sách yêu thích!'
                    ]);
                }
            } else {
                throw new Exception("Lỗi khi xóa khỏi danh sách yêu thích!");
            }
            break;
            
        case 'get_wishlist':
            $page = intval($_POST['page'] ?? 1);
            $item_type_filter = $_POST['item_type'] ?? '';
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            // Build where clause
            $where_conditions = ["w.user_id = ?"];
            $params = [$user_id];
            $param_types = ['i'];
            
            if ($item_type_filter !== '') {
                $where_conditions[] = "w.item_type = ?";
                $params[] = $item_type_filter;
                $param_types[] = 's';
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Đếm tổng số items
            $count_sql = "SELECT COUNT(*) as total FROM wishlist w WHERE " . $where_clause;
            $count_stmt = $conn->prepare($count_sql);
            
            if (!empty($params)) {
                $types = implode('', $param_types);
                $count_stmt->bind_param($types, ...$params);
            }
            
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $total_count = $count_result->fetch_assoc()['total'];
            
            // Lấy danh sách wishlist
            $sql = "SELECT 
                        w.wishlist_id,
                        w.product_id,
                        w.item_type,
                        w.added_at,
                        CASE 
                            WHEN w.item_type = 'product' THEN p.product_name
                            WHEN w.item_type = 'accessory' THEN a.accessory_name
                        END as item_name,
                        CASE 
                            WHEN w.item_type = 'product' THEN p.image_url
                            WHEN w.item_type = 'accessory' THEN a.image_url
                        END as image_url,
                        CASE 
                            WHEN w.item_type = 'product' THEN p.price
                            WHEN w.item_type = 'accessory' THEN a.price
                        END as price,
                        CASE 
                            WHEN w.item_type = 'product' THEN p.stock
                            WHEN w.item_type = 'accessory' THEN a.stock
                        END as stock
                    FROM wishlist w
                    LEFT JOIN products p ON w.item_type = 'product' AND w.product_id = p.product_id
                    LEFT JOIN accessories a ON w.item_type = 'accessory' AND w.product_id = a.accessory_id
                    WHERE " . $where_clause . "
                    ORDER BY w.added_at DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            $param_types[] = 'i';
            $param_types[] = 'i';
            
            $stmt = $conn->prepare($sql);
            
            if (!empty($params)) {
                $types = implode('', $param_types);
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $wishlist_items = [];
            while ($row = $result->fetch_assoc()) {
                $row['price_formatted'] = number_format($row['price'], 0, ',', '.') . '₫';
                $row['added_at_formatted'] = date('d/m/Y', strtotime($row['added_at']));
                $wishlist_items[] = $row;
            }
            
            // Pagination info
            $total_pages = ceil($total_count / $limit);
            $pagination = [
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_count,
                'items_per_page' => $limit
            ];
            
            echo json_encode([
                'success' => true,
                'wishlist_items' => $wishlist_items,
                'total' => $total_count,
                'pagination' => $pagination
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'check_wishlist_status':
            $item_id = intval($_POST['item_id'] ?? 0);
            $item_type = $_POST['item_type'] ?? 'product';
            
            if ($item_id <= 0) {
                throw new Exception("ID sản phẩm không hợp lệ!");
            }
            
            $check_sql = "SELECT wishlist_id FROM wishlist 
                         WHERE user_id = ? AND product_id = ? AND item_type = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iis", $user_id, $item_id, $item_type);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            echo json_encode([
                'success' => true,
                'in_wishlist' => $check_result->num_rows > 0
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Wishlist Handler Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'sql_error' => isset($conn) ? $conn->error : 'No connection'
        ]
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 