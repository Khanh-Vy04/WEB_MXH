<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

// Kiểm tra quyền admin (nếu cần)
// if (!isset($_SESSION['admin_id'])) {
//     header('Location: /WEB_MXH/admin/login.php');
//     exit;
// }

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng query
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where_clause .= " AND (o.order_id LIKE ? OR u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss";
}

// Lấy dữ liệu đơn hàng
$orders_query = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.final_amount,
        o.voucher_discount,
        u.username,
        u.full_name,
        u.email,
        u.phone,
        os.stage_name,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.buyer_id = u.user_id
    JOIN order_stages os ON o.stage_id = os.stage_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    {$where_clause}
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
";

if ($params) {
    $stmt = $conn->prepare($orders_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($orders_query);
}

// Set headers cho file Excel
$filename = "don_hang_" . date('Y-m-d_H-i-s') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Tạo output stream
$output = fopen('php://output', 'w');

// Thêm BOM để Excel hiển thị tiếng Việt đúng
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header columns
$headers = [
    'Mã đơn hàng',
    'Ngày đặt',
    'Tên khách hàng', 
    'Email',
    'Số điện thoại',
    'Username',
    'Số sản phẩm',
    'Tổng tiền gốc',
    'Giảm giá',
    'Thành tiền',
    'Trạng thái',
    'Thời gian đặt'
];

fputcsv($output, $headers);

// Dữ liệu
while ($order = $result->fetch_assoc()) {
    $row = [
        '#' . $order['order_id'],
        date('d/m/Y', strtotime($order['order_date'])),
        $order['full_name'],
        $order['email'],
        $order['phone'],
        $order['username'],
        $order['item_count'] . ' sản phẩm',
        number_format($order['total_amount'], 0, '.', ',') . 'đ',
        number_format($order['voucher_discount'], 0, '.', ',') . 'đ',
        number_format($order['final_amount'], 0, '.', ',') . 'đ',
        $order['stage_name'],
        date('d/m/Y H:i', strtotime($order['order_date']))
    ];
    
    fputcsv($output, $row);
}

fclose($output);
exit;
?> 