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

// Lấy ID nghệ sĩ từ URL
$artist_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($artist_id <= 0) {
    echo "<script>alert('ID nghệ sĩ không hợp lệ!'); window.location.href = 'artist_list.php';</script>";
    exit;
}

// Khởi tạo biến thông báo
$success_message = '';
$error_message = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artist_name = trim($_POST['artist_name']);
    $bio = trim($_POST['bio']);
    $image_url = trim($_POST['image_url']);
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;
    
    // Validate dữ liệu
    if (empty($artist_name)) {
        $error_message = 'Tên nghệ sĩ không được để trống!';
    } elseif (strlen($artist_name) > 100) {
        $error_message = 'Tên nghệ sĩ không được quá 100 ký tự!';
    } elseif (empty($bio)) {
        $error_message = 'Tiểu sử không được để trống!';
    } else {
        // Kiểm tra tên nghệ sĩ đã tồn tại (trừ nghệ sĩ hiện tại)
        $check_sql = "SELECT artist_id FROM artists WHERE artist_name = ? AND artist_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $artist_name, $artist_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = 'Tên nghệ sĩ đã tồn tại!';
        } else {
            // Cập nhật thông tin nghệ sĩ
            $update_sql = "UPDATE artists SET artist_name = ?, bio = ?, image_url = ?, status = ? WHERE artist_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssii", $artist_name, $bio, $image_url, $status, $artist_id);
            
            if ($update_stmt->execute()) {
                $success_message = 'Cập nhật thông tin nghệ sĩ thành công!';
            } else {
                $error_message = 'Có lỗi xảy ra khi cập nhật: ' . $conn->error;
            }
        }
    }
}

// Lấy thông tin nghệ sĩ hiện tại
$sql = "SELECT * FROM artists WHERE artist_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artist_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Không tìm thấy nghệ sĩ!'); window.location.href = 'artist_list.php';</script>";
    exit;
}

$artist = $result->fetch_assoc();

// Đếm số sản phẩm của nghệ sĩ
$count_sql = "SELECT COUNT(*) as product_count FROM artist_products WHERE artist_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $artist_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$product_count = $count_result->fetch_assoc()['product_count'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chỉnh Sửa Nghệ Sĩ - AuraDisc</title>
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
        
        /* Status Switch Styles */
        .status-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .status-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #412d3b;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
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
        
        .artist-preview {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px dashed #ddd;
            margin-bottom: 20px;
        }
        
        .artist-preview img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 2px solid #ddd;
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
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa Nghệ sĩ
                        </h2>
                        <p style="color: #444; margin-bottom: 0;">Cập nhật thông tin nghệ sĩ: <?php echo htmlspecialchars($artist['artist_name']); ?></p>
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
                <!-- Form Edit -->
                <div class="col-lg-8">
                    <div class="form-container">
                        <h4 style="color: #222; margin-bottom: 1.5rem; font-weight: 600;">
                            <i class="fas fa-user-edit me-2"></i>Thông tin Nghệ sĩ
                        </h4>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user me-2"></i>Tên Nghệ sĩ *
                                </label>
                                <input type="text" class="form-control" name="artist_name" 
                                       value="<?php echo htmlspecialchars($artist['artist_name']); ?>" 
                                       placeholder="Nhập tên nghệ sĩ..." required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-image me-2"></i>URL Ảnh đại diện
                                </label>
                                <input type="url" class="form-control" name="image_url" 
                                       value="<?php echo htmlspecialchars($artist['image_url']); ?>" 
                                       placeholder="https://example.com/image.jpg">
                                <small class="text-muted">Để trống nếu muốn sử dụng ảnh mặc định</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-book me-2"></i>Tiểu sử *
                                </label>
                                <textarea class="form-control" name="bio" rows="6" 
                                          placeholder="Nhập tiểu sử của nghệ sĩ..." required><?php echo htmlspecialchars($artist['bio']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on me-2"></i>Trạng thái
                                </label>
                                <div class="d-flex align-items-center">
                                    <label class="status-switch">
                                        <input type="hidden" name="status" value="0">
                                        <input type="checkbox" name="status" value="1" <?php echo $artist['status'] == 1 ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="ms-3" style="color: #666;">
                                        <?php echo $artist['status'] == 1 ? 'Hoạt động' : 'Ngừng hoạt động'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật
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
                            <i class="fas fa-eye me-2"></i>Xem trước
                        </h4>
                        
                        <div class="artist-preview">
                            <img src="<?php echo !empty($artist['image_url']) ? htmlspecialchars($artist['image_url']) : 'https://via.placeholder.com/120x120/ddd/999?text=Artist'; ?>" 
                                 alt="Artist Avatar" 
                                 onerror="this.src='https://via.placeholder.com/120x120/ddd/999?text=Artist'">
                            <h5 style="color: #222; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($artist['artist_name']); ?></h5>
                            <span class="badge <?php echo $artist['status'] == 1 ? 'bg-success' : 'bg-secondary'; ?> mb-3">
                                <?php echo $artist['status'] == 1 ? 'Hoạt động' : 'Ngừng hoạt động'; ?>
                            </span>
                            <p style="color: #666; font-size: 0.9rem; line-height: 1.5;">
                                <?php echo htmlspecialchars(substr($artist['bio'], 0, 150)) . (strlen($artist['bio']) > 150 ? '...' : ''); ?>
                            </p>
                        </div>
                        
                        <!-- Thống kê -->
                        <div class="mt-4">
                            <h6 style="color: #222; margin-bottom: 1rem;">Thống kê</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div style="background: #412d3b; padding: 1rem; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: bold; color: #deccca;">
                                            <?php echo $product_count; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #fff; text-transform: uppercase;">
                                            Sản Phẩm
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="background: #deccca; padding: 1rem; border-radius: 8px; text-align: center;">
                                        <div style="font-size: 1.5rem; font-weight: bold; color: #412d3b;">
                                            #<?php echo $artist['artist_id']; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #412d3b; text-transform: uppercase;">
                                            ID
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    
    const previewName = document.querySelector('.artist-preview h5');
    const previewImage = document.querySelector('.artist-preview img');
    const previewBio = document.querySelector('.artist-preview p');
    const previewStatus = document.querySelector('.artist-preview .badge');
    
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
});
</script>
</body>
</html> 