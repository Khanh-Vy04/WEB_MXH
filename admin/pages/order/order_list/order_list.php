<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';

$currentPage = 'order';

// Xử lý cập nhật stage và xóa đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_stage') {
        $order_id = (int)$_POST['order_id'];
        $new_stage_id = (int)$_POST['stage_id'];
        
        // Kiểm tra đơn hàng hiện tại có phải đã hủy không
        $check_query = "SELECT stage_id, buyer_id, final_amount, payment_method FROM orders WHERE order_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $order_id);
        $check_stmt->execute();
        $current_order = $check_stmt->get_result()->fetch_assoc();
        
        if (!$current_order) {
            $error_message = "Không tìm thấy đơn hàng #{$order_id}";
        } else if ($current_order['stage_id'] == -1) {
            $error_message = "Đơn hàng #{$order_id} đã bị hủy, không thể sửa đổi!";
        } else {
            // Nếu chuyển sang trạng thái hủy (-1), thực hiện hoàn tiền
            if ($new_stage_id == -1) {
                $conn->begin_transaction();
                try {
                    // Cập nhật trạng thái đơn hàng
                    $update_stmt = $conn->prepare("UPDATE orders SET stage_id = ? WHERE order_id = ?");
                    $update_stmt->bind_param("ii", $new_stage_id, $order_id);
                    
                    if (!$update_stmt->execute()) {
                        throw new Exception("Không thể cập nhật trạng thái đơn hàng");
                    }
                    
                    // Hoàn tiền nếu thanh toán bằng ví hoặc VNPay (đã thanh toán trước)
                    if ($current_order['payment_method'] === 'wallet' || $current_order['payment_method'] === 'vnpay') {
                        $refund_amount = $current_order['final_amount'];
                        $buyer_id = $current_order['buyer_id'];
                        
                        // Cộng tiền vào ví user
                        $refund_query = "UPDATE users SET balance = balance + ? WHERE user_id = ?";
                        $refund_stmt = $conn->prepare($refund_query);
                        $refund_stmt->bind_param("di", $refund_amount, $buyer_id);
                        
                        if (!$refund_stmt->execute()) {
                            throw new Exception("Không thể hoàn tiền vào ví");
                        }
                        
                        $success_message = "Đã hủy đơn hàng #{$order_id} và hoàn tiền " . number_format($refund_amount, 0, '.', ',') . "đ vào ví khách hàng!";
                    } else {
                        $success_message = "Đã hủy đơn hàng #{$order_id} thành công! (COD - không cần hoàn tiền)";
                    }
                    
                    $conn->commit();
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = "Lỗi hủy đơn hàng: " . $e->getMessage();
                }
            } else {
                // Cập nhật bình thường
                $update_stmt = $conn->prepare("UPDATE orders SET stage_id = ? WHERE order_id = ?");
                $update_stmt->bind_param("ii", $new_stage_id, $order_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Cập nhật trạng thái đơn hàng #{$order_id} thành công!";
                } else {
                    $error_message = "Lỗi cập nhật đơn hàng: " . $conn->error;
                }
            }
        }
    }
}

// Lấy tất cả stages để hiển thị dropdown
$stages_query = "SELECT * FROM order_stages WHERE is_active = 1 ORDER BY stage_id";
$stages_result = $conn->query($stages_query);
$stages = [];
while ($stage = $stages_result->fetch_assoc()) {
    $stages[$stage['stage_id']] = $stage;
}

// Xử lý tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rowsPerPage = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
$rowsPerPage = in_array($rowsPerPage, [10, 25, 50, 100]) ? $rowsPerPage : 10;
$currentPageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Xây dựng query với tìm kiếm
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $where_clause .= " AND (o.order_id LIKE ? OR u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param];
    $types = "ssss";
}

// Đếm tổng số đơn hàng
$count_query = "
    SELECT COUNT(*) as total 
    FROM orders o 
    JOIN users u ON o.buyer_id = u.user_id 
    JOIN order_stages os ON o.stage_id = os.stage_id 
    {$where_clause}
";

if ($params) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_result = $count_stmt->get_result();
} else {
    $total_result = $conn->query($count_query);
}

$totalRows = $total_result->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $rowsPerPage);
$currentPageNumber = max(1, min($currentPageNumber, $totalPages));
$offset = ($currentPageNumber - 1) * $rowsPerPage;

// Lấy dữ liệu đơn hàng
$orders_query = "
    SELECT 
        o.order_id,
        o.order_date,
        o.total_amount,
        o.final_amount,
        o.voucher_discount,
        o.stage_id,
        o.payment_method,
        u.user_id,
        u.username,
        u.full_name,
        u.email,
        os.stage_name,
        os.color_code,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.buyer_id = u.user_id
    JOIN order_stages os ON o.stage_id = os.stage_id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    {$where_clause}
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT ? OFFSET ?
";

