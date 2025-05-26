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
    
    if (empty($errors)) {
        // Nếu không có display_order, lấy số lớn nhất + 1
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Thêm Banner - Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .form-container {
            background: #191C24;
            border-radius: 10px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .page-title {
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: #BDC3C7;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            background: #2C3E50;
            border: 1px solid #34495E;
            border-radius: 8px;
            padding: 12px 15px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3498DB;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            background: #34495E;
        }
        
        .form-control::placeholder {
            color: #7F8C8D;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3498DB;
        }
        
        .checkbox-container label {
            color: #BDC3C7;
            margin: 0;
            cursor: pointer;
        }
        
        .preview-container {
            background: #2C3E50;
            border: 2px dashed #34495E;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-top: 15px;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .preview-text {
            color: #7F8C8D;
            font-style: italic;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn-submit {
            background: #27AE60;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: #229954;
        }
        
        .btn-cancel {
            background: #95A5A6;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-cancel:hover {
            background: #7F8C8D;
            color: white;
            text-decoration: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }
        
        .alert-error {
            background: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }
        
        .help-text {
            color: #7F8C8D;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .required {
            color: #E74C3C;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 0 15px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn-submit, .btn-cancel {
                width: 100%;
                text-align: center;
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
                        <div class="form-container">
                            <h2 class="page-title">
                                <i class="fas fa-plus-circle me-3"></i>Thêm Banner Mới
                            </h2>
                            
                            <!-- Alert Messages -->
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $messageType; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="bannerForm">
                                <!-- URL Banner -->
                                <div class="form-group">
                                    <label class="form-label">
                                        URL Hình Ảnh <span class="required">*</span>
                                    </label>
                                    <input type="url" 
                                           name="banner_url" 
                                           class="form-control" 
                                           placeholder="https://example.com/banner.jpg"
                                           value="<?php echo htmlspecialchars($banner_url); ?>"
                                           required
                                           id="bannerUrl">
                                    <div class="help-text">
                                        Nhập URL đầy đủ của hình ảnh banner. Khuyến nghị kích thước: 1200x400px
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
                                        Tiêu Đề Banner <span class="required">*</span>
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
                                    <label class="form-label">Mô Tả Banner</label>
                                    <textarea name="banner_description" 
                                              class="form-control" 
                                              placeholder="Nhập mô tả chi tiết về banner..."
                                              rows="4"><?php echo htmlspecialchars($banner_description); ?></textarea>
                                    <div class="help-text">
                                        Mô tả chi tiết về banner, mục đích sử dụng (tùy chọn)
                                    </div>
                                </div>
                                
                                <!-- Thứ tự hiển thị -->
                                <div class="form-group">
                                    <label class="form-label">Thứ Tự Hiển Thị</label>
                                    <input type="number" 
                                           name="display_order" 
                                           class="form-control" 
                                           placeholder="0"
                                           value="<?php echo $display_order; ?>"
                                           min="0"
                                           max="999">
                                    <div class="help-text">
                                        Thứ tự hiển thị banner (số nhỏ hơn sẽ hiển thị trước). Để trống để tự động
                                    </div>
                                </div>
                                
                                <!-- Trạng thái -->
                                <div class="form-group">
                                    <label class="form-label">Trạng Thái</label>
                                    <div class="checkbox-container">
                                        <input type="checkbox" 
                                               name="is_active" 
                                               id="is_active"
                                               <?php echo $is_active ? 'checked' : ''; ?>>
                                        <label for="is_active">Kích hoạt banner ngay sau khi thêm</label>
                                    </div>
                                    <div class="help-text">
                                        Banner chỉ hiển thị trên website khi được kích hoạt
                                    </div>
                                </div>
                                
                                <!-- Buttons -->
                                <div class="button-group">
                                    <button type="submit" class="btn-submit">
                                        <i class="fas fa-save me-2"></i>Thêm Banner
                                    </button>
                                    <a href="banner_list.php" class="btn-cancel">
                                        <i class="fas fa-times me-2"></i>Hủy Bỏ
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
    <script src="/WEB_MXH/admin/pages/dashboard/dashboard.js"></script>
    
    <script>
        // Preview image functionality
        document.getElementById('bannerUrl').addEventListener('input', function() {
            const url = this.value.trim();
            const previewContainer = document.getElementById('previewContainer');
            const previewImage = document.getElementById('previewImage');
            const previewText = document.getElementById('previewText');
            
            if (url && isValidUrl(url)) {
                previewImage.src = url;
                previewImage.style.display = 'block';
                previewText.style.display = 'none';
                
                previewImage.onerror = function() {
                    this.style.display = 'none';
                    previewText.style.display = 'block';
                    previewText.textContent = 'Không thể tải hình ảnh từ URL này';
                    previewText.style.color = '#E74C3C';
                };
                
                previewImage.onload = function() {
                    previewText.style.display = 'none';
                };
            } else {
                previewImage.style.display = 'none';
                previewText.style.display = 'block';
                previewText.textContent = 'Nhập URL để xem trước hình ảnh';
                previewText.style.color = '#7F8C8D';
            }
        });
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Form validation
        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            const url = document.getElementById('bannerUrl').value.trim();
            const title = document.querySelector('input[name="banner_title"]').value.trim();
            
            if (!url) {
                alert('Vui lòng nhập URL banner');
                e.preventDefault();
                return;
            }
            
            if (!isValidUrl(url)) {
                alert('URL banner không hợp lệ');
                e.preventDefault();
                return;
            }
            
            if (!title) {
                alert('Vui lòng nhập tiêu đề banner');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html> 