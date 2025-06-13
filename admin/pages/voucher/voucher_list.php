<?php
$currentPage = 'voucher';

try {
    require_once '../../../config/database.php';
    require_once '../../../includes/session.php';

    // Kiểm tra quyền admin
    if (!isAdmin()) {
        header("Location: ../../../index.php");
        exit();
    }

    // Kiểm tra bảng vouchers có tồn tại không
    $check_table = $conn->query("SHOW TABLES LIKE 'vouchers'");
    if ($check_table->num_rows == 0) {
        echo "<div class='alert alert-danger'>";
        echo "<h4>Lỗi: Bảng 'vouchers' chưa tồn tại!</h4>";
        echo "<p>Vui lòng chạy file SQL để tạo bảng:</p>";
        echo "<code>database_voucher_system.sql</code>";
        echo "<br><a href='#' onclick='createTable()' class='btn btn-primary mt-2'>Tạo bảng tự động</a>";
        echo "</div>";
        
        // Tạo bảng tự động
        echo "<script>
        function createTable() {
            if (confirm('Bạn có chắc muốn tạo bảng vouchers?')) {
                fetch('create_voucher_table.php')
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        }
        </script>";
    }

    // Xử lý xóa voucher
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $voucher_id = $_GET['delete'];
        $sql = "DELETE FROM vouchers WHERE voucher_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $voucher_id);
            if ($stmt->execute()) {
                $success_msg = "Xóa voucher thành công!";
            } else {
                $error_msg = "Lỗi khi xóa voucher: " . $stmt->error;
            }
        } else {
            $error_msg = "Lỗi prepare statement: " . $conn->error;
        }
    }

    // Xử lý thay đổi trạng thái voucher
    if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
        $voucher_id = $_GET['toggle'];
        $sql = "UPDATE vouchers SET is_active = 1 - is_active WHERE voucher_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $voucher_id);
            if ($stmt->execute()) {
                $success_msg = "Cập nhật trạng thái voucher thành công!";
            } else {
                $error_msg = "Lỗi khi cập nhật trạng thái: " . $stmt->error;
            }
        } else {
            $error_msg = "Lỗi prepare statement: " . $conn->error;
        }
    }

    // Thông báo thành công khi thêm voucher
    if (isset($_GET['success']) && $_GET['success'] == 'added') {
        $success_msg = "Thêm voucher mới thành công!";
    }

    // Lấy danh sách voucher
    $vouchers = [];
    $sql = "SELECT * FROM vouchers ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result === false) {
        $error_msg = "Lỗi query: " . $conn->error;
    } else {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $vouchers[] = $row;
            }
        }
    }

} catch (Exception $e) {
    die("Lỗi: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Voucher - AuraDisc Admin</title>
    
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        .voucher-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .voucher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .badge-percentage {
            background: #412d3b !important;
            color: #fff !important;
        }
        .badge-fixed {
            background: #412d3b !important;
            color: #fff !important;
        }
        .btn-action {
            margin: 2px;
        }
        .expired {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        .inactive {
            opacity: 0.5;
        }
        .badge[style*="background-color: #412d3b"] {
            color: #fff !important;
        }
        .content { background: #f3f4f6 !important; }
        .container-fluid { background: #f7f8f9; }
        .header-section {
            background: #fff;
            border-radius: 18px;
            padding: 1.1rem 1.5rem 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1.5px 4px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php 
    if (file_exists(__DIR__.'/../dashboard/sidebar.php')) {
        include __DIR__.'/../dashboard/sidebar.php'; 
    } else {
        echo "<div>Sidebar not found</div>";
    }
    ?>
    
    <div class="content">
        <?php 
        if (file_exists(__DIR__.'/../dashboard/navbar.php')) {
            include __DIR__.'/../dashboard/navbar.php'; 
        } else {
            echo "<div>Navbar not found</div>";
        }
        ?>
        
        <div class="container-fluid pt-4 px-4">
            <!-- Header -->
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 style="color: #000; font-size: 1.5rem; font-weight: 600; margin-bottom: 0; margin-left: 0.1rem;">
                            <i class="fas fa-ticket-alt me-2" style="color: #000 !important;"></i>Quản lý voucher
                        </h2>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="voucher_add.php" class="btn btn-lg" style="background-color: #deccca !important; color: #412d3b !important; border-color: #deccca !important;">
                            <i class="fas fa-plus fa-sm me-2"></i> Thêm voucher mới
                        </a>
                    </div>
                </div>
            </div>
            
            <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header text-white" style="background-color: #412d3b !important; color: #fff !important;">
                            <h5 class="mb-0" style="color: #fff !important;">
                                <i class="fas fa-list me-2"></i>Danh sách Voucher
                                <span class="badge ms-2" style="background-color: #412d3b !important; color: #fff !important; padding: 5px 10px; border-radius: 5px; border: 1px solid #fff;"><?php echo count($vouchers); ?> voucher</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($vouchers)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-ticket-alt fa-5x text-muted mb-4"></i>
                                    <h4 class="text-muted">Chưa có voucher nào</h4>
                                    <p class="text-muted">Hãy tạo voucher đầu tiên của bạn!</p>
                                    <a href="voucher_add.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Thêm voucher mới
                                    </a>
                                    
                                    <!-- Nút tạo voucher mẫu -->
                                    <button class="btn btn-secondary ms-2" onclick="createSampleVouchers()">
                                        <i class="fas fa-magic me-2"></i>Tạo voucher mẫu
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">ID</th>
                                                <th style="width: 10%;">Mã voucher</th>
                                                <th style="text-align: left !important; width: 20%;">Tên voucher</th>
                                                <th style="width: 10%;">Loại giảm giá</th>
                                                <th style="width: 10%;">Giá trị</th>
                                                <th style="width: 15%;">Điều kiện</th>
                                                <th style="width: 8%;">Sử dụng</th>
                                                <th style="width: 12%;">Thời gian</th>
                                                <th style="width: 10%;">Trạng thái</th>
                                                <th style="width: 10%;">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vouchers as $voucher): 
                                                $is_expired = strtotime($voucher['end_date']) < time();
                                                $row_class = '';
                                                if ($is_expired) $row_class .= ' expired';
                                                if (!$voucher['is_active']) $row_class .= ' inactive';
                                            ?>
                                                <tr class="<?php echo $row_class; ?>">
                                                    <td style="color: #444 !important;">
                                                        <strong>#<?php echo $voucher['voucher_id']; ?></strong>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <span class="badge p-2 rounded-3" style="font-size: 0.9em; background-color: #412d3b !important; color: #fff !important;">
                                                            <?php echo htmlspecialchars($voucher['voucher_code']); ?>
                                                        </span>
                                                    </td>
                                                    <td style="color: #444 !important; text-align: left !important;">
                                                        <strong><?php echo htmlspecialchars($voucher['voucher_name']); ?></strong>
                                                        <?php if ($voucher['description']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($voucher['description'], 0, 50)); ?>...</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <?php if ($voucher['discount_type'] == 'percentage'): ?>
                                                            <span class="badge badge-percentage text-white">
                                                                <i class="fas fa-percentage me-1"></i>Phần trăm
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge badge-fixed text-white">
                                                                <i class="fas fa-dollar-sign me-1"></i>Số tiền
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <?php if ($voucher['discount_type'] == 'percentage'): ?>
                                                            <strong class="text-success"><?php echo $voucher['discount_value']; ?>%</strong>
                                                            <?php if ($voucher['max_discount_amount']): ?>
                                                                <br><small class="text-muted">Tối đa: <?php echo number_format($voucher['max_discount_amount']); ?>đ</small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <strong class="text-success"><?php echo number_format($voucher['discount_value']); ?>đ</strong>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <?php if ($voucher['min_order_amount'] > 0): ?>
                                                            <small style="color: #000 !important;">
                                                                <i class="fas fa-shopping-cart me-1"></i>
                                                                Đơn tối thiểu: <?php echo number_format($voucher['min_order_amount']); ?>đ
                                                            </small>
                                                        <?php else: ?>
                                                            <small class="text-muted">Không có điều kiện</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <div class="text-center">
                                                            <strong><?php echo $voucher['used_count']; ?></strong>
                                                            <?php if ($voucher['usage_limit']): ?>
                                                                / <?php echo $voucher['usage_limit']; ?>
                                                                <div class="progress mt-1" style="height: 4px;">
                                                                    <div class="progress-bar" style="width: <?php echo ($voucher['used_count'] / $voucher['usage_limit']) * 100; ?>%"></div>
                                                                </div>
                                                            <?php else: ?>
                                                                <br><small class="text-muted">Không giới hạn</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <small>
                                                            <strong>Từ:</strong> <?php echo date('d/m/Y', strtotime($voucher['start_date'])); ?><br>
                                                            <strong>Đến:</strong> <?php echo date('d/m/Y', strtotime($voucher['end_date'])); ?>
                                                            <?php if ($is_expired): ?>
                                                                <br><span class="badge bg-danger">Đã hết hạn</span>
                                                            <?php elseif (strtotime($voucher['start_date']) > time()): ?>
                                                                <br><span class="badge bg-warning">Chưa hiệu lực</span>
                                                            <?php else: ?>
                                                                <br><span class="badge bg-success">Đang hiệu lực</span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <?php if ($voucher['is_active']): ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check me-1"></i>Hoạt động
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-times me-1"></i>Tạm dừng
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="color: #444 !important;">
                                                        <div class="btn-group-vertical" role="group">
                                                            <a href="voucher_edit.php?id=<?php echo $voucher['voucher_id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary btn-action">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="?toggle=<?php echo $voucher['voucher_id']; ?>" 
                                                               class="btn btn-sm btn-outline-warning btn-action"
                                                               onclick="return confirm('Bạn có chắc muốn thay đổi trạng thái voucher này?')">
                                                                <i class="fas fa-toggle-<?php echo $voucher['is_active'] ? 'on' : 'off'; ?>"></i>
                                                            </a>
                                                            <a href="?delete=<?php echo $voucher['voucher_id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger btn-action"
                                                               onclick="return confirm('Bạn có chắc muốn xóa voucher này? Hành động này không thể hoàn tác!')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php include __DIR__.'/../dashboard/footer.php'; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/chart/chart.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/easing/easing.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/waypoints/waypoints.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/owlcarousel/owl.carousel.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/moment.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>

<script>
    function createSampleVouchers() {
        if (confirm('Bạn có muốn tạo một số voucher mẫu không?')) {
            fetch('create_sample_vouchers.php')
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            })
            .catch(error => {
                alert('Lỗi: ' + error);
            });
        }
    }
</script>
</body>
</html> 