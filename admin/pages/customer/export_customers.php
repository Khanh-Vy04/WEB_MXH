<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header('Location: /WEB_MXH/admin/login.php');
    exit;
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Xây dựng query với tìm kiếm
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where_clause .= " AND (u.user_id LIKE ? OR u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param, $search_param];
    $types = "sssss";
}

// Lấy dữ liệu customers với thống kê đơn hàng
$customers_query = "
    SELECT 
        u.user_id,
        u.username,
        u.full_name,
        u.email,
        u.phone,
        u.gender,
        u.address,
        u.created_at,
        u.balance,
        u.role_id,
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.final_amount), 0) as total_spent,
        MAX(o.order_date) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.buyer_id
    {$where_clause}
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
";

if ($params) {
    $stmt = $conn->prepare($customers_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($customers_query);
}

// Set headers cho file Excel
$filename = "khach_hang_" . date('Y-m-d_H-i-s') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Tạo output stream
$output = fopen('php://output', 'w');

// Thêm BOM để Excel hiển thị tiếng Việt đúng
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header columns
$headers = [
    'ID Khách hàng',
    'Tên đầy đủ',
    'Username',
    'Email',
    'Số điện thoại',
    'Giới tính',
    'Địa chỉ',
    'Loại tài khoản',
    'Số dư',
    'Tổng đơn hàng',
    'Tổng chi tiêu',
    'Đơn hàng cuối',
    'Ngày đăng ký',
    'Thời gian đăng ký đầy đủ'
];

fputcsv($output, $headers);

// Dữ liệu
while ($customer = $result->fetch_assoc()) {
    $row = [
        '#' . $customer['user_id'],
        $customer['full_name'],
        $customer['username'],
        $customer['email'],
        $customer['phone'] ?: 'Chưa cập nhật',
        $customer['gender'] ?: 'Chưa cập nhật',
        $customer['address'] ?: 'Chưa cập nhật',
        ($customer['role_id'] == 1) ? 'ADMIN' : 'USER',
        number_format($customer['balance'], 0, '.', ',') . 'đ',
        $customer['total_orders'] . ' đơn',
        number_format($customer['total_spent'], 0, '.', ',') . 'đ',
        $customer['last_order_date'] ? date('d/m/Y', strtotime($customer['last_order_date'])) : 'Chưa có đơn hàng',
        date('d/m/Y', strtotime($customer['created_at'])),
        date('d/m/Y H:i:s', strtotime($customer['created_at']))
    ];
    
    fputcsv($output, $row);
}

fclose($output);
exit;
?> 