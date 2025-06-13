<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'banner';

// Kết nối database
$config_path = '../../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file at: " . $config_path);
}

require_once $config_path;

$message = '';
$messageType = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $banner_url = trim($_POST['banner_url']);
    $banner_title = trim($_POST['banner_title']);
    $banner_description = trim($_POST['banner_description']);
    $display_order = (int)$_POST['display_order'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate
    $errors = [];
    
    if (empty($banner_url)) {
        $errors[] = "URL banner là bắt buộc";
    } elseif (!filter_var($banner_url, FILTER_VALIDATE_URL)) {
        $errors[] = "URL banner không hợp lệ";
    }
    
    if (empty($banner_title)) {
        $errors[] = "Tiêu đề banner là bắt buộc";
    }
    
    if ($display_order < 0) {
        $errors[] = "Thứ tự hiển thị phải là số không âm";
    }
    
    // Kiểm tra display_order đã tồn tại chưa (nếu không phải 0)
    if (empty($errors) && $display_order > 0) {
        $check_order_sql = "SELECT banner_id FROM banners WHERE display_order = ?";
        $check_stmt = $conn->prepare($check_order_sql);
        $check_stmt->bind_param("i", $display_order);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Thứ tự hiển thị $display_order đã được sử dụng bởi banner khác";
        }
    }
    
    if (empty($errors)) {
        // Nếu display_order = 0, tự động lấy số lớn nhất + 1
        if ($display_order == 0) {
            $max_order_sql = "SELECT MAX(display_order) as max_order FROM banners";
            $max_result = $conn->query($max_order_sql);
            $max_row = $max_result->fetch_assoc();
            $display_order = ($max_row['max_order'] ?? 0) + 1;
        }
        
        // Insert vào database
        $insert_sql = "INSERT INTO banners (banner_url, banner_title, banner_description, display_order, is_active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("sssii", $banner_url, $banner_title, $banner_description, $display_order, $is_active);
        
        if ($stmt->execute()) {
            $message = "Thêm banner thành công!";
            $messageType = "success";
            
            // Reset form
            $banner_url = '';
            $banner_title = '';
            $banner_description = '';
            $display_order = 0;
            $is_active = 1;
        } else {
            $message = "Lỗi khi thêm banner: " . $conn->error;
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
} else {
    // Giá trị mặc định
    $banner_url = '';
    $banner_title = '';
    $banner_description = '';
    $display_order = 0;
    $is_active = 1;
}

// Lấy danh sách display_order đã sử dụng để hiển thị gợi ý
$used_orders_sql = "SELECT display_order FROM banners WHERE display_order > 0 ORDER BY display_order ASC";
$used_orders_result = $conn->query($used_orders_sql);
$used_orders = [];
while ($row = $used_orders_result->fetch_assoc()) {
    $used_orders[] = $row['display_order'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Thêm Banner Mới</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .banner-container {
            background: #F5F5F5;
            border-radius: 15px;
            padding: 0;
            margin: 0 auto;
            max-width: 800px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .page-header {
            background: linear-gradient(135deg, #deccca 0%, #c9b5b0 100%);
            color: #412d3b;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: #412d3b;
            padding: 10px 20px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: #412d3b;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .form-content {
            padding: 30px;
            background: #FFFFFF;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
            font-size: 1rem;
        }

        .required {
            color: #dc3545;
        }

        .form-control {
            background: #F8F9FA;
            border: 2px solid #E5E5E5;
            border-radius: 8px;
            padding: 12px 15px;
            color: #333;
            font-size: 1rem;
            transition: all 0.3s;
            width: 100%;
            box-sizing: border-box;
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: #deccca;
            box-shadow: 0 0 0 3px rgba(222, 204, 202, 0.1);
            outline: none;
        }

        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            line-height: 1.4;
        }

        .preview-container {
            margin-top: 15px;
            border: 2px dashed #deccca;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-text {
            color: #666;
            font-style: italic;
        }

        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .checkbox-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #deccca;
        }

        .checkbox-container label {
            font-weight: 500;
            color: #333;
            cursor: pointer;
            margin: 0;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E5E5;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #deccca;
            color: #412d3b;
        }

        .btn-primary:hover {
            background: #c9b5b0;
            color: #412d3b;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(65, 45, 59, 0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-1px);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            border: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .order-suggestion {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }

        .order-suggestion h6 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .order-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .order-item {
            background: #bbdefb;
            color: #0d47a1;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .order-available {
            color: #2e7d32;
            font-weight: 500;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .form-buttons {
                flex-direction: column;
            }
            
            .form-content {
                padding: 20px;
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
            
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="banner-container">
                            <!-- Header -->
                            <div class="page-header">
                                <h1 class="page-title">
                                    <i class="fas fa-plus-circle"></i>
                                    Thêm Banner Mới
                                </h1>
                                <a href="banner_list.php" class="btn-back">
                                    <i class="fas fa-arrow-left"></i>
                                    Quay Lại
                                </a>
                            </div>
                            
                            <div class="form-content">
                                <!-- Alert Messages -->
                                <?php if (!empty($message)): ?>
                                    <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-error'; ?>">
                                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                        <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" id="bannerForm">
                                    <!-- URL Banner -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-link me-2"></i>URL hình ảnh <span class="required">*</span>
                                        </label>
                                        <input type="url" 
                                               name="banner_url" 
                                               class="form-control" 
                                               placeholder="https://example.com/banner.jpg"
                                               value="<?php echo htmlspecialchars($banner_url); ?>"
                                               required
                                               id="bannerUrl">
                                        <div class="help-text">
                                            Nhập URL đầy đủ của hình ảnh banner. Kích thước khuyến nghị: 1200x400px
                                        </div>
                                        
                                        <!-- Preview Container -->
                                        <div class="preview-container" id="previewContainer">
                                            <div class="preview-text" id="previewText">
                                                Nhập URL để xem trước hình ảnh
                                            </div>
                                            <img id="previewImage" class="preview-image" style="display: none;">
                                        </div>
                                    </div>
                                    
                                    <!-- Tiêu đề Banner -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-heading me-2"></i>Tiêu đề banner <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               name="banner_title" 
                                               class="form-control" 
                                               placeholder="Nhập tiêu đề banner"
                                               value="<?php echo htmlspecialchars($banner_title); ?>"
                                               required
                                               maxlength="255">
                                        <div class="help-text">
                                            Tiêu đề sẽ được sử dụng để quản lý và có thể hiển thị trên website
                                        </div>
                                    </div>
                                    
                                    <!-- Mô tả Banner -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-align-left me-2"></i>Mô tả banner
                                        </label>
                                        <textarea name="banner_description" 
                                                  class="form-control" 
                                                  rows="4"
                                                  placeholder="Nhập mô tả chi tiết về banner, mục đích sử dụng..."><?php echo htmlspecialchars($banner_description); ?></textarea>
                                        <div class="help-text">
                                            Mô tả chi tiết về banner, mục đích sử dụng (tùy chọn)
                                        </div>
                                    </div>
                                    
                                    <!-- Thứ tự hiển thị -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-sort-numeric-up me-2"></i>Thứ tự hiển thị
                                        </label>
                                        <input type="number" 
                                               name="display_order" 
                                               class="form-control" 
                                               placeholder="0"
                                               value="<?php echo $display_order; ?>"
                                               min="0"
                                               max="999"
                                               id="displayOrder">
                                        <div class="help-text">
                                            Thứ tự hiển thị của banner (số nhỏ hơn sẽ hiển thị trước). Để trống hoặc 0 để tự động
                                        </div>
                                        
                                        <?php if (!empty($used_orders)): ?>
                                        <div class="order-suggestion">
                                            <h6><i class="fas fa-info-circle me-2"></i>Thông tin thứ tự</h6>
                                            <div>
                                                <strong>Đã sử dụng:</strong>
                                                <div class="order-list">
                                                    <?php foreach ($used_orders as $order): ?>
                                                        <span class="order-item"><?php echo $order; ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="order-available">
                                                <i class="fas fa-lightbulb me-1"></i>
                                                Gợi ý: Sử dụng số <?php echo max($used_orders) + 1; ?> cho vị trí cuối hoặc chọn số khác chưa được sử dụng
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Trạng thái -->
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-toggle-on me-2"></i>Trạng thái
                                        </label>
                                        <div class="checkbox-container">
                                            <input type="checkbox" 
                                                   name="is_active" 
                                                   id="is_active"
                                                   <?php echo $is_active ? 'checked' : ''; ?>>
                                            <label for="is_active">Kích hoạt banner ngay sau khi thêm</label>
                                        </div>
                                        <div class="help-text">
                                            Banner chỉ được hiển thị trên website khi ở trạng thái kích hoạt
                                        </div>
                                    </div>
                                    
                                    <!-- Buttons -->
                                    <div class="form-buttons">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i>
                                            Thêm banner
                                        </button>
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo"></i>
                                            Đặt lại
                                        </button>
                                    </div>
                                </form>
                            </div>
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
    <script src="/WEB_MXH/admin/pages/dashboard/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>

    <script>
        // Preview image khi thay đổi URL
        document.getElementById('bannerUrl').addEventListener('input', function() {
            const url = this.value.trim();
            const previewContainer = document.getElementById('previewContainer');
            const previewText = document.getElementById('previewText');
            const previewImage = document.getElementById('previewImage');
            
            if (url && isValidUrl(url)) {
                previewText.style.display = 'none';
                previewImage.src = url;
                previewImage.style.display = 'block';
                previewImage.onload = function() {
                    previewText.style.display = 'none';
                };
                previewImage.onerror = function() {
                    previewText.textContent = 'Không thể tải hình ảnh từ URL này';
                    previewText.style.display = 'block';
                    previewImage.style.display = 'none';
                };
            } else {
                previewText.textContent = url ? 'URL không hợp lệ' : 'Nhập URL để xem trước hình ảnh';
                previewText.style.display = 'block';
                previewImage.style.display = 'none';
            }
        });

        // Check valid URL
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Display order validation
        const usedOrders = <?php echo json_encode($used_orders); ?>;
        document.getElementById('displayOrder').addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value > 0 && usedOrders.includes(value)) {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '#fff5f5';
                
                // Show warning
                let warning = document.getElementById('orderWarning');
                if (!warning) {
                    warning = document.createElement('div');
                    warning.id = 'orderWarning';
                    warning.style.cssText = 'color: #dc3545; font-size: 0.85rem; margin-top: 5px; font-weight: 500;';
                    this.parentNode.insertBefore(warning, this.nextSibling.nextSibling);
                }
                warning.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Thứ tự ' + value + ' đã được sử dụng!';
            } else {
                this.style.borderColor = '#E5E5E5';
                this.style.backgroundColor = '#F8F9FA';
                
                // Remove warning
                const warning = document.getElementById('orderWarning');
                if (warning) {
                    warning.remove();
                }
            }
        });

        // Form validation
        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            const bannerUrl = document.querySelector('input[name="banner_url"]').value.trim();
            const bannerTitle = document.querySelector('input[name="banner_title"]').value.trim();
            const displayOrder = parseInt(document.querySelector('input[name="display_order"]').value) || 0;
            
            if (!bannerUrl) {
                e.preventDefault();
                alert('Vui lòng nhập URL hình ảnh!');
                return;
            }
            
            if (!isValidUrl(bannerUrl)) {
                e.preventDefault();
                alert('URL hình ảnh không hợp lệ!');
                return;
            }
            
            if (!bannerTitle) {
                e.preventDefault();
                alert('Vui lòng nhập tiêu đề banner!');
                return;
            }
            
            if (displayOrder > 0 && usedOrders.includes(displayOrder)) {
                e.preventDefault();
                alert('Thứ tự hiển thị ' + displayOrder + ' đã được sử dụng! Vui lòng chọn số khác.');
                return;
            }
        });
    </script>
</body>
</html> 