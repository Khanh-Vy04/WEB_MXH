<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/session.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    die('Không có quyền truy cập');
}

// Thiết lập timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Xử lý tuần được chọn
$week_input = isset($_GET['week']) ? $_GET['week'] : date('Y-W');
if ($week_input) {
    $year = substr($week_input, 0, 4);
    $week_num = substr($week_input, 6);
    $selected_date = date('Y-m-d', strtotime($year . 'W' . $week_num . '1'));
} else {
    $selected_date = date('Y-m-d');
}

$start_of_week = date('Y-m-d 00:00:00', strtotime('monday this week', strtotime($selected_date)));
$end_of_week = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($selected_date)));

// Lấy dữ liệu thống kê
try {
    // 1. Thống kê tổng quan
    $orders_this_week_query = "SELECT COUNT(*) as count FROM orders WHERE order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($orders_this_week_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $orders_this_week = $stmt->get_result()->fetch_assoc()['count'];

    $total_orders_query = "SELECT COUNT(*) as count FROM orders";
    $total_orders = $conn->query($total_orders_query)->fetch_assoc()['count'];

    $revenue_this_week_query = "SELECT COALESCE(SUM(final_amount), 0) as revenue FROM orders WHERE order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($revenue_this_week_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $revenue_this_week = $stmt->get_result()->fetch_assoc()['revenue'];

    $total_revenue_query = "SELECT COALESCE(SUM(final_amount), 0) as revenue FROM orders";
    $total_revenue = $conn->query($total_revenue_query)->fetch_assoc()['revenue'];

    // 2. Dữ liệu theo ngày
    $daily_stats = [];
    for ($i = 0; $i < 7; $i++) {
        $day_start = date('Y-m-d 00:00:00', strtotime($start_of_week . " +{$i} day"));
        $day_end = date('Y-m-d 23:59:59', strtotime($start_of_week . " +{$i} day"));
        
        $daily_query = "
            SELECT 
                COUNT(o.order_id) as orders_count,
                COALESCE(SUM(o.final_amount), 0) as revenue
            FROM orders o 
            WHERE o.order_date BETWEEN ? AND ?
        ";
        $stmt = $conn->prepare($daily_query);
        $stmt->bind_param("ss", $day_start, $day_end);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $daily_stats[] = [
            'day' => date('l', strtotime($day_start)),
            'day_vi' => [
                'Monday' => 'Thứ 2',
                'Tuesday' => 'Thứ 3', 
                'Wednesday' => 'Thứ 4',
                'Thursday' => 'Thứ 5',
                'Friday' => 'Thứ 6',
                'Saturday' => 'Thứ 7',
                'Sunday' => 'Chủ nhật'
            ][date('l', strtotime($day_start))],
            'orders' => $result['orders_count'],
            'revenue' => $result['revenue'],
            'date' => date('d/m/Y', strtotime($day_start))
        ];
    }

    // 3. Top sản phẩm
    $top_products_query = "
        SELECT 
            oi.item_name,
            oi.item_type,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.total_price) as total_revenue
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_date BETWEEN ? AND ?
        GROUP BY oi.item_name, oi.item_type
        ORDER BY total_quantity DESC
        LIMIT 10
    ";
    $stmt = $conn->prepare($top_products_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    die('Lỗi truy vấn database: ' . $e->getMessage());
}

// Sửa lỗi: Xuất CSV thay vì giả mạo Excel
// Clear any output buffer để đảm bảo CSV sạch
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Dashboard_Report_' . date('Y-m-d', strtotime($selected_date)) . '.csv"');
header('Cache-Control: max-age=0');

// Tạo nội dung CSV đơn giản
$data = [];

// Header
$data[] = ['BÁOCÁO DASHBOARD - TUẦN ' . date('d/m/Y', strtotime($start_of_week)) . ' - ' . date('d/m/Y', strtotime($end_of_week))];
$data[] = ['Tạo lúc: ' . date('d/m/Y H:i:s')];
$data[] = [''];

// Thống kê tổng quan
$data[] = ['THỐNG KÊ TỔNG QUAN'];
$data[] = ['Chỉ số', 'Giá trị'];
$data[] = ['Đơn hàng tuần này', $orders_this_week];
$data[] = ['Tổng đơn hàng', $total_orders];
$data[] = ['Doanh thu tuần này (VNĐ)', number_format($revenue_this_week, 0, '.', ',')];
$data[] = ['Tổng doanh thu (VNĐ)', number_format($total_revenue, 0, '.', ',')];
$data[] = [''];

// Thống kê theo ngày
$data[] = ['THỐNG KÊ THEO NGÀY'];
$data[] = ['Ngày', 'Thứ', 'Số đơn hàng', 'Doanh thu (VNĐ)'];
foreach ($daily_stats as $day) {
    $data[] = [$day['date'], $day['day_vi'], $day['orders'], number_format($day['revenue'], 0, '.', ',')];
}
$data[] = [''];

// Top sản phẩm
$data[] = ['TOP SẢN PHẨM BÁN CHẠY'];
$data[] = ['Sản phẩm', 'Loại', 'Số lượng bán', 'Doanh thu (VNĐ)'];
foreach ($top_products as $product) {
    $data[] = [
        $product['item_name'], 
        $product['item_type'], 
        $product['total_quantity'], 
        number_format($product['total_revenue'], 0, '.', ',')
    ];
}

// Xuất CSV
$output = fopen('php://output', 'w');

// Thêm BOM để hỗ trợ Unicode
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

foreach ($data as $row) {
    fputcsv($output, $row, ','); // Sử dụng comma delimiter cho CSV
}

fclose($output);
exit(); // Dừng execution để không xuất HTML 