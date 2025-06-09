<?php
require_once 'config/database.php';

echo "<h2>🧪 TEST ORDER DETAIL PAGE</h2>";

// Test với order ID = 3
$test_order_id = 3;
echo "<h3>✅ Test Order ID: {$test_order_id}</h3>";

// Lấy thông tin đơn hàng
$order_query = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.final_amount,
        o.voucher_discount,
        u.user_id,
        u.username,
        u.full_name,
        u.email,
        u.phone,
        u.address,
        os.stage_name,
        os.color_code
    FROM orders o
    JOIN users u ON o.buyer_id = u.user_id
    JOIN order_stages os ON o.stage_id = os.stage_id
    WHERE o.order_id = ?
";

$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $test_order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$order = $order_result->fetch_assoc();

if ($order) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'><th colspan='2'>Thông tin đơn hàng</th></tr>";
    foreach ($order as $key => $value) {
        echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
    }
    echo "</table>";
    
    // Lấy items
    echo "<h3>📦 Chi tiết sản phẩm:</h3>";
    $items_query = "
        SELECT 
            oi.quantity,
            oi.unit_price,
            oi.total_price,
            oi.item_type,
            oi.item_name,
            p.description as product_description,
            a.description as accessory_description
        FROM order_items oi
        LEFT JOIN products p ON oi.item_id = p.product_id AND oi.item_type = 'product'
        LEFT JOIN accessories a ON oi.item_id = a.accessory_id AND oi.item_type = 'accessory'
        WHERE oi.order_id = ?
        ORDER BY oi.order_item_id
    ";
    
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $test_order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Tên sản phẩm</th><th>Loại</th><th>Đơn giá</th><th>Số lượng</th><th>Thành tiền</th>";
    echo "</tr>";
    
    $total_quantity = 0;
    while ($item = $items_result->fetch_assoc()) {
        $total_quantity += $item['quantity'];
        echo "<tr>";
        echo "<td>{$item['item_name']}</td>";
        echo "<td>" . ($item['item_type'] === 'product' ? 'Album nhạc' : 'Phụ kiện') . "</td>";
        echo "<td>" . number_format($item['unit_price'], 0, '.', ',') . "đ</td>";
        echo "<td>{$item['quantity']}</td>";
        echo "<td>" . number_format($item['total_price'], 0, '.', ',') . "đ</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Tổng số lượng:</strong> {$total_quantity}</p>";
    
} else {
    echo "❌ Không tìm thấy đơn hàng với ID: {$test_order_id}";
}

echo "<hr>";
echo "<h3>🔗 Links test:</h3>";
echo "<p><a href='admin/pages/order/order_detail/order_detail.php?id={$test_order_id}' target='_blank'>👁️ Xem Order Detail Page</a></p>";
echo "<p><a href='admin/pages/order/order_list/order_list.php' target='_blank'>📋 Quay lại Order List</a></p>";

echo "<h3>📋 Các thay đổi đã thực hiện:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Xóa tất cả checkbox</strong> (không còn checkbox nào trong bảng)</li>";
echo "<li>✅ <strong>Xóa tất cả button Edit</strong> (không còn link Edit nào)</li>";
echo "<li>✅ <strong>Xóa Order Tracking section</strong> hoàn toàn</li>";
echo "<li>✅ <strong>Xóa hình ảnh customer</strong> - thay bằng avatar chữ cái</li>";
echo "<li>✅ <strong>Sử dụng dữ liệu thật</strong> từ database</li>";
echo "<li>✅ <strong>Giao diện hiện đại</strong> với gradient và card design</li>";
echo "<li>✅ <strong>Hiển thị đúng sản phẩm</strong>: BORN PINK (album) + Loa Marshall (phụ kiện)</li>";
echo "<li>✅ <strong>Responsive design</strong> với mobile-friendly layout</li>";
echo "</ul>";
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    max-width: 1000px; 
    margin: 20px auto; 
    padding: 20px; 
    background: #f5f5f5; 
}
h2 { color: #333; border-bottom: 2px solid #ff6b35; padding-bottom: 10px; }
h3 { color: #555; margin-top: 30px; }
table { margin: 10px 0; background: white; }
table th, table td { padding: 8px 12px; text-align: left; }
table th { background: #667eea; color: white; }
a { color: #ff6b35; text-decoration: none; }
a:hover { text-decoration: underline; }
ul li { margin: 5px 0; }
</style> 