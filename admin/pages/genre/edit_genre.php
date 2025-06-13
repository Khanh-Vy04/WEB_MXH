<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'genre';

// Kết nối database
$config_path = '../../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file");
}

require_once $config_path;

$message = '';
$messageType = '';
$genre = null;

// Lấy ID từ URL
$genre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($genre_id <= 0) {
    header('Location: genre_list.php');
    exit;
}

// Lấy thông tin dòng nhạc hiện tại
$sql = "SELECT * FROM genres WHERE genre_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $genre_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: genre_list.php');
    exit;
}

$genre = $result->fetch_assoc();

// Đếm số sản phẩm thuộc dòng nhạc này
$count_sql = "SELECT COUNT(*) as product_count FROM products WHERE genre_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $genre_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$product_count = $count_result->fetch_assoc()['product_count'];

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
    
    // Kiểm tra tên dòng nhạc đã tồn tại chưa (ngoại trừ chính nó)
    if (empty($errors)) {
        $check_sql = "SELECT genre_id FROM genres WHERE genre_name = ? AND genre_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $genre_name, $genre_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên dòng nhạc đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, cập nhật database
    if (empty($errors)) {
        $update_sql = "UPDATE genres SET genre_name = ?, description = ? WHERE genre_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $genre_name, $description, $genre_id);
        
        if ($update_stmt->execute()) {
            $message = "Cập nhật dòng nhạc thành công!";
            $messageType = "success";
            
            // Cập nhật lại thông tin genre
            $genre['genre_name'] = $genre_name;
            $genre['description'] = $description;
        } else {
            $message = "Lỗi khi cập nhật dòng nhạc: " . $conn->error;
            $messageType = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Chỉnh Sửa Dòng Nhạc</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="/WEB_MXH/admin/img/favicon.ico" rel="icon">
    <link href="/WEB_MXH/admin/pages/dashboard/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/WEB_MXH/admin/pages/dashboard/css/style.css" rel="stylesheet">
    <style>
        .edit-container {
            background: #F5F5F5;
            border-radius: 15px;
            padding: 0;
            margin: 0 auto;
            max-width: 1200px;
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

        .main-content {
            padding: 30px;
            background: #FFFFFF;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            margin-top: 20px;
        }

        .form-section {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #E5E5E5;
        }

        .sidebar-section {
            background: #FFFFFF;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #E5E5E5;
            height: fit-content;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #deccca;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
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
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: #deccca;
            box-shadow: 0 0 0 3px rgba(222, 204, 202, 0.1);
            outline: none;
        }

        .form-help {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }

        .form-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
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

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #deccca;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #deccca;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .preview-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border: 2px dashed #deccca;
        }

        .preview-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .preview-content {
            background: white;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        .preview-genre-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #deccca;
            margin-bottom: 10px;
        }

        .preview-description {
            color: #666;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .form-buttons {
                flex-direction: column;
            }
            
            .main-content {
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
                        <div class="edit-container">
                            <!-- Header -->
                            <div class="page-header">
                                <h1 class="page-title">
                                    <i class="fas fa-edit"></i>
                                    Chỉnh sửa dòng nhạc
                                </h1>
                                <a href="genre_list.php" class="btn-back">
                                    <i class="fas fa-arrow-left"></i>
                                    Quay lại
                                </a>
                            </div>
                            
                            <div class="main-content">
                                <!-- Alert Messages -->
                                <?php if (!empty($message)): ?>
                                    <div class="alert <?php echo $messageType == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                        <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="content-grid">
                                    <!-- Form Section -->
                                    <div class="form-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-music"></i>
                                            Thông tin dòng nhạc
                                        </h3>
                                        
                                        <form method="POST" id="editGenreForm">
                                            <div class="form-group">
                                                <label for="genre_name" class="form-label">
                                                    <i class="fas fa-tag"></i>
                                                    Tên dòng nhạc <span class="required">*</span>
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="genre_name" 
                                                       name="genre_name" 
                                                       value="<?php echo htmlspecialchars($genre['genre_name']); ?>"
                                                       placeholder="Nhập tên dòng nhạc (VD: Rock, Pop, Jazz...)"
                                                       maxlength="100"
                                                       required>
                                                <div class="form-help">Tối đa 100 ký tự</div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="description" class="form-label">
                                                    <i class="fas fa-align-left"></i>
                                                    Mô Tả <span class="required">*</span>
                                                </label>
                                                <textarea class="form-control" 
                                                          id="description" 
                                                          name="description" 
                                                          rows="6"
                                                          placeholder="Nhập mô tả chi tiết về dòng nhạc này..."
                                                          required><?php echo htmlspecialchars($genre['description']); ?></textarea>
                                                <div class="form-help">Mô tả đặc điểm, phong cách và thông tin liên quan của dòng nhạc.</div>
                                            </div>
                                            
                                            <div class="form-buttons">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i>
                                                    Cập nhật
                                                </button>
                                                <button type="reset" class="btn btn-secondary">
                                                    <i class="fas fa-undo"></i>
                                                    Đặt lại
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <!-- Preview Section -->
                                        <div class="preview-section">
                                            <div class="preview-title">
                                                <i class="fas fa-eye me-2"></i>Xem trước
                                            </div>
                                            <div class="preview-content">
                                                <div class="preview-genre-name" id="previewGenreName">
                                                    <?php echo htmlspecialchars($genre['genre_name']); ?>
                                                </div>
                                                <div class="preview-description" id="previewDescription">
                                                    <?php echo htmlspecialchars($genre['description']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Sidebar -->
                                    <div class="sidebar-section">
                                        <h3 class="section-title">
                                            <i class="fas fa-info-circle"></i>
                                            Thông tin chi tiết
                                        </h3>
                                        
                                        <div class="info-card">
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-hashtag me-2"></i>ID
                                                </span>
                                                <span class="info-value"><?php echo $genre['genre_id']; ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="info-label">
                                                    <i class="fas fa-music me-2"></i>Số sản phẩm
                                                </span>
                                                <span class="info-value"><?php echo number_format($product_count); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="stats-grid">
                                            <div class="stat-card">
                                                <div class="stat-number"><?php echo $genre['genre_id']; ?></div>
                                                <div class="stat-label">Mã dòng nhạc</div>
                                            </div>
                                            <div class="stat-card">
                                                <div class="stat-number"><?php echo $product_count; ?></div>
                                                <div class="stat-label">Sản phẩm</div>
                                            </div>
                                        </div>
                                        
                                        <!-- Quick Actions -->
                                        <div style="margin-top: 25px;">
                                            <h4 style="font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 15px;">
                                                <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                                            </h4>
                                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                                <a href="genre_list.php" class="btn" style="background: #f8f9fa; color: #333; justify-content: center;">
                                                    <i class="fas fa-list"></i>
                                                    Danh sách dòng nhạc
                                                </a>
                                                <a href="add_genre.php" class="btn" style="background: #28a745; color: white; justify-content: center;">
                                                    <i class="fas fa-plus"></i>
                                                    Thêm dòng nhạc mới
                                                </a>
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

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>

    <script>
        // Real-time preview
        document.getElementById('genre_name').addEventListener('input', function() {
            document.getElementById('previewGenreName').textContent = this.value || 'Tên dòng nhạc...';
        });

        document.getElementById('description').addEventListener('input', function() {
            document.getElementById('previewDescription').textContent = this.value || 'Mô tả dòng nhạc...';
        });

        // Form validation
        document.getElementById('editGenreForm').addEventListener('submit', function(e) {
            const genreName = document.getElementById('genre_name').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if (!genreName) {
                e.preventDefault();
                alert('Vui lòng nhập tên dòng nhạc!');
                document.getElementById('genre_name').focus();
                return;
            }
            
            if (!description) {
                e.preventDefault();
                alert('Vui lòng nhập mô tả!');
                document.getElementById('description').focus();
                return;
            }
            
            if (genreName.length > 100) {
                e.preventDefault();
                alert('Tên dòng nhạc không được vượt quá 100 ký tự!');
                document.getElementById('genre_name').focus();
                return;
            }
        });

        // Character counter for genre name
        const genreNameInput = document.getElementById('genre_name');
        const genreNameHelp = genreNameInput.nextElementSibling;
        
        genreNameInput.addEventListener('input', function() {
            const remaining = 100 - this.value.length;
            genreNameHelp.textContent = `Còn lại ${remaining} ký tự`;
            
            if (remaining < 10) {
                genreNameHelp.style.color = '#dc3545';
            } else if (remaining < 20) {
                genreNameHelp.style.color = '#ffc107';
            } else {
                genreNameHelp.style.color = '#666';
            }
        });
    </script>
</body>
</html>