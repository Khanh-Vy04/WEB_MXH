<?php
$currentPage = 'voucher';

require_once '../../../config/database.php';
require_once '../../../includes/session.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header("Location: ../../../index.php");
    exit();
}

// Xử lý thêm voucher
if (isset($_POST['add_voucher'])) {
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
    
    // Kiểm tra mã voucher đã tồn tại chưa
    if (empty($errors)) {
        $check_sql = "SELECT voucher_id FROM vouchers WHERE voucher_code = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $voucher_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Mã voucher đã tồn tại";
        }
    }
    
    // Thêm voucher nếu không có lỗi
    if (empty($errors)) {
        $sql = "INSERT INTO vouchers (voucher_code, voucher_name, description, discount_type, discount_value, 
                min_order_amount, max_discount_amount, usage_limit, per_user_limit, start_date, end_date, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssddiisssi", $voucher_code, $voucher_name, $description, $discount_type, 
                         $discount_value, $min_order_amount, $max_discount_amount, $usage_limit, 
                         $per_user_limit, $start_date, $end_date, $is_active);
        
        if ($stmt->execute()) {
            header("Location: voucher_list.php?success=added");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm voucher: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm voucher - AuraDisc Admin</title>
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
                        <h2><i class="fas fa-plus-circle me-2" style="color: #000 !important;"></i><span style="color: #000 !important;">Thêm voucher mới</span></h2>
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

        <div class="row">
            <!-- Form thêm voucher -->
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
                                               value="<?php echo isset($_POST['voucher_code']) ? htmlspecialchars($_POST['voucher_code']) : ''; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                        <small class="text-muted">Mã voucher sẽ được chuyển thành chữ in hoa</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tên voucher <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="voucher_name" 
                                               placeholder="VD: Giảm giá 10% cho khách hàng mới" required 
                                               value="<?php echo isset($_POST['voucher_name']) ? htmlspecialchars($_POST['voucher_name']) : ''; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              placeholder="Mô tả chi tiết về voucher..." style="background-color: #e9ecef !important; color: #000 !important;"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Cấu hình giảm giá -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-percentage me-2"></i>Cấu hình giảm giá</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                        <select class="form-control" name="discount_type" id="discount_type" required style="background-color: #e9ecef !important; color: #000 !important;">
                                            <option value="percentage" <?php echo (isset($_POST['discount_type']) && $_POST['discount_type'] == 'percentage') ? 'selected' : ''; ?>>Phần trăm (%)</option>
                                            <option value="fixed_amount" <?php echo (isset($_POST['discount_type']) && $_POST['discount_type'] == 'fixed_amount') ? 'selected' : ''; ?>>Số tiền cố định (VNĐ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="discount_value" id="discount_value" 
                                               step="0.01" min="0" required 
                                               value="<?php echo isset($_POST['discount_value']) ? $_POST['discount_value'] : ''; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                        <small class="text-muted">Nhập phần trăm (0-100)</small>
                                    </div>
                                    <div class="col-md-4" id="max_discount_section">
                                        <label class="form-label">Giảm tối đa (VNĐ)</label>
                                        <input type="number" class="form-control" name="max_discount_amount" 
                                               step="1000" min="0" placeholder="Không giới hạn" 
                                               value="<?php echo isset($_POST['max_discount_amount']) ? $_POST['max_discount_amount'] : ''; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
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
                                               step="1000" min="0" value="0" 
                                               value="<?php echo isset($_POST['min_order_amount']) ? $_POST['min_order_amount'] : '0'; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn sử dụng tổng</label>
                                        <input type="number" class="form-control" name="usage_limit" 
                                               min="1" placeholder="Không giới hạn" 
                                               value="<?php echo isset($_POST['usage_limit']) ? $_POST['usage_limit'] : ''; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giới hạn per user <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="per_user_limit" 
                                               min="1" value="1" required 
                                               value="<?php echo isset($_POST['per_user_limit']) ? $_POST['per_user_limit'] : '1'; ?>" style="background-color: #e9ecef !important; color: #000 !important;">
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
                                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="end_date" required 
                                               value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d', strtotime('+30 days')); ?>" style="background-color: #e9ecef !important; color: #000 !important;">
                                    </div>
                                </div>
                            </div>

                            <!-- Trạng thái -->
                            <div class="form-section">
                                <h6 class="text-primary mb-3" style="color: #000 !important;"><i class="fas fa-toggle-on me-2"></i>Trạng thái</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                           <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?> style="background-color: #deccca !important; border-color: #deccca !important;">
                                    <label class="form-check-label" for="is_active" style="color: #000 !important;">
                                        Kích hoạt voucher ngay
                                    </label>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" name="add_voucher" class="btn btn-lg" style="background-color: #deccca !important; color: #000 !important;">
                                    <i class="fas fa-save me-2"></i>Thêm voucher
                                </button>
                                <a href="voucher_list.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Preview voucher -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" style="background-color: #412d3b !important;">
                        <h6 class="mb-0" style="color: #fff !important;"><i class="fas fa-eye me-2" style="color: #fff !important;"></i>Xem trước Voucher</h6>
                    </div>
                    <div class="card-body">
                        <div class="preview-voucher" id="voucherPreview">
                            <div class="text-center">
                                <i class="fas fa-ticket-alt fa-3x mb-3"></i>
                                <div class="voucher-code-display" id="previewCode">VOUCHER CODE</div>
                                <h5 id="previewName">Tên voucher</h5>
                                <p id="previewDescription">Mô tả voucher</p>
                                <hr style="border-color: rgba(255,255,255,0.3);">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <strong>Giảm</strong><br>
                                        <span id="previewDiscount">0%</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Đơn tối thiểu</strong><br>
                                        <span id="previewMinOrder">0đ</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6 class="text-primary">Chi tiết:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Loại:</strong> <span id="previewType">Phần trăm</span></li>
                                <li><strong>Sử dụng tối đa:</strong> <span id="previewUsageLimit">Không giới hạn</span></li>
                                <li><strong>Per user:</strong> <span id="previewPerUser">1 lần</span></li>
                                <li><strong>Thời gian:</strong> <span id="previewDuration">-</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        </div> <!-- container-fluid pt-4 px-4 -->
        
        <!-- Footer Start -->
        <?php include __DIR__.'/../dashboard/footer.php'; ?>
        <!-- Footer End -->
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
            document.getElementById('previewDiscount').textContent = discountValue + '%';
            document.getElementById('previewType').textContent = 'Phần trăm';
            if (maxDiscount) {
                document.getElementById('previewDiscount').textContent += ' (Tối đa: ' + parseInt(maxDiscount).toLocaleString() + 'đ)';
            }
        } else {
            document.getElementById('previewDiscount').textContent = parseInt(discountValue).toLocaleString() + 'đ';
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

    // Add event listeners to all form inputs
    document.querySelectorAll('#voucherForm input, #voucherForm select, #voucherForm textarea').forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });

    // Initialize
    updatePreview();
    document.getElementById('discount_type').dispatchEvent(new Event('change'));
</script>
</body>
</html> 