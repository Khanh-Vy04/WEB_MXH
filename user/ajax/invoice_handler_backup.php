<?php
// BACKUP - Working version of invoice_handler.php
// Đã sửa lỗi 500 Internal Server Error bằng cách:
// 1. Sửa đường dẫn database từ '../config/database.php' thành '../../config/database.php'
// 2. Đơn giản hóa query để tránh lỗi với parameter binding
// 3. Thêm error handling tốt hơn

// Bắt đầu session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Check if user is logged in - use default for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 4; // Default for testing
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_invoices':
            $status_filter = $_POST['status'] ?? '';
            $date_filter = $_POST['date'] ?? '';
            $page = intval($_POST['page'] ?? 1);
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            // Build base query
            $where_conditions = ["o.buyer_id = $user_id"];
            
            if ($status_filter !== '') {
                $where_conditions[] = "o.stage_id = " . intval($status_filter);
            }
            
            if ($date_filter !== '') {
                $where_conditions[] = "DATE(o.order_date) = '" . $conn->real_escape_string($date_filter) . "'";
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Get total count
            $count_sql = "SELECT COUNT(*) as total FROM orders o WHERE " . $where_clause;
            $count_result = $conn->query($count_sql);
            
            if (!$count_result) {
                throw new Exception("Lỗi query count: " . $conn->error);
            }
            
            $total_count = $count_result->fetch_assoc()['total'];
            
            // Get invoices with simplified query
            $sql = "SELECT 
                        o.order_id as id,
                        o.order_date as created_at,
                        o.total_amount,
                        o.final_amount,
                        o.stage_id as status,
                        o.voucher_discount as discount_amount,
                        1 as total_items,
                        'Sản phẩm...' as items_preview,
                        'wallet' as payment_method
                    FROM orders o 
                    WHERE " . $where_clause . "
                    ORDER BY o.order_date DESC 
                    LIMIT $limit OFFSET $offset";
            
            $result = $conn->query($sql);
            
            if (!$result) {
                throw new Exception("Lỗi query main: " . $conn->error);
            }
            
            $invoices = [];
            while ($row = $result->fetch_assoc()) {
                // Get actual item count
                $item_count_sql = "SELECT COUNT(*) as count FROM order_items WHERE order_id = " . $row['id'];
                $item_count_result = $conn->query($item_count_sql);
                if ($item_count_result) {
                    $row['total_items'] = $item_count_result->fetch_assoc()['count'];
                }
                
                // Get items preview
                $items_sql = "SELECT 
                                CASE 
                                    WHEN oi.item_type = 'product' THEN p.product_name
                                    WHEN oi.item_type = 'accessory' THEN a.accessory_name
                                    ELSE oi.item_name
                                END as item_name
                              FROM order_items oi
                              LEFT JOIN products p ON (oi.item_type = 'product' AND oi.item_id = p.product_id)
                              LEFT JOIN accessories a ON (oi.item_type = 'accessory' AND oi.item_id = a.accessory_id)
                              WHERE oi.order_id = " . $row['id'] . "
                              LIMIT 3";
                              
                $items_result = $conn->query($items_sql);
                $items_preview = [];
                if ($items_result) {
                    while ($item = $items_result->fetch_assoc()) {
                        if ($item['item_name']) {
                            $items_preview[] = $item['item_name'];
                        }
                    }
                }
                $row['items_preview'] = implode(', ', $items_preview) ?: 'Đang tải...';
                
                $invoices[] = $row;
            }
            
            // Format data
            foreach ($invoices as &$invoice) {
                $invoice['created_at'] = date('d/m/Y H:i', strtotime($invoice['created_at']));
                $invoice['payment_method'] = 'Ví điện tử';
                $invoice['subtotal'] = $invoice['total_amount'];
                $invoice['total_amount'] = $invoice['final_amount'];
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
                'invoices' => $invoices,
                'total' => $total_count,
                'pagination' => $pagination
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'get_invoice_detail':
            $order_id = (int)($_POST['order_id'] ?? 0);
            
            if (!$order_id) {
                throw new Exception("ID hóa đơn không hợp lệ!");
            }
            
            // Kiểm tra quyền truy cập
            $check_sql = "SELECT COUNT(*) as count FROM orders WHERE order_id = $order_id AND buyer_id = $user_id";
            $check_result = $conn->query($check_sql);
            
            if (!$check_result || $check_result->fetch_assoc()['count'] == 0) {
                throw new Exception("Không có quyền truy cập hóa đơn này!");
            }
            
            // Lấy thông tin đơn hàng
            $order_sql = "SELECT 
                            o.*,
                            os.stage_name,
                            u.full_name as buyer_name,
                            u.phone,
                            u.email
                          FROM orders o
                          LEFT JOIN order_stages os ON o.stage_id = os.stage_id
                          LEFT JOIN users u ON o.buyer_id = u.user_id
                          WHERE o.order_id = $order_id";
            
            $order_result = $conn->query($order_sql);
            
            if (!$order_result) {
                throw new Exception("Lỗi query order: " . $conn->error);
            }
            
            $order = $order_result->fetch_assoc();
            
            if (!$order) {
                throw new Exception("Không tìm thấy hóa đơn!");
            }
            
            // Lấy chi tiết sản phẩm
            $items_sql = "SELECT 
                            oi.*,
                            CASE 
                                WHEN oi.item_type = 'product' THEN p.product_name
                                WHEN oi.item_type = 'accessory' THEN a.accessory_name
                                ELSE oi.item_name
                            END as item_name,
                            CASE 
                                WHEN oi.item_type = 'product' THEN p.image_url
                                WHEN oi.item_type = 'accessory' THEN a.image_url
                                ELSE NULL
                            END as item_image
                          FROM order_items oi
                          LEFT JOIN products p ON oi.item_type = 'product' AND oi.item_id = p.product_id
                          LEFT JOIN accessories a ON oi.item_type = 'accessory' AND oi.item_id = a.accessory_id
                          WHERE oi.order_id = $order_id";
            
            $items_result = $conn->query($items_sql);
            
            if (!$items_result) {
                throw new Exception("Lỗi query items: " . $conn->error);
            }
            
            $items = $items_result->fetch_all(MYSQLI_ASSOC);
            
            // Format dữ liệu
            $order['total_amount_formatted'] = number_format($order['total_amount'], 0, ',', '.') . '₫';
            $date = new DateTime($order['order_date']);
            $order['order_date_formatted'] = $date->format('d/m/Y H:i');
            
            foreach ($items as &$item) {
                // Sử dụng unit_price nếu có, nếu không thì dùng product_price
                $price = $item['unit_price'] ?? $item['product_price'] ?? 0;
                $item['item_price_formatted'] = number_format($price, 0, ',', '.') . '₫';
                $item['total_price'] = $price * $item['quantity'];
                $item['total_price_formatted'] = number_format($item['total_price'], 0, ',', '.') . '₫';
            }
            
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ!']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Invoice Handler Error: " . $e->getMessage());
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