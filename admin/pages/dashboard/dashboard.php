<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../../../config/database.php';
    echo "<!-- Database connection successful -->\n";
} catch (Exception $e) {
    echo "<!-- Database connection failed: " . $e->getMessage() . " -->\n";
    die("Database connection error. Please check console for details.");
}

// Thiết lập timezone Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

$currentPage = 'dashboard';

// Xử lý tuần được chọn từ form hoặc mặc định là tuần có dữ liệu
if (isset($_GET['week']) && $_GET['week']) {
    // Format từ week picker: "2025-W23"
    $week_input = $_GET['week'];
    $year = substr($week_input, 0, 4);
    $week_num = substr($week_input, 6);
    $selected_date = date('Y-m-d', strtotime($year . 'W' . $week_num . '1')); // Thứ 2 của tuần đó
} else {
    $selected_date = '2025-06-08'; // Mặc định là ngày có dữ liệu
}

$start_of_week = date('Y-m-d 00:00:00', strtotime('monday this week', strtotime($selected_date)));
$end_of_week = date('Y-m-d 23:59:59', strtotime('sunday this week', strtotime($selected_date)));

// 1. Thống kê đơn hàng tuần này
try {
    $orders_this_week_query = "SELECT COUNT(*) as count FROM orders WHERE order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($orders_this_week_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $orders_this_week = $stmt->get_result()->fetch_assoc()['count'];
} catch (Exception $e) {
    echo "<!-- Error getting orders this week: " . $e->getMessage() . " -->\n";
    $orders_this_week = 0;
}

// 2. Tổng đơn hàng
try {
    $total_orders_query = "SELECT COUNT(*) as count FROM orders";
    $total_orders = $conn->query($total_orders_query)->fetch_assoc()['count'];
} catch (Exception $e) {
    echo "<!-- Error getting total orders: " . $e->getMessage() . " -->\n";
    $total_orders = 0;
}

// 3. Doanh thu tuần này
try {
    $revenue_this_week_query = "SELECT COALESCE(SUM(final_amount), 0) as revenue FROM orders WHERE order_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($revenue_this_week_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $revenue_this_week = $stmt->get_result()->fetch_assoc()['revenue'];
} catch (Exception $e) {
    echo "<!-- Error getting revenue this week: " . $e->getMessage() . " -->\n";
    $revenue_this_week = 0;
}

// 4. Tổng doanh thu
try {
    $total_revenue_query = "SELECT COALESCE(SUM(final_amount), 0) as revenue FROM orders";
    $total_revenue = $conn->query($total_revenue_query)->fetch_assoc()['revenue'];
} catch (Exception $e) {
    echo "<!-- Error getting total revenue: " . $e->getMessage() . " -->\n";
    $total_revenue = 0;
}

// 5. Top 3 sản phẩm bán chạy tuần này
try {
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
        LIMIT 3
    ";
    $stmt = $conn->prepare($top_products_query);
    $stmt->bind_param("ss", $start_of_week, $end_of_week);
    $stmt->execute();
    $top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo "<!-- Top products found: " . count($top_products) . " -->\n";
} catch (Exception $e) {
    echo "<!-- Error getting top products: " . $e->getMessage() . " -->\n";
    $top_products = [];
}

// 6. Dữ liệu biểu đồ theo ngày trong tuần
$daily_stats = [];
try {
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
                'Monday' => 'T2',
                'Tuesday' => 'T3', 
                'Wednesday' => 'T4',
                'Thursday' => 'T5',
                'Friday' => 'T6',
                'Saturday' => 'T7',
                'Sunday' => 'CN'
            ][date('l', strtotime($day_start))],
            'orders' => $result['orders_count'],
            'revenue' => $result['revenue'],
            'date' => date('d/m', strtotime($day_start))
        ];
    }
    echo "<!-- Daily stats calculated for " . count($daily_stats) . " days -->\n";
} catch (Exception $e) {
    echo "<!-- Error getting daily stats: " . $e->getMessage() . " -->\n";
    // Fallback data
    for ($i = 0; $i < 7; $i++) {
        $fallback_day = date('Y-m-d', strtotime($start_of_week . " +{$i} day"));
        $daily_stats[] = [
            'day' => date('l', strtotime($fallback_day)),
            'day_vi' => ['Monday' => 'T2', 'Tuesday' => 'T3', 'Wednesday' => 'T4', 'Thursday' => 'T5', 'Friday' => 'T6', 'Saturday' => 'T7', 'Sunday' => 'CN'][date('l', strtotime($fallback_day))],
            'orders' => 0,
            'revenue' => 0,
            'date' => date('d/m', strtotime($fallback_day))
        ];
    }
}

