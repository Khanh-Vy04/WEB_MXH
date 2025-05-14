<?php
$currentPage = 'order';

// Get order ID from URL
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// TODO: Replace with actual database query
// For now using mock data
$orderDetails = [
    [
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png',
        'name' => 'The Beatles - Abbey Road',
        'description' => 'Vinyl, 180g, 2019 Remaster',
        'price' => 650000,
        'quantity' => 1,
        'total' => 650000
    ],
    [
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png',
        'name' => 'Miles Davis - Kind of Blue',
        'description' => 'Vinyl, 180g, Jazz Classic',
        'price' => 720000,
        'quantity' => 2,
        'total' => 1440000
    ],
    [
        'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/5e/Vinyl_record_icon.png',
        'name' => 'Pink Floyd - The Wall',
        'description' => 'Vinyl, 2LP, Gatefold',
        'price' => 800000,
        'quantity' => 1,
        'total' => 800000
    ],
];

// Data giả cho tổng tiền
$orderSummary = [
    'subtotal' => 2890000,
    'discount' => 0,
    'tax' => 20000,
    'total' => 2910000
];

// Data giả cho khách hàng
$customer = [
    'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
    'name' => 'Nguyen Van A',
    'customer_id' => '20001',
    'orders_count' => 5,
    'email' => 'nguyenvana@gmail.com',
    'phone' => '+84 901 234 567'
];

// Data giả cho địa chỉ
$shipping_address = "123 Le Loi, District 1<br>Ho Chi Minh City<br>Vietnam";
$billing_address = "123 Le Loi, District 1<br>Ho Chi Minh City<br>Vietnam";
$payment = [
    'method' => 'Visa',
    'card_number' => '******1234'
];

// Data giả cho shipping activity
$orderTracking = [
    [
        'status' => 'Order was placed (Order ID: #' . $order_id . ')',
        'desc' => 'Your order for vinyl records has been placed successfully',
        'time' => 'Monday 09:00 AM',
        'active' => true
    ],
    [
        'status' => 'Pick-up',
        'desc' => 'Pick-up scheduled with courier',
        'time' => 'Monday 15:00 PM',
        'active' => true
    ],
    [
        'status' => 'Dispatched',
        'desc' => 'Records picked up by courier',
        'time' => 'Tuesday 10:00 AM',
        'active' => true
    ],
    [
        'status' => 'Package arrived',
        'desc' => 'Arrived at Ho Chi Minh City sorting center',
        'time' => 'Tuesday 18:00 PM',
        'active' => true
    ],
    [
        'status' => 'Dispatched for delivery',
        'desc' => 'Out for delivery to customer',
        'time' => 'Wednesday 08:00 AM',
        'active' => true
    ],
    [
        'status' => 'Delivery',
        'desc' => 'Expected delivery by today',
        'time' => '',
        'active' => false
    ],
];

// Add back button to return to order list
$backButton = '<a href="/WEB_MXH/admin/pages/order/order_list/order_list.php" class="btn btn-primary mb-3">
    <i class="fas fa-arrow-left"></i> Back to Order List
</a>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <title>Order Detail</title>
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

    <!-- Libraries Stylesheet -->
    <link href="/WEB_MXH/admin/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="/WEB_MXH/admin/pages/order/order_detail/order_detail.css" />
</head>
<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>
    <div class="content">
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <div class="container-fluid pt-4 px-4">
            <?php echo $backButton; ?>
            <div class="row g-4">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Order details</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 order-table-bg">
                                <thead>
                                    <tr class="order-table-header">
                                        <th scope="col" class="text-center" style="width:40px;"><input type="checkbox"></th>
                                        <th scope="col">PRODUCTS</th>
                                        <th scope="col" class="text-center">PRICE</th>
                                        <th scope="col" class="text-center">QTY</th>
                                        <th scope="col" class="text-center">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderDetails as $item): ?>
                                        <tr>
                                            <td class="text-center"><input type="checkbox"></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="40" class="rounded me-2">
                                                    <div>
                                                        <div class="product-name-bold"><?= htmlspecialchars($item['name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= number_format($item['price']) ?>₫</td>
                                            <td class="text-center"><?= $item['quantity'] ?></td>
                                            <td class="text-center"><?= number_format($item['total']) ?>₫</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex flex-column align-items-end mt-4">
                            <div>Subtotal: <b>2,890,000₫</b></div>
                            <div>Discount: <b>0₫</b></div>
                            <div>Tax: <b>20,000₫</b></div>
                            <div>Total: <b>2,910,000₫</b></div>
                        </div>
                    </div>
                    <!-- Shipping Activity -->
                    <div class="bg-white-box rounded p-4">
                        <h5 class="mb-3 section-title">Order Tracking</h5>
                        <ul class="timeline">
                            <?php foreach ($orderTracking as $item): ?>
                                <li class="timeline-item <?= $item['active'] ? 'active' : '' ?>">
                                    <span class="timeline-point"></span>
                                    <div class="timeline-content">
                                        <b><?= htmlspecialchars($item['status']) ?></b>
                                        <div><?= htmlspecialchars($item['desc']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['time']) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <!-- Customer & Address Details -->
                <div class="col-lg-4">
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Customer details</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?= htmlspecialchars($customer['avatar']) ?>" alt="avatar" class="rounded-circle me-2" width="40">
                            <div>
                                <div><b><?= htmlspecialchars($customer['name']) ?></b></div>
                                <div class="text-muted small">Customer ID: #<?= htmlspecialchars($customer['customer_id']) ?></div>
                                <div class="text-success small"><i class="fa fa-check-circle"></i> <?= htmlspecialchars($customer['orders_count']) ?> Orders</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="fw-bold">Contact info</div>
                            <div>Email: <a href="mailto:<?= htmlspecialchars($customer['email']) ?>"><?= htmlspecialchars($customer['email']) ?></a></div>
                            <div>Mobile: <a href="tel:<?= htmlspecialchars($customer['phone']) ?>"><?= htmlspecialchars($customer['phone']) ?></a></div>
                        </div>
                    </div>
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Shipping address</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div><?= htmlspecialchars($shipping_address) ?></div>
                    </div>
                    <div class="bg-white-box rounded p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0 section-title">Billing address</h5>
                            <a href="#" class="text-primary">Edit</a>
                        </div>
                        <div><?= htmlspecialchars($billing_address) ?></div>
                        <div class="mt-2">
                            <div class="fw-bold">Visa</div>
                            <div>Card Number: <?= htmlspecialchars($payment['card_number']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
</body>
</html>
