<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'genre';

// Kết nối database
$config_path = '../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file");
}

require_once $config_path;

$message = '';
$messageType = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $genre_name = trim($_POST['genre_name']);
    $description = trim($_POST['description']);
    
    // Validate dữ liệu
    $errors = [];
    
    if (empty($genre_name)) {
        $errors[] = "Tên dòng nhạc không được để trống";
    } elseif (strlen($genre_name) > 100) {
        $errors[] = "Tên dòng nhạc không được vượt quá 100 ký tự";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả không được để trống";
    }
    
    // Kiểm tra tên dòng nhạc đã tồn tại chưa
    if (empty($errors)) {
        $check_sql = "SELECT genre_id FROM genres WHERE genre_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $genre_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên dòng nhạc đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, thêm vào database
    if (empty($errors)) {
        $sql = "INSERT INTO genres (genre_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $genre_name, $description);
        
        if ($stmt->execute()) {
            $message = "Thêm dòng nhạc thành công!";
            $messageType = "success";
            
            // Reset form
            $genre_name = '';
            $description = '';
        } else {
            $message = "Lỗi khi thêm dòng nhạc: " . $conn->error;
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
    <title>Add Genre</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .form-container {
            background: #191C24;
            border-radius: 15px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: #fff;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            font-size: 1rem;
        }
        
        .form-control {
            background: #2C3E50;
            border: 2px solid #34495E;
            color: #fff;
            border-radius: 8px;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background: #34495E;
            border-color: #3498DB;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #BDC3C7;
        }
        
        .btn-primary {
            background: #3498DB;
            border-color: #3498DB;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #2980B9;
            border-color: #2980B9;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6C757D;
            border-color: #6C757D;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background: #5A6268;
            border-color: #5A6268;
            transform: translateY(-2px);
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
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
        }
        
        .btn-back {
            background: #6C757D;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5A6268;
            color: white;
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
            color: #E74C3C;
        }
        
        .form-help {
            color: #BDC3C7;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 0 15px;
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
                            <!-- Header -->
                            <div class="page-header">
                                <h2 class="page-title">
                                    <i class="fas fa-plus-circle me-3"></i>Thêm Dòng Nhạc Mới
                                </h2>
                                <a href="genre_list.php" class="btn-back">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>
                            </div>
                            
                            <!-- Alert Messages -->
                            <?php if (!empty($message)): ?>
                                <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Form -->
                            <form method="POST" id="addGenreForm">
                                <div class="form-group">
                                    <label for="genre_name" class="form-label">
                                        <i class="fas fa-music me-2"></i>Tên Dòng Nhạc <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="genre_name" 
                                           name="genre_name" 
                                           value="<?php echo isset($genre_name) ? htmlspecialchars($genre_name) : ''; ?>"
                                           placeholder="Nhập tên dòng nhạc (VD: Rock, Pop, Jazz...)"
                                           maxlength="100"
                                           required>
                                    <div class="form-help">Tối đa 100 ký tự</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Mô Tả <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="6"
                                              placeholder="Nhập mô tả chi tiết về dòng nhạc này..."
                                              required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                    <div class="form-help">Mô tả đặc điểm, phong cách và các thông tin liên quan đến dòng nhạc</div>
                                </div>
                                
                                <div class="form-buttons">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Thêm Dòng Nhạc
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Đặt Lại
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
        // Validate form trước khi submit
        $('#addGenreForm').submit(function(e) {
            const genreName = $('#genre_name').val().trim();
            const description = $('#description').val().trim();
            
            if (!genreName) {
                alert('Vui lòng nhập tên dòng nhạc');
                e.preventDefault();
                return false;
            }
            
            if (genreName.length > 100) {
                alert('Tên dòng nhạc không được vượt quá 100 ký tự');
                e.preventDefault();
                return false;
            }
            
            if (!description) {
                alert('Vui lòng nhập mô tả');
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
    </script>
</body>
</html> 