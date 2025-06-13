<?php
session_start();
require_once '../../../config/database.php';

// Set current page for sidebar navigation
$currentPage = 'accessory';

$success_message = '';
$error_message = '';
$errors = [];
$accessory = null;

// Get accessory ID from URL
$accessory_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($accessory_id <= 0) {
    header('Location: accessory_list.php');
    exit();
}

// Fetch accessory data
$fetch_sql = "SELECT * FROM accessories WHERE accessory_id = ?";
$fetch_stmt = $conn->prepare($fetch_sql);
$fetch_stmt->bind_param('i', $accessory_id);
$fetch_stmt->execute();
$result = $fetch_stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: accessory_list.php');
    exit();
}

$accessory = $result->fetch_assoc();
$fetch_stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form data
    $accessory_name = trim($_POST['accessory_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $image_url = trim($_POST['image_url'] ?? '');
    
    // Validation
    if (empty($accessory_name)) {
        $errors[] = "Tên accessory không được để trống";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả không được để trống";
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Giá phải là số dương";
    }
    
    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors[] = "Số lượng tồn kho phải là số không âm";
    }
    
    if (empty($image_url)) {
        $image_url = 'https://via.placeholder.com/400x400/ff6b35/ffffff?text=No+Image';
    }
    
    // Check if accessory name already exists (excluding current accessory)
    if (empty($errors)) {
        $check_sql = "SELECT accessory_id FROM accessories WHERE accessory_name = ? AND accessory_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('si', $accessory_name, $accessory_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Tên accessory đã tồn tại";
        }
        $check_stmt->close();
    }
    
    // Update data if no errors
    if (empty($errors)) {
        $update_sql = "UPDATE accessories SET accessory_name = ?, description = ?, price = ?, stock = ?, image_url = ? WHERE accessory_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssdisi', $accessory_name, $description, $price, $stock, $image_url, $accessory_id);
        
        if ($stmt->execute()) {
            $success_message = "Cập nhật accessory thành công!";
            // Update accessory array with new data
            $accessory['accessory_name'] = $accessory_name;
            $accessory['description'] = $description;
            $accessory['price'] = $price;
            $accessory['stock'] = $stock;
            $accessory['image_url'] = $image_url;
        } else {
            $error_message = "Lỗi khi cập nhật accessory: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa phụ kiện - AuraDisc Admin</title>
    
    <!-- CSS giống các trang khác -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        .form-container {
            background: #ebecef;
            border-radius: 15px;
            padding: 30px;
            max-width: 95%;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            font-size: 1rem;
        }
        
        .form-control {
            background: #fff;
            border: 2px solid #ddd;
            color: #333;
            border-radius: 8px;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background: #fff;
            border-color: #412d3b;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(65, 45, 59, 0.25);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #888;
        }
        
        .form-select {
            background: #fff;
            border: 2px solid #ddd;
            color: #333;
            border-radius: 8px;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-select:focus {
            background: #fff;
            border-color: #412d3b;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(65, 45, 59, 0.25);
            outline: none;
        }
        
        .form-select option {
            background: #fff;
            color: #333;
        }
        
        .btn-primary {
            background: #412d3b;
            border-color: #412d3b;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #2d1e26;
            border-color: #2d1e26;
            transform: translateY(-2px);
            color: #fff;
        }
        
        .btn-secondary {
            background: #deccca;
            border-color: #deccca;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            color: #412d3b;
        }
        
        .btn-secondary:hover {
            background: #c9b5b0;
            border-color: #c9b5b0;
            transform: translateY(-2px);
            color: #412d3b;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #D4EDDA;
            border-color: #C3E6CB;
            color: #155724;
        }
        
        .alert-danger {
            background: #F8D7DA;
            border-color: #F5C6CB;
            color: #721C24;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            color: #333;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        .btn-back {
            background: #deccca;
            color: #412d3b;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #c9b5b0;
            color: #412d3b;
            text-decoration: none;
            transform: translateY(-2px);
        }
        
        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-help {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #ddd;
        }
        
        .image-preview-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px dashed #ddd;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        
        .info-box h6 {
            color: #412d3b;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .info-box .small {
            color: #666;
        }
        
        .info-box .stock-status {
            font-weight: 500;
        }
        
        .stock-status.out {
            color: #dc3545;
        }
        
        .stock-status.low {
            color: #ffc107;
        }
        
        .stock-status.medium {
            color: #fd7e14;
        }
        
        .stock-status.high {
            color: #28a745;
        }
        
        .row {
            margin-left: -15px;
            margin-right: -15px;
        }
        
        .col-md-6 {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 0 10px;
                max-width: calc(100% - 20px);
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .form-buttons {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                text-align: center;
            }
        }
        
        @media (min-width: 1200px) {
            .form-container {
                max-width: 90%;
            }
        }
    </style>
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php 
    if (file_exists(__DIR__.'/../dashboard/sidebar.php')) {
        include __DIR__.'/../dashboard/sidebar.php'; 
    }
    ?>
    
    <div class="content">
        <?php 
        if (file_exists(__DIR__.'/../dashboard/navbar.php')) {
            include __DIR__.'/../dashboard/navbar.php'; 
        }
        ?>
        
        <div class="container-fluid pt-4 px-2">
            <div class="row g-2">
                <div class="col-12">
                    <div class="form-container">
                        <!-- Header -->
                        <div class="page-header">
                            <h2 class="page-title">
                                <i class="fas fa-edit me-3"></i>Chỉnh sửa accessory
                            </h2>
                            <a href="accessory_list.php" class="btn-back">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                        
                        <!-- Alert Messages -->
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Lỗi:</strong>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Edit Form -->
                        <form method="POST" id="editAccessoryForm">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label for="accessory_name" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Tên accessory <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="accessory_name" 
                                               name="accessory_name" 
                                               value="<?php echo htmlspecialchars($accessory['accessory_name']); ?>" 
                                               placeholder="Nhập tên accessory"
                                               required>
                                        <div class="form-help">Tên này sẽ hiển thị cho khách hàng</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-align-left me-2"></i>Mô tả <span class="required">*</span>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="6"
                                                  placeholder="Nhập mô tả chi tiết về accessory..."
                                                  required><?php echo htmlspecialchars($accessory['description']); ?></textarea>
                                        <div class="form-help">Mô tả đầy đủ về accessory, bao gồm thông tin về tính năng và đặc điểm</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="price" class="form-label">
                                                    <i class="fas fa-dollar-sign me-2"></i>Giá <span class="required">*</span>
                                                </label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="price" 
                                                       name="price" 
                                                       value="<?php echo $accessory['price']; ?>" 
                                                       placeholder="0.00"
                                                       step="0.01"
                                                       min="0"
                                                       required>
                                                <div class="form-help">Đơn vị: VNĐ</div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="stock" class="form-label">
                                                    <i class="fas fa-boxes me-2"></i>Số lượng tồn kho <span class="required">*</span>
                                                </label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="stock" 
                                                       name="stock" 
                                                       value="<?php echo $accessory['stock']; ?>" 
                                                       placeholder="0"
                                                       min="0"
                                                       required>
                                                <div class="form-help">Số lượng accessory có sẵn</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="image_url" class="form-label">
                                            <i class="fas fa-image me-2"></i>URL hình ảnh
                                        </label>
                                        <input type="url" 
                                               class="form-control" 
                                               id="image_url" 
                                               name="image_url" 
                                               value="<?php echo htmlspecialchars($accessory['image_url']); ?>" 
                                               placeholder="https://example.com/image.jpg">
                                        <div class="form-help">URL hình ảnh accessory (để trống sẽ dùng ảnh mặc định)</div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="image-preview-container">
                                        <h6 class="mb-3">Hình Ảnh Hiện Tại</h6>
                                        <img id="imagePreview" 
                                             src="<?php echo htmlspecialchars($accessory['image_url']); ?>" 
                                             alt="Preview" 
                                             class="preview-image"
                                             onerror="this.src='https://via.placeholder.com/200x200/dc3545/ffffff?text=Error'">
                                        <div class="mt-2">
                                            <small class="text-muted">Hình ảnh sẽ hiển thị như thế này</small>
                                        </div>
                                    </div>
                                    
                                    <div class="info-box">
                                        <h6><i class="fas fa-info-circle me-2"></i>Thông tin thêm</h6>
                                        <div class="small">
                                            <p><strong>Được tạo:</strong><br>
                                            <?php echo date('d/m/Y H:i', strtotime($accessory['created_at'])); ?></p>
                                            <p><strong>Cập nhật lần cuối:</strong><br>
                                            <?php echo date('d/m/Y H:i', strtotime($accessory['updated_at'])); ?></p>
                                            <p><strong>Tình trạng tồn kho:</strong><br>
                                            <?php
                                            $stock = $accessory['stock'];
                                            if ($stock == 0) {
                                                echo '<span class="stock-status out">Hết hàng</span>';
                                            } elseif ($stock < 5) {
                                                echo '<span class="stock-status low">Sắp hết</span>';
                                            } elseif ($stock < 15) {
                                                echo '<span class="stock-status medium">Ít</span>';
                                            } else {
                                                echo '<span class="stock-status high">Còn nhiều</span>';
                                            }
                                            ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-box">
                                        <h6><i class="fas fa-lightbulb me-2"></i>Gợi Ý</h6>
                                        <ul class="small mb-0">
                                            <li>Sử dụng hình ảnh chất lượng cao</li>
                                            <li>Kích thước khuyến nghị: 400x400px</li>
                                            <li>Format: JPG, PNG</li>
                                            <li>Có thể dùng Unsplash.com cho ảnh mẫu</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật accessory
                                </button>
                                <a href="accessory_list.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php 
        if (file_exists(__DIR__.'/../dashboard/footer.php')) {
            include __DIR__.'/../dashboard/footer.php'; 
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>

<script>
    // Validate form trước khi submit
    $('#editAccessoryForm').submit(function(e) {
        const accessoryName = $('#accessory_name').val().trim();
        const description = $('#description').val().trim();
        const price = parseFloat($('#price').val());
        const stock = parseInt($('#stock').val());
        
        if (!accessoryName) {
            alert('Vui lòng nhập tên accessory');
            e.preventDefault();
            return false;
        }
        
        if (!description) {
            alert('Vui lòng nhập mô tả');
            e.preventDefault();
            return false;
        }
        
        if (isNaN(price) || price <= 0) {
            alert('Vui lòng nhập giá hợp lệ (lớn hơn 0)');
            e.preventDefault();
            return false;
        }
        
        if (isNaN(stock) || stock < 0) {
            alert('Vui lòng nhập số lượng tồn kho hợp lệ (không âm)');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Auto-resize textarea
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Image preview
    $('#image_url').on('input', function() {
        const url = $(this).val().trim();
        const preview = $('#imagePreview');
        
        if (url) {
            preview.attr('src', url).show();
            preview.on('error', function() {
                $(this).attr('src', 'https://via.placeholder.com/200x200/dc3545/ffffff?text=Error');
            });
        } else {
            preview.attr('src', 'https://via.placeholder.com/200x200/ff6b35/ffffff?text=Preview');
        }
    });
    
    // Format price input
    $('#price').on('input', function() {
        let value = $(this).val();
        if (value && !isNaN(value)) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
</body>
</html>

<?php
$conn->close();
?> 