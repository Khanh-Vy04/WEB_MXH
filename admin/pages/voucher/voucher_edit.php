<?php
$currentPage = 'voucher';

require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header("Location: ../../../index.php");
    exit();
}

// Lấy ID voucher từ URL
$voucher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($voucher_id <= 0) {
    header("Location: voucher_list.php?error=invalid_id");
    exit();
}

// Lấy thông tin voucher hiện tại
$sql = "SELECT * FROM vouchers WHERE voucher_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $voucher_id);
$stmt->execute();
$result = $stmt->get_result();
$voucher = $result->fetch_assoc();

if (!$voucher) {
    header("Location: voucher_list.php?error=not_found");
    exit();
}

// Lấy thống kê sử dụng voucher
$stats_sql = "SELECT 
    COUNT(*) as total_assigned,
    SUM(CASE WHEN is_used = 1 THEN 1 ELSE 0 END) as total_used,
    SUM(CASE WHEN is_used = 0 THEN 1 ELSE 0 END) as total_unused
    FROM user_vouchers WHERE voucher_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $voucher_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();

// Xử lý cập nhật voucher
if (isset($_POST['update_voucher'])) {
    $voucher_code = strtoupper(trim($_POST['voucher_code']));
    $voucher_name = trim($_POST['voucher_name']);
    $description = trim($_POST['description']);
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_order_amount = floatval($_POST['min_order_amount']);
    $max_discount_amount = !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : null;
    $usage_limit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
    $per_user_limit = intval($_POST['per_user_limit']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validate
    $errors = [];
    
    if (empty($voucher_code)) {
        $errors[] = "Mã voucher không được để trống";
    }
    
    if (empty($voucher_name)) {
        $errors[] = "Tên voucher không được để trống";
    }
    
    if ($discount_value <= 0) {
        $errors[] = "Giá trị giảm giá phải lớn hơn 0";
    }
    
    if ($discount_type == 'percentage' && $discount_value > 100) {
        $errors[] = "Phần trăm giảm giá không được vượt quá 100%";
    }
    
    if (strtotime($start_date) >= strtotime($end_date)) {
        $errors[] = "Ngày kết thúc phải sau ngày bắt đầu";
    }
    
    // Kiểm tra mã voucher đã tồn tại chưa (trừ voucher hiện tại)
    if (empty($errors)) {
        $check_sql = "SELECT voucher_id FROM vouchers WHERE voucher_code = ? AND voucher_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $voucher_code, $voucher_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Mã voucher đã tồn tại";
        }
    }
    
    // Kiểm tra nếu có voucher đã sử dụng và thay đổi các thông số quan trọng
    if (empty($errors) && $stats['total_used'] > 0) {
        if ($voucher['discount_type'] != $discount_type || 
            $voucher['discount_value'] != $discount_value ||
            $voucher['min_order_amount'] != $min_order_amount ||
            $voucher['max_discount_amount'] != $max_discount_amount) {
            $errors[] = "Không thể thay đổi thông số giảm giá khi đã có người sử dụng voucher này (" . $stats['total_used'] . " lượt đã sử dụng)";
        }
    }
    
    // Kiểm tra usage_limit không nhỏ hơn số lượng đã sử dụng
    if (empty($errors) && $usage_limit && $usage_limit < $stats['total_used']) {
        $errors[] = "Giới hạn sử dụng tổng không thể nhỏ hơn số lượt đã sử dụng (" . $stats['total_used'] . ")";
    }
    
    // Cập nhật voucher nếu không có lỗi
    if (empty($errors)) {
        $sql = "UPDATE vouchers SET voucher_code = ?, voucher_name = ?, description = ?, discount_type = ?, 
                discount_value = ?, min_order_amount = ?, max_discount_amount = ?, usage_limit = ?, 
                per_user_limit = ?, start_date = ?, end_date = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE voucher_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssddiisssii", $voucher_code, $voucher_name, $description, $discount_type, 
                         $discount_value, $min_order_amount, $max_discount_amount, $usage_limit, 
                         $per_user_limit, $start_date, $end_date, $is_active, $voucher_id);
        
        if ($stmt->execute()) {
            header("Location: voucher_list.php?success=updated");
            exit();
        } else {
            $errors[] = "Lỗi khi cập nhật voucher: " . $conn->error;
        }
    }
}

