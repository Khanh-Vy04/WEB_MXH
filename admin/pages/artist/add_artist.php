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

$message = '';
$messageType = '';

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
    
    if (empty($image_url)) {
        $errors[] = "URL hình ảnh không được để trống";
    } elseif (strlen($image_url) > 255) {
        $errors[] = "URL hình ảnh không được vượt quá 255 ký tự";
    } elseif (!filter_var($image_url, FILTER_VALIDATE_URL)) {
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
        $sql = "INSERT INTO artists (artist_name, bio, image_url, status) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $artist_name, $bio, $image_url, $status);
        
        if ($stmt->execute()) {
            $message = "Thêm nghệ sĩ thành công!";
            $messageType = "success";
            
            // Reset form
            $artist_name = '';
            $bio = '';
            $image_url = '';
            $status = 1;
        } else {
            $message = "Lỗi khi thêm nghệ sĩ: " . $conn->error;
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Artist</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .form-container {
            background: #F5F5F5;
            border-radius: 10px;
            padding: 30px;
            border: 1px solid #E0E0E0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            background: #FFFFFF;
            border: 1px solid #DDD;
            color: #333;
            border-radius: 5px;
            padding: 12px 15px;
        }
        .form-control:focus {
            background: #FFFFFF;
            border-color: #deccca;
            color: #333;
            box-shadow: 0 0 0 0.2rem rgba(222, 204, 202, 0.25);
        }
        .btn-primary {
            background: #deccca;
            border-color: #deccca;
            color: #412d3b;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #c9b5b2;
            border-color: #c9b5b2;
            color: #412d3b;
            transform: translateY(-1px);
        }
        .btn-primary:active,
        .btn-primary:focus,
        .btn-primary.active {
            background: #c9b5b2 !important;
            border-color: #c9b5b2 !important;
            color: #412d3b !important;
            box-shadow: none !important;
        }
        .btn-secondary {
            background: #deccca;
            border-color: #deccca;
            color: #412d3b;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: #c9b5b2;
            border-color: #c9b5b2;
            color: #412d3b;
            transform: translateY(-1px);
        }
        .btn-secondary:active,
        .btn-secondary:focus,
        .btn-secondary.active {
            background: #c9b5b2 !important;
            border-color: #c9b5b2 !important;
            color: #412d3b !important;
            box-shadow: none !important;
        }
        .alert {
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
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
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #ddd;
            display: none;
        }
        .image-preview-container {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px dashed #ddd;
            margin-bottom: 20px;
            margin-top: 35px;
        }
        .form-check {
            margin-top: 10px;
        }
        .form-check-input {
            background: #FFFFFF;
            border: 2px solid #DDD;
            width: 20px;
            height: 20px;
        }
        .form-check-input:checked {
            background: #deccca;
            border-color: #deccca;
        }
        .form-check-input:checked::before {
            content: "✓";
            color: #412d3b;
            font-weight: bold;
            font-size: 12px;
            position: absolute;
            top: 1px;
            left: 3px;
        }
        .form-check-input:focus {
            border-color: #deccca;
            box-shadow: 0 0 0 0.2rem rgba(222, 204, 202, 0.25);
        }
        .form-check-label {
            color: #333;
            margin-left: 8px;
        }
        .text-white {
            color: #333 !important;
        }
        .text-muted {
            color: #666 !important;
        }
        #noImageText {
            color: #666;
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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="text-white mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>Add New Artist
                                </h4>
                                <a href="artist_list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" id="addArtistForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="artist_name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Artist Name *
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="artist_name" 
                                                   name="artist_name" 
                                                   value="<?php echo isset($artist_name) ? htmlspecialchars($artist_name) : ''; ?>"
                                                   placeholder="Enter the name of the artist..."    
                                                   maxlength="255"
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="bio" class="form-label">
                                                <i class="fas fa-file-alt me-2"></i>Biography *
                                            </label>
                                            <textarea class="form-control" 
                                                      id="bio" 
                                                      name="bio" 
                                                      rows="6"
                                                      placeholder="Enter the biography of the artist..."
                                                      required><?php echo isset($bio) ? htmlspecialchars($bio) : ''; ?></textarea>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="image_url" class="form-label">
                                                <i class="fas fa-image me-2"></i>Image URL *
                                            </label>
                                            <input type="url" 
                                                   class="form-control" 
                                                   id="image_url" 
                                                   name="image_url" 
                                                   value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>"
                                                   placeholder="https://example.com/image.jpg"
                                                   maxlength="255"
                                                   required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="status" 
                                                       name="status" 
                                                       value="1">
                                                <label class="form-check-label" for="status">
                                                    <p>Activate artist</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="image-preview-container">
                                            <h6 class="mb-3" style="color: #333; font-weight: 600;">
                                                <i class="fas fa-eye me-2"></i>Preview Image
                                            </h6>
                                            <img id="imagePreview" 
                                                 class="image-preview" 
                                                 src="" 
                                                 alt="Preview"
                                                 onerror="this.src='https://via.placeholder.com/200x200/dc3545/ffffff?text=Error'">
                                            <div id="noImageText" class="mt-3">
                                                <i class="fas fa-image fa-3x mb-2" style="color: #666;"></i>
                                                <p style="color: #666;">Enter URL to preview</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary me-3">
                                        <i class="fas fa-save me-2"></i>Add Artist
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
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
        // Preview image khi nhập URL
        $('#image_url').on('input', function() {
            const url = $(this).val();
            const preview = $('#imagePreview');
            const noImageText = $('#noImageText');
            
            if (url && isValidUrl(url)) {
                preview.attr('src', url);
                preview.show();
                noImageText.hide();
                
                // Xử lý lỗi load ảnh
                preview.on('error', function() {
                    $(this).hide();
                    noImageText.show();
                });
            } else {
                preview.hide();
                noImageText.show();
            }
        });
        
        // Kiểm tra URL hợp lệ
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Reset form
        $('button[type="reset"]').click(function() {
            $('#imagePreview').hide();
            $('#noImageText').show();
        });
        
        // Validate form trước khi submit
        $('#addArtistForm').submit(function(e) {
            const artistName = $('#artist_name').val().trim();
            const bio = $('#bio').val().trim();
            const imageUrl = $('#image_url').val().trim();
            
            if (!artistName) {
                alert('Vui lòng nhập tên nghệ sĩ');
                e.preventDefault();
                return false;
            }
            
            if (!bio) {
                alert('Vui lòng nhập tiểu sử');
                e.preventDefault();
                return false;
            }
            
            if (!imageUrl || !isValidUrl(imageUrl)) {
                alert('Vui lòng nhập URL hình ảnh hợp lệ');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Load preview nếu có URL sẵn
        $(document).ready(function() {
            const existingUrl = $('#image_url').val();
            if (existingUrl) {
                $('#image_url').trigger('input');
            }
        });
    </script>
</body>
</html> 