// 7. Đơn hàng gần đây (5 đơn mới nhất)
try {
    $recent_orders_query = "
        SELECT 
            o.order_id,
            o.order_date,
            o.final_amount,
            u.full_name,
            u.username,
            os.stage_name,
            os.color_code
        FROM orders o
        JOIN users u ON o.buyer_id = u.user_id
        JOIN order_stages os ON o.stage_id = os.stage_id
        ORDER BY o.order_date DESC
        LIMIT 5
    ";
    $recent_orders = $conn->query($recent_orders_query)->fetch_all(MYSQLI_ASSOC);
    echo "<!-- Recent orders found: " . count($recent_orders) . " -->\n";
} catch (Exception $e) {
    echo "<!-- Error getting recent orders: " . $e->getMessage() . " -->\n";
    $recent_orders = [];
}

// 8. Tin nhắn gần đây (từ bảng support_replies)
try {
    $recent_messages_query = "
        SELECT 
            sr.reply_message,
            sr.created_at,
            sr.is_customer_reply,
            u.full_name,
            u.username
        FROM support_replies sr
        LEFT JOIN users u ON sr.user_id = u.user_id
        ORDER BY sr.created_at DESC
        LIMIT 5
    ";
    $recent_messages = $conn->query($recent_messages_query)->fetch_all(MYSQLI_ASSOC);
    echo "<!-- Recent messages found: " . count($recent_messages) . " -->\n";
} catch (Exception $e) {
    echo "<!-- Error getting recent messages: " . $e->getMessage() . " -->\n";
    $recent_messages = [];
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>AuraDisc - Admin Dashboard</title>
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
    
    <!-- Custom Dashboard Styles - Brand Colors -->
    <style>
        .dashboard-card {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            border-radius: 15px;
            color: white;
            box-shadow: 0 4px 15px rgba(65, 45, 59, 0.2);
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        
        .dashboard-card h6 {
            font-weight: 600;
            font-size: 1.1rem;
            color: white !important;
        }
        
        .dashboard-card p {
            font-weight: 500;
            opacity: 0.9;
            color: white !important;
        }
        
        .dashboard-card .text-warning {
            color: #deccca !important;
        }
        
        .chart-container {
            min-height: 400px;
            height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .chart-container canvas {
            width: 100% !important;
            height: 320px !important;
            max-height: 320px;
            margin: 0 auto;
            display: block;
        }
        
        .table-custom {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .table-custom th {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            color: white !important;
            font-weight: 600;
            border: none;
        }
        
        .table-custom td {
            color: #333;
            font-weight: 500;
            vertical-align: middle;
        }
        
        .messages-widget {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .messages-widget h6 {
            color: #412d3b;
            font-weight: 600;
        }
        
        .bg-light {
            background: linear-gradient(135deg, rgba(222,204,202,0.3) 0%, rgba(222,204,202,0.1) 100%) !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            border: none;
            color: white !important;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6c4a57 0%, #412d3b 100%);
            transform: translateY(-1px);
            color: white !important;
        }
        
        .badge.bg-primary {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%) !important;
        }
        
        .text-primary {
            color: #412d3b !important;
        }
        
        .export-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #6c4a57 0%, #412d3b 100%);
            transform: translateY(-2px);
            color: white;
        }
        
        .export-btn i {
            margin-right: 8px;
        }

        /* Custom Week Selector Styles */
        input[type="week"] {
            background: linear-gradient(135deg, #412d3b 0%, #6c4a57 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        input[type="week"]:hover {
            background: linear-gradient(135deg, #6c4a57 0%, #412d3b 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(65, 45, 59, 0.2);
        }

        input[type="week"]:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(222, 204, 202, 0.3);
        }

        /* Custom Calendar Icon Color */
        input[type="week"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        input[type="week"]::-webkit-calendar-picker-indicator:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <?php include 'sidebar.php'; ?>
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->
        
        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <?php include 'navbar.php'; ?>
            <!-- Navbar End -->
            
            <!-- Week Selector Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded p-4 shadow-sm">
                            <form method="GET" class="row align-items-center">
                                <div class="col-md-3">
                                    <label for="week-selector" class="form-label fw-bold text-dark">
                                        <i class="fas fa-calendar-week me-2 text-primary"></i>Chọn tuần thống kê:
                                    </label>
                                </div>
                                <div class="col-md-3">
                                    <input type="week" 
                                           id="week-selector" 
                                           name="week" 
                                           class="form-control" 
                                           value="<?php echo date('Y', strtotime($selected_date)) . '-W' . date('W', strtotime($selected_date)); ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Áp dụng
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetToCurrentWeek()">
                                        <i class="fas fa-refresh me-2"></i>Tuần hiện tại
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-dark">
                                        <strong>
                                            <i class="fas fa-info-circle me-1 text-info"></i>
                                            Tuần được chọn: 
                                        </strong>
                                        <br>
                                        <span class="badge bg-primary">
                                            <?php echo date('d/m/Y', strtotime($start_of_week)); ?> - 
                                            <?php echo date('d/m/Y', strtotime($end_of_week)); ?>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Week Selector End -->
            
            <!-- Export Section Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="export-section">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-2" style="color: #412d3b;">
                                        <i class="fas fa-file-csv me-2" style="color: #28a745;"></i>
                                        Xuất báo cáo CSV
                                    </h6>
                                    <p class="text-muted mb-0">
                                        Tạo file CSV chứa dữ liệu thống kê cho tuần được chọn (có thể mở bằng Excel)
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                                                            <button type="button" class="export-btn" onclick="exportDashboardExcel()">
                                        <i class="fas fa-download"></i>
                                        Xuất CSV báo cáo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Export Section End -->
            
            <!-- Sale & Revenue Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="dashboard-card d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-line fa-3x text-warning"></i>
                            <div class="ms-3 text-end">
                                <p class="mb-2">Đơn hàng tuần này</p>
                                <h6 class="mb-0"><?php echo $orders_this_week; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="dashboard-card d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-bar fa-3x text-warning"></i>
                            <div class="ms-3 text-end">
                                <p class="mb-2">Tổng đơn hàng</p>
                                <h6 class="mb-0"><?php echo $total_orders; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="dashboard-card d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-area fa-3x text-warning"></i>
                            <div class="ms-3 text-end">
                                <p class="mb-2">Doanh thu tuần này</p>
                                <h6 class="mb-0"><?php echo number_format($revenue_this_week, 0, '.', ','); ?>đ</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="dashboard-card d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-pie fa-3x text-warning"></i>
                            <div class="ms-3 text-end">
                                <p class="mb-2">Tổng doanh thu</p>
                                <h6 class="mb-0"><?php echo number_format($total_revenue, 0, '.', ','); ?>đ</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sale & Revenue End -->

            <!-- Sales Chart Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-6">
                        <div class="chart-container text-center rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Top 3 sản phẩm bán chạy tuần này</h6>
                            </div>
                            <?php if (empty($top_products)): ?>
                                <div class="py-4">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Không có dữ liệu</h6>
                                    <p class="text-muted">Chưa có sản phẩm nào được bán trong tuần này</p>
                                </div>
                            <?php else: ?>
                                <canvas id="worldwide-sales"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-12 col-xl-6">
                        <div class="chart-container text-center rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">Đơn hàng và doanh thu theo ngày trong tuần</h6>
                            </div>
                            <canvas id="salse-revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sales Chart End -->

            <!-- Recent Sales Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="table-custom text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Đơn hàng gần đây</h6>
                        <a href="/WEB_MXH/admin/pages/order/order_list/order_list.php" class="btn btn-primary btn-sm" style="color: white !important;">
                            <i class="fas fa-eye me-2"></i>Xem tất cả
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-white">
                                    <th scope="col">Ngày</th>
                                    <th scope="col">Mã đơn</th>
                                    <th scope="col">Khách hàng</th>
                                    <th scope="col">Số tiền</th>
                                    <th scope="col">Trạng thái</th>
                                    <th scope="col">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Chưa có đơn hàng nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                                <small class="text-muted">@<?php echo htmlspecialchars($order['username']); ?></small>
                                            </td>
                                            <td><?php echo number_format($order['final_amount'], 0, '.', ','); ?>đ</td>
                                            <td>
                                                <span class="badge" style="background-color: <?php echo $order['color_code']; ?>">
                                                    <?php echo $order['stage_name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a class="btn btn-sm btn-primary" 
                                                   href="/WEB_MXH/admin/pages/order/order_detail/order_detail.php?id=<?php echo $order['order_id']; ?>" 
                                                   target="_blank" style="color: white !important;">Chi tiết</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Recent Sales End -->

            <!-- Messages Widget Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-md-6 col-xl-12">
                        <div class="messages-widget h-100 rounded p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Tin nhắn gần đây</h6>
                                <a href="/WEB_MXH/admin/pages/customer_support/message/message.php" class="btn btn-primary btn-sm" style="color: white !important;">
                                    <i class="fas fa-comments me-2"></i>Xem tất cả tin nhắn
                                </a>
                            </div>
                            <?php if (empty($recent_messages)): ?>
                                <div class="text-center py-3">
                                    <p class="text-muted">Chưa có tin nhắn nào</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_messages as $index => $message): ?>
                                    <div class="d-flex align-items-center <?php echo $index < count($recent_messages) - 1 ? 'border-bottom' : ''; ?> py-3">
                                        <div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; background: linear-gradient(45deg, #412d3b, #6c4a57); color: white; font-weight: bold;">
                                            <?php 
                                                if ($message['full_name']) {
                                                    $name_parts = explode(' ', trim($message['full_name']));
                                                    echo strtoupper(substr($name_parts[0], 0, 1));
                                                    if (count($name_parts) > 1) {
                                                        echo strtoupper(substr(end($name_parts), 0, 1));
                                                    }
                                                } else {
                                                    echo 'A'; // Admin
                                                }
                                            ?>
                                        </div>
                                        <div class="w-100 ms-3">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-0">
                                                    <?php echo $message['full_name'] ? htmlspecialchars($message['full_name']) : 'Admin'; ?>
                                                    <?php if ($message['is_customer_reply']): ?>
                                                        <small class="badge bg-info">Khách hàng</small>
                                                    <?php else: ?>
                                                        <small class="badge bg-success">Admin</small>
                                                    <?php endif; ?>
                                                </h6>
                                                <small><?php echo date('d/m H:i', strtotime($message['created_at'])); ?></small>
                                            </div>
                                            <span class="text-truncate" style="max-width: 300px; display: block;">
                                                <?php echo htmlspecialchars(substr($message['reply_message'], 0, 50)) . (strlen($message['reply_message']) > 50 ? '...' : ''); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Messages Widget End -->
            
            <!-- Footer Start -->
            <?php include 'footer.php'; ?>
            <!-- Footer End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/lib/chart/chart.min.js"></script>
    <script src="/WEB_MXH/admin/lib/easing/easing.min.js"></script>
    <script src="/WEB_MXH/admin/lib/waypoints/waypoints.min.js"></script>
    <script src="/WEB_MXH/admin/lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="/WEB_MXH/admin/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script>
        // Console debug để kiểm tra dữ liệu
        console.log('Dashboard page loaded successfully');
        console.log('Start of week:', '<?php echo $start_of_week; ?>');
        console.log('End of week:', '<?php echo $end_of_week; ?>');
        console.log('PHP Data loaded:', {
            orders_this_week: <?php echo $orders_this_week; ?>,
            total_orders: <?php echo $total_orders; ?>,
            revenue_this_week: <?php echo $revenue_this_week; ?>,
            total_revenue: <?php echo $total_revenue; ?>
        });
        
        // Dữ liệu cho biểu đồ Top sản phẩm
        const topProductsData = <?php echo json_encode($top_products); ?>;
        const dailyStatsData = <?php echo json_encode($daily_stats); ?>;
        
        console.log('Top Products Data:', topProductsData);
        console.log('Daily Stats Data:', dailyStatsData);
        
        // Kiểm tra jQuery và Chart.js đã load chưa
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        
        // Hide spinner after page loads
        setTimeout(function() {
            const spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.remove('show');
                console.log('Spinner hidden');
            }
        }, 1000);
        
        // Biểu đồ Top 3 sản phẩm bán chạy
        try {
            if (typeof $ !== 'undefined' && typeof Chart !== 'undefined' && topProductsData.length > 0) {
                var ctx1 = $("#worldwide-sales").get(0).getContext("2d");
                var myChart1 = new Chart(ctx1, {
                    type: "doughnut",
                    data: {
                        labels: topProductsData.map(item => item.item_name + ' (' + item.item_type + ')'),
                        datasets: [{
                            backgroundColor: [
                                "rgba(65, 45, 59, .8)",
                                "rgba(108, 74, 87, .8)",
                                "rgba(222, 204, 202, .8)",
                                "rgba(65, 45, 59, .6)",
                                "rgba(108, 74, 87, .6)"
                            ],
                            data: topProductsData.map(item => item.total_quantity)
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            }
                        }
                    }
                });
                console.log('Top products chart created successfully');
            } else {
                console.log('Cannot create top products chart - missing data or libraries');
                document.getElementById('worldwide-sales').style.display = 'none';
            }
        } catch (error) {
            console.error('Error creating top products chart:', error);
        }

        // Biểu đồ đơn hàng và doanh thu theo ngày trong tuần
        try {
            if (typeof $ !== 'undefined' && typeof Chart !== 'undefined' && dailyStatsData.length > 0) {
                var ctx2 = $("#salse-revenue").get(0).getContext("2d");
                var myChart2 = new Chart(ctx2, {
                    type: "line",
                    data: {
                        labels: dailyStatsData.map(item => item.day_vi + ' (' + item.date + ')'),
                        datasets: [
                            {
                                label: "Đơn hàng",
                                data: dailyStatsData.map(item => item.orders),
                                backgroundColor: "rgba(65, 45, 59, .2)",
                                borderColor: "rgba(65, 45, 59, .8)",
                                pointBackgroundColor: "rgba(65, 45, 59, .8)",
                                pointBorderColor: "rgba(65, 45, 59, .8)",
                                fill: true
                            },
                            {
                                label: "Doanh thu (triệu đồng)",
                                data: dailyStatsData.map(item => Math.round(item.revenue / 1000000)),
                                backgroundColor: "rgba(222, 204, 202, .4)",
                                borderColor: "rgba(108, 74, 87, 1)",
                                pointBackgroundColor: "rgba(108, 74, 87, 1)",
                                pointBorderColor: "rgba(108, 74, 87, 1)",
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                console.log('Daily stats chart created successfully');
            } else {
                console.log('Cannot create daily stats chart - missing data or libraries');
                document.getElementById('salse-revenue').style.display = 'none';
            }
        } catch (error) {
            console.error('Error creating daily stats chart:', error);
        }
        
        // Function để reset về tuần hiện tại
        function resetToCurrentWeek() {
            const now = new Date();
            const year = now.getFullYear();
            const week = getWeekNumber(now);
            document.getElementById('week-selector').value = year + '-W' + (week < 10 ? '0' + week : week);
        }
        
        // Function để tính week number
        function getWeekNumber(date) {
            const firstDayOfYear = new Date(date.getFullYear(), 0, 1);
            const pastDaysOfYear = (date - firstDayOfYear) / 86400000;
            return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
        }
        
        // Function xuất Excel với biểu đồ
        function exportDashboardExcel() {
            const weekSelector = document.getElementById('week-selector');
            const selectedWeek = weekSelector ? weekSelector.value : '';
            
            // Hiển thị loading
            const exportBtn = document.querySelector('.export-btn');
            if (exportBtn) {
                const originalContent = exportBtn.innerHTML;
                exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo file...';
                exportBtn.disabled = true;
                
                // Tạo URL với tuần được chọn
                const exportUrl = '/WEB_MXH/admin/pages/dashboard/export_dashboard_excel.php' + 
                                (selectedWeek ? '?week=' + encodeURIComponent(selectedWeek) : '');
                
                // Tạo link download ẩn
                const downloadLink = document.createElement('a');
                downloadLink.href = exportUrl;
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                // Khôi phục button sau 3 giây
                setTimeout(() => {
                    exportBtn.innerHTML = originalContent;
                    exportBtn.disabled = false;
                }, 3000);
                
                // Hiển thị thông báo thành công
                setTimeout(() => {
                    showNotification('success', 'File CSV đã được tạo và tải xuống! (Có thể mở bằng Excel)');
                }, 1000);
            }
        }
        
        // Function hiển thị thông báo
        function showNotification(type, message) {
            // Tạo notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            `;
            
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
    </script>
</body>

</html>