// Sử dụng dữ liệu từ POST nếu có lỗi, nếu không thì dùng dữ liệu từ database
$form_data = [];
if (!empty($errors)) {
    $form_data = $_POST;
} else {
    $form_data = $voucher;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Voucher - AuraDisc Admin</title>
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .form-section {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .preview-voucher {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .voucher-code-display {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 10px;
            margin: 10px 0;
        }
        .stats-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            margin-bottom: 10px;
        }
        .stats-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
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
            <div class="bg-secondary rounded h-100 p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="text-primary"><i class="fas fa-edit me-3"></i>Chỉnh sửa Voucher</h2>
                        <small class="text-muted">ID: <?php echo $voucher_id; ?> | Mã: <?php echo htmlspecialchars($voucher['voucher_code']); ?></small>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="voucher_list.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại Danh sách
                        </a>
                    </div>
                </div>
            </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Có lỗi xảy ra:</h6>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($stats['total_used'] > 0): ?>
            <div class="warning-box">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                <strong>Lưu ý:</strong> Voucher này đã được sử dụng <?php echo $stats['total_used']; ?> lần. 
                Bạn không thể thay đổi các thông số giảm giá để đảm bảo tính nhất quán với các đơn hàng đã thanh toán.
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Form chỉnh sửa voucher -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Thông tin Voucher</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="voucherForm">
                            <!-- Thông tin cơ bản -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Mã voucher <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="voucher_code" id="voucher_code" 
                                               placeholder="VD: SALE10, WELCOME20" maxlength="50" required 
                                               value="<?php echo htmlspecialchars($form_data['voucher_code']); ?>">
                                        <small class="text-muted">Mã voucher sẽ được chuyển thành chữ in hoa</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tên voucher <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="voucher_name" 
                                               placeholder="VD: Giảm giá 10% cho khách hàng mới" required 
                                               value="<?php echo htmlspecialchars($form_data['voucher_name']); ?>">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              placeholder="Mô tả chi tiết về voucher..."><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                                </div>
                            </div>

                            <!-- Cấu hình giảm giá -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-percentage me-2"></i>Cấu hình giảm giá</h6>
                                <?php if ($stats['total_used'] > 0): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-lock me-2"></i>Các thông số giảm giá không thể thay đổi vì đã có người sử dụng.
                                    </div>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                        <select class="form-control" name="discount_type" id="discount_type" required 
                                                <?php echo ($stats['total_used'] > 0) ? 'disabled' : ''; ?>>
                                            <option value="percentage" <?php echo ($form_data['discount_type'] == 'percentage') ? 'selected' : ''; ?>>Phần trăm (%)</option>
                                            <option value="fixed_amount" <?php echo ($form_data['discount_type'] == 'fixed_amount') ? 'selected' : ''; ?>>Số tiền cố định (VNĐ)</option>
                                        </select>
                                        <?php if ($stats['total_used'] > 0): ?>
                                            <input type="hidden" name="discount_type" value="<?php echo $form_data['discount_type']; ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="discount_value" id="discount_value" 
                                               step="0.01" min="0" required 
                                               value="<?php echo $form_data['discount_value']; ?>"
                                               <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?>>
                                        <small class="text-muted" id="discount_hint">Nhập phần trăm (0-100)</small>
                                    </div>
                                    <div class="col-md-4" id="max_discount_section">
                                        <label class="form-label">Giảm tối đa (VNĐ)</label>
                                        <input type="number" class="form-control" name="max_discount_amount" 
                                               step="1000" min="0" placeholder="Không giới hạn" 
                                               value="<?php echo $form_data['max_discount_amount']; ?>"
                                               <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?>>
                                        <small class="text-muted">Chỉ áp dụng cho giảm theo %</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Điều kiện áp dụng -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-rules me-2"></i>Điều kiện áp dụng</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Đơn hàng tối thiểu (VNĐ)</label>
                                        <input type="number" class="form-control" name="min_order_amount" 
                                               step="1000" min="0" 
                                               value="<?php echo $form_data['min_order_amount']; ?>"
                                               <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn sử dụng tổng</label>
                                        <input type="number" class="form-control" name="usage_limit" 
                                               min="<?php echo $stats['total_used']; ?>" placeholder="Không giới hạn" 
                                               value="<?php echo $form_data['usage_limit']; ?>">
                                        <?php if ($stats['total_used'] > 0): ?>
                                            <small class="text-muted">Tối thiểu: <?php echo $stats['total_used']; ?> (đã sử dụng)</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn per user <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="per_user_limit" 
                                               min="1" required 
                                               value="<?php echo $form_data['per_user_limit']; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Thời gian hiệu lực -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-calendar me-2"></i>Thời gian hiệu lực</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="start_date" required 
                                               value="<?php echo $form_data['start_date']; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="end_date" required 
                                               value="<?php echo $form_data['end_date']; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Trạng thái -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-toggle-on me-2"></i>Trạng thái</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Kích hoạt voucher
                                    </label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="update_voucher" class="btn btn-warning btn-lg">
                                    <i class="fas fa-save me-2"></i>Cập nhật Voucher
                                </button>
                                <a href="voucher_list.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Thống kê và Preview -->
            <div class="col-md-4">
                <!-- Thống kê sử dụng -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê sử dụng</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo $stats['total_assigned']; ?></div>
                                    <div class="text-muted">Đã phát</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo $stats['total_used']; ?></div>
                                    <div class="text-muted">Đã dùng</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo $stats['total_unused']; ?></div>
                                    <div class="text-muted">Chưa dùng</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo $voucher['used_count']; ?></div>
                                    <div class="text-muted">Tổng sử dụng</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong>Lưu ý:</strong> "Đã phát" là số user đã được gán voucher này. 
                                "Tổng sử dụng" bao gồm cả việc 1 user có thể dùng nhiều lần.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Preview voucher -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Xem trước Voucher</h6>
                    </div>
                    <div class="card-body">
                        <div class="preview-voucher" id="voucherPreview">
                            <div class="text-center">
                                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                <div class="voucher-code-display" id="previewCode"><?php echo htmlspecialchars($voucher['voucher_code']); ?></div>
                                <h5 id="previewName"><?php echo htmlspecialchars($voucher['voucher_name']); ?></h5>
                                <p id="previewDescription"><?php echo htmlspecialchars($voucher['description']); ?></p>
                                <hr style="border-color: rgba(255,255,255,0.3);">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <strong>Giảm</strong><br>
                                        <span id="previewDiscount">
                                            <?php 
                                            if ($voucher['discount_type'] == 'percentage') {
                                                echo $voucher['discount_value'] . '%';
                                                if ($voucher['max_discount_amount']) {
                                                    echo '<br><small>(Tối đa: ' . number_format($voucher['max_discount_amount']) . 'đ)</small>';
                                                }
                                            } else {
                                                echo number_format($voucher['discount_value']) . 'đ';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Đơn tối thiểu</strong><br>
                                        <span id="previewMinOrder"><?php echo number_format($voucher['min_order_amount']); ?>đ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6 class="text-primary">Chi tiết:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Loại:</strong> <span id="previewType"><?php echo ($voucher['discount_type'] == 'percentage') ? 'Phần trăm' : 'Số tiền cố định'; ?></span></li>
                                <li><strong>Sử dụng tối đa:</strong> <span id="previewUsageLimit"><?php echo $voucher['usage_limit'] ? $voucher['usage_limit'] . ' lần' : 'Không giới hạn'; ?></span></li>
                                <li><strong>Per user:</strong> <span id="previewPerUser"><?php echo $voucher['per_user_limit']; ?> lần</span></li>
                                <li><strong>Thời gian:</strong> <span id="previewDuration"><?php echo date('d/m/Y', strtotime($voucher['start_date'])) . ' - ' . date('d/m/Y', strtotime($voucher['end_date'])); ?></span></li>
                                <li><strong>Trạng thái:</strong> 
                                    <span class="badge <?php echo $voucher['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $voucher['is_active'] ? 'Đang hoạt động' : 'Tạm dừng'; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        </div> <!-- container-fluid pt-4 px-4 -->
    </div> <!-- content -->
</div> <!-- container-fluid position-relative d-flex p-0 -->

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
    // Preview voucher real-time
    function updatePreview() {
        const code = document.getElementById('voucher_code').value.toUpperCase() || 'VOUCHER CODE';
        const name = document.querySelector('input[name="voucher_name"]').value || 'Tên voucher';
        const description = document.querySelector('textarea[name="description"]').value || 'Mô tả voucher';
        const discountType = document.getElementById('discount_type').value;
        const discountValue = document.getElementById('discount_value').value || '0';
        const minOrder = document.querySelector('input[name="min_order_amount"]').value || '0';
        const maxDiscount = document.querySelector('input[name="max_discount_amount"]').value;
        const usageLimit = document.querySelector('input[name="usage_limit"]').value;
        const perUserLimit = document.querySelector('input[name="per_user_limit"]').value || '1';
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;

        document.getElementById('previewCode').textContent = code;
        document.getElementById('previewName').textContent = name;
        document.getElementById('previewDescription').textContent = description;
        
        if (discountType === 'percentage') {
            let discountText = discountValue + '%';
            if (maxDiscount) {
                discountText += '<br><small>(Tối đa: ' + parseInt(maxDiscount).toLocaleString() + 'đ)</small>';
            }
            document.getElementById('previewDiscount').innerHTML = discountText;
            document.getElementById('previewType').textContent = 'Phần trăm';
        } else {
            document.getElementById('previewDiscount').innerHTML = parseInt(discountValue).toLocaleString() + 'đ';
            document.getElementById('previewType').textContent = 'Số tiền cố định';
        }
        
        document.getElementById('previewMinOrder').textContent = parseInt(minOrder).toLocaleString() + 'đ';
        document.getElementById('previewUsageLimit').textContent = usageLimit ? usageLimit + ' lần' : 'Không giới hạn';
        document.getElementById('previewPerUser').textContent = perUserLimit + ' lần';
        
        if (startDate && endDate) {
            document.getElementById('previewDuration').textContent = 
                new Date(startDate).toLocaleDateString('vi-VN') + ' - ' + 
                new Date(endDate).toLocaleDateString('vi-VN');
        }
    }

    // Event listeners
    document.getElementById('voucher_code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        updatePreview();
    });

    document.getElementById('discount_type').addEventListener('change', function() {
        const maxSection = document.getElementById('max_discount_section');
        const hint = document.getElementById('discount_hint');
        
        if (this.value === 'percentage') {
            maxSection.style.display = 'block';
            hint.textContent = 'Nhập phần trăm (0-100)';
            document.getElementById('discount_value').setAttribute('max', '100');
        } else {
            maxSection.style.display = 'none';
            hint.textContent = 'Nhập số tiền (VNĐ)';
            document.getElementById('discount_value').removeAttribute('max');
        }
        updatePreview();
    });

    // Add event listeners to all form inputs (chỉ những input không bị disabled)
    document.querySelectorAll('#voucherForm input:not([disabled]):not([readonly]), #voucherForm select:not([disabled]), #voucherForm textarea').forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });

    // Initialize
    updatePreview();
    document.getElementById('discount_type').dispatchEvent(new Event('change'));
</script>
</body>
</html> 