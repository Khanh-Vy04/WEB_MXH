<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';

$currentPage = 'order';

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header('Location: /WEB_MXH/admin/pages/order/order_list/order_list.php');
    exit;
}

// Lấy thông tin đơn hàng từ database
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
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: /WEB_MXH/admin/pages/order/order_list/order_list.php');
    exit;
}

$order = $order_result->fetch_assoc();

// Lấy chi tiết các sản phẩm trong đơn hàng
$order_items_query = "
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

$items_stmt = $conn->prepare($order_items_query);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/WEB_MXH/admin/pages/order/order_detail/order_detail.css" />
    
    <style>
        .content { background: #f3f4f6 !important; }
        .container-fluid { background: #f7f8f9; }
        
        .header-section {
            background: #fff;
            border-radius: 18px;
            padding: 1.1rem 1.5rem 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1.5px 4px rgba(0, 0, 0, 0.04);
        }
        
        .header-section h2 {
            color: #222;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0;
            margin-left: 0.1rem;
        }
        
        .order-detail-header {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .order-detail-header h2, .order-detail-header p, .order-detail-header span, .order-detail-header h4 {
            color: #fff !important;
        }
        
        .order-status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            color: white;
            display: inline-block;
        }
        
        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e3e6f0;
        }
        
        .info-card h5 {
            color: #444;
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 2px solid #deccca;
            padding-bottom: 10px;
        }
        
        .product-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #412d3b;
        }
        
        .product-item:last-child {
            margin-bottom: 0;
        }
        
        .product-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .price-text {
            color: #6c4a57;
            font-weight: 700;
        }
        
        .total-summary {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .contact-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .contact-info i {
            color: #412d3b;
            width: 20px;
            margin-right: 10px;
        }
        
        .text-primary {
            color: #412d3b !important;
        }
        
        .badge.bg-info {
            background-color: #412d3b !important;
        }
        
        /* Tên sản phẩm màu đen */
        .product-item h6 {
            color: #000 !important;
            font-weight: 600;
        }
        
        /* Cập nhật màu cho địa chỉ giao hàng */
        .shipping-address-box {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%) !important;
            color: white !important;
            padding: 15px;
            border-radius: 10px;
        }
        
        /* Cập nhật màu cho thống kê đơn hàng */
        .stat-box-primary {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%) !important;
            color: white !important;
        }
        
        .stat-box-secondary {
            background: linear-gradient(135deg, #deccca 0%, #c4b5b0 100%) !important;
            color: #412d3b !important;
        }
        
        .stat-box-primary .h4 {
            color: white !important;
        }
        
        .stat-box-secondary .h4 {
            color: #412d3b !important;
        }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>
        
        <div class="content">
            <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
            
            <div class="container-fluid pt-4 px-4">
                <!-- Header -->
                <div class="header-section">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2>
                                <i class="fas fa-file-invoice me-2" style="color: #000 !important;"></i>
                                <span style="color: #000 !important;">Chi tiết đơn hàng #<?php echo $order_id; ?></span>
                            </h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="/WEB_MXH/admin/pages/order/order_list/order_list.php" class="btn btn-lg" style="background-color: #deccca !important; color: #412d3b !important; border-color: #deccca !important;">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Order Header -->
                <div class="order-detail-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="mb-0" style="color: #fff !important;"><i class="fas fa-file-invoice me-2"></i>Đơn hàng #<?php echo $order_id; ?></h2>
                            <p class="mb-0 text-muted" style="color: #fff !important;">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="order-status-badge" style="background-color: <?php echo ($order['stage_name'] == 'Đặt hàng') ? '#FFD700' : $order['color_code']; ?> !important; color: <?php echo ($order['stage_name'] == 'Đặt hàng') ? '#000' : '#fff'; ?> !important;"><?php echo $order['stage_name']; ?></span>
                            <h4 class="mt-2 mb-0" style="color: #fff !important;"><?php echo number_format($order['total_amount']); ?>đ</h4>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Order Details -->
                    <div class="col-lg-8">
                        <!-- Products List -->
                        <div class="info-card">
                            <h5>
                                <i class="fas fa-shopping-cart me-2"></i>
                                Chi tiết sản phẩm
                            </h5>
                            
                            <?php if (empty($order_items)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Không có sản phẩm nào trong đơn hàng này</h6>
                                </div>
                            <?php else: ?>
                                <?php foreach ($order_items as $item): ?>
                                    <div class="product-item">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <div class="product-icon">
                                                    <?php if ($item['item_type'] === 'product'): ?>
                                                        <i class="fas fa-compact-disc"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-headphones"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                                </h6>
                                                <p class="text-muted mb-0 small">
                                                    <?php 
                                                        if ($item['item_type'] === 'product') {
                                                            echo htmlspecialchars($item['product_description']);
                                                        } else {
                                                            echo htmlspecialchars($item['accessory_description']);
                                                        }
                                                    ?>
                                                </p>
                                                <span class="badge bg-info">
                                                    <?php echo $item['item_type'] === 'product' ? 'Album nhạc' : 'Phụ kiện'; ?>
                                                </span>
                                            </div>
                                            <div class="col-auto text-end">
                                                <div class="price-text">
                                                    <?php echo number_format($item['unit_price'], 0, '.', ','); ?>đ
                                                </div>
                                                <div class="text-muted small">
                                                    Số lượng: <?php echo $item['quantity']; ?>
                                                </div>
                                                <div class="fw-bold">
                                                    Tổng: <?php echo number_format($item['total_price'], 0, '.', ','); ?>đ
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            
                            <!-- Order Summary -->
                            <div class="total-summary">
                                <div class="row">
                                    <div class="col-6">
                                        <div>Tổng tiền gốc:</div>
                                        <?php if ($order['voucher_discount'] > 0): ?>
                                            <div>Giảm giá:</div>
                                        <?php endif; ?>
                                        <div class="h5 mb-0 mt-2">Thành tiền:</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div><?php echo number_format($order['total_amount'], 0, '.', ','); ?>đ</div>
                                        <?php if ($order['voucher_discount'] > 0): ?>
                                            <div>-<?php echo number_format($order['voucher_discount'], 0, '.', ','); ?>đ</div>
                                        <?php endif; ?>
                                        <div class="h5 mb-0 mt-2"><?php echo number_format($order['final_amount'], 0, '.', ','); ?>đ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Details -->
                    <div class="col-lg-4">
                        <!-- Customer Info -->
                        <div class="info-card">
                            <h5>
                                <i class="fas fa-user me-2"></i>
                                Thông tin khách hàng
                            </h5>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="customer-avatar me-3">
                                    <?php 
                                        $name_parts = explode(' ', trim($order['full_name']));
                                        echo strtoupper(substr($name_parts[0], 0, 1));
                                        if (count($name_parts) > 1) {
                                            echo strtoupper(substr(end($name_parts), 0, 1));
                                        }
                                    ?>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($order['full_name']); ?></h6>
                                    <div class="text-muted small">@<?php echo htmlspecialchars($order['username']); ?></div>
                                    <div class="text-muted small">ID: #<?php echo $order['user_id']; ?></div>
                                </div>
                            </div>
                            
                            <div class="contact-info">
                                <i class="fas fa-envelope"></i>
                                <div>
                                    <div class="small text-muted">Email</div>
                                    <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($order['email']); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <?php if (!empty($order['phone'])): ?>
                                <div class="contact-info">
                                    <i class="fas fa-phone"></i>
                                    <div>
                                        <div class="small text-muted">Số điện thoại</div>
                                        <a href="tel:<?php echo htmlspecialchars($order['phone']); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($order['phone']); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Shipping Address -->
                        <?php if (!empty($order['address'])): ?>
                            <div class="info-card">
                                <h5>
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    Địa chỉ giao hàng
                                </h5>
                                <div class="shipping-address-box">
                                    <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Order Statistics -->
                        <div class="info-card">
                            <h5>
                                <i class="fas fa-chart-bar me-2"></i>
                                Thống kê đơn hàng
                            </h5>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="p-3 stat-box-primary rounded">
                                        <div class="h4 mb-1"><?php echo count($order_items); ?></div>
                                        <div class="small">Sản phẩm</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 stat-box-secondary rounded">
                                        <div class="h4 mb-1">
                                            <?php echo array_sum(array_column($order_items, 'quantity')); ?>
                                        </div>
                                        <div class="small">Số lượng</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include __DIR__.'/../../dashboard/footer.php'; ?>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    
    <script>
        // Print function
        function printOrder() {
            window.print();
        }
        
        // Add print button if needed
        document.addEventListener('DOMContentLoaded', function() {
            // You can add additional JavaScript functionality here
            console.log('Order Detail Page Loaded - Order ID: <?php echo $order_id; ?>');
        });
    </script>
</body>
</html>
