<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'artist';

// Kết nối database
$config_path = '../../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file");
}

require_once $config_path;

$success_message = '';
$error_message = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $artist_name = trim($_POST['artist_name']);
    $bio = trim($_POST['bio']);
    $image_url = trim($_POST['image_url']);
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    // Validate dữ liệu
    $errors = [];
    
    if (empty($artist_name)) {
        $errors[] = "Tên nghệ sĩ không được để trống";
    } elseif (strlen($artist_name) > 255) {
        $errors[] = "Tên nghệ sĩ không được vượt quá 255 ký tự";
    }
    
    if (empty($bio)) {
        $errors[] = "Tiểu sử không được để trống";
    }
    
    if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
        $errors[] = "URL hình ảnh không hợp lệ";
    }
    
    // Kiểm tra tên nghệ sĩ đã tồn tại chưa
    if (empty($errors)) {
        $check_sql = "SELECT artist_id FROM artists WHERE artist_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $artist_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên nghệ sĩ đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, thêm vào database
    if (empty($errors)) {
        // Sử dụng ảnh mặc định nếu không có URL
        if (empty($image_url)) {
            $image_url = 'https://via.placeholder.com/120x120/ddd/999?text=Artist';
        }
        
        $sql = "INSERT INTO artists (artist_name, bio, image_url, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $artist_name, $bio, $image_url, $status);
        
        if ($stmt->execute()) {
            $success_message = "Thêm nghệ sĩ thành công!";
            
            // Reset form
            $artist_name = '';
            $bio = '';
            $image_url = '';
            $status = 1;
        } else {
            $error_message = "Lỗi khi thêm nghệ sĩ: " . $conn->error;
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thêm Nghệ Sĩ - AuraDisc</title>
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
            
            <div class="container-fluid pt-4 px-4">
                <!-- Header Section -->
                <div class="header-section" style="background: #fff; border-radius: 18px; padding: 1.1rem 1.5rem 1.2rem 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1.5px 4px rgba(0, 0, 0, 0.04);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 style="color: #222; font-size: 1.5rem; font-weight: 600; margin-bottom: 0.7rem; margin-left: 0.1rem;">
                                <i class="fas fa-plus-circle me-2"></i>Thêm Nghệ Sĩ Mới
                            </h2>
                            <p style="color: #444; margin-bottom: 0;">Thêm thông tin nghệ sĩ mới vào hệ thống</p>
                        </div>
                        <a href="artist_list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay Lại
                        </a>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Form Add -->
                    <div class="col-lg-8">
                        <div class="form-container">
                            <h4 style="color: #222; margin-bottom: 1.5rem; font-weight: 600;">
                                <i class="fas fa-user-plus me-2"></i>Thông Tin Nghệ Sĩ
                            </h4>
                            
                            <form method="POST">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user me-2"></i>Tên Nghệ Sĩ *
                                    </label>
                                    <input type="text" class="form-control" name="artist_name" 
                                           value="<?php echo isset($artist_name) ? htmlspecialchars($artist_name) : ''; ?>" 
                                           placeholder="Nhập tên nghệ sĩ..." required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-image me-2"></i>URL Ảnh Đại Diện
                                    </label>
                                    <input type="url" class="form-control" name="image_url" 
                                           value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>" 
                                           placeholder="https://example.com/image.jpg">
                                    <small class="text-muted">Để trống nếu muốn sử dụng ảnh mặc định</small>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-book me-2"></i>Tiểu Sử *
                                    </label>
                                    <textarea class="form-control" name="bio" rows="6" 
                                              placeholder="Nhập tiểu sử của nghệ sĩ..." required><?php echo isset($bio) ? htmlspecialchars($bio) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-toggle-on me-2"></i>Trạng Thái
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <label class="status-switch">
                                            <input type="hidden" name="status" value="0">
                                            <input type="checkbox" name="status" value="1" <?php echo (!isset($status) || $status == 1) ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="ms-3" style="color: #666;">
                                            Hoạt động
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Thêm Nghệ Sĩ
                                    </button>
                                    <a href="artist_list.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Preview -->
                    <div class="col-lg-4">
                        <div class="form-container">
                            <h4 style="color: #222; margin-bottom: 1.5rem; font-weight: 600;">
                                <i class="fas fa-eye me-2"></i>Xem Trước
                            </h4>
                            
                            <div class="artist-preview">
                                <img src="https://via.placeholder.com/120x120/ddd/999?text=Artist" 
                                     alt="Artist Avatar" id="preview-image">
                                <h5 style="color: #222; margin-bottom: 0.5rem;" id="preview-name">Tên nghệ sĩ</h5>
                                <span class="badge bg-success mb-3" id="preview-status">Hoạt động</span>
                                <p style="color: #666; font-size: 0.9rem; line-height: 1.5;" id="preview-bio">
                                    Tiểu sử nghệ sĩ...
                                </p>
                            </div>
                            
                            <!-- Hướng dẫn -->
                            <div class="mt-4">
                                <h6 style="color: #222; margin-bottom: 1rem;">Hướng Dẫn</h6>
                                <ul style="color: #666; font-size: 0.9rem; line-height: 1.5; padding-left: 1.2rem;">
                                    <li>Tên nghệ sĩ phải là duy nhất trong hệ thống</li>
                                    <li>Tiểu sử nên mô tả ngắn gọn về nghệ sĩ</li>
                                    <li>URL ảnh có thể để trống để sử dụng ảnh mặc định</li>
                                    <li>Trạng thái "Hoạt động" cho phép hiển thị nghệ sĩ</li>
                                </ul>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    <script>
    // Cập nhật preview khi thay đổi form
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.querySelector('input[name="artist_name"]');
        const imageInput = document.querySelector('input[name="image_url"]');
        const bioInput = document.querySelector('textarea[name="bio"]');
        const statusInput = document.querySelector('input[name="status"][type="checkbox"]');
        
        const previewName = document.getElementById('preview-name');
        const previewImage = document.getElementById('preview-image');
        const previewBio = document.getElementById('preview-bio');
        const previewStatus = document.getElementById('preview-status');
        
        function updatePreview() {
            if (nameInput) previewName.textContent = nameInput.value || 'Tên nghệ sĩ';
            if (imageInput) {
                previewImage.src = imageInput.value || 'https://via.placeholder.com/120x120/ddd/999?text=Artist';
            }
            if (bioInput) {
                const bio = bioInput.value.substring(0, 150) + (bioInput.value.length > 150 ? '...' : '');
                previewBio.textContent = bio || 'Tiểu sử nghệ sĩ...';
            }
            if (statusInput && previewStatus) {
                previewStatus.textContent = statusInput.checked ? 'Hoạt động' : 'Ngừng hoạt động';
                previewStatus.className = 'badge ' + (statusInput.checked ? 'bg-success' : 'bg-secondary') + ' mb-3';
            }
        }
        
        // Gán sự kiện
        if (nameInput) nameInput.addEventListener('input', updatePreview);
        if (imageInput) imageInput.addEventListener('input', updatePreview);
        if (bioInput) bioInput.addEventListener('input', updatePreview);
        if (statusInput) statusInput.addEventListener('change', updatePreview);
        
        // Xử lý switch status
        const statusText = document.querySelector('.status-switch + span');
        if (statusInput && statusText) {
            statusInput.addEventListener('change', function() {
                statusText.textContent = this.checked ? 'Hoạt động' : 'Ngừng hoạt động';
            });
        }
        
        // Khởi tạo preview với dữ liệu hiện tại
        updatePreview();
    });
    </script>
</body>
</html> 