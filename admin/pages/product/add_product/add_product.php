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
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $image_url = trim($_POST['image_url']);
    $genre_id = !empty($_POST['genre_id']) ? intval($_POST['genre_id']) : null;
    $artist_id = !empty($_POST['artist_id']) ? intval($_POST['artist_id']) : null;
    
    // Validate dữ liệu
    $errors = [];
    
    if (empty($product_name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    } elseif (strlen($product_name) > 255) {
        $errors[] = "Tên sản phẩm không được vượt quá 255 ký tự";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả không được để trống";
    }
    
    if ($price <= 0) {
        $errors[] = "Giá sản phẩm phải lớn hơn 0";
    }
    
    if ($stock < 0) {
        $errors[] = "Số lượng tồn kho không được âm";
    }
    
    if (empty($image_url)) {
        $errors[] = "URL hình ảnh không được để trống";
    }
    
    // Kiểm tra tên sản phẩm đã tồn tại chưa
    if (empty($errors)) {
        $check_sql = "SELECT product_id FROM products WHERE product_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $product_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = "Tên sản phẩm đã tồn tại";
        }
    }
    
    // Nếu không có lỗi, thêm vào database
    if (empty($errors)) {
        $conn->begin_transaction();
        
        try {
            // Thêm sản phẩm
            $sql = "INSERT INTO products (product_name, description, price, stock, image_url, genre_id) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdisi", $product_name, $description, $price, $stock, $image_url, $genre_id);
            
            if ($stmt->execute()) {
                $product_id = $conn->insert_id;
                
                // Nếu có chọn artist, thêm vào bảng artist_products
                if ($artist_id) {
                    $artist_sql = "INSERT INTO artist_products (artist_id, product_id) VALUES (?, ?)";
                    $artist_stmt = $conn->prepare($artist_sql);
                    $artist_stmt->bind_param("ii", $artist_id, $product_id);
                    $artist_stmt->execute();
                }
                
                $conn->commit();
                $message = "Thêm sản phẩm thành công!";
                $messageType = "success";
                
                // Reset form
                $product_name = '';
                $description = '';
                $price = '';
                $stock = '';
                $image_url = '';
                $genre_id = '';
                $artist_id = '';
            } else {
                throw new Exception("Lỗi khi thêm sản phẩm: " . $conn->error);
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
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Thêm Sản Phẩm Mới</title>
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
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
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
      <?php
        if (file_exists(__DIR__.'/../../dashboard/sidebar.php')) {
        include __DIR__.'/../../dashboard/sidebar.php';
        }
      ?>
        
      <div class="content">
            <?php 
            if (file_exists(__DIR__.'/../../dashboard/navbar.php')) {
                include __DIR__.'/../../dashboard/navbar.php'; 
            }
            ?>
            
            <div class="container-fluid pt-4 px-2">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="form-container">
                            <!-- Header -->
                            <div class="page-header">
                                <h2 class="page-title">
                                    <i class="fas fa-plus-circle me-3"></i>Thêm sản phẩm mới
                                </h2>
                                <a href="../product_list/product_list.php" class="btn-back">
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
                            <form method="POST" id="addProductForm">
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
                                                   value="<?php echo isset($product_name) ? htmlspecialchars($product_name) : ''; ?>"
                                                   placeholder="Nhập tên sản phẩm"
                                                   maxlength="255"
                                                   required>
                                            <div class="form-help">Tối đa 255 ký tự</div>
              </div>
            </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price" class="form-label">
                                                <i class="fas fa-dollar-sign me-2"></i>Giá <span class="required">*</span>
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="price" 
                                                   name="price" 
                                                   value="<?php echo isset($price) ? $price : ''; ?>"
                                                   placeholder="0.00"
                                                   step="0.01"
                                                   min="0"
                                                   required>
                                            <div class="form-help">Đơn vị: VNĐ</div>
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
                                                   value="<?php echo isset($stock) ? $stock : ''; ?>"
                                                   placeholder="0"
                                                   min="0"
                                                   required>
                                            <div class="form-help">Số lượng sản phẩm có sẵn</div>
              </div>
            </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="genre_id" class="form-label">
                                                <i class="fas fa-music me-2"></i>Dòng nhạc
                                            </label>
                                            <select class="form-select" id="genre_id" name="genre_id">
                                                <option value="">-- Chọn dòng nhạc --</option>
                                                <?php foreach ($genres as $genre): ?>
                                                <option value="<?php echo $genre['genre_id']; ?>" 
                                                        <?php echo (isset($genre_id) && $genre_id == $genre['genre_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($genre['genre_name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-help">Chọn dòng nhạc phù hợp</div>
                  </div>
                </div>
              </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
              <div class="form-group">
                                            <label for="artist_id" class="form-label">
                                                <i class="fas fa-user-music me-2"></i>Nghệ Sĩ
                </label>
                                            <select class="form-select" id="artist_id" name="artist_id">
                                                <option value="">-- Chọn nghệ sĩ --</option>
                                                <?php foreach ($artists as $artist): ?>
                                                <option value="<?php echo $artist['artist_id']; ?>" 
                                                        <?php echo (isset($artist_id) && $artist_id == $artist['artist_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($artist['artist_name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-help">Chọn nghệ sĩ thực hiện</div>
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
                                                   value="<?php echo isset($image_url) ? htmlspecialchars($image_url) : ''; ?>"
                                                   placeholder="https://example.com/image.jpg"
                                                   required>
                                            <div class="form-help">URL hình ảnh sản phẩm</div>
                                            <img id="image_preview" class="image-preview" alt="Preview">
                  </div>
                </div>
              </div>
                                
              <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Mô tả <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="6"
                                              placeholder="Nhập mô tả chi tiết về sản phẩm..."
                                              required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                                    <div class="form-help">Mô tả đầy đủ về sản phẩm, bao gồm thông tin về album, ca khúc, v.v.</div>
                  </div>
                                
                                <div class="form-buttons">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Thêm sản phẩm
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Đặt lại
                                    </button>
                  </div>
                            </form>
                  </div>
                </div>
              </div>
            </div>
            
            <?php 
            if (file_exists(__DIR__.'/../../dashboard/footer.php')) {
                include __DIR__.'/../../dashboard/footer.php'; 
            }
            ?>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/WEB_MXH/admin/pages/dashboard/js/main.js"></script>
    
    <script>
        // Validate form trước khi submit
        $('#addProductForm').submit(function(e) {
            const productName = $('#product_name').val().trim();
            const description = $('#description').val().trim();
            const price = parseFloat($('#price').val());
            const stock = parseInt($('#stock').val());
            const imageUrl = $('#image_url').val().trim();
            
            if (!productName) {
                alert('Vui lòng nhập tên sản phẩm');
                e.preventDefault();
                return false;
            }
            
            if (productName.length > 255) {
                alert('Tên sản phẩm không được vượt quá 255 ký tự');
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
            
            if (!imageUrl) {
                alert('Vui lòng nhập URL hình ảnh');
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
            const preview = $('#image_preview');
            
            if (url) {
                preview.attr('src', url).show();
                preview.on('error', function() {
                    $(this).hide();
                });
            } else {
                preview.hide();
            }
        });
        
        // Format price input
        $('#price').on('input', function() {
            let value = $(this).val();
            if (value && !isNaN(value)) {
                $(this).val(parseFloat(value).toFixed(2));
            }
      });
    </script>
  </body>
</html>
