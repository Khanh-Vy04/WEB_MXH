<?php
session_start();
require_once '../../../config/database.php';

// Set current page for sidebar navigation
$currentPage = 'accessory';

$success_message = '';
$error_message = '';
$errors = [];

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
    
    // Check if accessory name already exists
    if (empty($errors)) {
        $check_sql = "SELECT accessory_id FROM accessories WHERE accessory_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('s', $accessory_name);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Tên accessory đã tồn tại";
        }
        $check_stmt->close();
    }
    
    // Insert data if no errors
    if (empty($errors)) {
        $insert_sql = "INSERT INTO accessories (accessory_name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param('ssdis', $accessory_name, $description, $price, $stock, $image_url);
        
        if ($stmt->execute()) {
            $success_message = "Thêm accessory thành công!";
            // Clear form data
            $accessory_name = $description = $price = $stock = $image_url = '';
        } else {
            $error_message = "Lỗi khi thêm accessory: " . $stmt->error;
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
    <title>Thêm phụ kiện mới - AuraDisc Admin</title>
    
    <!-- CSS giống các trang khác -->
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    
    <style>
        .main-content {
            margin-left: 0;
            padding: 20px;
            background: #1a1a1a;
            min-height: 100vh;
            color: #fff;
        }
        
        .content-header {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: white;
        }
        
        .form-container {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 30px;
            border: 1px solid #444;
        }
        
        .form-control {
            background: #3d3d3d;
            border: 1px solid #555;
            color: #fff;
        }
        
        .form-control:focus {
            background: #3d3d3d;
            border-color: #ff6b35;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        }
        
        .form-label {
            color: #fff;
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border: none;
            padding: 12px 30px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #e55a2b, #e08420);
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            padding: 12px 30px;
        }
        
        .alert-success {
            background: #155724;
            border-color: #c3e6cb;
            color: #d4edda;
        }
        
        .alert-danger {
            background: #721c24;
            border-color: #f5c6cb;
            color: #f8d7da;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #444;
        }
        
        .image-preview-container {
            background: #3d3d3d;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px dashed #555;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-text {
            color: #adb5bd;
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
        
        <div class="container-fluid pt-4 px-4">
            <!-- Header -->
            <div class="bg-secondary rounded h-100 p-4 mb-4">
                <h2 class="mb-2"><i class="fas fa-plus me-2"></i>Thêm Phụ Kiện Mới</h2>
                <p class="mb-0">Thêm phụ kiện âm nhạc mới vào hệ thống</p>
            </div>
            
            <!-- Messages -->
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <strong>Lỗi:</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Add Form -->
            <div class="form-container">
                <h4 class="mb-4"><i class="fas fa-headphones"></i> Thông Tin Accessory</h4>
                
                <form method="POST" id="addAccessoryForm">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label for="accessory_name" class="form-label">
                                    Tên Accessory <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="accessory_name" name="accessory_name" 
                                       value="<?php echo htmlspecialchars($accessory_name ?? ''); ?>" 
                                       placeholder="Nhập tên accessory..." required>
                                <div class="form-text">Tên này sẽ hiển thị cho khách hàng</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Mô Tả <span class="required">*</span>
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="5" 
                                          placeholder="Nhập mô tả chi tiết về accessory..." required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                <div class="form-text">Mô tả chi tiết về tính năng và đặc điểm của accessory</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">
                                            Giá ($) <span class="required">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               value="<?php echo htmlspecialchars($price ?? ''); ?>" 
                                               step="0.01" min="0" placeholder="0.00" required>
                                        <div class="form-text">Giá bán của accessory</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">
                                            Số Lượng Tồn Kho <span class="required">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="stock" name="stock" 
                                               value="<?php echo htmlspecialchars($stock ?? ''); ?>" 
                                               min="0" placeholder="0" required>
                                        <div class="form-text">Số lượng hiện có trong kho</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL Hình Ảnh</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       value="<?php echo htmlspecialchars($image_url ?? ''); ?>" 
                                       placeholder="https://example.com/image.jpg">
                                <div class="form-text">URL của hình ảnh accessory (để trống sẽ dùng ảnh mặc định)</div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="image-preview-container">
                                <h6 class="mb-3">Xem Trước Hình Ảnh</h6>
                                <img id="imagePreview" src="https://via.placeholder.com/200x200/ff6b35/ffffff?text=Preview" 
                                     alt="Preview" class="preview-image">
                                <div class="mt-2">
                                    <small class="text-muted">Hình ảnh sẽ hiển thị như thế này</small>
                                </div>
                            </div>
                            
                            <div class="mt-3 p-3" style="background: #3d3d3d; border-radius: 8px;">
                                <h6><i class="fas fa-lightbulb"></i> Gợi Ý</h6>
                                <ul class="small mb-0">
                                    <li>Sử dụng hình ảnh chất lượng cao</li>
                                    <li>Kích thước khuyến nghị: 400x400px</li>
                                    <li>Format: JPG, PNG</li>
                                    <li>Có thể dùng Unsplash.com cho ảnh mẫu</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <hr style="border-color: #444;">
                    
                    <div class="d-flex justify-content-between">
                        <a href="accessory_list.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Thêm Accessory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Image preview functionality
    document.getElementById('image_url').addEventListener('input', function() {
        const imageUrl = this.value;
        const preview = document.getElementById('imagePreview');
        
        if (imageUrl) {
            preview.src = imageUrl;
            preview.onerror = function() {
                this.src = 'https://via.placeholder.com/200x200/dc3545/ffffff?text=Error';
            };
        } else {
            preview.src = 'https://via.placeholder.com/200x200/ff6b35/ffffff?text=Preview';
        }
    });
    
    // Form validation
    document.getElementById('addAccessoryForm').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        const stock = parseInt(document.getElementById('stock').value);
        
        if (price <= 0) {
            e.preventDefault();
            alert('Giá phải lớn hơn 0');
            return;
        }
        
        if (stock < 0) {
            e.preventDefault();
            alert('Số lượng tồn kho không được âm');
            return;
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
    
    // Load initial image if provided
    window.addEventListener('load', function() {
        const imageUrl = document.getElementById('image_url').value;
        if (imageUrl) {
            document.getElementById('imagePreview').src = imageUrl;
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?> 