if ($params) {
    $params[] = $rowsPerPage;
    $params[] = $offset;
    $types .= "ii";
    $stmt = $conn->prepare($orders_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $orders_result = $stmt->get_result();
} else {
    $stmt = $conn->prepare($orders_query);
    $stmt->bind_param("ii", $rowsPerPage, $offset);
    $stmt->execute();
    $orders_result = $stmt->get_result();
}

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
}

// Tạo URL với các tham số hiện tại
function getUrlWithParams($params) {
    $currentParams = $_GET;
    $currentParams = array_merge($currentParams, $params);
    return '?' . http_build_query($currentParams);
}

// Thống kê theo stage
$stats_query = "
    SELECT 
        os.stage_id,
        os.stage_name,
        os.color_code,
        COUNT(o.order_id) as count
    FROM order_stages os
    LEFT JOIN orders o ON os.stage_id = o.stage_id
    WHERE os.is_active = 1
    GROUP BY os.stage_id, os.stage_name, os.color_code
    ORDER BY os.stage_id
";
$stats_result = $conn->query($stats_query);
$stage_stats = [];
while ($stat = $stats_result->fetch_assoc()) {
    $stage_stats[] = $stat;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý đơn hàng - Admin</title>
    <!-- Favicon -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <!-- Bootstrap & FontAwesome -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Main Style -->
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="order_list.css" />
    
    <style>
        .stage-badge {
            padding: 5px 12px;
            border-radius: 20px;
            color: white;
            font-weight: 500;
            font-size: 0.8rem;
            text-align: center;
            min-width: 80px;
            display: inline-block;
        }
        
        .stage-dropdown {
            min-width: 150px;
            border: none;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .stage-dropdown:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,.25);
        }
        
        .order-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .update-btn {
            padding: 4px 12px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .update-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .stats-card {
            border-radius: 15px;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            border: none;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin: 5px;
            background: rgba(255,255,255,0.1);
        }
        
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .amount-text {
            font-weight: 700;
            color: #e74a3b;
        }
        
        .table-custom {
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        
        .table-custom td {
            vertical-align: middle;
            border: none;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-custom tr td:first-child {
            border-radius: 10px 0 0 10px;
        }
        
        .table-custom tr td:last-child {
            border-radius: 0 10px 10px 0;
        }
        
        .alert-custom {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid #e3e6f0;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
    </style>
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Sidebar -->
        <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar -->
            <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
            
            <!-- Page Content -->
            <div class="container-fluid pt-4 px-4">
                <!-- Header -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h3 class="mb-0">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Quản lý đơn hàng
                                </h3>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" onclick="window.location.reload()">
                                        <i class="fas fa-sync-alt me-1"></i> Làm mới
                                    </button>
                                    <button class="btn btn-success" onclick="exportOrders()">
                                        <i class="fas fa-download me-1"></i> Xuất Excel
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Thông báo -->
                            <?php if (isset($success_message)): ?>
                                <div class="alert alert-success alert-custom">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger alert-custom">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Thống kê nhanh -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card stats-card">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-chart-bar me-2"></i>
                                                Thống kê đơn hàng theo trạng thái
                                            </h5>
                                            <div class="row">
                                                <?php foreach ($stage_stats as $stat): ?>
                                                    <div class="col-md-2 col-sm-4 col-6">
                                                        <div class="stat-item">
                                                            <div class="h4 mb-2"><?php echo $stat['count']; ?></div>
                                                            <div class="small"><?php echo $stat['stage_name']; ?></div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filters -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <form method="GET" class="d-flex">
                                        <input type="hidden" name="entries" value="<?php echo $rowsPerPage; ?>">
                                        <input type="text" 
                                               name="search" 
                                               class="form-control search-box me-2" 
                                               placeholder="Tìm kiếm theo ID, tên khách hàng, email..."
                                               value="<?php echo htmlspecialchars($search); ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end">
                                    <form method="GET" class="d-flex align-items-center">
                                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                        <label class="me-2">Hiển thị:</label>
                                        <select name="entries" class="form-select" style="width: auto;" onchange="this.form.submit()">
                                            <option value="10" <?php echo $rowsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $rowsPerPage == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $rowsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                                            <option value="100" <?php echo $rowsPerPage == 100 ? 'selected' : ''; ?>>100</option>
                                        </select>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Orders Table -->
                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead>
                                        <tr style="background: #667eea; color: white;">
                                            <th style="border-radius: 10px 0 0 10px; padding: 15px;">
                                                <i class="fas fa-hashtag me-1"></i> Đơn hàng
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-user me-1"></i> Khách hàng
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-calendar me-1"></i> Ngày đặt
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-box me-1"></i> Sản phẩm
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-credit-card me-1"></i> Thanh toán
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-money-bill me-1"></i> Tổng tiền
                                            </th>
                                            <th style="padding: 15px;">
                                                <i class="fas fa-tasks me-1"></i> Trạng thái
                                            </th>
                                            <th style="border-radius: 0 10px 10px 0; padding: 15px;">
                                                <i class="fas fa-cogs me-1"></i> Thao tác
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($orders)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center" style="border-radius: 10px;">
                                                    <div class="py-4">
                                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                        <h5 class="text-muted">Không có đơn hàng nào</h5>
                                                        <p class="text-muted">Chưa có dữ liệu đơn hàng để hiển thị</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold text-primary">#<?php echo $order['order_id']; ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
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
                                                                <div class="fw-bold"><?php echo htmlspecialchars($order['full_name']); ?></div>
                                                                <div class="text-muted small">@<?php echo htmlspecialchars($order['username']); ?></div>
                                                                <div class="text-muted small"><?php echo htmlspecialchars($order['email']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></div>
                                                        <div class="text-muted small"><?php echo date('H:i', strtotime($order['order_date'])); ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo $order['item_count']; ?> sản phẩm</span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $payment_icons = [
                                                            'wallet' => 'fas fa-wallet',
                                                            'vnpay' => 'fas fa-university', 
                                                            'cash' => 'fas fa-money-bill-wave'
                                                        ];
                                                        $payment_names = [
                                                            'wallet' => 'Ví AuraDisc',
                                                            'vnpay' => 'VNPay',
                                                            'cash' => 'Tiền mặt (COD)'
                                                        ];
                                                        $payment_colors = [
                                                            'wallet' => 'primary',
                                                            'vnpay' => 'info',
                                                            'cash' => 'success'
                                                        ];
                                                        $payment_method = $order['payment_method'] ?? 'cash';
                                                        ?>
                                                        <span class="badge bg-<?php echo $payment_colors[$payment_method]; ?>">
                                                            <i class="<?php echo $payment_icons[$payment_method]; ?> me-1"></i>
                                                            <?php echo $payment_names[$payment_method]; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="amount-text"><?php echo number_format($order['final_amount'], 0, '.', ','); ?>đ</div>
                                                        <?php if ($order['voucher_discount'] > 0): ?>
                                                            <div class="text-muted small">
                                                                Giảm: -<?php echo number_format($order['voucher_discount'], 0, '.', ','); ?>đ
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <form method="POST" style="display: inline;" id="stageForm_<?php echo $order['order_id']; ?>">
                                                            <input type="hidden" name="action" value="update_stage">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                            <select name="stage_id" class="stage-dropdown" 
                                                                    onchange="handleStageChange(this, <?php echo $order['order_id']; ?>, '<?php echo addslashes($order['payment_method']); ?>', <?php echo $order['final_amount']; ?>)"
                                                                    <?php echo $order['stage_id'] == -1 ? 'disabled' : ''; ?>>
                                                                <?php foreach ($stages as $stage_id => $stage): ?>
                                                                    <option value="<?php echo $stage_id; ?>" 
                                                                            <?php echo $order['stage_id'] == $stage_id ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($stage['stage_name']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </form>
                                                        <div class="mt-2">
                                                            <span class="stage-badge" style="background-color: <?php echo $order['color_code']; ?>">
                                                                <?php echo htmlspecialchars($order['stage_name']); ?>
                                                                <?php if ($order['stage_id'] == -1): ?>
                                                                    <i class="fas fa-lock ms-1" title="Đơn hàng đã hủy không thể sửa đổi"></i>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" onclick="viewOrderDetail(<?php echo $order['order_id']; ?>)" title="Xem chi tiết">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted">
                                        Hiển thị <?php echo ($currentPageNumber - 1) * $rowsPerPage + 1; ?> - 
                                        <?php echo min($currentPageNumber * $rowsPerPage, $totalRows); ?> 
                                        trong tổng số <?php echo $totalRows; ?> đơn hàng
                                    </div>
                                    
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination">
                                            <!-- Previous -->
                                            <li class="page-item <?php echo $currentPageNumber <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="<?php echo getUrlWithParams(['page' => $currentPageNumber - 1]); ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                            
                                            <!-- Page Numbers -->
                                            <?php
                                            $start_page = max(1, $currentPageNumber - 2);
                                            $end_page = min($totalPages, $currentPageNumber + 2);
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++):
                                            ?>
                                                <li class="page-item <?php echo $i == $currentPageNumber ? 'active' : ''; ?>">
                                                    <a class="page-link" href="<?php echo getUrlWithParams(['page' => $i]); ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Next -->
                                            <li class="page-item <?php echo $currentPageNumber >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="<?php echo getUrlWithParams(['page' => $currentPageNumber + 1]); ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php include __DIR__.'/../../dashboard/footer.php'; ?>
        </div>
        <!-- Content End -->
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cancelOrderModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Xác nhận hủy đơn hàng
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Cảnh báo!</strong> Hành động này không thể hoàn tác.
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-receipt me-2"></i>Thông tin đơn hàng:</h6>
                            <ul class="list-unstyled ms-3">
                                <li><strong>Mã đơn hàng:</strong> <span id="cancel-order-id"></span></li>
                                <li><strong>Phương thức thanh toán:</strong> <span id="cancel-payment-method"></span></li>
                                <li><strong>Số tiền:</strong> <span id="cancel-amount"></span></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" id="refund-info" style="display: none;">
                        <i class="fas fa-wallet me-2"></i>
                        <strong>Hoàn tiền:</strong> Số tiền <span id="refund-amount"></span> sẽ được hoàn lại vào ví khách hàng.
                    </div>
                    
                    <div class="alert alert-secondary" id="cod-info" style="display: none;">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        <strong>COD:</strong> Đơn hàng thanh toán khi nhận hàng, không cần hoàn tiền.
                    </div>
                    
                    <p class="text-muted">
                        Bạn có chắc chắn muốn hủy đơn hàng này không?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Không, giữ đơn hàng
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">
                        <i class="fas fa-trash-alt me-1"></i> Có, hủy đơn hàng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    
    <script>
        let currentOrderForm = null;
        let currentOrderData = null;
        
        function viewOrderDetail(orderId) {
            // Mở modal hoặc chuyển trang xem chi tiết
            window.open(`/WEB_MXH/admin/pages/order/order_detail/order_detail.php?id=${orderId}`, '_blank');
        }
        
        function handleStageChange(selectElement, orderId, paymentMethod, amount) {
            const newStageId = selectElement.value;
            const form = document.getElementById(`stageForm_${orderId}`);
            
            // Nếu chọn stage hủy đơn hàng (-1), hiển thị popup xác nhận
            if (newStageId == -1) {
                // Reset về giá trị cũ để tránh thay đổi khi chưa xác nhận
                selectElement.value = selectElement.getAttribute('data-original-value') || selectElement.options[0].value;
                
                // Lưu thông tin để sử dụng sau
                currentOrderForm = form;
                currentOrderData = {
                    orderId: orderId,
                    paymentMethod: paymentMethod,
                    amount: amount
                };
                
                // Cập nhật thông tin trong modal
                document.getElementById('cancel-order-id').textContent = '#' + orderId;
                
                const paymentNames = {
                    'wallet': 'Ví AuraDisc',
                    'vnpay': 'VNPay',
                    'cash': 'Tiền mặt (COD)'
                };
                document.getElementById('cancel-payment-method').textContent = paymentNames[paymentMethod] || paymentMethod;
                document.getElementById('cancel-amount').textContent = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
                
                // Hiển thị thông tin hoàn tiền
                if (paymentMethod === 'wallet' || paymentMethod === 'vnpay') {
                    document.getElementById('refund-amount').textContent = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
                    document.getElementById('refund-info').style.display = 'block';
                    document.getElementById('cod-info').style.display = 'none';
                } else {
                    document.getElementById('refund-info').style.display = 'none';
                    document.getElementById('cod-info').style.display = 'block';
                }
                
                // Hiển thị modal
                const modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
                modal.show();
                
            } else {
                // Cập nhật bình thường cho các trạng thái khác
                form.submit();
            }
        }
        
        // Xử lý xác nhận hủy đơn hàng
        document.getElementById('confirmCancelBtn').addEventListener('click', function() {
            if (currentOrderForm && currentOrderData) {
                // Cập nhật select về giá trị -1 và submit form
                const select = currentOrderForm.querySelector('select[name="stage_id"]');
                select.value = -1;
                currentOrderForm.submit();
            }
        });
        
        // Lưu giá trị ban đầu của các select để có thể reset
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.stage-dropdown');
            selects.forEach(select => {
                select.setAttribute('data-original-value', select.value);
            });
        });

        function exportOrders() {
            // Xuất dữ liệu ra Excel
            const search = '<?php echo addslashes($search); ?>';
            window.location.href = `/WEB_MXH/admin/pages/order/export_orders.php?search=${encodeURIComponent(search)}`;
        }
        
        // Auto refresh notification
        <?php if (isset($success_message) || isset($error_message)): ?>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        <?php endif; ?>
        
        // Smooth stage update
        document.querySelectorAll('.stage-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                this.style.background = '#007bff';
                this.style.color = 'white';
                
                setTimeout(() => {
                    this.style.background = '#f8f9fa';
                    this.style.color = 'inherit';
                }, 1000);
            });
        });
        
        // Search form enhancement
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    this.closest('form').submit();
                }
            });
        }
    </script>
</body>
</html> 