<?php
// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

$currentPage = 'product';

// Kết nối database
$config_path = '../../../../config/database.php';
if (!file_exists($config_path)) {
    die("Cannot find database config file at: " . $config_path);
}

require_once $config_path;

$message = '';
$messageType = '';

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id == 0) {
    header("Location: product_list.php");
    exit();
}

// Lấy thông tin sản phẩm hiện tại
$product_sql = "SELECT p.*, ap.artist_id 
                FROM products p 
                LEFT JOIN artist_products ap ON p.product_id = ap.product_id 
                WHERE p.product_id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows == 0) {
    header("Location: product_list.php");
    exit();
}

$product = $product_result->fetch_assoc();

// Lấy danh sách artists từ database
$artists_sql = "SELECT artist_id, artist_name FROM artists WHERE status = 1 ORDER BY artist_name";
$artists_result = $conn->query($artists_sql);
$artists = [];
if ($artists_result->num_rows > 0) {
    while($row = $artists_result->fetch_assoc()) {
        $artists[] = $row;
    }
}

// Lấy danh sách genres từ database
$genres_sql = "SELECT genre_id, genre_name FROM genres ORDER BY genre_name";
$genres_result = $conn->query($genres_sql);
$genres = [];
if ($genres_result->num_rows > 0) {
    while($row = $genres_result->fetch_assoc()) {
        $genres[] = $row;
    }
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    // Xử lý price - loại bỏ dấu phẩy và convert thành decimal
    $price_clean = str_replace(',', '', $_POST['price']); // Loại bỏ dấu phẩy
    $price = floatval($price_clean); // Convert thành float
    $stock = intval($_POST['stock']);
    $image_url = trim($_POST['image_url']);
    $genre_id = !empty($_POST['genre_id']) ? intval($_POST['genre_id']) : null;
    $artist_id = !empty($_POST['artist_id']) ? intval($_POST['artist_id']) : null;
    
    // Validate dữ liệu
    $errors = [];
    
    // Debug giá trị price
    $debug_info = "Debug: Input price = '" . $_POST['price'] . "', Clean price = '$price_clean', Final price = '$price'";
    
    if (empty($product_name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    } elseif (strlen($product_name) > 255) {
        $errors[] = "Tên sản phẩm không được vượt quá 255 ký tự";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả không được để trống";
    }
    
    if ($price <= 0 || $price > 99999999) {
        $errors[] = "Giá sản phẩm phải từ 1đ đến 99,999,999đ (Debug: $debug_info)";
    }
    
    if ($stock < 0) {
        $errors[] = "Số lượng tồn kho không được âm";
    }
    
    if (empty($image_url)) {
        $errors[] = "URL hình ảnh không được để trống";
    }
    
    // Kiểm tra tên sản phẩm đã tồn tại chưa (ngoại trừ sản phẩm hiện tại)
    if (empty($errors)) {
        $check_sql = "SELECT product_id FROM products WHERE product_name = ? AND product_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $product_name, $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên sản phẩm đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, cập nhật database
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Debug thông tin trước khi cập nhật
            error_log("Debug - Product ID: $product_id");
            error_log("Debug - Price value: $price");
            error_log("Debug - Price type: " . gettype($price));
            
            // Cập nhật sản phẩm
            $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, image_url = ?, genre_id = ? WHERE product_id = ?";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ssdisii", $product_name, $description, $price, $stock, $image_url, $genre_id, $product_id);
            
            if ($stmt->execute()) {
                // Xóa liên kết artist cũ
                $delete_artist_sql = "DELETE FROM artist_products WHERE product_id = ?";
                $delete_artist_stmt = $conn->prepare($delete_artist_sql);
                $delete_artist_stmt->bind_param("i", $product_id);
                $delete_artist_stmt->execute();
                
                // Nếu có chọn artist mới, thêm vào bảng artist_products
                if ($artist_id) {
                    $artist_sql = "INSERT INTO artist_products (artist_id, product_id) VALUES (?, ?)";
                    $artist_stmt = $conn->prepare($artist_sql);
                    $artist_stmt->bind_param("ii", $artist_id, $product_id);
                    $artist_stmt->execute();
                }
                
                $conn->commit();
                $message = "Cập nhật sản phẩm thành công! (Price saved: $price)";
                $messageType = "success";
                
                // Cập nhật lại thông tin sản phẩm
                $product['product_name'] = $product_name;
                $product['description'] = $description;
                $product['price'] = $price;
                $product['stock'] = $stock;
                $product['image_url'] = $image_url;
                $product['genre_id'] = $genre_id;
                $product['artist_id'] = $artist_id;
                
            } else {
                throw new Exception("Lỗi khi cập nhật sản phẩm: " . $stmt->error . " | MySQL Error: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
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
    <title>Chỉnh Sửa Sản Phẩm - AuraDisc</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
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
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            border: 2px solid #ddd;
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
      <!-- Sidebar Start -->
      <?php include __DIR__.'/../../dashboard/sidebar.php'; ?>
      <!-- Sidebar End -->

      <!-- Content Start -->
      <div class="content">
        <!-- Navbar Start -->
        <?php include __DIR__.'/../../dashboard/navbar.php'; ?>
        <!-- Navbar End -->

        <!-- Form Start -->
        <div class="container-fluid pt-4 px-4">
            <div class="form-container">
                <!-- Header -->
                <div class="page-header">
                    <h2 class="page-title">
                        <i class="fas fa-edit me-3"></i>Chỉnh sửa sản phẩm #<?php echo $product_id; ?>
                    </h2>
                    <a href="product_list.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="product_name" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Tên sản phẩm <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="product_name" 
                                       name="product_name" 
                                       value="<?php echo htmlspecialchars($product['product_name']); ?>"
                                       placeholder="Nhập tên sản phẩm..." 
                                       required>
                                <div class="form-help">Tối đa 255 ký tự</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price" class="form-label">
                                    <i class="fas fa-money-bill-wave me-2"></i>Giá (VNĐ) <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="price" 
                                       name="price" 
                                       value="<?php echo number_format($product['price'], 0, '', ','); ?>"
                                       placeholder="Ví dụ: 800,000" 
                                       pattern="[0-9,]*"
                                       required>
                                <div class="form-help">Đơn vị: VNĐ</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="genre_id" class="form-label">
                                    <i class="fas fa-music me-2"></i>Thể loại
                                </label>
                                <select class="form-select" id="genre_id" name="genre_id">
                                    <option value="">-- Chọn thể loại --</option>
                                    <?php foreach ($genres as $genre): ?>
                                        <option value="<?php echo $genre['genre_id']; ?>" 
                                                <?php echo ($product['genre_id'] == $genre['genre_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($genre['genre_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-help">Chọn thể loại phù hợp</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="artist_id" class="form-label">
                                    <i class="fas fa-user-tie me-2"></i>Nghệ Sĩ
                                </label>
                                <select class="form-select" id="artist_id" name="artist_id">
                                    <option value="">-- Chọn nghệ sĩ --</option>
                                    <?php foreach ($artists as $artist): ?>
                                        <option value="<?php echo $artist['artist_id']; ?>"
                                                <?php echo ($product['artist_id'] == $artist['artist_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($artist['artist_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-help">Chọn nghệ sĩ thực hiện</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stock" class="form-label">
                                    <i class="fas fa-boxes me-2"></i>Số lượng tồn kho <span class="required">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock" 
                                       name="stock" 
                                       value="<?php echo $product['stock']; ?>"
                                       placeholder="Nhập số lượng tồn kho..." 
                                       min="0" 
                                       required>
                                <div class="form-help">Số lượng sản phẩm có sẵn</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="image_url" class="form-label">
                                    <i class="fas fa-image me-2"></i>URL hình ảnh <span class="required">*</span>
                                </label>
                                <input type="url" 
                                       class="form-control" 
                                       id="image_url" 
                                       name="image_url" 
                                       value="<?php echo htmlspecialchars($product['image_url']); ?>"
                                       placeholder="https://example.com/image.jpg" 
                                       required>
                                <div class="form-help">URL hình ảnh sản phẩm</div>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                         alt="Preview" 
                                         class="image-preview" 
                                         id="imagePreview">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Mô tả sản phẩm <span class="required">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="6" 
                                  placeholder="Nhập mô tả chi tiết về sản phẩm..."
                                  required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        <div class="form-help">Mô tả đầy đủ về sản phẩm, bao gồm thông tin về album, ca khúc, v.v.</div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Cập nhật sản phẩm
                        </button>
                        <a href="product_list.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i>Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <!-- Form End -->

        <!-- Footer Start -->
        <?php include __DIR__.'/../../dashboard/footer.php'; ?>
        <!-- Footer End -->
      </div>
      <!-- Content End -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    
    <script>
        // Preview image khi thay đổi URL
        document.getElementById('image_url').addEventListener('input', function() {
            const url = this.value;
            const preview = document.getElementById('imagePreview');
            
            if (url) {
                if (!preview) {
                    const img = document.createElement('img');
                    img.id = 'imagePreview';
                    img.className = 'image-preview';
                    img.alt = 'Preview';
                    this.parentNode.appendChild(img);
                }
                document.getElementById('imagePreview').src = url;
            } else if (preview) {
                preview.remove();
            }
        });

        // Format giá tiền VND với dấu phẩy
        document.getElementById('price').addEventListener('input', function() {
            // Loại bỏ tất cả ký tự không phải số
            let value = this.value.replace(/[^\d]/g, '');
            
            // Nếu có giá trị, format với dấu phẩy
            if (value) {
                // Chuyển thành số và format lại
                let numValue = parseInt(value);
                if (numValue > 99999999) {
                    numValue = 99999999; // Giới hạn tối đa
                }
                this.value = numValue.toLocaleString('vi-VN');
            }
        });

        // Kiểm tra khi submit form
        document.querySelector('form').addEventListener('submit', function(e) {
            const priceInput = document.getElementById('price');
            const priceValue = priceInput.value.replace(/[^\d]/g, '');
            
            if (!priceValue || parseInt(priceValue) <= 0) {
                e.preventDefault();
                alert('Vui lòng nhập giá sản phẩm hợp lệ!');
                priceInput.focus();
                return false;
            }
            
            if (parseInt(priceValue) > 99999999) {
                e.preventDefault();
                alert('Giá sản phẩm không được vượt quá 99,999,999đ!');
                priceInput.focus();
                return false;
            }
        });

        // Hiển thị thông báo thành công
        <?php if ($messageType === 'success'): ?>
        setTimeout(function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
        <?php endif; ?>
    </script>
  </body>
</html> 