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
        .preview-voucher {
            background: linear-gradient(135deg, #deccca);
            color: #412d3b;
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
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        /* Thêm các style cho header-section để đồng bộ với trang add */
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
        .stat-box {
            min-height: 100px; /* Adjust as needed */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa !important; /* Retain the light gray background */
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
                        <h2><i class="fas fa-edit me-2" style="color: #000 !important;"></i><span style="color: #000 !important;">Chỉnh sửa Voucher</span></h2>
                        <small class="text-muted">ID: <?php echo $voucher_id; ?> | Mã: <?php echo htmlspecialchars($voucher['voucher_code']); ?></small>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="voucher_list.php" class="btn btn-lg" style="background-color: #deccca !important; color: #412d3b !important; border-color: #deccca !important;">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
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
                    <div class="card-header" style="background-color: #412d3b !important;">
                        <h5 class="mb-0" style="color: #fff !important;"><i class="fas fa-edit me-2" style="color: #fff !important;"></i>Thông tin Voucher</h5>
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
                                               value="<?php echo htmlspecialchars($form_data['voucher_code']); ?>" <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?> style="background-color: #e9ecef !important; color: #000 !important;">
                                        <small class="text-muted">Mã voucher sẽ được chuyển thành chữ in hoa</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tên voucher <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="voucher_name" 
                                               placeholder="VD: Giảm giá 10% cho khách hàng mới" required 
                                               value="<?php echo htmlspecialchars($form_data['voucher_name']); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              placeholder="Mô tả chi tiết về voucher..." style="background-color: #e9ecef !important; color: #000 !important;"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                                </div>
                            </div>

                            <!-- Cấu hình giảm giá -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-percentage me-2"></i>Cấu hình giảm giá</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                        <select class="form-control" name="discount_type" id="discount_type" required <?php echo ($stats['total_used'] > 0) ? 'disabled' : ''; ?> style="background-color: #e9ecef !important; color: #000 !important;">
                                            <option value="percentage" <?php echo ($form_data['discount_type'] == 'percentage') ? 'selected' : ''; ?>>Phần trăm (%)</option>
                                            <option value="fixed_amount" <?php echo ($form_data['discount_type'] == 'fixed_amount') ? 'selected' : ''; ?>>Số tiền cố định (VNĐ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="discount_value" id="discount_value" 
                                               step="0.01" min="0" required 
                                               value="<?php echo htmlspecialchars($form_data['discount_value']); ?>" <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?> style="background-color: #e9ecef !important; color: #000 !important;">
                                        <small class="text-muted">Nhập phần trăm (0-100)</small>
                                    </div>
                                    <div class="col-md-4" id="max_discount_section">
                                        <label class="form-label">Giảm tối đa (VNĐ)</label>
                                        <input type="number" class="form-control" name="max_discount_amount" 
                                               step="1000" min="0" placeholder="Không giới hạn" 
                                               value="<?php echo htmlspecialchars($form_data['max_discount_amount']); ?>" <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?> style="background-color: #e9ecef !important; color: #000 !important;">
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
                                               value="<?php echo htmlspecialchars($form_data['min_order_amount']); ?>" <?php echo ($stats['total_used'] > 0) ? 'readonly' : ''; ?> style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn sử dụng tổng</label>
                                        <input type="number" class="form-control" name="usage_limit" 
                                               min="1" placeholder="Không giới hạn" 
                                               value="<?php echo htmlspecialchars($form_data['usage_limit']); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn per user <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="per_user_limit" 
                                               min="1" required 
                                               value="<?php echo htmlspecialchars($form_data['per_user_limit']); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                </div>
                            </div>

                            <!-- Thời gian áp dụng và trạng thái -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i>Thời gian áp dụng & Trạng thái</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" name="start_date" required 
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($form_data['start_date'])); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" name="end_date" required 
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($form_data['end_date'])); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                </div>
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?php echo ($form_data['is_active'] == 1) ? 'checked' : ''; ?> style="background-color: #deccca !important; border-color: #deccca !important;">
                                    <label class="form-check-label" for="is_active" style="color: #000 !important;">Kích hoạt voucher ngay</label>
                                </div>
                            </div>

                            <button type="submit" name="update_voucher" class="btn btn-primary mt-3" style="background-color: #deccca !important; color: #000 !important; border-color: #deccca !important;">
                                <i class="fas fa-sync-alt me-2"></i>Cập nhật Voucher
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Phần xem trước và thống kê voucher -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #412d3b !important;">
                        <h5 class="mb-0" style="color: #fff !important;"><i class="fas fa-eye me-2" style="color: #fff !important;"></i>Xem trước Voucher</h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-voucher">
                            <h4 id="preview_name">Tên Voucher</h4>
                            <div class="voucher-code-display" id="preview_code">MÃ VOUCHER</div>
                            <p id="preview_desc">Mô tả voucher...</p>
                            <hr>
                            <p>Giảm giá: <span id="preview_discount">0%</span></p>
                            <p id="preview_min_order">Đơn tối thiểu: 0đ</p>
                            <p id="preview_dates">Ngày: DD/MM/YYYY - DD/MM/YYYY</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" style="background-color: #412d3b !important;">
                        <h6 class="mb-0" style="color: #fff !important;"><i class="fas fa-eye me-2" style="color: #fff !important;"></i>Thống kê sử dụng</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="p-3 rounded-3 stat-box">
                                    <div class="stats-number" style="color: #000 !important;"><?php echo $stats['total_assigned']; ?></div>
                                    <small style="color: #000 !important;">Đã tạo</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 stat-box">
                                    <div class="stats-number" style="color: #000 !important;"><?php echo $stats['total_used']; ?></div>
                                    <small style="color: #000 !important;">Đã dùng</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 stat-box">
                                    <div class="stats-number" style="color: #000 !important;"><?php echo $stats['total_unused']; ?></div>
                                    <small style="color: #000 !important;">Chưa dùng</small>
                                </div>
                            </div>
                        </div>
                        <?php if ($voucher['usage_limit']): ?>
                            <div class="mt-3 text-center">
                                <small class="text-muted">Tổng giới hạn sử dụng: <?php echo $voucher['usage_limit']; ?></small>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo ($stats['total_used'] / $voucher['usage_limit']) * 100; ?>%;" aria-valuenow="<?php echo ($stats['total_used'] / $voucher['usage_limit']) * 100; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php 
        if (file_exists(__DIR__.'/../dashboard/footer.php')) {
            include __DIR__.'/../dashboard/footer.php'; 
        } else {
            echo "<div>Footer not found</div>";
        }
        ?>
    </div>
    <!-- Content End -->
</div>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/chart/chart.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/easing/easing.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/waypoints/waypoints.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/owlcarousel/owl.carousel.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/moment.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Template Javascript -->
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>

<script>
    // Cập nhật preview
    function updatePreview() {
        $('#preview_name').text($('[name="voucher_name"]').val() || 'Tên Voucher');
        $('#preview_code').text($('[name="voucher_code"]').val().toUpperCase() || 'MÃ VOUCHER');
        $('#preview_desc').text($('[name="description"]').val() || 'Mô tả voucher...');

        var discountType = $('#discount_type').val();
        var discountValue = $('#discount_value').val();
        var maxDiscount = $('[name="max_discount_amount"]').val();

        if (discountType === 'percentage') {
            $('#preview_discount').text(discountValue + '%');
            if (maxDiscount) {
                $('#preview_discount').append('<br><small class="text-muted">Tối đa: ' + new Intl.NumberFormat('vi-VN').format(maxDiscount) + 'đ</small>');
            }
        } else {
            $('#preview_discount').text(new Intl.NumberFormat('vi-VN').format(discountValue) + 'đ');
        }

        var minOrder = $('[name="min_order_amount"]').val();
        if (minOrder > 0) {
            $('#preview_min_order').text('Đơn tối thiểu: ' + new Intl.NumberFormat('vi-VN').format(minOrder) + 'đ');
        } else {
            $('#preview_min_order').text('Không có điều kiện');
        }

        var startDate = $('[name="start_date"]').val();
        var endDate = $('[name="end_date"]').val();

        if (startDate && endDate) {
            var formattedStartDate = new Date(startDate).toLocaleDateString('vi-VN');
            var formattedEndDate = new Date(endDate).toLocaleDateString('vi-VN');
            $('#preview_dates').text('Ngày: ' + formattedStartDate + ' - ' + formattedEndDate);
        } else {
            $('#preview_dates').text('Ngày: DD/MM/YYYY - DD/MM/YYYY');
        }
    }

    // Hiển thị/ẩn Max Discount Amount
    function toggleMaxDiscountField() {
        if ($('#discount_type').val() === 'percentage') {
            $('#max_discount_section').show();
        } else {
            $('#max_discount_section').hide();
        }
    }

    $(document).ready(function() {
        updatePreview();
        toggleMaxDiscountField();

        $('#voucherForm input, #voucherForm textarea, #voucherForm select').on('input change', updatePreview);
        $('#discount_type').on('change', toggleMaxDiscountField);
    });
</script>
</body>
</html> 